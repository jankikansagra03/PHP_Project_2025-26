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
        /* margin-bottom: 5px; */
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

    /* Fixed Viewport Layout */
    body {
        height: 100vh;
        overflow: hidden;
    }

    main {
        display: flex !important;
        flex-direction: column;
        overflow: hidden;
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }

    /* Target the container-fluid from layout.php */
    main>.container-fluid {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding-bottom: 0 !important;
    }

    .dashboard-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .dashboard-container>.row {
        height: 100%;
        /* overflow: hidden; Removed to prevent clipping of shadows */
    }

    .sidebar-column {
        height: 100%;
        overflow-y: auto;
        padding-bottom: 80px;
        /* Increased bottom padding */
        /* Hide scrollbar */
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .main-content-column {
        height: 100%;
        overflow-y: auto;
        padding-right: 15px;
        /* Increased right padding */
        padding-bottom: 80px;
        /* Increased bottom padding */
        /* Hide scrollbar */
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .sidebar-column::-webkit-scrollbar,
    .main-content-column::-webkit-scrollbar {
        display: none;
    }
</style>

<div class="container fade-in-up dashboard-container">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3 sidebar-column">
            <div class="card border-0 shadow-sm" style="min-height: 100%;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="images/profile_pictures/default.png" alt="Profile" class="rounded-circle shadow-sm"
                                style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #fff;">
                            <span
                                class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2"></span>
                        </div>
                        <h5 class="fw-bold mb-1">John Doe</h5>
                        <p class="text-muted small mb-0">john.doe@example.com</p>
                    </div>

                    <nav class="nav flex-column">
                        <a href="user_dashboard.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="user_profile.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'profile') ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="user_orders.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'orders') ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i> My Orders
                        </a>
                        <a href="user_cart.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'cart') ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                        <a href="user_wishlist.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'wishlist') ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                        <a href="user_addresses.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'addresses') ? 'active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i> Addresses
                        </a>
                        <a href="user_change_password.php"
                            class="sidebar-link <?php echo ($active_sidebar == 'password') ? 'active' : ''; ?>">
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
        <div class="col-lg-9 main-content-column">
            <?php if (isset($dashboard_content)) echo $dashboard_content; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>