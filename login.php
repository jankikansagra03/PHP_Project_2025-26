<?php
session_start();
$title = "Login - JK Store";
include_once 'db_config.php';
if (isset($_POST['login_btn'])) {
    $em = $_POST['email'];
    $pwd = $_POST['password'];
    $q = "select * from registration where email='$em' and password='$pwd'";
    $result = mysqli_query($con, $q);
    $count = mysqli_num_rows($result);
    if ($count == 1) {
        $user_data = mysqli_fetch_assoc($result);
        if ($user_data['status'] == 'Active' || $user_data['status'] == 'active') {
            if ($user_data['role'] == 'Admin' || $user_data['role'] == 'admin') {
                $_SESSION['admin'] = $user_data['email'];
                setcookie('success', "Login Successfull", time() + 5);  ?>
                <script>
                    window.location.href = "admin_dashboard.php";
                </script>
            <?php
            } else {
                $_SESSION['user'] = $user_data['email'];
                setcookie('success', "Login Successfull", time() + 5);  ?>
                <script>
                    window.location.href = "dashboard.php";
                </script>
            <?php
            }
        } else {
            setcookie("error", "Email address is not verified. kindly verify your email address", time() + 5);  ?>
            <script>
                window.location.href = "login.php";
            </script>
        <?php
        }
    } else {
        setcookie("error", "Incorrect username or password", time() + 5);
        ?>
        <script>
            window.location.href = "login.php";
        </script>
<?php
    }
}
ob_start();
?>
<style>
    .login-shell {
        max-width: 1120px;
        margin: 0 auto;
    }

    .login-wrap {
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(24, 29, 63, 0.22);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.93);
    }

    .login-aside {
        position: relative;
        color: #fff;
        padding: 2.75rem 2.25rem;
        background:
            radial-gradient(circle at 15% 15%, rgba(255, 255, 255, 0.24), transparent 44%),
            radial-gradient(circle at 85% 20%, rgba(240, 147, 251, 0.25), transparent 38%),
            linear-gradient(165deg, #5f71e7 0%, #6d57c2 55%, #8c4fa8 100%);
    }

    .login-aside::after {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        right: -60px;
        bottom: -80px;
        background: rgba(255, 255, 255, 0.1);
        filter: blur(2px);
    }

    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 999px;
        padding: 0.45rem 0.95rem;
        background: rgba(255, 255, 255, 0.18);
        font-size: 0.86rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 1.4rem;
    }

    .login-title {
        font-size: clamp(1.8rem, 2.2vw, 2.35rem);
        line-height: 1.2;
        font-weight: 700;
    }

    .feature-list {
        margin-top: 2rem;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.92);
    }

    .feature-item i {
        margin-top: 0.15rem;
        color: #ffd66b;
    }

    .login-form-pane {
        padding: 2.75rem 2.2rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(249, 250, 255, 0.93));
    }

    .login-heading {
        color: #4f62d8;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .login-form-pane .form-control {
        border-radius: 12px;
        border: 1px solid #d6dcfa;
        padding: 0.72rem 0.95rem;
        transition: all 0.2s ease;
    }

    .login-form-pane .form-control:focus {
        border-color: #7f90ef;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.18);
    }

    .login-form-pane .form-label {
        margin-bottom: 0.4rem;
        color: #2f3552;
    }

    .auth-link {
        color: #5f71e7;
        font-weight: 600;
        text-decoration: none;
    }

    .auth-link:hover {
        color: #4b5ecf;
    }

    .social-btn {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .social-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(60, 74, 141, 0.15);
    }

    @media (max-width: 991.98px) {
        .login-aside {
            padding: 2rem 1.5rem;
        }

        .login-form-pane {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="container login-shell fade-in-up">
    <div class="login-wrap">
        <div class="row g-0">
            <div class="col-lg-4 d-none d-lg-block">
                <aside class="login-aside h-100">
                    <span class="brand-pill"><i class="fas fa-lock"></i> JK Store Access</span>
                    <h2 class="login-title">Welcome back to your shopping dashboard.</h2>
                    <p class="mt-3 mb-0 text-white-50">Sign in to continue where you left off and manage your orders in one place.</p>

                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-box-open"></i>
                            <span>Track current and previous orders instantly.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-heart"></i>
                            <span>Access saved wishlist items across devices.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-badge-check"></i>
                            <span>Enjoy a faster, verified, and secure checkout.</span>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="col-lg-8">
                <div class="login-form-pane">
                    <div class="mb-4 text-center text-lg-start">
                        <h3 class="login-heading">Welcome Back</h3>
                        <p class="text-muted mb-0">Login to your account and continue shopping.</p>
                    </div>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" data-validation="required email">
                            <span id="email_error"></span>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" data-validation="required">
                            <span id="password_error"></span>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="forgot_password.php" class="auth-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="login_btn">Login</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Don't have an account? <a href="register.php" class="auth-link">Sign Up</a></p>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-3">Or login with</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary flex-fill social-btn">
                                <i class="fab fa-google"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary flex-fill social-btn">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary flex-fill social-btn">
                                <i class="fab fa-github"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>