<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title          = "My Orders - JK Store";
$active_sidebar = 'orders';

$esc_email  = mysqli_real_escape_string($con, $email);

// Status filter
$filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$allowed_filters = ['all', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
if (!in_array($filter, $allowed_filters)) $filter = 'all';

$where = "user_email='$esc_email'" . ($filter !== 'all' ? " AND order_status='" . mysqli_real_escape_string($con, $filter) . "'" : '');
$orders_q = mysqli_query($con, "SELECT * FROM orders WHERE $where ORDER BY id DESC");
$orders   = [];
while ($r = mysqli_fetch_assoc($orders_q)) $orders[] = $r;

// Status badge styles
$status_styles = [
    'Pending'    => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'fa-clock'],
    'Processing' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'fa-cog'],
    'Shipped'    => ['bg' => '#ede9fe', 'color' => '#5b21b6', 'icon' => 'fa-shipping-fast'],
    'Delivered'  => ['bg' => '#dcfce7', 'color' => '#15803d', 'icon' => 'fa-check-circle'],
    'Cancelled'  => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => 'fa-times-circle'],
];
$pay_styles = [
    'Paid'    => ['bg' => '#dcfce7', 'color' => '#15803d'],
    'Pending' => ['bg' => '#fef3c7', 'color' => '#92400e'],
    'Failed'  => ['bg' => '#fee2e2', 'color' => '#991b1b'],
    'Refunded'=> ['bg' => '#e0f2fe', 'color' => '#0369a1'],
];

function statusStyle($map, $key, $prop) {
    return $map[$key][$prop] ?? ($prop === 'bg' ? '#f3f4f6' : '#374151');
}

ob_start();
?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">

        <!-- Header + Filter Tabs -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-0 heading-primary">My Orders</h4>
                <p class="text-muted small mb-0 mt-1"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?> found</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?php
                $tabs = ['all' => 'All', 'Pending' => 'Pending', 'Processing' => 'Processing',
                         'Shipped' => 'Shipped', 'Delivered' => 'Delivered', 'Cancelled' => 'Cancelled'];
                foreach ($tabs as $val => $label):
                    $active = $filter === $val ? 'btn-gradient' : 'btn-outline-secondary';
                ?>
                <a href="?status=<?= $val ?>" class="btn btn-sm <?= $active ?> rounded-pill px-3"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($orders)): ?>
        <div style="text-align:center;padding:3rem 1rem;background:#f8fafc;border-radius:16px;border:2px dashed #e2e8f0;">
            <div style="font-size:3.5rem;margin-bottom:1rem;">📦</div>
            <h5 style="font-weight:700;color:#374151;">No orders found</h5>
            <p style="color:#94a3b8;font-size:.9rem;"><?= $filter === 'all' ? "You haven't placed any orders yet." : "No '$filter' orders." ?></p>
            <a href="shop.php" class="btn btn-gradient rounded-pill px-4 mt-2">
                <i class="fas fa-store me-2"></i>Start Shopping
            </a>
        </div>

        <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($orders as $order):
                $os  = statusStyle($status_styles, $order['order_status'], 'bg');
                $oc  = statusStyle($status_styles, $order['order_status'], 'color');
                $oic = $status_styles[$order['order_status']]['icon'] ?? 'fa-circle';
                $ps  = statusStyle($pay_styles, $order['payment_status'], 'bg');
                $pc  = statusStyle($pay_styles, $order['payment_status'], 'color');

                // Fetch first item for preview
                $first_q = mysqli_query($con, "SELECT * FROM order_items WHERE order_id=" . (int)$order['id'] . " LIMIT 1");
                $first   = $first_q ? mysqli_fetch_assoc($first_q) : null;
                $item_count_q = mysqli_query($con, "SELECT COUNT(*) as c FROM order_items WHERE order_id=" . (int)$order['id']);
                $item_count = ($item_count_q ? mysqli_fetch_assoc($item_count_q) : null)['c'] ?? 0;

                $pm_icons = ['cod'=>'fa-money-bill-wave','cashfree'=>'fa-bolt','razorpay'=>'fa-credit-card','paypal'=>'fa-paypal'];
                $pm_icon  = $pm_icons[strtolower($order['payment_method'] ?? 'cod')] ?? 'fa-credit-card';
            ?>
            <div class="card border-0 shadow-sm rounded-4 order-row-card" style="border-left:4px solid <?= $oc ?>!important;">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">

                        <!-- Product Preview -->
                        <div class="col-auto">
                            <div style="width:64px;height:64px;background:#f8fafc;border-radius:12px;overflow:hidden;display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;">
                                <?php if ($first): ?>
                                <img src="<?= htmlspecialchars($first['product_image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                     style="width:56px;height:56px;object-fit:contain;mix-blend-mode:multiply;">
                                <?php else: ?>
                                <i class="fas fa-box text-muted fs-4"></i>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Order Info -->
                        <div class="col">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($order['order_number']) ?></h6>
                                <span style="background:<?= $os ?>;color:<?= $oc ?>;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;">
                                    <i class="fas <?= $oic ?> me-1"></i><?= htmlspecialchars($order['order_status']) ?>
                                </span>
                                <span style="background:<?= $ps ?>;color:<?= $pc ?>;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;">
                                    <?= htmlspecialchars($order['payment_status']) ?>
                                </span>
                            </div>
                            <?php if ($first): ?>
                            <p class="text-muted small mb-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                <?= htmlspecialchars($first['product_name']) ?>
                                <?= $item_count > 1 ? '<span class="ms-1 badge bg-light text-muted border">+' . ($item_count-1) . ' more</span>' : '' ?>
                            </p>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-3 flex-wrap mt-1">
                                <span class="text-muted small">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?= date('M j, Y', strtotime($order['order_date'] ?? $order['created_at'] ?? 'now')) ?>
                                </span>
                                <span class="text-muted small">
                                    <i class="fas <?= $pm_icon ?> me-1"></i>
                                    <?= strtoupper(htmlspecialchars($order['payment_method'] ?? 'COD')) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Total + Actions -->
                        <div class="col-auto text-end">
                            <div style="font-size:1.2rem;font-weight:900;color:var(--theme-primary,#1f7a8c);">
                                ₹<?= number_format($order['total_amount'], 2) ?>
                            </div>
                            <div class="d-flex gap-2 mt-2 justify-content-end">
                                <button class="btn btn-sm btn-gradient rounded-pill px-3 d-inline-flex align-items-center"
                                        onclick="viewOrderDetails(<?= (int)$order['id'] ?>)">
                                    <i class="fas fa-eye me-1"></i>Details
                                </button>
                                
                                <?php if (in_array($order['order_status'], ['Pending','Processing']) && $order['payment_status'] !== 'Paid'): ?>
                                <a href="pay_order.php?order_id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 d-inline-flex align-items-center">
                                    <i class="fas fa-redo me-1"></i>Pay Now
                                </a>
                                <?php endif; ?>

                                <?php 
                                // Show "Write Review" button if order is delivered and has at least 1 unreviewed item
                                if ($order['order_status'] === 'Delivered'):
                                    $unrev_q = mysqli_query($con, "
                                        SELECT oi.product_id 
                                        FROM order_items oi 
                                        LEFT JOIN reviews r ON oi.product_id = r.product_id AND r.user_email = '$esc_email'
                                        WHERE oi.order_id = " . (int)$order['id'] . " AND r.id IS NULL 
                                        LIMIT 1
                                    ");
                                    $unreviewed = $unrev_q ? mysqli_fetch_assoc($unrev_q) : null;
                                    
                                    if ($unreviewed):
                                ?>
                                <a href="product_detail.php?id=<?= $unreviewed['product_id'] ?>#reviews" target="_blank" class="btn btn-sm rounded-pill px-3 d-inline-flex align-items-center" title="Review Items" style="background:#fffbeb;border:1px solid #fde68a;color:#d97706;font-weight:600;">
                                    <i class="fas fa-star me-1" style="color:#f59e0b;"></i>Review
                                </a>
                                <?php endif; ?>

                                <a href="order_receipt.php?order_id=<?= (int)$order['id'] ?>" target="_blank"
                                   class="btn btn-sm btn-outline-success rounded-pill px-3 d-inline-flex align-items-center"
                                   title="Download Receipt">
                                    <i class="fas fa-download me-1"></i>Receipt
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="detailModalTitle">Order Details</h5>
                    <p class="text-muted small mb-0" id="detailModalOrderNum"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detailModalBody">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-row-card { transition: transform .2s, box-shadow .2s; }
.order-row-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,.1)!important; }
</style>

<script>
function viewOrderDetails(orderId) {
    var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    document.getElementById('detailModalBody').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';
    document.getElementById('detailModalOrderNum').textContent = '';
    modal.show();

    $.get('order_detail_ajax.php', { order_id: orderId }, function(htmlData) {
        // If the server returns an alert-danger string, it means there was an error
        document.getElementById('detailModalBody').innerHTML = htmlData;
        
        // Extract order number from the hidden container
        var container = document.getElementById('modalDataContainer');
        if (container) {
            document.getElementById('detailModalOrderNum').textContent = container.getAttribute('data-order-number');
            document.getElementById('detailModalTitle').textContent = 'Order Details';
        } else {
            document.getElementById('detailModalTitle').textContent = 'Error';
        }
    }, 'text').fail(function() {
        document.getElementById('detailModalBody').innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
    });
}
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>