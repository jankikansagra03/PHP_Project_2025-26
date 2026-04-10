<?php

/**
 * Admin Layout
 *
 * Expected variables set by the including page:
 *   $title            – <title> tag text (default: "Admin – JK Store")
 *   $admin_active     – active sidebar key: 'dashboard'|'users'|'orders'|'products' (default: '')
 *   $admin_page_title – heading shown in the top bar (default: 'Admin')
 *   $admin_content    – HTML string captured by the page via ob_get_clean()
 */

@session_start();
include_once 'db_config.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['admin'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$_al_email  = mysqli_real_escape_string($con, $_SESSION['admin']);
$_al_result = mysqli_query($con, "SELECT fullname, email, profile_picture, role FROM registration WHERE email='$_al_email' LIMIT 1");
$_al_data   = mysqli_fetch_assoc($_al_result);

if (!$_al_data || strtolower($_al_data['role'] ?? '') !== 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

$admin_name    = trim($_al_data['fullname'] ?? '');
if ($admin_name === '') $admin_name = 'Admin';
$admin_picture = !empty($_al_data['profile_picture']) ? $_al_data['profile_picture'] : 'default.png';

// ── Defaults ──────────────────────────────────────────────────────────────────
if (!isset($title))            $title            = 'Admin – JK Store';
if (!isset($admin_active))     $admin_active     = '';
if (!isset($admin_page_title)) $admin_page_title = 'Admin';
if (!isset($admin_content))    $admin_content    = '';

// ── Sidebar nav items ─────────────────────────────────────────────────────────
$nav_items = [
    ['section' => 'Main'],
    ['key' => 'dashboard',      'href' => 'admin_dashboard.php',                     'icon' => 'fas fa-th-large',       'label' => 'Dashboard'],

    ['section' => 'Commerce Tables'],
    ['key' => 'products',       'href' => 'admin_products.php',                      'icon' => 'fas fa-box-open',       'label' => 'Products'],
    ['key' => 'categories',     'href' => 'admin_categories.php',                    'icon' => 'fas fa-layer-group',    'label' => 'Categories'],
    ['key' => 'offers',         'href' => 'admin_offers.php',                        'icon' => 'fas fa-tags',           'label' => 'Offers'],
    ['key' => 'offer_usage',    'href' => 'admin_offer_usage.php',                   'icon' => 'fas fa-ticket-alt',     'label' => 'Offer Usage'],
    ['key' => 'orders',         'href' => 'admin_orders.php',                        'icon' => 'fas fa-shopping-bag',   'label' => 'Orders'],
    ['key' => 'order_items',    'href' => 'admin_order_items.php',                   'icon' => 'fas fa-receipt',        'label' => 'Order Items'],
    ['key' => 'reviews',        'href' => 'admin_reviews.php',                       'icon' => 'fas fa-star',           'label' => 'Reviews'],
    ['key' => 'cart',           'href' => 'admin_cart.php',                          'icon' => 'fas fa-cart-shopping',  'label' => 'Cart'],
    ['key' => 'wishlist',       'href' => 'admin_wishlist.php',                      'icon' => 'fas fa-heart',          'label' => 'Wishlist'],

    ['section' => 'User Tables'],
    ['key' => 'users',          'href' => 'admin_users.php',                         'icon' => 'fas fa-users',          'label' => 'Registration'],
    ['key' => 'addresses',      'href' => 'admin_addresses.php',                     'icon' => 'fas fa-map-marker-alt', 'label' => 'Addresses'],
    ['key' => 'password_token', 'href' => 'admin_password_tokens.php',               'icon' => 'fas fa-key',            'label' => 'Password Tokens'],

    ['section' => 'Content Tables'],
    ['key' => 'site_pages',     'href' => 'admin_site_pages.php',                    'icon' => 'fas fa-file-lines',     'label' => 'Site Pages'],
    ['key' => 'contact_info',   'href' => 'admin_contact_info.php',                  'icon' => 'fas fa-address-book',   'label' => 'Contact Info'],
    ['key' => 'team_members',   'href' => 'admin_team_members.php',                  'icon' => 'fas fa-user-group',     'label' => 'Team Members'],
    ['key' => 'faq',            'href' => 'admin_faq.php',                           'icon' => 'fas fa-circle-question', 'label' => 'FAQ'],
    ['key' => 'contact_us',     'href' => 'admin_contact_us.php',                    'icon' => 'fas fa-envelope-open-text', 'label' => 'Contact Messages'],

    ['section' => 'Static Pages'],
    ['key' => 'about_page',     'href' => 'admin_site_pages.php?slug=about',         'icon' => 'fas fa-circle-info',    'label' => 'About Page'],
    ['key' => 'contact_page',   'href' => 'admin_site_pages.php?slug=contact',       'icon' => 'fas fa-envelope',       'label' => 'Contact Page'],
    ['key' => 'privacy_page',   'href' => 'admin_site_pages.php?slug=privacy-policy', 'icon' => 'fas fa-user-shield',    'label' => 'Privacy Policy'],
    ['key' => 'terms_page',     'href' => 'admin_site_pages.php?slug=terms-of-service', 'icon' => 'fas fa-file-contract', 'label' => 'Terms of Service'],
    ['key' => 'faq_page',       'href' => 'admin_site_pages.php?slug=faq',           'icon' => 'fas fa-comments',       'label' => 'FAQ Page'],

    ['section' => 'Account'],
    ['key' => 'profile',        'href' => 'dashboard.php',                           'icon' => 'fas fa-user-circle',    'label' => 'Admin Profile'],
    ['key' => 'change_password', 'href' => 'change_password.php',                     'icon' => 'fas fa-lock',           'label' => 'Change Password'],
    ['key' => 'logout',         'href' => 'logout.php',                              'icon' => 'fas fa-sign-out-alt',   'label' => 'Logout', 'danger' => true],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <link rel="stylesheet" href="css/theme-core.css">
    <link rel="stylesheet" href="css/theme-admin.css">
    <link rel="stylesheet" href="css/theme-pages.css">
</head>

<body>

    <!-- ═══════════ Sidebar overlay (mobile) ═══════════ -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ═══════════════════ SIDEBAR ═══════════════════ -->
    <aside class="admin-sidebar" id="adminSidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <h4><i class="fas fa-store me-2"></i>JK Store</h4>
            <small>Admin Panel</small>
        </div>

        <!-- Logged-in admin -->
        <div class="sidebar-user">
            <img src="images/profile_pictures/<?= htmlspecialchars($admin_picture, ENT_QUOTES, 'UTF-8') ?>" alt="Admin">
            <div class="sidebar-user-meta">
                <div class="su-role">Logged in as</div>
                <div class="su-name"><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>

        <!-- Nav links -->
        <nav class="sidebar-nav">
            <?php foreach ($nav_items as $item): ?>
                <?php if (isset($item['section'])): ?>
                    <div class="sidebar-section"><?= htmlspecialchars($item['section'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php else: ?>
                    <?php
                    $is_active = ($admin_active === $item['key']);
                    $extra_cls = '';
                    if (!empty($item['danger'])) $extra_cls = ' link-danger';
                    if ($is_active)              $extra_cls = ' active';
                    ?>
                    <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                        class="sidebar-link<?= $extra_cls ?>">
                        <i class="<?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">&copy; <?= date('Y') ?> JK Store</div>
    </aside>

    <!-- ═══════════════════ MAIN AREA ═════════════════ -->
    <div class="admin-main">

        <!-- Top bar -->
        <div class="admin-topbar">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="topbar-title">
                <?= htmlspecialchars($admin_page_title, ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <div class="topbar-right">
                <button class="btn btn-sm btn-light border rounded-pill" id="themeFab" type="button" aria-label="Open theme settings">
                    <i class="fas fa-palette"></i>
                </button>
                <span class="text-muted small d-none d-md-inline">
                    <i class="far fa-calendar-alt me-1"></i><?= date('D, d M Y') ?>
                </span>
                <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-store me-1"></i>
                    <span class="d-none d-sm-inline">View Store</span>
                </a>

                <div class="dropdown">
                    <button class="btn btn-sm btn-light border rounded-pill d-flex align-items-center gap-2 px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="images/profile_pictures/<?= htmlspecialchars($admin_picture, ENT_QUOTES, 'UTF-8') ?>" alt="Admin" class="admin-header-avatar">
                        <span class="d-none d-md-inline fw-semibold text-dark"><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end admin-user-menu shadow-sm border-0">
                        <li class="px-3 py-2">
                            <div class="fw-semibold"><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($_al_data['email'], ENT_QUOTES, 'UTF-8') ?></small>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="dashboard.php">
                                <i class="fas fa-user me-2"></i>Admin Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="change_password.php">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-right-from-bracket me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Flash messages -->
        <?php if (isset($_COOKIE['success']) || isset($_COOKIE['error'])): ?>
            <div class="admin-alerts">
                <?php if (isset($_COOKIE['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                        <i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($_COOKIE['success'], ENT_QUOTES, 'UTF-8') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_COOKIE['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-0 <?= isset($_COOKIE['success']) ? 'mt-2' : '' ?>" role="alert">
                        <i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($_COOKIE['error'], ENT_QUOTES, 'UTF-8') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Page content -->
        <div class="admin-content">
            <?= $admin_content ?>
        </div>

        <!-- Footer -->
        <footer class="admin-footer">
            &copy; <?= date('Y') ?> JK Store - Admin Panel
        </footer>

    </div><!-- /.admin-main -->

    <div class="theme-panel" id="themePanel" aria-hidden="true">
        <div class="theme-panel-head">
            <h6>Theme Studio</h6>
            <button type="button" class="btn-close" id="themePanelClose" aria-label="Close"></button>
        </div>
        <div class="theme-panel-body">
            <div class="theme-presets" aria-label="Theme presets" id="themePresetList"></div>
            <label class="theme-control"><span>Primary</span><input type="color" data-theme-var="--theme-primary" value="#1f7a8c"></label>
            <label class="theme-control"><span>Secondary</span><input type="color" data-theme-var="--theme-secondary" value="#bf5af2"></label>
            <label class="theme-control"><span>Accent</span><input type="color" data-theme-var="--theme-accent" value="#ff7a59"></label>
            <label class="theme-control"><span>Surface</span><input type="color" data-theme-var="--theme-surface" value="#ffffff"></label>
            <label class="theme-control"><span>Text</span><input type="color" data-theme-var="--theme-text" value="#1f2937"></label>
            <label class="theme-control"><span>BG Start</span><input type="color" data-theme-var="--theme-bg-start" value="#0f172a"></label>
            <label class="theme-control"><span>BG End</span><input type="color" data-theme-var="--theme-bg-end" value="#1f2937"></label>
            <div class="theme-actions">
                <button class="btn btn-sm btn-outline-secondary" id="themeReset" type="button">Reset</button>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-customizer.js"></script>
    <script>
        function openSidebar() {
            $('#adminSidebar').addClass('open');
            $('#sidebarOverlay').addClass('show');
        }

        function closeSidebar() {
            $('#adminSidebar').removeClass('open');
            $('#sidebarOverlay').removeClass('show');
        }

        $('#sidebarToggle').on('click', function() {
            $('#adminSidebar').hasClass('open') ? closeSidebar() : openSidebar();
        });

        $('#sidebarOverlay').on('click', closeSidebar);
    </script>
</body>

</html>