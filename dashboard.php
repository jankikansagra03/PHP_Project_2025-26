<?php
$title = "Dashboard - JK Store";
$active_sidebar = 'dashboard';
ob_start();
?>
<style>
    .dashboard-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 20px;
        overflow: hidden;
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
</style>

<!-- Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 dashboard-card" style="background: var(--primary-gradient); color: white;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Welcome Back, John! 👋</h2>
                <p class="mb-0 opacity-90">Here's what's happening with your account today.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="fas fa-chart-line fa-4x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4 text-center">
                <div class="stat-icon mx-auto mb-3" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3 class="fw-bold mb-1">24</h3>
                <p class="text-muted small mb-0">Total Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4 text-center">
                <div class="stat-icon mx-auto mb-3" style="background: rgba(245, 87, 108, 0.1); color: #f5576c;">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="fw-bold mb-1">12</h3>
                <p class="text-muted small mb-0">Wishlist</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4 text-center">
                <div class="stat-icon mx-auto mb-3" style="background: rgba(67, 233, 123, 0.1); color: #43e97b;">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="fw-bold mb-1">$450</h3>
                <p class="text-muted small mb-0">Wallet</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4 text-center">
                <div class="stat-icon mx-auto mb-3" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="fw-bold mb-1">4.8</h3>
                <p class="text-muted small mb-0">Rating</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm dashboard-card">
            <div class="card-header bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Orders</h5>
                    <a href="my_orders.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                </div>
            </div>
            <div class="card-body p-1">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded" style="width: 60px; height: 60px; background: #f8f9fa;">
                                    <img src="images/product-1.jpg" alt="" class="img-fluid rounded">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Wireless Headphones</h6>
                                <p class="text-muted small mb-0">Order #ORD-7829 • Oct 24, 2023</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Delivered</span>
                                <p class="fw-bold mb-0 mt-2">$120.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded" style="width: 60px; height: 60px; background: #f8f9fa;">
                                    <img src="images/product-2.jpg" alt="" class="img-fluid rounded">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Smart Fitness Watch</h6>
                                <p class="text-muted small mb-0">Order #ORD-7828 • Oct 22, 2023</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Processing</span>
                                <p class="fw-bold mb-0 mt-2">$180.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded" style="width: 60px; height: 60px; background: #f8f9fa;">
                                    <img src="images/product-2.jpg" alt="" class="img-fluid rounded">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Smart Fitness Watch</h6>
                                <p class="text-muted small mb-0">Order #ORD-7828 • Oct 22, 2023</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Processing</span>
                                <p class="fw-bold mb-0 mt-2">$180.00</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->

</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>