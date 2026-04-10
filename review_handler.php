<?php
session_start();
include_once 'db_config.php';

if (!isset($_SESSION['user'])) {
    echo 'error: Please login to submit a review.';
    exit;
}

$email      = $_SESSION['user'];
$esc_email  = mysqli_real_escape_string($con, $email);
$product_id = (int)($_POST['product_id'] ?? 0);
$rating     = (int)($_POST['rating']     ?? 0);
$title      = trim($_POST['title']       ?? '');
$review     = trim($_POST['review']      ?? '');

if ($rating < 1 || $rating > 5) {
    echo 'error: Please select a rating between 1 and 5.';
    exit;
}
if (strlen($title) < 3) {
    echo 'error: Review title must be at least 3 characters.';
    exit;
}
if (strlen($review) < 10) {
    echo 'error: Review must be at least 10 characters.';
    exit;
}

$existing = mysqli_query($con, "SELECT id FROM reviews WHERE product_id=$product_id AND user_email='$esc_email' LIMIT 1");
if ($existing && mysqli_num_rows($existing) > 0) {
    echo 'error: You have already reviewed this product.';
    exit;
}

$uq        = mysqli_query($con, "SELECT fullname FROM registration WHERE email='$esc_email' LIMIT 1");
$user      = $uq ? mysqli_fetch_assoc($uq) : null;
$user_name = $user ? $user['fullname'] : explode('@', $email)[0];
$esc_name  = mysqli_real_escape_string($con, $user_name);
$esc_title = mysqli_real_escape_string($con, $title);
$esc_rev   = mysqli_real_escape_string($con, $review);

$q = "INSERT INTO reviews (product_id, user_email, user_name, rating, title, review, status)
      VALUES ($product_id, '$esc_email', '$esc_name', $rating, '$esc_title', '$esc_rev', 'Approved')";

if (mysqli_query($con, $q)) {
    echo 'success';
} else {
    echo 'error: Failed to submit review.';
}
