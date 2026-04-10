<?php

/**
 * paypal_create_order.php
 * AJAX POST — Creates a pending DB order + PayPal order.
 * Returns a plain-text status reply.
 */
session_start();
include_once 'db_config.php';
include_once 'payment_config.php';
include_once 'order_helper.php';
include_once 'response_helper.php';

if (!isset($_SESSION['user'])) {
    send_status(false, 'Please login to continue.');
}

$email       = $_SESSION['user'];
$address_id  = (int)($_POST['address_id']  ?? 0);
$coupon_code = trim($_POST['coupon_code']  ?? '');

// 1. Create pending DB order
$result = createPendingOrder($con, $email, 'paypal', $address_id, $coupon_code);
if (!$result['success']) {
    send_status(false, $result['message'] ?? 'Unable to create order.');
}

$db_order_id = $result['order_id'];
$total       = $result['total'];

// 2. Get PayPal access token
$ch = curl_init(PP_API_BASE . '/v1/oauth2/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_USERPWD        => PP_CLIENT_ID . ':' . PP_CLIENT_SECRET,
    CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
    CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Accept-Language: en_US'],
]);
$tok_resp  = json_decode(curl_exec($ch), true);
curl_close($ch);

$access_token = $tok_resp['access_token'] ?? null;
if (!$access_token) {
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$db_order_id");
    send_status(false, 'PayPal authentication failed.');
}

// 3. Create PayPal order
$pp_payload = [
    'intent'        => 'CAPTURE',
    'purchase_units' => [[
        'reference_id' => 'JK_' . $db_order_id,
        'amount'       => ['currency_code' => 'USD', 'value' => number_format($total / 83, 2)], // approx INR→USD
    ]],
    'application_context' => [
        'return_url' => APP_URL . '/paypal_capture.php?db_order_id=' . $db_order_id,
        'cancel_url' => APP_URL . '/checkout.php',
    ],
];

$ch = curl_init(PP_API_BASE . '/v2/checkout/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($pp_payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
    ],
]);
$pp_resp = json_decode(curl_exec($ch), true);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 201 || empty($pp_resp['id'])) {
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$db_order_id");
    send_status(false, 'PayPal order creation failed.');
}

$_SESSION['pp_pending'] = ['db_order_id' => $db_order_id, 'offer_id' => $result['offer_id'], 'access_token' => $access_token];

send_status(true, 'PayPal order created successfully.', [
    'paypal_order_id' => $pp_resp['id'],
    'db_order_id'     => $db_order_id,
]);
