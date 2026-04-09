<?php
session_start();
include_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.', 'redirect' => 'login.php']);
    exit;
}

$email      = mysqli_real_escape_string($con, $_SESSION['user']);
$action     = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

switch ($action) {

    case 'add':
        // Check product exists
        $chk = mysqli_query($con, "SELECT id FROM products WHERE id=$product_id AND status='Active'");
        if (!$chk || mysqli_num_rows($chk) === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }
        // Already in wishlist?
        $ex = mysqli_query($con, "SELECT id FROM wishlist WHERE user_email='$email' AND product_id=$product_id");
        if ($ex && mysqli_num_rows($ex) > 0) {
            echo json_encode(['success' => true, 'message' => 'Already in your wishlist!']);
            exit;
        }
        mysqli_query($con, "INSERT INTO wishlist (user_email, product_id) VALUES ('$email', $product_id)");
        echo json_encode(['success' => true, 'message' => 'Added to wishlist!']);
        break;

    case 'remove':
        mysqli_query($con, "DELETE FROM wishlist WHERE user_email='$email' AND product_id=$product_id");
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
