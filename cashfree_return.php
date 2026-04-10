<?php

/**
 * cashfree_return.php
 * Cashfree redirects here after payment attempt.
 * Verifies payment status via Cashfree API, then finalises the order.
 */
session_start();
include_once 'db_config.php';
include_once 'payment_config.php';
include_once 'order_helper.php';

$db_order_id = (int)($_GET['db_order_id'] ?? 0);
$cf_order_id = trim($_GET['cf_order_id']  ?? '');

if (!$db_order_id || !$cf_order_id || !isset($_SESSION['user'])) {
    setcookie('error', 'Invalid payment return request.', time() + 5, '/');
    header('Location: cart.php');
    exit;
}

$email     = $_SESSION['user'];
$esc_email = mysqli_real_escape_string($con, $email);

// Verify order belongs to this user
$oq = mysqli_query($con, "SELECT * FROM orders WHERE id=$db_order_id AND user_email='$esc_email' LIMIT 1");
$order = $oq ? mysqli_fetch_assoc($oq) : null;
if (!$order) {
    setcookie('error', 'Order not found for this payment.', time() + 5, '/');
    header('Location: my_orders.php');
    exit;
}

// Check Cashfree payment status
$ch = curl_init(CF_API_BASE . '/orders/' . $cf_order_id . '/payments');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'x-client-id: '     . CF_APP_ID,
        'x-client-secret: ' . CF_SECRET_KEY,
        'x-api-version: '   . CF_API_VERSION,
        'Content-Type: application/json',
    ],
]);
$response = curl_exec($ch);
curl_close($ch);

$payments = json_decode($response, true);
$paid = false;
$txn_id = '';
if (is_array($payments)) {
    foreach ($payments as $p) {
        if (isset($p['payment_status']) && $p['payment_status'] === 'SUCCESS') {
            $paid = true;
            $txn_id = $p['cf_payment_id'] ?? $cf_order_id;
            break;
        }
    }
}

if ($paid) {
    // Update order status with transaction ID
    $esc_txn = mysqli_real_escape_string($con, $txn_id);
    mysqli_query($con, "UPDATE orders SET payment_status='Paid', order_status='Processing', payment_txn_id='$esc_txn' WHERE id=$db_order_id");

    if (isset($_SESSION['pay_existing']) && $_SESSION['pay_existing']['db_order_id'] == $db_order_id) {
        mysqli_query($con, "UPDATE orders SET payment_method='cashfree' WHERE id=$db_order_id AND user_email='$esc_email'");
        unset($_SESSION['pay_existing']);
    } else {
        // Complete: decrement stock, clear cart, record coupon
        $offer_id = $_SESSION['cf_pending']['offer_id'] ?? null;
        completeOnlineOrder($con, $email, $db_order_id, $offer_id);
    }
    unset($_SESSION['cf_pending']);

    setcookie('success', 'Payment completed successfully.', time() + 5, '/');
    header('Location: order_success.php?order_id=' . $db_order_id);
    exit;
}

// Payment failed / pending
mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$db_order_id");
setcookie('error', 'Cashfree payment was not completed. Please try again.', time() + 5, '/');
header('Location: checkout.php');
exit;
