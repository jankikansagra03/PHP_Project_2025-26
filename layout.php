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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-bg: #1a1a2e;
            --card-bg: #16213e;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
        }

        /* Navbar Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            font-weight: 500;
            color: #333 !important;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 0.5rem;
        }

        .nav-link:hover {
            color: #667eea !important;
            transform: translateY(-2px);
        }

        .nav-item:not(.dropdown) .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-item:not(.dropdown) .nav-link:hover::after {
            width: 80%;
        }

        .nav-link.dropdown-toggle {
            white-space: nowrap;
        }

        .nav-link.dropdown-toggle::after {
            margin-left: 0.45rem;
            vertical-align: 0.15em;
        }

        .nav-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.35);
        }

        .dropdown-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .user-dropdown {
            min-width: 280px;
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .btn-outline-gradient {
            border: 2px solid #667eea;
            color: #667eea;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            background: transparent;
            transition: all 0.3s ease;
        }

        .btn-outline-gradient:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 3rem 0;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            padding: 2rem 0;
            margin-top: auto;
        }

        footer a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        footer a:hover {
            color: #f093fb;
            transform: translateX(5px);
        }

        /* Card Styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
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
                    ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="images/profile_pictures/<?= htmlspecialchars($display_picture, ENT_QUOTES, 'UTF-8') ?>" alt="Profile" class="nav-user-avatar">
                                <span class="d-none d-md-inline"><?= htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                                <li class="px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="images/profile_pictures/<?= $display_picture ?>" alt="Profile" class="dropdown-user-avatar">
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
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-gauge-high me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="edit_profile.php"><i class="fas fa-user-pen me-2"></i>Edit Profile</a></li>
                                <li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key me-2"></i>Change Password</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
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
        <div class="container-fluid px-5">
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

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>