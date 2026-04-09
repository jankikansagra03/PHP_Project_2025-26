<?php
session_start();
$title = "Login - JK Store";
include_once 'db_config.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$base_path = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$google_callback_url = $scheme . '://' . $host . $base_path . '/google_login.php';

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
                            <script src="https://accounts.google.com/gsi/client" async defer> </script>

                            <div id="g_id_onload"
                                data-client_id="315515659521-jhjkoroi9n75d08l4vescphgnu6qdbb8.apps.googleusercontent.com"
                                data-context="signin"
                                data-ux-mode="popup"
                                data-login_uri="<?= htmlspecialchars($google_callback_url, ENT_QUOTES, 'UTF-8'); ?>"
                                data-auto_prompt="false">
                            </div>

                            <div class="g_id_signin">
                                <div class="g_id_signin" data-type="standard"></div>
                            </div>
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
