<?php
session_start();
include_once 'db_config.php';

if (!isset($_SESSION['user'])) {
    echo 'error: Please login to continue.';
    exit;
}

$email      = mysqli_real_escape_string($con, $_SESSION['user']);
$action     = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);

if ($action == 'add') {
    $ex = mysqli_query($con, "SELECT id FROM wishlist WHERE user_email='$email' AND product_id=$product_id");
    if (!($ex && mysqli_num_rows($ex) > 0)) {
        mysqli_query($con, "INSERT INTO wishlist (user_email, product_id) VALUES ('$email', $product_id)");
    }
    echo 'success';
    exit;

} elseif ($action == 'remove') {
    mysqli_query($con, "DELETE FROM wishlist WHERE user_email='$email' AND product_id=$product_id");
    echo 'success';
    exit;

} else {
    echo 'error: Unknown action.';
    exit;
}
