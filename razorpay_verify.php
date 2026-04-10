<?php

/**
 * razorpay_verify.php
 * AJAX POST — Verifies Razorpay payment signature, then finalises the order.
 * Returns a plain-text status reply.
 */
session_start();
include_once 'db_config.php';
include_once 'payment_config.php';
include_once 'order_helper.php';
include_once 'response_helper.php';

if (!isset($_SESSION['user'])) {
    send_status(false, 'Not authenticated.');
}

$email              = $_SESSION['user'];
$rzp_order_id       = trim($_POST['razorpay_order_id']   ?? '');
$rzp_payment_id     = trim($_POST['razorpay_payment_id'] ?? '');
$rzp_signature      = trim($_POST['razorpay_signature']  ?? '');
$db_order_id        = (int)($_SESSION['rzp_pending']['db_order_id'] ?? 0);
$offer_id           = $_SESSION['rzp_pending']['offer_id'] ?? null;

if (!$rzp_order_id || !$rzp_payment_id || !$rzp_signature || !$db_order_id) {
    send_status(false, 'Missing payment data.');
}

// Verify HMAC-SHA256 signature
$expected = hash_hmac('sha256', $rzp_order_id . '|' . $rzp_payment_id, RPY_KEY_SECRET);

if (!hash_equals($expected, $rzp_signature)) {
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$db_order_id");
    send_status(false, 'Payment signature verification failed.');
}

// Payment verified — finalise order
$esc_email = mysqli_real_escape_string($con, $email);

if (isset($_SESSION['pay_existing']) && $_SESSION['pay_existing']['db_order_id'] == $db_order_id) {
    mysqli_query($con, "UPDATE orders SET payment_status='Paid', order_status='Processing', payment_method='razorpay', payment_txn_id='$rzp_payment_id' WHERE id=$db_order_id AND user_email='$esc_email'");
    unset($_SESSION['pay_existing']);
} else {
    mysqli_query($con, "UPDATE orders SET payment_status='Paid', order_status='Processing', payment_txn_id='$rzp_payment_id' WHERE id=$db_order_id AND user_email='$esc_email'");
    completeOnlineOrder($con, $email, $db_order_id, $offer_id);
}

unset($_SESSION['rzp_pending']);

send_status(true, 'Payment verified successfully!', ['order_id' => $db_order_id]);
