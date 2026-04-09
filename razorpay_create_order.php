<?php
/**
 * razorpay_create_order.php
 * AJAX POST — Creates a pending DB order + Razorpay order.
 * Returns JSON { success, rzp_order_id, amount, currency, key_id, order_id, order_number }
 */
session_start();
include_once 'db_config.php';
include_once 'payment_config.php';
include_once 'order_helper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']); exit;
}

$email       = $_SESSION['user'];
$address_id  = (int)($_POST['address_id']  ?? 0);
$coupon_code = trim($_POST['coupon_code']  ?? '');

// 1. Create pending order in DB
$result = createPendingOrder($con, $email, 'razorpay', $address_id, $coupon_code);
if (!$result['success']) { echo json_encode($result); exit; }

$order_id     = $result['order_id'];
$order_number = $result['order_number'];
$total        = $result['total'];

// 2. Call Razorpay API to create an order
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_USERPWD        => RPY_KEY_ID . ':' . RPY_KEY_SECRET,
    CURLOPT_POSTFIELDS     => json_encode([
        'amount'   => (int)round($total * 100), // Razorpay uses paise
        'currency' => 'INR',
        'receipt'  => 'JK_' . $order_id,
        'notes'    => ['db_order_id' => (string)$order_id],
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$rzp = json_decode($response, true);

if ($http_code !== 200 || empty($rzp['id'])) {
    mysqli_query($con, "UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=$order_id");
    echo json_encode([
        'success' => false,
        'message' => 'Razorpay order creation failed: ' . ($rzp['error']['description'] ?? 'Unknown error'),
    ]); exit;
}

$_SESSION['rzp_pending'] = ['db_order_id' => $order_id, 'offer_id' => $result['offer_id']];

echo json_encode([
    'success'      => true,
    'rzp_order_id' => $rzp['id'],
    'amount'       => $rzp['amount'],        // in paise
    'currency'     => $rzp['currency'],
    'key_id'       => RPY_KEY_ID,
    'order_id'     => $order_id,
    'order_number' => $order_number,
]);
