<?php
session_start();
include_once 'db_config.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.', 'redirect' => 'login.php']);
    exit;
}

$email  = mysqli_real_escape_string($con, $_SESSION['user']);
$action = $_POST['action'] ?? '';

switch ($action) {

    // ─── ADD TO CART ─────────────────────────────────────────────────────────
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        // Verify product exists and is active
        $chk = mysqli_query($con, "SELECT id, stock FROM products WHERE id=$product_id AND status='Active'");
        if (!$chk || mysqli_num_rows($chk) === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found or unavailable.']);
            exit;
        }
        $prod = mysqli_fetch_assoc($chk);
        if ($prod['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock available.']);
            exit;
        }

        // Check if already in cart → update quantity
        $existing = mysqli_query($con, "SELECT id, quantity FROM cart WHERE user_email='$email' AND product_id=$product_id");
        if ($existing && mysqli_num_rows($existing) > 0) {
            $row     = mysqli_fetch_assoc($existing);
            $new_qty = $row['quantity'] + $quantity;
            if ($new_qty > $prod['stock']) {
                echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock.']);
                exit;
            }
            $cart_id = (int)$row['id'];
            mysqli_query($con, "UPDATE cart SET quantity=$new_qty WHERE id=$cart_id");
            $msg = 'Cart updated successfully!';
        } else {
            mysqli_query($con, "INSERT INTO cart (user_email, product_id, quantity) VALUES ('$email', $product_id, $quantity)");
            $msg = 'Product added to cart!';
        }

        // Return new cart count
        $cnt_res   = mysqli_query($con, "SELECT SUM(quantity) as total FROM cart WHERE user_email='$email'");
        $cart_count = (int)(mysqli_fetch_assoc($cnt_res)['total'] ?? 0);

        echo json_encode(['success' => true, 'message' => $msg, 'cart_count' => $cart_count]);
        break;

    // ─── REMOVE ITEM ─────────────────────────────────────────────────────────
    case 'remove':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
            exit;
        }
        mysqli_query($con, "DELETE FROM cart WHERE id=$cart_id AND user_email='$email'");

        $cnt_res    = mysqli_query($con, "SELECT SUM(quantity) as total FROM cart WHERE user_email='$email'");
        $cart_count = (int)(mysqli_fetch_assoc($cnt_res)['total'] ?? 0);

        echo json_encode(['success' => true, 'message' => 'Item removed from cart.', 'cart_count' => $cart_count]);
        break;

    // ─── UPDATE QUANTITY ──────────────────────────────────────────────────────
    case 'update_qty':
        $cart_id  = (int)($_POST['cart_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($cart_id <= 0 || $quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit;
        }

        // Fetch product stock
        $cart_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM cart WHERE id=$cart_id AND user_email='$email'"));
        if (!$cart_row) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
            exit;
        }
        $product_id = (int)$cart_row['product_id'];
        $stock_row  = mysqli_fetch_assoc(mysqli_query($con, "SELECT stock, price, final_price FROM products WHERE id=$product_id"));
        if (!$stock_row || $quantity > $stock_row['stock']) {
            echo json_encode(['success' => false, 'message' => 'Cannot exceed available stock (' . ($stock_row['stock'] ?? 0) . ').']);
            exit;
        }

        mysqli_query($con, "UPDATE cart SET quantity=$quantity WHERE id=$cart_id AND user_email='$email'");

        // Recalculate totals
        $total_res  = mysqli_query($con, "
            SELECT SUM(p.final_price * c.quantity) as subtotal, SUM(c.quantity) as total_qty
            FROM cart c JOIN products p ON c.product_id = p.id
            WHERE c.user_email='$email'
        ");
        $totals     = mysqli_fetch_assoc($total_res);
        $item_total = (float)($stock_row['final_price'] ?? $stock_row['price']) * $quantity;

        $cnt_res    = mysqli_query($con, "SELECT SUM(quantity) as total FROM cart WHERE user_email='$email'");
        $cart_count = (int)(mysqli_fetch_assoc($cnt_res)['total'] ?? 0);

        echo json_encode([
            'success'     => true,
            'message'     => 'Quantity updated.',
            'item_total'  => number_format($item_total, 2),
            'subtotal'    => number_format((float)($totals['subtotal'] ?? 0), 2),
            'cart_count'  => $cart_count,
            'total_qty'   => (int)($totals['total_qty'] ?? 0),
        ]);
        break;

    // ─── CLEAR CART ──────────────────────────────────────────────────────────
    case 'clear':
        mysqli_query($con, "DELETE FROM cart WHERE user_email='$email'");
        echo json_encode(['success' => true, 'message' => 'Cart cleared.', 'cart_count' => 0]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
