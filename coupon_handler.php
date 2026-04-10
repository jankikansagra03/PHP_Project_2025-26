<?php
session_start();
include_once 'db_config.php';

if (!isset($_SESSION['user'])) {
    echo 'error: Please login.';
    exit;
}

$action = $_POST['action'] ?? '';

if ($action == 'apply') {

    $code  = mysqli_real_escape_string($con, strtoupper(trim($_POST['coupon_code'] ?? '')));
    $email = mysqli_real_escape_string($con, $_SESSION['user']);

    if (!$code) {
        echo 'error: Please enter a coupon code.';
        exit;
    }

    $result = mysqli_query($con, "SELECT * FROM offers WHERE code='$code' AND status='Active' AND (valid_from IS NULL OR valid_from <= CURDATE()) AND (valid_to IS NULL OR valid_to >= CURDATE())");
    if (!$result || mysqli_num_rows($result) == 0) {
        echo 'error: Invalid or expired coupon code.';
        exit;
    }

    $offer = mysqli_fetch_assoc($result);

    // Check usage limit
    if ($offer['usage_limit'] !== null) {
        $used = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS cnt FROM offer_usage WHERE offer_id=" . $offer['id']));
        if ($used['cnt'] >= $offer['usage_limit']) {
            echo 'error: This coupon has reached its usage limit.';
            exit;
        }
    }

    // Check if user already used it
    $usedByUser = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS cnt FROM offer_usage WHERE offer_id=" . $offer['id'] . " AND user_email='$email'"));
    if ($usedByUser['cnt'] > 0) {
        echo 'error: You have already used this coupon.';
        exit;
    }

    $_SESSION['applied_coupon'] = $offer;

    echo 'success';
    exit;
} elseif ($action == 'remove') {

    unset($_SESSION['applied_coupon']);
    echo 'success';
    exit;
} else {
    echo 'error: Unknown action.';
    exit;
}
