<?php
/**
 * review_handler.php
 * AJAX POST — Submit a product review.
 * Returns JSON { success, message }
 */
session_start();
include_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit;
}

$email      = $_SESSION['user'];
$esc_email  = mysqli_real_escape_string($con, $email);
$product_id = (int)($_POST['product_id'] ?? 0);
$rating     = (int)($_POST['rating']     ?? 0);
$title      = trim($_POST['title']       ?? '');
$review     = trim($_POST['review']      ?? '');

// Validate
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']); exit;
}
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5.']); exit;
}
if (mb_strlen($title) < 3) {
    echo json_encode(['success' => false, 'message' => 'Review title must be at least 3 characters.']); exit;
}
if (mb_strlen($review) < 10) {
    echo json_encode(['success' => false, 'message' => 'Review must be at least 10 characters.']); exit;
}

// Check product exists
$pq  = mysqli_query($con, "SELECT id FROM products WHERE id=$product_id AND status='Active' LIMIT 1");
if (!$pq || mysqli_num_rows($pq) === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']); exit;
}

// Check if already reviewed (one review per user per product)
$existing = mysqli_query($con, "SELECT id FROM reviews WHERE product_id=$product_id AND user_email='$esc_email' LIMIT 1");
if ($existing && mysqli_num_rows($existing) > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']); exit;
}

// Get user name from registration table
$uq   = mysqli_query($con, "SELECT fullname FROM registration WHERE email='$esc_email' LIMIT 1");
$user = $uq ? mysqli_fetch_assoc($uq) : null;
$user_name = $user['fullname'] ?? explode('@', $email)[0];
$esc_name  = mysqli_real_escape_string($con, $user_name);
$esc_title = mysqli_real_escape_string($con, $title);
$esc_rev   = mysqli_real_escape_string($con, $review);

// Insert review (status = 'Approved' so it shows immediately; change to 'Pending' to moderate)
$q = "INSERT INTO reviews (product_id, user_email, user_name, rating, title, review, status)
      VALUES ($product_id, '$esc_email', '$esc_name', $rating, '$esc_title', '$esc_rev', 'Approved')";

if (mysqli_query($con, $q)) {
    echo json_encode([
        'success'   => true,
        'message'   => 'Your review has been submitted successfully!',
        'user_name' => $user_name,
        'rating'    => $rating,
        'title'     => htmlspecialchars($title),
        'review'    => htmlspecialchars($review),
        'date'      => date('d M Y'),
        'initial'   => strtoupper(substr($user_name, 0, 1)),
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review. ' . mysqli_error($con)]);
}
