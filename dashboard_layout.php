<?php
// Default active sidebar item if not set
if (!isset($active_sidebar)) {
    $active_sidebar = '';
}
ob_start();
?>
<style>
    .sidebar-link {
        border-radius: 10px;
        margin-bottom: 5px;
        transition: all 0.3s ease;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #555;
        font-weight: 500;
    }

    .sidebar-link:hover,
    .sidebar-link.active {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        transform: translateX(5px);
    }

    .sidebar-link.active {
        background: var(--primary-gradient);
        color: white;
    }

    .sidebar-link i {
        width: 25px;
    }
</style>

<div class="container fade-in-up">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="images/default-avatar.png" alt="Profile" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #fff;">
                            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2"></span>
                        </div>
                        <h5 class="fw-bold mb-1">John Doe</h5>
                        <p class="text-muted small mb-0">john.doe@example.com</p>
                    </div>

                    <nav class="nav flex-column">
                        <a href="dashboard.php" class="sidebar-link <?php echo ($active_sidebar == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="profile.php" class="sidebar-link <?php echo ($active_sidebar == 'profile') ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="my_orders.php" class="sidebar-link <?php echo ($active_sidebar == 'orders') ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i> My Orders
                        </a>
                        <a href="cart.php" class="sidebar-link <?php echo ($active_sidebar == 'cart') ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                        <a href="wishlist.php" class="sidebar-link <?php echo ($active_sidebar == 'wishlist') ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                        <a href="saved_addresses.php" class="sidebar-link <?php echo ($active_sidebar == 'addresses') ? 'active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i> Addresses
                        </a>
                        <a href="change_password.php" class="sidebar-link <?php echo ($active_sidebar == 'password') ? 'active' : ''; ?>">
                            <i class="fas fa-lock"></i> Change Password
                        </a>
                        <a href="logout.php" class="sidebar-link text-danger mt-3">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if (isset($dashboard_content)) echo $dashboard_content; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>