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

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-w: 260px;
            --topbar-h: 64px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) fixed;
            min-height: 100vh;
        }

        /* ────────────────────── Sidebar ──────────────────────── */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(14px);
            box-shadow: 4px 0 28px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            z-index: 1050;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.4rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(102, 126, 234, 0.12);
            flex-shrink: 0;
        }

        .sidebar-brand h4 {
            margin: 0;
            font-weight: 800;
            font-size: 1.35rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-brand small {
            font-size: 0.7rem;
            color: #aaa;
            font-weight: 600;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        .sidebar-user {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border-bottom: 1px solid rgba(102, 126, 234, 0.09);
            flex-shrink: 0;
        }

        .sidebar-user img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.3);
            flex-shrink: 0;
        }

        .sidebar-user .su-role {
            font-size: 0.68rem;
            color: #aaa;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .sidebar-user .su-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.75rem;
            overflow-y: auto;
        }

        .sidebar-section {
            font-size: 0.67rem;
            text-transform: uppercase;
            letter-spacing: 0.11em;
            color: #c0c0c0;
            font-weight: 700;
            padding: 0.75rem 0.75rem 0.2rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 0.88rem;
            transition: all 0.22s ease;
            margin-bottom: 2px;
        }

        .sidebar-link i {
            width: 19px;
            text-align: center;
            font-size: 0.92rem;
            opacity: 0.75;
            flex-shrink: 0;
        }

        .sidebar-link:hover {
            background: rgba(102, 126, 234, 0.08);
            color: #667eea;
            transform: translateX(4px);
        }

        .sidebar-link.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 14px rgba(102, 126, 234, 0.32);
        }

        .sidebar-link.active i {
            opacity: 1;
        }

        .sidebar-link.link-danger {
            color: #e74c3c;
        }

        .sidebar-link.link-danger:hover {
            background: rgba(231, 76, 60, 0.08);
            color: #c0392b;
        }

        /* sidebar footer */
        .sidebar-footer {
            padding: 0.9rem 1.5rem;
            border-top: 1px solid rgba(102, 126, 234, 0.09);
            font-size: 0.72rem;
            color: #ccc;
            text-align: center;
            flex-shrink: 0;
        }

        /* ────────────────────── Main area ────────────────────── */
        .admin-main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ────────────────────── Top bar ──────────────────────── */
        .admin-topbar {
            height: var(--topbar-h);
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.28);
            display: flex;
            align-items: center;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 2px 18px rgba(0, 0, 0, 0.05);
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .topbar-toggle {
            display: none;
            background: none;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.35rem 0.6rem;
            color: #666;
            cursor: pointer;
            flex-shrink: 0;
        }

        .topbar-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #333;
            margin: 0;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .admin-header-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.3);
        }

        .admin-user-menu {
            min-width: 230px;
        }

        /* ────────────────────── Content ──────────────────────── */
        .admin-content {
            flex: 1;
            padding: 1.75rem;
        }

        /* ────────────────────── Footer ───────────────────────── */
        .admin-footer {
            padding: 1rem 1.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        /* ────────────────────── Alert banner ─────────────────── */
        .admin-alerts {
            padding: 0 1.75rem;
            padding-top: 1rem;
        }

        /* ────────────────────── Shared component styles ──────── */
        .stat-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 16px;
            padding: 1.4rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.1;
            color: #222;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #999;
            font-weight: 500;
        }

        .data-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .data-card-header {
            padding: 1rem 1.4rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .data-card-header h6 {
            margin: 0;
            font-weight: 700;
            color: #333;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .admin-table th {
            padding: 0.7rem 1rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #b0b0b0;
            font-weight: 700;
            background: #fafafa;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }

        .admin-table td {
            padding: 0.8rem 1rem;
            color: #444;
            border-bottom: 1px solid #f8f8f8;
            vertical-align: middle;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .admin-table tbody tr:hover td {
            background: rgba(102, 126, 234, 0.025);
        }

        .status-badge {
            padding: 0.28rem 0.72rem;
            border-radius: 50px;
            font-size: 0.74rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-delivered {
            background: rgba(67, 233, 123, 0.12);
            color: #27ae60;
        }

        .badge-processing {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .badge-pending {
            background: rgba(243, 156, 18, 0.12);
            color: #e67e22;
        }

        .badge-cancelled {
            background: rgba(231, 76, 60, 0.12);
            color: #e74c3c;
        }

        .badge-paid {
            background: rgba(67, 233, 123, 0.12);
            color: #27ae60;
        }

        .badge-unpaid {
            background: rgba(231, 76, 60, 0.12);
            color: #e74c3c;
        }

        .badge-admin {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .badge-user {
            background: rgba(149, 165, 166, 0.18);
            color: #7f8c8d;
        }

        .table-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .welcome-banner {
            background: var(--primary-gradient);
            border-radius: 16px;
            padding: 1.4rem 1.75rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: 0 6px 22px rgba(102, 126, 234, 0.32);
        }

        .page-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
            padding: 1.5rem;
        }

        /* ────────────────────── Responsive ───────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1040;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .topbar-toggle {
                display: block;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* ────────────────────── Shared modal styles ─────────── */
        .modal-content {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
        }

        .modal-header,
        .modal-footer {
            background: #fafbff;
        }

        .modal-header {
            border-bottom: 1px solid #edf0fb;
        }

        .modal-footer {
            border-top: 1px solid #edf0fb;
        }

        .modal .btn {
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .modal .form-control,
        .modal .form-select {
            border-radius: 12px;
            border-color: #e5e7f3;
            box-shadow: none;
        }

        .modal .form-control:focus,
        .modal .form-select:focus {
            border-color: rgba(102, 126, 234, 0.6);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.16);
        }

        .modal-body-scroll {
            max-height: calc(85vh - 140px);
            overflow-y: auto;
        }

        /* ────────────────────── Shared page-card btn/input ──── */
        .page-card .btn {
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .page-card .btn-primary,
        .page-card .btn-success {
            border: 0;
            background: var(--primary-gradient);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.22);
        }

        .page-card .btn-primary:hover,
        .page-card .btn-success:hover {
            filter: brightness(1.03);
        }

        .page-card .form-control,
        .page-card .form-select {
            border-radius: 12px;
            border-color: #e5e7f3;
            box-shadow: none;
        }

        .page-card .form-control:focus,
        .page-card .form-select:focus {
            border-color: rgba(102, 126, 234, 0.6);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.16);
        }

        /* ────────────────────── page-card layout ──── */
        .page-card {
            padding: 0;
            overflow: hidden;
            border-radius: 16px;
        }

        .products-header {
            padding: 1rem 1.3rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            background: linear-gradient(180deg, #fbfcff 0%, #f7f8ff 100%);
        }

        .products-body {
            padding: 1.2rem;
        }

        /* ────────────────────── Shared admin table ──── */
        .products-table {
            border-radius: 12px;
            overflow: hidden;
        }

        .products-table th {
            padding: 0.7rem 0.9rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #a9a9a9;
            font-weight: 700;
            background: #fafafa;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }

        .products-table td {
            padding: 0.78rem 0.9rem;
            vertical-align: middle;
        }

        .products-table tbody tr:hover td {
            background: rgba(102, 126, 234, 0.03);
        }

        .products-table img {
            border-radius: 8px;
            object-fit: cover;
        }

        /* ────────────────────── Action buttons ──── */
        .products-actions .btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .small-preview {
            width: 58px;
            height: 58px;
        }

        /* ────────────────────── Pagination ──── */
        .products-pagination {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .products-pagination-meta {
            font-size: 0.84rem;
            color: #8f93a8;
            font-weight: 600;
        }

        .products-pagination-list {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-wrap: wrap;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .products-pagination-item {
            margin: 0;
        }

        .products-pagination-link {
            min-width: 42px;
            height: 42px;
            padding: 0 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #e4e8f7;
            background: #fff;
            color: #667085;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(17, 24, 39, 0.04);
        }

        .products-pagination-link:hover {
            border-color: rgba(102, 126, 234, 0.35);
            color: #667eea;
            background: rgba(102, 126, 234, 0.06);
        }

        .products-pagination-item.active .products-pagination-link {
            border: 0;
            color: #fff;
            background: var(--primary-gradient);
            box-shadow: 0 8px 18px rgba(102, 126, 234, 0.26);
        }

        .products-pagination-item.disabled .products-pagination-link {
            background: #f8f9fc;
            border-color: #edf0f7;
            color: #b9bfd3;
            pointer-events: none;
            box-shadow: none;
        }

        .products-pagination-link.is-nav {
            padding: 0 1rem;
        }

        @media (max-width: 767.98px) {
            .products-pagination {
                justify-content: center;
            }

            .products-pagination-meta {
                width: 100%;
                text-align: center;
            }
        }
    </style>
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
            <div style="min-width:0;">
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
            &copy; <?= date('Y') ?> JK Store &mdash; Admin Panel
        </footer>

    </div><!-- /.admin-main -->

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        toggleBtn.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
        overlay.addEventListener('click', closeSidebar);
    </script>
</body>

</html>