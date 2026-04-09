<?php
/**
 * order_detail_ajax.php
 * GET ?order_id=X 
 * Returns pre-rendered HTML for the order details modal. Very simple for students!
 */
session_start();
include_once 'db_config.php';

if (!isset($_SESSION['user'])) {
    echo '<div class="alert alert-danger">Not authenticated.</div>'; exit;
}

$email     = $_SESSION['user'];
$esc_email = mysqli_real_escape_string($con, $email);
$order_id  = (int)($_GET['order_id'] ?? 0);

if (!$order_id) { echo '<div class="alert alert-danger">Invalid order.</div>'; exit; }

$oq    = mysqli_query($con, "SELECT * FROM orders WHERE id=$order_id AND user_email='$esc_email' LIMIT 1");
$order = $oq ? mysqli_fetch_assoc($oq) : null;
if (!$order) { echo '<div class="alert alert-danger">Order not found.</div>'; exit; }

$items_q = mysqli_query($con, "
    SELECT oi.*, 
           (SELECT COUNT(*) FROM reviews r WHERE r.product_id = oi.product_id AND r.user_email = '$esc_email') as review_count
    FROM order_items oi 
    WHERE oi.order_id=$order_id
");
$items = [];
while ($r = mysqli_fetch_assoc($items_q)) {
    $r['has_review'] = ((int)$r['review_count']) > 0;
    $items[] = $r;
}

// Prepare timeline logic
$steps = ['Pending','Processing','Shipped','Delivered'];
$isCancelled = ($order['order_status'] === 'Cancelled');
$currentStep = array_search($order['order_status'], $steps);
if($currentStep === false) $currentStep = -1;

$statusColors = ['Pending'=>'#92400e','Processing'=>'#1e40af','Shipped'=>'#5b21b6','Delivered'=>'#15803d','Cancelled'=>'#991b1b'];
$statusBgs    = ['Pending'=>'#fef3c7','Processing'=>'#dbeafe','Shipped'=>'#ede9fe','Delivered'=>'#dcfce7','Cancelled'=>'#fee2e2'];

// --- START HTML RENDERING ---
?>
<div id="modalDataContainer" data-order-number="<?= htmlspecialchars($order['order_number']) ?>"></div>

<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <?php if ($isCancelled): ?>
        <span style="background:#fee2e2;color:#991b1b;padding:6px 16px;border-radius:20px;font-weight:700;font-size:.85rem;">
            <i class="fas fa-times-circle me-1"></i>Order Cancelled
        </span>
    <?php else: ?>
        <?php foreach($steps as $i => $step): 
            $done  = $i <= $currentStep;
            $color = $done ? ($statusColors[$step] ?? '#1f7a8c') : '#94a3b8';
            $bg    = $done ? ($statusBgs[$step] ?? '#f8fafc') : '#f1f5f9';
        ?>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="background:<?= $bg ?>;color:<?= $color ?>;padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:700;">
                    <?= $step ?>
                </span>
                <?php if ($i < count($steps) - 1): ?>
                    <i class="fas fa-chevron-right text-muted" style="font-size:.7rem;"></i>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="mb-4">
    <h6 class="fw-bold mb-3">Items Ordered</h6>
    <?php foreach($items as $item): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
            <div style="width:52px;height:52px;background:#f8fafc;border-radius:10px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;">
                <img src="<?= htmlspecialchars($item['product_image'] ?: 'images/placeholder.png') ?>" style="width:44px;height:44px;object-fit:contain;mix-blend-mode:multiply;">
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.88rem;color:#1e293b;"><?= htmlspecialchars($item['product_name']) ?></div>
                <div style="color:#94a3b8;font-size:.78rem;">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></div>
                
                <?php if ($order['order_status'] === 'Delivered' && !$item['has_review']): ?>
                    <div class="mt-2">
                        <a href="product_detail.php?id=<?= $item['product_id'] ?>#reviews" target="_blank" class="btn btn-sm rounded-pill px-3 d-inline-flex align-items-center" style="background:#fffbeb;border:1px solid #fde68a;color:#d97706;font-size:.7rem;font-weight:700;">
                            <i class="fas fa-star me-1" style="color:#f59e0b;"></i>Write a Review
                        </a>
                    </div>
                <?php elseif ($order['order_status'] === 'Delivered' && $item['has_review']): ?>
                    <div class="mt-2">
                        <span class="badge" style="background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;font-size:.65rem;letter-spacing:.5px;">
                            <i class="fas fa-check-circle me-1"></i>Reviewed
                        </span>
                    </div>
                <?php endif; ?>

            </div>
            <div style="font-weight:800;font-size:.9rem;">₹<?= number_format($item['subtotal'], 2) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="mb-4">
    <h6 class="fw-bold mb-2">Delivery Address</h6>
    <div style="background:#f8fafc;border-radius:12px;padding:.85rem 1rem;">
        <p class="fw-bold mb-1" style="color:#1e293b;"><?= htmlspecialchars($order['delivery_name']) ?></p>
        <p class="text-muted small mb-1"><?= htmlspecialchars($order['delivery_address']) ?></p>
        <p class="text-muted small mb-0"><i class="fas fa-phone me-1" style="font-size:.7rem;"></i><?= htmlspecialchars($order['delivery_mobile']) ?></p>
    </div>
</div>

<div>
    <h6 class="fw-bold mb-2">Payment Summary</h6>
    <div style="background:#f8fafc;border-radius:12px;padding:.85rem 1rem;">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Subtotal</span>
            <span class="fw-semibold">₹<?= number_format($order['subtotal'], 2) ?></span>
        </div>
        <?php if ($order['discount'] > 0): ?>
            <div class="d-flex justify-content-between mb-2">
                <span style="color:#16a34a;font-size:.85rem;">Discount</span>
                <span style="color:#16a34a;font-weight:700;">-₹<?= number_format($order['discount'], 2) ?></span>
            </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Shipping</span>
            <span style="color:#16a34a;font-size:.75rem;font-weight:700;">FREE</span>
        </div>
        <hr style="border-color:#e2e8f0;margin:8px 0;">
        <div class="d-flex justify-content-between">
            <span class="fw-bold">Total Paid</span>
            <span style="font-weight:900;font-size:1.2rem;color:var(--theme-primary,#1f7a8c);">₹<?= number_format($order['total_amount'], 2) ?></span>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <span class="text-muted small">Payment Method</span>
            <span class="fw-semibold small"><?= htmlspecialchars(strtoupper($order['payment_method'])) ?></span>
        </div>
    </div>
</div>
