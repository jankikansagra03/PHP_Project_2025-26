<?php
include_once("db_config.php");
@session_start();

$nav_user_data = null;
if (isset($_SESSION['user'])) {
    $session_email = mysqli_real_escape_string($con, $_SESSION['user']);
    $nav_user_query = "SELECT * FROM registration WHERE email='$session_email' LIMIT 1";
    $nav_user_result = mysqli_query($con, $nav_user_query);
    if ($nav_user_result && mysqli_num_rows($nav_user_result) === 1) {
        $nav_user_data = mysqli_fetch_assoc($nav_user_result);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'My Website'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.bundle.min.js"></script>


    <!-- FontAwesome -->
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <script src="js/jquery.js"></script>
    <script src="js/validate.js"></script>
    <!-- Custom CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="css/theme-core.css">
    <link rel="stylesheet" href="css/theme-dashboard.css">
    <link rel="stylesheet" href="css/theme-pages.css">
</head>

<body>
    <!-- Navigation -->


    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">JK Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="offers.php">Offers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php
                    if (isset($_SESSION['user'])) {
                        $display_name = 'User';
                        $display_email = $_SESSION['user'];
                        $display_role = 'User';
                        $display_picture = 'default.png';

                        if ($nav_user_data) {
                            if (isset($nav_user_data['fullname']) && trim($nav_user_data['fullname']) !== '') {
                                $display_name = $nav_user_data['fullname'];
                            } elseif (isset($nav_user_data['name']) && trim($nav_user_data['name']) !== '') {
                                $display_name = $nav_user_data['name'];
                            }

                            if (isset($nav_user_data['email']) && trim($nav_user_data['email']) !== '') {
                                $display_email = $nav_user_data['email'];
                            }

                            if (isset($nav_user_data['role']) && trim($nav_user_data['role']) !== '') {
                                $display_role = $nav_user_data['role'];
                            }

                            if (isset($nav_user_data['profile_picture']) && trim($nav_user_data['profile_picture']) !== '') {
                                $display_picture = $nav_user_data['profile_picture'];
                            }
                        }

                        // Cart & Wishlist counts
                        $nav_cart_email    = mysqli_real_escape_string($con, $_SESSION['user']);
                        $nav_cart_res      = mysqli_query($con, "SELECT SUM(quantity) as total FROM cart WHERE user_email='$nav_cart_email'");
                        $nav_cart_count    = (int)(mysqli_fetch_assoc($nav_cart_res)['total'] ?? 0);
                        $nav_wl_res        = mysqli_query($con, "SELECT COUNT(*) as total FROM wishlist WHERE user_email='$nav_cart_email'");
                        $nav_wl_count      = (int)(mysqli_fetch_assoc($nav_wl_res)['total'] ?? 0);
                    ?>
                        <!-- Wishlist Icon -->
                        <li class="nav-item me-1">
                            <a href="wishlist.php" class="nav-link position-relative" title="My Wishlist">
                                <i class="fas fa-heart fs-5"></i>
                                <span id="navWishlistBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-inline-flex align-items-center justify-content-center"
                                    style="font-size:0.6rem; min-width:18px; height:18px; <?= $nav_wl_count === 0 ? 'display:none!important;' : '' ?>">
                                    <?= $nav_wl_count ?>
                                </span>
                            </a>
                        </li>
                        <!-- Cart Icon -->
                        <li class="nav-item me-2">
                            <a href="cart.php" class="nav-link position-relative" title="My Cart">
                                <i class="fas fa-shopping-cart fs-5"></i>
                                <span id="navCartBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-inline-flex align-items-center justify-content-center"
                                    style="font-size:0.6rem; min-width:18px; height:18px; <?= $nav_cart_count === 0 ? 'display:none!important;' : '' ?>">
                                    <?= $nav_cart_count ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="images/profile_pictures/<?= htmlspecialchars($display_picture, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Profile" class="nav-user-avatar">
                                <span
                                    class="d-none d-md-inline"><?= htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                                <li class="px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="images/profile_pictures/<?= $display_picture ?>" alt="Profile"
                                            class="dropdown-user-avatar">
                                        <div>
                                            <h6 class="mb-0"><?= $display_name ?></h6>
                                            <small class="text-muted d-block"><?= $display_email ?></small>
                                            <small class="badge text-bg-light border mt-1"><?= $display_role ?></small>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="dashboard.php"><i
                                            class="fas fa-gauge-high me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="edit_profile.php"><i
                                            class="fas fa-user-pen me-2"></i>Edit Profile</a></li>
                                <li><a class="dropdown-item" href="change_password.php"><i
                                            class="fas fa-key me-2"></i>Change Password</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i
                                            class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php
                    } else {
                    ?>
                        <li class="nav-item ms-3">
                            <a class="btn btn-outline-gradient" href="login.php">Login</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-gradient" href="register.php">Register</a>
                        </li>
                    <?php
                    }

                    ?>
                </ul>
            </div>
        </div>
    </nav>
    <br>
    <div class="container">
        <?php
        if (isset($_COOKIE['success'])) {
        ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success</strong> <?= $_COOKIE['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php

        }

        if (isset($_COOKIE['error'])) {
        ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error</strong> <?= $_COOKIE['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>


        <?php
        }
        ?>

    </div>
    <!-- Main Content -->
    <main>
        <div class="row m-3 p-4">
            <?php echo $content; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> JK Store. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="privacy_policy.php" class="me-3">Privacy Policy</a>
                    <a href="terms_of_service.php" class="me-3">Terms of Service</a>
                    <a href="faq.php" class="me-3">FAQ</a>
                    <a href="contact.php">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <button class="theme-fab" id="themeFab" type="button" aria-label="Open theme settings">
        <i class="fas fa-palette"></i>
    </button>
    <div class="theme-panel" id="themePanel" aria-hidden="true">
        <div class="theme-panel-head">
            <h6>Theme Studio</h6>
            <button type="button" class="btn-close" id="themePanelClose" aria-label="Close"></button>
        </div>
        <div class="theme-panel-body">
            <div class="theme-presets" aria-label="Theme presets" id="themePresetList"></div>
            <label class="theme-control"><span>Primary</span><input type="color" data-theme-var="--theme-primary"
                    value="#1f7a8c"></label>
            <label class="theme-control"><span>Secondary</span><input type="color" data-theme-var="--theme-secondary"
                    value="#bf5af2"></label>
            <label class="theme-control"><span>Accent</span><input type="color" data-theme-var="--theme-accent"
                    value="#ff7a59"></label>
            <label class="theme-control"><span>Surface</span><input type="color" data-theme-var="--theme-surface"
                    value="#ffffff"></label>
            <label class="theme-control"><span>Text</span><input type="color" data-theme-var="--theme-text"
                    value="#1f2937"></label>
            <label class="theme-control"><span>BG Start</span><input type="color" data-theme-var="--theme-bg-start"
                    value="#0f172a"></label>
            <label class="theme-control"><span>BG End</span><input type="color" data-theme-var="--theme-bg-end"
                    value="#1f2937"></label>
            <div class="theme-actions">
                <button class="btn btn-sm btn-outline-secondary" id="themeReset" type="button">Reset</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-customizer.js"></script>
</body>

</html>