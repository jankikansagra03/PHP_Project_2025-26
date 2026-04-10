<?php
include_once 'user_authentication.php';
include_once 'db_config.php';

$email  = mysqli_real_escape_string($con, $_SESSION['user']);
$action = $_POST['action'] ?? '';

if ($action == 'add') {

    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = (int)($_POST['quantity'] ?? 1);
    if ($quantity < 1) $quantity = 1;

    $chk = mysqli_query($con, "SELECT id, stock FROM products WHERE id=$product_id AND status='Active'");
    if (!$chk || mysqli_num_rows($chk) == 0) {
        echo 'error: Product not found or unavailable.';
        exit;
    }
    $prod = mysqli_fetch_assoc($chk);

    if ($prod['stock'] < $quantity) {
        echo 'error: Insufficient stock available.';
        exit;
    }

    $existing = mysqli_query($con, "SELECT id, quantity FROM cart WHERE user_email='$email' AND product_id=$product_id");
    if ($existing && mysqli_num_rows($existing) > 0) {
        $row     = mysqli_fetch_assoc($existing);
        $new_qty = $row['quantity'] + $quantity;
        if ($new_qty > $prod['stock']) {
            echo 'error: Cannot add more than available stock.';
            exit;
        }
        $cart_id = (int)$row['id'];
        mysqli_query($con, "UPDATE cart SET quantity=$new_qty WHERE id=$cart_id");
    } else {
        mysqli_query($con, "INSERT INTO cart (user_email, product_id, quantity) VALUES ('$email', $product_id, $quantity)");
    }
    echo 'success';
    exit;
} elseif ($action == 'remove') {

    $cart_id = (int)($_POST['cart_id'] ?? 0);
    mysqli_query($con, "DELETE FROM cart WHERE id=$cart_id AND user_email='$email'");
    echo 'success';
    exit;
} elseif ($action == 'update_qty') {

    $cart_id  = (int)($_POST['cart_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($quantity < 1) $quantity = 1;

    $cart_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM cart WHERE id=$cart_id AND user_email='$email'"));
    if ($cart_row) {
        $product_id = (int)$cart_row['product_id'];
        $stock_row  = mysqli_fetch_assoc(mysqli_query($con, "SELECT stock FROM products WHERE id=$product_id"));
        if ($stock_row && $quantity > $stock_row['stock']) {
            echo 'error: Cannot exceed available stock (' . $stock_row['stock'] . ').';
            exit;
        }
        mysqli_query($con, "UPDATE cart SET quantity=$quantity WHERE id=$cart_id AND user_email='$email'");
    }
    echo 'success';
    exit;
} elseif ($action == 'clear') {

    mysqli_query($con, "DELETE FROM cart WHERE user_email='$email'");
    echo 'success';
    exit;
} else {
    echo 'error: Unknown action.';
    exit;
}
