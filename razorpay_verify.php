<?php
/**
 * razorpay_verify.php
 * AJAX POST — Verifies Razorpay payment signature, then finalises the order.
 * Returns JSON { success, message, order_id }
 */
session_start();
include_once 'db_config.php';
include_once 'payment_config.php';
include_once 'order_helper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']); exit;
}

$email              = $_SESSION['user'];
$rzp_order_id       = trim($_POST['razorpay_order_id']   ?? '');
$rzp_payment_id     = trim($_POST['razorpay_payment_id'] ?? '');
$rzp_signature      = trim($_POST['razorpay_signature']  ?? '');
$db_order_id        = (int)($_SESSION['rzp_pending']['db_order_id'] ?? 0);
$offer_id           = $_SESSION['rzp_pending']['offer_id'] ?? null;

if (!$rzp_order_id || !$rzp_payment_id || !$rzp_signature || !$db_order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing payment data.']); exit;
}

// Verify HMAC-SHA256 signature
$expected = hash_hmac('sha256', $rzp_order_id . '|' . $rzp_payment_id, RPY_KEY_SECRET);

if (!hash_equals($expected, $rzp_signature)) {
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$db_order_id");
    echo json_encode(['success' => false, 'message' => 'Payment signature verification failed.']); exit;
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

echo json_encode([
    'success'  => true,
    'message'  => 'Payment verified successfully!',
    'order_id' => $db_order_id,
]);
