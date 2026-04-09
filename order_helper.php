<?php
/**
 * order_helper.php
 * Internal helper functions — NOT callable directly from the browser.
 *
 * createPendingOrder()  — Creates a DB order with payment_status='Pending'.
 *                         Does NOT clear cart or decrement stock yet.
 * completeOnlineOrder() — Finalises after payment verified: decrements stock,
 *                         clears cart, records coupon, unsets session.
 */

/**
 * @param  mysqli $con           DB connection
 * @param  string $email         Logged-in user email
 * @param  string $payment_method e.g. 'cashfree','razorpay','paypal','cod'
 * @param  int    $address_id
 * @param  string $coupon_code   Applied coupon code (or '')
 * @return array  ['success'=>bool, ...]
 */
function createPendingOrder($con, $email, $payment_method, $address_id, $coupon_code = '') {
    $esc_email  = mysqli_real_escape_string($con, $email);
    $address_id = (int)$address_id;

    // ── 1. Validate address ──────────────────────────────
    if (!$address_id) return ['success' => false, 'message' => 'No delivery address selected.'];
    $addr_q = mysqli_query($con, "SELECT * FROM addresses WHERE id=$address_id AND email='$esc_email' LIMIT 1");
    $addr   = $addr_q ? mysqli_fetch_assoc($addr_q) : null;
    if (!$addr) return ['success' => false, 'message' => 'Invalid delivery address.'];

    // ── 2. Fetch cart ────────────────────────────────────
    $cart_q = mysqli_query($con, "
        SELECT c.quantity,
               p.id as product_id, p.name, p.image, p.final_price, p.discount, p.stock
        FROM cart c JOIN products p ON c.product_id = p.id
        WHERE c.user_email = '$esc_email'
    ");
    $cart_items = []; $cart_subtotal = 0.0;
    while ($row = mysqli_fetch_assoc($cart_q)) {
        if ($row['stock'] < $row['quantity']) {
            return ['success' => false, 'message' => '"' . $row['name'] . '" is out of stock.'];
        }
        $row['line_total'] = (float)$row['final_price'] * (int)$row['quantity'];
        $cart_subtotal    += $row['line_total'];
        $cart_items[]      = $row;
    }
    if (empty($cart_items)) return ['success' => false, 'message' => 'Your cart is empty.'];

    // ── 3. Coupon ────────────────────────────────────────
    $coupon_code     = strtoupper(trim($coupon_code));
    $discount_amount = 0.0; $offer_id = null;
    $coupon          = $_SESSION['applied_coupon'] ?? null;
    if ($coupon && $coupon['code'] === $coupon_code) {
        $offer_id = (int)($coupon['offer_id'] ?? 0);
        if ($coupon['discount_type'] === 'percent') {
            $discount_amount = $cart_subtotal * ((float)$coupon['discount_value'] / 100);
            if (!empty($coupon['max_discount_amount']))
                $discount_amount = min($discount_amount, (float)$coupon['max_discount_amount']);
        } else {
            $discount_amount = (float)$coupon['discount_amount'];
        }
        $discount_amount = min(round($discount_amount, 2), $cart_subtotal);
    }

    $total            = round($cart_subtotal - $discount_amount, 2);
    $order_number     = 'JK' . strtoupper(uniqid());
    $delivery_address = $addr['address'] . ', ' . ($addr['city'] ?? '') . ', '
                      . ($addr['state'] ?? '') . ($addr['zip'] ? ' - ' . $addr['zip'] : '');
    $payment_method   = mysqli_real_escape_string($con, $payment_method);

    // ── 4. Create DB order ───────────────────────────────
    mysqli_begin_transaction($con);
    try {
        $ins = mysqli_prepare($con, "
            INSERT INTO orders
                (order_number, user_email, delivery_name, delivery_email, delivery_mobile,
                 delivery_address, subtotal, discount, shipping_fee, total_amount,
                 payment_method, payment_status, order_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, 'Pending', 'Pending')
        ");
        mysqli_stmt_bind_param($ins, 'ssssssddds',
            $order_number, $email,
            $addr['name'], $email, $addr['phone'], $delivery_address,
            $cart_subtotal, $discount_amount, $total, $payment_method
        );
        if (!mysqli_stmt_execute($ins))
            throw new Exception('Failed to create order: ' . mysqli_stmt_error($ins));
        $order_id = mysqli_insert_id($con);
        mysqli_stmt_close($ins);

        // Insert order items (stock NOT decremented yet for online payments)
        foreach ($cart_items as $ci) {
            $pid    = (int)$ci['product_id'];
            $qty    = (int)$ci['quantity'];
            $uprice = (float)$ci['final_price'];
            $disc   = (float)$ci['discount'];
            $sub    = round($uprice * $qty, 2);
            $pname  = mysqli_real_escape_string($con, $ci['name']);
            $pimage = mysqli_real_escape_string($con, $ci['image'] ?? '');
            $ii = mysqli_prepare($con, "
                INSERT INTO order_items
                    (order_id, product_id, product_name, product_image, price, discount, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param($ii, 'iissddid', $order_id, $pid, $pname, $pimage, $uprice, $disc, $qty, $sub);
            if (!mysqli_stmt_execute($ii)) throw new Exception('Failed to insert order item.');
            mysqli_stmt_close($ii);
        }

        mysqli_commit($con);
        return [
            'success'         => true,
            'order_id'        => $order_id,
            'order_number'    => $order_number,
            'total'           => $total,
            'cart_subtotal'   => $cart_subtotal,
            'discount_amount' => $discount_amount,
            'offer_id'        => $offer_id,
            'addr'            => $addr,
        ];
    } catch (Exception $e) {
        mysqli_rollback($con);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Called after online payment is confirmed.
 * Decrements stock, clears cart, records coupon usage.
 *
 * @param  mysqli $con
 * @param  string $email
 * @param  int    $order_id
 * @param  int|null $offer_id  Offer ID if a coupon was applied (optional, uses session fallback)
 */
function completeOnlineOrder($con, $email, $order_id, $offer_id = null) {
    $esc_email = mysqli_real_escape_string($con, $email);
    $order_id  = (int)$order_id;

    // Decrement stock
    $items_q = mysqli_query($con, "SELECT product_id, quantity FROM order_items WHERE order_id=$order_id");
    while ($item = mysqli_fetch_assoc($items_q)) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['quantity'];
        mysqli_query($con, "UPDATE products SET stock = stock - $qty WHERE id=$pid AND stock >= $qty");
    }

    // Clear cart
    mysqli_query($con, "DELETE FROM cart WHERE user_email='$esc_email'");

    // Coupon usage
    $coupon  = $_SESSION['applied_coupon'] ?? null;
    $oid     = $offer_id ?? (int)($coupon['offer_id'] ?? 0);
    if ($oid) {
        $u = mysqli_prepare($con, "INSERT INTO offer_usage (offer_id, user_email, order_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($u, 'isi', $oid, $email, $order_id);
        mysqli_stmt_execute($u);
        mysqli_stmt_close($u);
        mysqli_query($con, "UPDATE offers SET times_used = times_used + 1 WHERE id=$oid");
    }
    unset($_SESSION['applied_coupon']);
}
