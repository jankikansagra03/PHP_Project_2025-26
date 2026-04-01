<?php
include_once 'db_config.php';

$admin_email = mysqli_real_escape_string($con, $_SESSION['admin'] ?? '');
$admin_q = "SELECT fullname FROM registration WHERE email='$admin_email' LIMIT 1";
$admin_res = mysqli_query($con, $admin_q);
$admin_row = $admin_res ? mysqli_fetch_assoc($admin_res) : null;
$admin_name = trim($admin_row['fullname'] ?? 'Admin');
if ($admin_name === '') {
    $admin_name = 'Admin';
}

// Dashboard stats
$total_users_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM registration");
$total_users_row = $total_users_result ? mysqli_fetch_assoc($total_users_result) : ['cnt' => 0];
$total_users = (int) ($total_users_row['cnt'] ?? 0);

$total_orders_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM orders");
$total_orders_row = $total_orders_result ? mysqli_fetch_assoc($total_orders_result) : ['cnt' => 0];
$total_orders = (int) ($total_orders_row['cnt'] ?? 0);

$total_revenue_result = mysqli_query($con, "SELECT COALESCE(SUM(total_amount), 0) AS rev FROM orders WHERE payment_status='Paid'");
$total_revenue_row = $total_revenue_result ? mysqli_fetch_assoc($total_revenue_result) : ['rev' => 0];
$total_revenue = (float) ($total_revenue_row['rev'] ?? 0);

$total_products_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM products");
$total_products_row = $total_products_result ? mysqli_fetch_assoc($total_products_result) : ['cnt' => 0];
$total_products = (int) ($total_products_row['cnt'] ?? 0);

// Operational analytics
$paid_orders_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM orders WHERE payment_status='Paid'");
$paid_orders_row = $paid_orders_result ? mysqli_fetch_assoc($paid_orders_result) : ['cnt' => 0];
$paid_orders = (int) ($paid_orders_row['cnt'] ?? 0);

$pending_orders_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM orders WHERE order_status='Pending'");
$pending_orders_row = $pending_orders_result ? mysqli_fetch_assoc($pending_orders_result) : ['cnt' => 0];
$pending_orders = (int) ($pending_orders_row['cnt'] ?? 0);

$delivered_orders_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM orders WHERE order_status='Delivered'");
$delivered_orders_row = $delivered_orders_result ? mysqli_fetch_assoc($delivered_orders_result) : ['cnt' => 0];
$delivered_orders = (int) ($delivered_orders_row['cnt'] ?? 0);

$cancelled_orders_result = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM orders WHERE order_status='Cancelled'");
$cancelled_orders_row = $cancelled_orders_result ? mysqli_fetch_assoc($cancelled_orders_result) : ['cnt' => 0];
$cancelled_orders = (int) ($cancelled_orders_row['cnt'] ?? 0);

// Period-over-period analytics (current 30 days vs previous 30 days)
$current_30_result = mysqli_query($con, "
    SELECT
        COUNT(*) AS orders_count,
        COALESCE(SUM(CASE WHEN payment_status='Paid' THEN total_amount ELSE 0 END), 0) AS paid_revenue
    FROM orders
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$current_30_row = $current_30_result ? mysqli_fetch_assoc($current_30_result) : ['orders_count' => 0, 'paid_revenue' => 0];
$current_30_orders = (int) ($current_30_row['orders_count'] ?? 0);
$current_30_revenue = (float) ($current_30_row['paid_revenue'] ?? 0);

$previous_30_result = mysqli_query($con, "
    SELECT
        COUNT(*) AS orders_count,
        COALESCE(SUM(CASE WHEN payment_status='Paid' THEN total_amount ELSE 0 END), 0) AS paid_revenue
    FROM orders
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
      AND order_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$previous_30_row = $previous_30_result ? mysqli_fetch_assoc($previous_30_result) : ['orders_count' => 0, 'paid_revenue' => 0];
$previous_30_orders = (int) ($previous_30_row['orders_count'] ?? 0);
$previous_30_revenue = (float) ($previous_30_row['paid_revenue'] ?? 0);

// Additional computed KPIs
$avg_order_value = $paid_orders > 0 ? ($total_revenue / $paid_orders) : 0;
$payment_success_rate = $total_orders > 0 ? (($paid_orders / $total_orders) * 100) : 0;
$delivery_rate = $total_orders > 0 ? (($delivered_orders / $total_orders) * 100) : 0;
$cancellation_rate = $total_orders > 0 ? (($cancelled_orders / $total_orders) * 100) : 0;

$orders_growth = $previous_30_orders > 0 ? (($current_30_orders - $previous_30_orders) / $previous_30_orders) * 100 : 0;
$revenue_growth = $previous_30_revenue > 0 ? (($current_30_revenue - $previous_30_revenue) / $previous_30_revenue) * 100 : 0;

// Top products by quantity sold
$top_products_result = mysqli_query($con, "
    SELECT
        oi.product_id,
        oi.product_name,
        COALESCE(SUM(oi.quantity), 0) AS units_sold,
        COALESCE(SUM(oi.subtotal), 0) AS gross_value
    FROM order_items oi
    GROUP BY oi.product_id, oi.product_name
    ORDER BY units_sold DESC
    LIMIT 5
");

// Revenue split by payment status
$payment_breakdown_result = mysqli_query($con, "
    SELECT payment_status, COUNT(*) AS cnt
    FROM orders
    GROUP BY payment_status
");

$recent_orders_result = mysqli_query($con, "SELECT id, user_email, order_date, total_amount, order_status, payment_status FROM orders ORDER BY id DESC LIMIT 8");
$recent_users_result = mysqli_query($con, "SELECT fullname, email, profile_picture, role FROM registration ORDER BY id DESC LIMIT 8");

$title = 'Admin Dashboard - JK Store';
$admin_active = 'dashboard';
$admin_page_title = 'Dashboard';

ob_start();
?>

<style>
    .metric-chip {
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.9);
        padding: 0.9rem 1rem;
        border: 1px solid rgba(102, 126, 234, 0.08);
    }

    .metric-chip .label {
        font-size: 0.78rem;
        color: #8d8d8d;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .metric-chip .value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #2f2f2f;
        line-height: 1.15;
    }

    .progress-thin {
        height: 8px;
        border-radius: 999px;
        background: #eef1ff;
    }

    .insight-note {
        background: rgba(102, 126, 234, 0.08);
        border: 1px solid rgba(102, 126, 234, 0.15);
        color: #4e57a0;
        border-radius: 12px;
        padding: 0.8rem 0.95rem;
        font-size: 0.85rem;
    }

    .growth-up {
        color: #27ae60;
        font-weight: 700;
    }

    .growth-down {
        color: #e74c3c;
        font-weight: 700;
    }
</style>

<div class="welcome-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?>!</h4>
        <p class="mb-0 opacity-75 small">Here is what is happening in your store today.</p>
    </div>
    <i class="fas fa-chart-line fa-3x opacity-25 d-none d-md-block"></i>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(102,126,234,0.12);color:#667eea;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($total_users) ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(240,147,251,0.12);color:#f093fb;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($total_orders) ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(67,233,123,0.12);color:#27ae60;">
                <i class="fas fa-indian-rupee-sign"></i>
            </div>
            <div>
                <div class="stat-value">&#8377;<?= number_format($total_revenue, 0) ?></div>
                <div class="stat-label">Revenue (Paid)</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(245,87,108,0.12);color:#f5576c;">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($total_products) ?></div>
                <div class="stat-label">Products</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="data-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-column me-2" style="color:#667eea;"></i>Performance Snapshot</h6>
                <span class="small text-muted">Last 30 days vs previous 30 days</span>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="metric-chip">
                        <div class="label">Orders (30d)</div>
                        <div class="value"><?= number_format($current_30_orders) ?></div>
                        <div class="small mt-1 <?= $orders_growth >= 0 ? 'growth-up' : 'growth-down' ?>">
                            <?= $orders_growth >= 0 ? '+' : '' ?><?= number_format($orders_growth, 1) ?>% vs previous window
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="metric-chip">
                        <div class="label">Paid Revenue (30d)</div>
                        <div class="value">&#8377;<?= number_format($current_30_revenue, 0) ?></div>
                        <div class="small mt-1 <?= $revenue_growth >= 0 ? 'growth-up' : 'growth-down' ?>">
                            <?= $revenue_growth >= 0 ? '+' : '' ?><?= number_format($revenue_growth, 1) ?>% vs previous window
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="metric-chip">
                        <div class="label">Average Order Value</div>
                        <div class="value">&#8377;<?= number_format($avg_order_value, 2) ?></div>
                        <div class="small text-muted mt-1">Based on paid orders</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="metric-chip">
                        <div class="label">Payment Success Rate</div>
                        <div class="value"><?= number_format($payment_success_rate, 1) ?>%</div>
                        <div class="small text-muted mt-1"><?= number_format($paid_orders) ?> of <?= number_format($total_orders) ?> orders paid</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="data-card p-3 p-md-4 h-100">
            <h6 class="mb-3 fw-bold"><i class="fas fa-heart-pulse me-2" style="color:#667eea;"></i>Order Health</h6>

            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Delivered</span>
                    <strong><?= number_format($delivery_rate, 1) ?>%</strong>
                </div>
                <div class="progress progress-thin">
                    <div class="progress-bar bg-success" style="width: <?= max(0, min(100, $delivery_rate)) ?>%"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Cancelled</span>
                    <strong><?= number_format($cancellation_rate, 1) ?>%</strong>
                </div>
                <div class="progress progress-thin">
                    <div class="progress-bar bg-danger" style="width: <?= max(0, min(100, $cancellation_rate)) ?>%"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Pending Queue</span>
                    <strong><?= number_format($pending_orders) ?></strong>
                </div>
                <div class="progress progress-thin">
                    <div class="progress-bar" style="background:#667eea;width: <?= $total_orders > 0 ? max(0, min(100, ($pending_orders / $total_orders) * 100)) : 0 ?>%"></div>
                </div>
            </div>

            <div class="insight-note mt-3">
                <i class="fas fa-lightbulb me-1"></i>
                <?= $cancellation_rate > 10 ? 'Cancellation rate is elevated. Review shipping times and COD confirmation calls.' : 'Cancellation rate is under control. Keep monitoring payment and fulfillment SLAs.' ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="data-card">
            <div class="data-card-header">
                <h6><i class="fas fa-cubes me-2" style="color:#667eea;"></i>Top Selling Products</h6>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Units</th>
                            <th>Gross Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$top_products_result || mysqli_num_rows($top_products_result) === 0): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No order item data yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($tp = mysqli_fetch_assoc($top_products_result)): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($tp['product_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format((int) ($tp['units_sold'] ?? 0)) ?></td>
                                    <td class="fw-semibold">&#8377;<?= number_format((float) ($tp['gross_value'] ?? 0), 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="data-card">
            <div class="data-card-header">
                <h6><i class="fas fa-credit-card me-2" style="color:#667eea;"></i>Payment Status Mix</h6>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$payment_breakdown_result || mysqli_num_rows($payment_breakdown_result) === 0): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No payment data available.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($pb = mysqli_fetch_assoc($payment_breakdown_result)): ?>
                                <?php
                                $pb_count = (int) ($pb['cnt'] ?? 0);
                                $pb_share = $total_orders > 0 ? ($pb_count / $total_orders) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($pb['payment_status'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format($pb_count) ?></td>
                                    <td><?= number_format($pb_share, 1) ?>%</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="data-card">
            <div class="data-card-header">
                <h6><i class="fas fa-shopping-bag me-2" style="color:#667eea;"></i>Recent Orders</h6>
                <a href="admin_orders.php" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:0.78rem;">View All</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recent_orders_result || mysqli_num_rows($recent_orders_result) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                                <?php
                                $order_status = strtolower($order['order_status'] ?? '');
                                $payment_status = strtolower($order['payment_status'] ?? '');
                                $order_badge = str_contains($order_status, 'deliver') ? 'badge-delivered' : (str_contains($order_status, 'cancel') ? 'badge-cancelled' : 'badge-processing');
                                $payment_badge = ($payment_status === 'paid') ? 'badge-paid' : 'badge-unpaid';
                                ?>
                                <tr>
                                    <td class="fw-bold text-secondary">#<?= (int) $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= !empty($order['order_date']) ? date('d M Y', strtotime($order['order_date'])) : '-' ?></td>
                                    <td class="fw-semibold">&#8377;<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></td>
                                    <td><span class="status-badge <?= $order_badge ?>"><?= htmlspecialchars($order['order_status'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><span class="status-badge <?= $payment_badge ?>"><?= htmlspecialchars($order['payment_status'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="data-card">
            <div class="data-card-header">
                <h6><i class="fas fa-users me-2" style="color:#667eea;"></i>Recent Users</h6>
                <a href="admin_users.php" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:0.78rem;">View All</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recent_users_result || mysqli_num_rows($recent_users_result) === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($user = mysqli_fetch_assoc($recent_users_result)): ?>
                                <?php
                                $pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png';
                                $role = strtolower($user['role'] ?? 'user');
                                $role_badge = ($role === 'admin') ? 'badge-admin' : 'badge-user';
                                ?>
                                <tr>
                                    <td><img src="images/profile_pictures/<?= htmlspecialchars($pic, ENT_QUOTES, 'UTF-8') ?>" alt="user" class="table-avatar"></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($user['fullname'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted" style="font-size:0.8rem;"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="status-badge <?= $role_badge ?>"><?= ucfirst($role) ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$admin_content = ob_get_clean();
include 'admin_layout.php';
