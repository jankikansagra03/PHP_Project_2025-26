<?php
/**
 * init_existing_payment.php
 * Initializes payment gateway for an EXISTING database order.
 * Expects POST: order_id, payment_method
 */
session_start();
include_once 'db_config.php';
include_once 'payment_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']); exit;
}

$email      = $_SESSION['user'];
$esc_email  = mysqli_real_escape_string($con, $email);
$order_id   = (int)($_POST['order_id'] ?? 0);
$method     = trim($_POST['payment_method'] ?? '');

$oq = mysqli_query($con, "SELECT * FROM orders WHERE id=$order_id AND user_email='$esc_email' AND payment_status != 'Paid' LIMIT 1");
$order = mysqli_fetch_assoc($oq);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Invalid or already paid order.']); exit;
}

$total        = (float)$order['total_amount'];
$order_number = $order['order_number'];

// Set flag so verification scripts know this is an existing order payment (skip stock deduction)
$_SESSION['pay_existing'] = [
    'db_order_id' => $order_id,
    'method'      => $method
];

if ($method === 'razorpay') {
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => RPY_KEY_ID . ':' . RPY_KEY_SECRET,
        CURLOPT_POSTFIELDS     => json_encode([
            'amount'   => (int)round($total * 100),
            'currency' => 'INR',
            'receipt'  => 'JK_' . $order_id,
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $rzp = json_decode($response, true);
    if ($http_code !== 200 || empty($rzp['id'])) {
        echo json_encode(['success' => false, 'message' => 'Razorpay error: ' . ($rzp['error']['description'] ?? '')]); exit;
    }
    
    $_SESSION['rzp_pending'] = ['db_order_id' => $order_id, 'offer_id' => null];
    
    echo json_encode([
        'success'      => true,
        'rzp_order_id' => $rzp['id'],
        'amount'       => $rzp['amount'],
        'currency'     => $rzp['currency'],
        'key_id'       => RPY_KEY_ID,
        'order_id'     => $order_id,
        'order_number' => $order_number,
    ]);

} elseif ($method === 'cashfree') {
    $ch = curl_init(CF_API_BASE . '/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'order_id'       => 'CF_' . uniqid() . '_' . $order_id,
            'order_amount'   => round($total, 2),
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id'    => 'CUST_' . preg_replace('/[^a-zA-Z0-9]/', '', $email),
                'customer_email' => $email,
                'customer_phone' => preg_replace('/[^0-9]/', '', $order['delivery_mobile'] ?? '9999999999')
            ],
            'order_meta' => [
                'return_url' => APP_URL . '/cashfree_return.php?cf_order_id={order_id}&db_order_id=' . $order_id
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            'x-client-id: '     . CF_APP_ID,
            'x-client-secret: ' . CF_SECRET_KEY,
            'x-api-version: '   . CF_API_VERSION,
            'Content-Type: application/json'
        ]
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $cf = json_decode($response, true);
    if ($http_code !== 200 || empty($cf['payment_session_id'])) {
        echo json_encode(['success' => false, 'message' => 'Cashfree error: ' . ($cf['message'] ?? 'Unknown')]); exit;
    }

    $_SESSION['cf_pending'] = ['db_order_id' => $order_id, 'offer_id' => null];
    
    echo json_encode([
        'success'            => true,
        'payment_session_id' => $cf['payment_session_id'],
        'cf_order_id'        => $cf['order_id'] ?? ''
    ]);

} elseif ($method === 'paypal') {
    // 1. Get access token
    $ch = curl_init(PP_API_BASE . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => PP_CLIENT_ID . ':' . PP_CLIENT_SECRET,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
    ]);
    $tok = json_decode(curl_exec($ch), true);
    curl_close($ch);
    if (empty($tok['access_token'])) {
        echo json_encode(['success' => false, 'message' => 'PayPal auth failed.']); exit;
    }
    
    // 2. Create Order
    $ch = curl_init(PP_API_BASE . '/v2/checkout/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'JK_' . $order_id,
                'amount' => ['currency_code' => 'INR', 'value' => number_format($total, 2, '.', '')]
            ]],
            'application_context' => ['return_url' => APP_URL, 'cancel_url' => APP_URL]
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $tok['access_token']
        ],
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $pp = json_decode($response, true);
    if ($http_code !== 201 || empty($pp['id'])) {
        echo json_encode(['success' => false, 'message' => 'PayPal order creation failed.']); exit;
    }

    $_SESSION['pp_pending'] = [
        'db_order_id'  => $order_id,
        'offer_id'     => null,
        'access_token' => $tok['access_token']
    ];

    echo json_encode(['success' => true, 'paypal_order_id' => $pp['id']]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid payment method.']);
}
