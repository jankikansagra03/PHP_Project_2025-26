<?php
/**
 * coupon_handler.php
 * AJAX endpoint — validate & apply a coupon code on a cart
 * POST: action=validate, code=COUPONCODE, cart_subtotal=1234.00
 * POST: action=remove
 */
session_start();
include_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
    exit;
}

$email  = mysqli_real_escape_string($con, $_SESSION['user']);
$action = $_POST['action'] ?? 'validate';

// ── Remove coupon ────────────────────────────────────────
if ($action === 'remove') {
    unset($_SESSION['applied_coupon']);
    echo json_encode(['success' => true, 'message' => 'Coupon removed.']);
    exit;
}

// ── Validate & Apply coupon ──────────────────────────────
$code         = strtoupper(trim($_POST['code'] ?? ''));
$cart_subtotal = (float)($_POST['cart_subtotal'] ?? 0);
$category_id   = (int)($_POST['category_id'] ?? 0); // pass 0 for site-wide

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
    exit;
}

// Fetch offer
$esc_code = mysqli_real_escape_string($con, $code);
$now      = date('Y-m-d H:i:s');
$offer_q  = mysqli_query($con, "SELECT * FROM offers WHERE code='$esc_code' AND status='Active' LIMIT 1");

if (!$offer_q || mysqli_num_rows($offer_q) === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon code. Please check and try again.']);
    exit;
}
$offer = mysqli_fetch_assoc($offer_q);

// ── Validity window check ────────────────────────────────
if (!empty($offer['valid_from']) && $now < $offer['valid_from']) {
    echo json_encode(['success' => false, 'message' => 'This offer has not started yet.']);
    exit;
}
if (!empty($offer['valid_to']) && $now > $offer['valid_to']) {
    echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
    exit;
}

// ── Minimum order check ──────────────────────────────────
if (!empty($offer['min_order_amount']) && $cart_subtotal < (float)$offer['min_order_amount']) {
    echo json_encode([
        'success' => false,
        'message' => 'Minimum order amount of ₹' . number_format((float)$offer['min_order_amount'], 2) . ' required for this coupon.'
    ]);
    exit;
}

// ── Total usage limit check ──────────────────────────────
if (!empty($offer['usage_limit'])) {
    $used_count_q = mysqli_query($con, "SELECT COUNT(*) as cnt FROM offer_usage WHERE offer_id=" . (int)$offer['id']);
    $used_count   = (int)(mysqli_fetch_assoc($used_count_q)['cnt'] ?? 0);
    if ($used_count >= (int)$offer['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'This coupon has reached its maximum usage limit.']);
        exit;
    }
}

// ── Per-user usage limit check ───────────────────────────
if (!empty($offer['per_user_limit'])) {
    $user_used_q = mysqli_query($con, "SELECT COUNT(*) as cnt FROM offer_usage WHERE offer_id=" . (int)$offer['id'] . " AND user_email='$email'");
    $user_used   = (int)(mysqli_fetch_assoc($user_used_q)['cnt'] ?? 0);
    if ($user_used >= (int)$offer['per_user_limit']) {
        echo json_encode(['success' => false, 'message' => 'You have already used this coupon the maximum number of times.']);
        exit;
    }
}

// ── Category restriction check ───────────────────────────
if ($offer['applies_to'] === 'category' && !empty($offer['category_id'])) {
    // Check if any cart item belongs to the required category
    $offer_cat_id = (int)$offer['category_id'];
    $cat_check = mysqli_query($con, "
        SELECT COUNT(*) as cnt 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_email='$email' AND p.category_id=$offer_cat_id
    ");
    $has_cat_item = (int)(mysqli_fetch_assoc($cat_check)['cnt'] ?? 0);
    if ($has_cat_item === 0) {
        // Get category name for message
        $cat_name_q = mysqli_query($con, "SELECT category_name FROM categories WHERE id=$offer_cat_id LIMIT 1");
        $cat_name   = $cat_name_q ? (mysqli_fetch_assoc($cat_name_q)['category_name'] ?? 'the required category') : 'the required category';
        echo json_encode(['success' => false, 'message' => "This coupon only applies to '$cat_name' products. Add eligible items to your cart."]);
        exit;
    }
    // Compute subtotal only for eligible category items
    $cat_total_q = mysqli_query($con, "
        SELECT SUM(p.final_price * c.quantity) as total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_email='$email' AND p.category_id=$offer_cat_id
    ");
    $eligible_subtotal = (float)(mysqli_fetch_assoc($cat_total_q)['total'] ?? 0);
} else {
    $eligible_subtotal = $cart_subtotal;
}

// ── Calculate discount amount ────────────────────────────
if ($offer['discount_type'] === 'percent') {
    $discount_amount = $eligible_subtotal * ((float)$offer['discount_value'] / 100);
    if (!empty($offer['max_discount_amount'])) {
        $discount_amount = min($discount_amount, (float)$offer['max_discount_amount']);
    }
} else {
    // fixed
    $discount_amount = (float)$offer['discount_value'];
}
$discount_amount = min($discount_amount, $cart_subtotal); // cannot exceed cart total
$discount_amount = round($discount_amount, 2);
$final_total     = round($cart_subtotal - $discount_amount, 2);

// ── Store in session ─────────────────────────────────────
$_SESSION['applied_coupon'] = [
    'offer_id'            => (int)$offer['id'],
    'code'                => $offer['code'],
    'discount_type'       => $offer['discount_type'],
    'discount_value'      => $offer['discount_value'],
    'discount_amount'     => $discount_amount,
    'max_discount_amount' => $offer['max_discount_amount'],
    'applies_to'          => $offer['applies_to'],
    'category_id'         => $offer['category_id'],
    'description'         => $offer['description'],
];

echo json_encode([
    'success'         => true,
    'message'         => '🎉 Coupon applied successfully!',
    'code'            => $offer['code'],
    'discount_label'  => $offer['discount_type'] === 'percent'
                            ? $offer['discount_value'] . '% off'
                            : '₹' . number_format((float)$offer['discount_value'], 2) . ' off',
    'discount_amount' => number_format($discount_amount, 2),
    'final_total'     => number_format($final_total, 2),
    'description'     => $offer['description'],
]);
