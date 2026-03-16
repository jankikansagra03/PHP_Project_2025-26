<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$q = "select * from registration where email='$email'";
$result = mysqli_query($con, $q);
$user_data = mysqli_fetch_assoc($result);
$dashboard_name = 'User';
if (isset($user_data['fullname']) && trim($user_data['fullname']) !== '') {
    $dashboard_name = $user_data['fullname'];
} elseif (isset($user_data['name']) && trim($user_data['name']) !== '') {
    $dashboard_name = $user_data['name'];
}
$title = "Dashboard - JK Store";
$active_sidebar = 'dashboard';
ob_start();
?>
<style>
    .dashboard-wrap {
        display: grid;
        gap: 1.5rem;
    }

    .dashboard-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 20px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .activity-item {
        padding: 1rem;
        border-left: 3px solid #667eea;
        margin-bottom: 1rem;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 0 10px 10px 0;
    }

    .welcome-card .card-body {
        padding: 2rem;
    }

    .welcome-title {
        font-size: clamp(1.4rem, 2.3vw, 2rem);
    }

    .stat-value {
        font-size: 1.6rem;
        line-height: 1;
    }

    .recent-orders .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    .order-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 14px;
        transition: background-color 0.25s ease;
    }

    .order-item:hover {
        background: rgba(102, 126, 234, 0.04);
    }

    .order-thumb {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        background: #f8f9fa;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .order-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-main {
        min-width: 0;
        flex: 1;
    }

    .order-meta {
        white-space: nowrap;
        text-align: right;
    }

    @media (max-width: 991.98px) {
        .welcome-card .card-body {
            padding: 1.5rem;
        }

        .order-item {
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .order-meta {
            width: 100%;
            text-align: left;
            margin-left: 80px;
        }
    }

    @media (max-width: 575.98px) {
        .order-meta {
            margin-left: 0;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            font-size: 1.25rem;
        }

        .stat-value {
            font-size: 1.35rem;
        }
    }
</style>

<!-- Welcome Banner -->
<div class="dashboard-wrap">
    <div class="card border-0 shadow-sm mb-0 dashboard-card welcome-card" style="background: var(--primary-gradient); color: white;">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2 welcome-title">Welcome Back, <?= htmlspecialchars($dashboard_name, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="mb-0 opacity-90">Here's what's happening with your account today.</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="fas fa-chart-line fa-4x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 g-lg-4 mb-0">
        <div class="col-md-4 col-4">
            <a href="my_orders.php" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <?php
                        $q = "select count(*) as total_orders from orders where `user_email`='" . $user_data["email"] . "'";
                        $result = mysqli_query($con, $q);
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <h3 class="fw-bold mb-1 stat-value"><?= $row['total_orders'] ?></h3>
                        <p class="text-muted small mb-0">Total Orders</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-4">
            <a href="wishlist.php" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="background: rgba(245, 87, 108, 0.1); color: #f5576c;">
                            <i class="fas fa-heart"></i>
                        </div>
                        <?php
                        $q = "select count(*) as total_wishlist from wishlist where `user_email`='" . $user_data["email"] . "'";
                        $result = mysqli_query($con, $q);
                        $row = mysqli_fetch_assoc($result);

                        ?>
                        <h3 class="fw-bold mb-1 stat-value"><?= $row['total_wishlist'] ?></h3>
                        <p class="text-muted small mb-0">Wishlist</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-4">
            <a href="cart.php" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="background: rgba(67, 233, 123, 0.1); color: #43e97b;">
                            <i class="fas fa-cart-shopping"></i>
                        </div>
                        <?php
                        $q = "select count(*) as total_cart from cart where `user_email`='" . $user_data["email"] . "'";
                        $result = mysqli_query($con, $q);
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <h3 class="fw-bold mb-1 stat-value"><?= $row['total_cart'] ?></h3>
                        <p class="text-muted small mb-0">Cart</p>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm dashboard-card recent-orders">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Recent Orders</h5>
                        <a href="my_orders.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                    </div>
                </div>
                <?php
                $q1 = "select * from orders where `user_email`='" . $user_data["email"] . "' order by id desc limit 3";

                $result = mysqli_query($con, $q1);
                $count = mysqli_num_rows($result);
                if ($count == 0) {
                    echo '<div class="card-body p-4 text-center">
                            <p class="mb-0 opacity-75">You have no recent orders.</p>
                          </div>';
                } else {

                ?>
                    <div class="card-body p-3">
                        <div class="list-group list-group-flush">

                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <div class="list-group-item border-0 px-2 py-1">
                                    <div class="order-item">
                                        <div class="order-thumb">
                                            <img src="images/product-1.jpg" alt="Wireless Headphones">
                                        </div>
                                        <div class="order-main">
                                            <h6 class="fw-bold mb-1">Wireless Headphones</h6>
                                            <p class="text-muted small mb-0">Order #ORD-7829 • Oct 24, 2023</p>
                                        </div>
                                        <div class="order-meta">
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Delivered</span>
                                            <p class="fw-bold mb-0 mt-2">$120.00</p>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>