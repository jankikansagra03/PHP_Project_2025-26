<?php
/**
 * paypal_capture.php
 * AJAX POST — Captures approved PayPal payment & finalises the order.
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

$email          = $_SESSION['user'];
$paypal_order_id = trim($_POST['paypal_order_id'] ?? '');
$db_order_id    = (int)($_SESSION['pp_pending']['db_order_id'] ?? ($_GET['db_order_id'] ?? 0));
$offer_id       = $_SESSION['pp_pending']['offer_id'] ?? null;
$access_token   = $_SESSION['pp_pending']['access_token'] ?? null;

if (!$paypal_order_id || !$db_order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing payment data.']); exit;
}

// Refresh access token if needed (session may have expired)
if (!$access_token) {
    $ch = curl_init(PP_API_BASE . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => PP_CLIENT_ID . ':' . PP_CLIENT_SECRET,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $tok = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $access_token = $tok['access_token'] ?? null;
}

if (!$access_token) {
    echo json_encode(['success' => false, 'message' => 'PayPal authentication failed.']); exit;
}

// Capture the payment
$ch = curl_init(PP_API_BASE . '/v2/checkout/orders/' . $paypal_order_id . '/capture');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => '{}',
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
    ],
]);
$pp_resp   = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pp = json_decode($pp_resp, true);

if ($http_code !== 201 || ($pp['status'] ?? '') !== 'COMPLETED') {
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$db_order_id");
    echo json_encode(['success' => false, 'message' => 'PayPal capture failed. Status: ' . ($pp['status'] ?? 'Unknown')]); exit;
}

// Payment captured — finalise
$esc_email  = mysqli_real_escape_string($con, $email);
$capture_id = $pp['purchase_units'][0]['payments']['captures'][0]['id'] ?? $pp['id'] ?? $paypal_order_id;
$esc_cap    = mysqli_real_escape_string($con, $capture_id);
mysqli_query($con, "UPDATE orders SET payment_status='Paid', order_status='Processing', payment_txn_id='$esc_cap' WHERE id=$db_order_id AND user_email='$esc_email'");

if (isset($_SESSION['pay_existing']) && $_SESSION['pay_existing']['db_order_id'] == $db_order_id) {
    mysqli_query($con, "UPDATE orders SET payment_method='paypal' WHERE id=$db_order_id AND user_email='$esc_email'");
    unset($_SESSION['pay_existing']);
} else {
    completeOnlineOrder($con, $email, $db_order_id, $offer_id);
}

unset($_SESSION['pp_pending']);

echo json_encode([
    'success'  => true,
    'message'  => 'PayPal payment captured successfully!',
    'order_id' => $db_order_id,
]);
