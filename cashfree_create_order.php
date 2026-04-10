<?php

/**
 * cashfree_create_order.php
 * AJAX POST — Creates a pending DB order + Cashfree order.
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

// 1. Create pending order in DB
$result = createPendingOrder($con, $email, 'cashfree', $address_id, $coupon_code);
if (!$result['success']) {
    send_status(false, $result['message'] ?? 'Unable to create order.');
}

$order_id     = $result['order_id'];
$order_number = $result['order_number'];
$total        = $result['total'];
$addr         = $result['addr'];

// 2. Call Cashfree API to create order
$cf_order_id = 'CF_' . $order_id . '_' . time();
$payload = [
    'order_id'         => $cf_order_id,
    'order_amount'     => round($total, 2),
    'order_currency'   => 'INR',
    'customer_details' => [
        'customer_id'    => 'CUST_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $email),
        'customer_email' => $email,
        'customer_phone' => preg_replace('/[^0-9]/', '', $addr['phone']) ?: '9999999999',
        'customer_name'  => $addr['name'],
    ],
    'order_meta' => [
        'return_url' => APP_URL . '/cashfree_return.php?db_order_id=' . $order_id . '&cf_order_id={order_id}',
        'notify_url' => APP_URL . '/cashfree_return.php',
    ],
];

$ch = curl_init(CF_API_BASE . '/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-client-id: '      . CF_APP_ID,
        'x-client-secret: '  . CF_SECRET_KEY,
        'x-api-version: '    . CF_API_VERSION,
    ],
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$cf = json_decode($response, true);

if ($http_code !== 200 || empty($cf['payment_session_id'])) {
    // Mark the pending order as failed
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$order_id");
    send_status(false, 'Cashfree order creation failed: ' . ($cf['message'] ?? 'Unknown error'));
}

// Store mapping for verification
$_SESSION['cf_pending'] = ['db_order_id' => $order_id, 'offer_id' => $result['offer_id']];

send_status(true, 'Cashfree order created successfully.', [
    'payment_session_id'  => $cf['payment_session_id'],
    'cf_order_id'         => $cf['cf_order_id'],
    'order_id'            => $order_id,
]);
