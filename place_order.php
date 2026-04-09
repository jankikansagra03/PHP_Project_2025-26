<?php
/**
 * place_order.php
 * AJAX POST — creates an order, clears cart, records coupon usage
 * Returns JSON {success, order_id, message}
 */
session_start();
include_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
    exit;
}

$email     = $_SESSION['user'];
$esc_email = mysqli_real_escape_string($con, $email);

$address_id     = (int)($_POST['address_id']     ?? 0);
$payment_method = in_array($_POST['payment_method'] ?? 'cod', ['cod','razorpay']) ? $_POST['payment_method'] : 'cod';
$coupon_code    = strtoupper(trim($_POST['coupon_code'] ?? ''));

// ── 1. Validate address ───────────────────────────────────
if (!$address_id) {
    echo json_encode(['success' => false, 'message' => 'No delivery address selected.']);
    exit;
}
$addr_q = mysqli_query($con, "SELECT * FROM addresses WHERE id=$address_id AND email='$esc_email' LIMIT 1");
$addr   = $addr_q ? mysqli_fetch_assoc($addr_q) : null;
if (!$addr) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery address.']);
    exit;
}

// ── 2. Fetch cart items ───────────────────────────────────
$cart_q = mysqli_query($con, "
    SELECT c.id as cart_id, c.quantity,
           p.id as product_id, p.name, p.image, p.final_price, p.price, p.discount, p.stock
    FROM cart c JOIN products p ON c.product_id=p.id
    WHERE c.user_email='$esc_email'
");
$cart_items    = [];
$cart_subtotal = 0.0;
while ($row = mysqli_fetch_assoc($cart_q)) {
    if ($row['stock'] < $row['quantity']) {
        echo json_encode(['success' => false, 'message' => '"' . $row['name'] . '" is out of stock. Please update your cart.']);
        exit;
    }
    $row['line_total'] = (float)$row['final_price'] * (int)$row['quantity'];
    $cart_subtotal    += $row['line_total'];
    $cart_items[]      = $row;
}
if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

// ── 3. Calculate discount ─────────────────────────────────
$discount_amount = 0.0;
$offer_id        = null;
$coupon          = $_SESSION['applied_coupon'] ?? null;

if ($coupon && $coupon['code'] === $coupon_code) {
    $offer_id = (int)$coupon['offer_id'];
    if ($coupon['discount_type'] === 'percent') {
        $discount_amount = $cart_subtotal * ((float)$coupon['discount_value'] / 100);
        if (!empty($coupon['max_discount_amount'])) {
            $discount_amount = min($discount_amount, (float)$coupon['max_discount_amount']);
        }
    } else {
        $discount_amount = (float)$coupon['discount_amount'];
    }
    $discount_amount = min(round($discount_amount, 2), $cart_subtotal);
}

$shipping_fee = 0.00;
$total        = round($cart_subtotal - $discount_amount + $shipping_fee, 2);

// ── 4. Generate unique order number ──────────────────────
$order_number = 'JK' . strtoupper(uniqid());

// ── 5. Build delivery address string ─────────────────────
$delivery_address = $addr['address'] . ', ' . ($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ($addr['zip'] ? ' - ' . $addr['zip'] : '');

// ── 6. Insert order ───────────────────────────────────────
mysqli_begin_transaction($con);
try {
    $ins = mysqli_prepare($con, "
        INSERT INTO orders
            (order_number, user_email, delivery_name, delivery_email, delivery_mobile, delivery_address,
             subtotal, discount, shipping_fee, total_amount, payment_method, payment_status, order_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending')
    ");
    $pay_status = 'Pending';
    $delivery_email  = $esc_email;
    mysqli_stmt_bind_param($ins, 'ssssssdddds',
        $order_number, $email,
        $addr['name'], $delivery_email, $addr['phone'], $delivery_address,
        $cart_subtotal, $discount_amount, $shipping_fee, $total,
        $payment_method
    );
    if (!mysqli_stmt_execute($ins)) {
        throw new Exception('Failed to create order: ' . mysqli_stmt_error($ins));
    }
    $order_id = mysqli_insert_id($con);
    mysqli_stmt_close($ins);

    // ── 7. Insert order items + update stock ──────────────
    foreach ($cart_items as $ci) {
        $pid     = (int)$ci['product_id'];
        $qty     = (int)$ci['quantity'];
        $uprice  = (float)$ci['final_price'];
        $disc    = (float)$ci['discount'];
        $sub     = round($uprice * $qty, 2);
        $pname   = mysqli_real_escape_string($con, $ci['name']);
        $pimage  = mysqli_real_escape_string($con, $ci['image'] ?? '');

        $item_ins = mysqli_prepare($con,
            "INSERT INTO order_items (order_id, product_id, product_name, product_image, price, discount, quantity, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($item_ins, 'iissddid', $order_id, $pid, $pname, $pimage, $uprice, $disc, $qty, $sub);
        if (!mysqli_stmt_execute($item_ins)) {
            throw new Exception('Failed to insert order item.');
        }
        mysqli_stmt_close($item_ins);

        // Decrement stock
        mysqli_query($con, "UPDATE products SET stock = stock - $qty WHERE id = $pid AND stock >= $qty");
    }

    // ── 8. Record coupon usage ────────────────────────────
    if ($offer_id) {
        $usage_ins = mysqli_prepare($con, "INSERT INTO offer_usage (offer_id, user_email, order_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($usage_ins, 'isi', $offer_id, $email, $order_id);
        mysqli_stmt_execute($usage_ins);
        mysqli_stmt_close($usage_ins);
        // Increment times_used
        mysqli_query($con, "UPDATE offers SET times_used = times_used + 1 WHERE id = $offer_id");
    }

    // ── 9. Clear user's cart ──────────────────────────────
    mysqli_query($con, "DELETE FROM cart WHERE user_email='$esc_email'");

    // ── 10. Clear coupon session ──────────────────────────
    unset($_SESSION['applied_coupon']);

    mysqli_commit($con);

    echo json_encode([
        'success'      => true,
        'order_id'     => $order_id,
        'order_number' => $order_number,
        'message'      => 'Order placed successfully!'
    ]);

} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
