<?php
@session_start();
include 'mailer.php';
include 'mail_content.php';
include_once 'db_config.php';

$title = "Forgot Password - JK Store";

$db_success = false;
$send_email = false;
$redirect_url = null;
$user_data = null;

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setcookie('error', 'Please enter a valid email address.', time() + 5, "/");
        $redirect_url = 'forgot_password.php';
    }

    if ($redirect_url === null) {
        $safe_email = mysqli_real_escape_string($con, $email);
        $user_query = "SELECT fullname FROM registration WHERE email = '$safe_email' LIMIT 1";
        $user_result = mysqli_query($con, $user_query);

        if ($user_result === false) {
            die("Query failed: " . mysqli_error($con));
        }

        $user_data = mysqli_fetch_assoc($user_result);
        mysqli_free_result($user_result);

        if (!$user_data) {
            setcookie('error', 'Email is not registered. Please enter a registered email address.', time() + 5, "/");
            $redirect_url = 'forgot_password.php';
        }
    }

    if ($redirect_url === null) {
        $token_query = "SELECT otp_attempts FROM password_token WHERE email = '$safe_email' LIMIT 1";
        $result = mysqli_query($con, $token_query);

        if ($result === false) {
            die("Query failed: " . mysqli_error($con));
        }

        $token_data = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        $new_otp = rand(100000, 999999);
        $email_time = date("Y-m-d H:i:s");
        $expiry_time = date("Y-m-d H:i:s", strtotime('+2 minutes'));
        $safe_email_time = mysqli_real_escape_string($con, $email_time);
        $safe_expiry_time = mysqli_real_escape_string($con, $expiry_time);

        if ($token_data) {
            $attempts = (int) $token_data['otp_attempts'];

            if ($attempts >= 3) {
                setcookie('error', "Maximum OTP limit reached. Please try again after 24 hours.", time() + 5, "/");
                $redirect_url = "login.php";
            } else {
                $attempts += 1;

                $update_query = "UPDATE password_token SET otp = $new_otp, created_at = '$safe_email_time', expires_at = '$safe_expiry_time', otp_attempts = $attempts, last_resend = CURRENT_TIMESTAMP WHERE email = '$safe_email'";

                if (mysqli_query($con, $update_query)) {
                    $db_success = true;
                    $send_email = true;
                }
            }
        } else {
            $attempts = 0;

            $insert_query = "INSERT INTO password_token (email, otp, created_at, expires_at, otp_attempts) VALUES ('$safe_email', $new_otp, '$safe_email_time', '$safe_expiry_time', $attempts)";

            if (mysqli_query($con, $insert_query)) {
                $db_success = true;
                $send_email = true;
            }
        }
    }

    if ($db_success && $send_email) {
        $subject = "Password Reset - OTP";
        $body = getForgotPasswordOtpEmailBody($new_otp, $user_data['fullname'] ?? 'User');

        if (sendEmail($email, $subject, $body, "")) {
            $_SESSION['forgot_email'] = $email;
            unset($_SESSION['forgot_otp_verified']);
            setcookie('success', 'OTP sent! Please check your email (and spam folder).', time() + 5, "/");
            $redirect_url = "verify_otp.php";
        } else {
            setcookie('error', 'Failed to send the email. Please try again.', time() + 5, "/");
            $redirect_url = "forgot_password.php";
        }
    } else if ($db_success == false && $redirect_url == null) {
        setcookie('error', 'Failed to update the database. Please try again.', time() + 5, "/");
        $redirect_url = "forgot_password.php";
    }

    if ($redirect_url) {
        echo "<script>window.location.href = '$redirect_url';</script>";
        exit();
    }
}

ob_start();
?>
<style>
    .forgot-shell {
        max-width: 1120px;
        margin: 0 auto;
    }

    .forgot-wrap {
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(24, 29, 63, 0.22);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.93);
    }

    .forgot-aside {
        position: relative;
        color: #fff;
        padding: 2.75rem 2.25rem;
        background:
            radial-gradient(circle at 15% 12%, rgba(255, 255, 255, 0.24), transparent 44%),
            radial-gradient(circle at 82% 24%, rgba(240, 147, 251, 0.25), transparent 38%),
            linear-gradient(165deg, #5f71e7 0%, #6d57c2 55%, #8c4fa8 100%);
    }

    .forgot-aside::after {
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

    .forgot-title {
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

    .forgot-form-pane {
        padding: 2.75rem 2.2rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(249, 250, 255, 0.93));
    }

    .forgot-heading {
        color: #4f62d8;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .forgot-form-pane .form-control {
        border-radius: 12px;
        border: 1px solid #d6dcfa;
        padding: 0.72rem 0.95rem;
        transition: all 0.2s ease;
    }

    .forgot-form-pane .form-control:focus {
        border-color: #7f90ef;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.18);
    }

    .forgot-form-pane .form-label {
        margin-bottom: 0.4rem;
        color: #2f3552;
    }

    .forgot-info {
        border: 1px solid #dbe3ff;
        border-radius: 12px;
        background: #f4f7ff;
        color: #33407a;
        padding: 0.9rem 1rem;
        margin-bottom: 1.25rem;
    }

    .auth-link {
        color: #5f71e7;
        font-weight: 600;
        text-decoration: none;
    }

    .auth-link:hover {
        color: #4b5ecf;
    }

    @media (max-width: 991.98px) {
        .forgot-aside {
            padding: 2rem 1.5rem;
        }

        .forgot-form-pane {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="container forgot-shell fade-in-up">
    <div class="forgot-wrap">
        <div class="row g-0">
            <div class="col-lg-4 d-none d-lg-block">
                <aside class="forgot-aside h-100">
                    <span class="brand-pill"><i class="fas fa-key"></i> Account Recovery</span>
                    <h2 class="forgot-title">Reset your password quickly and securely.</h2>
                    <p class="mt-3 mb-0 text-white-50">Enter your registered email and we will send a one-time passcode to continue.</p>

                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>OTP expires in 2 minutes for stronger protection.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-envelope-open-text"></i>
                            <span>Works with inbox and spam/junk folders.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-user-shield"></i>
                            <span>Only registered accounts can request a reset.</span>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="col-lg-8">
                <div class="forgot-form-pane">
                    <div class="mb-4 text-center text-lg-start">
                        <h3 class="forgot-heading">Forgot Password?</h3>
                        <p class="text-muted mb-0">Enter your registered email to receive an OTP.</p>
                    </div>

                    <form id="forgotForm" method="post" action="forgot_password.php">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" data-validation="required email">
                            <span class="text-danger small" id="email_error"></span>
                        </div>

                        <div class="forgot-info">
                            <i class="fas fa-circle-info me-2"></i>
                            OTP is valid for 2 minutes. Check spam/junk folder if needed.
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="send_otp" id="sendOtpButton">Send OTP</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Remember your password?
                                <a href="login.php" class="auth-link">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    $(document).ready(function() {
        $('#email').on('blur', function() {
            var email = $(this).val();
            if (email.length == 0) {
                $('#email_error').text('');
                $('#email').removeClass('is-invalid');
                $('#sendOtpButton').prop('disabled', false);
                return;
            }
            $.ajax({
                type: 'GET',
                url: 'check_duplicate_Email.php',
                data: {
                    email1: email
                },
                success: function(response) {
                    var responseTrimmed = response.trim();

                    if (responseTrimmed == 'false') {
                        $('#email_error').text('Email is not registered. Please enter a registered email address.').show();
                        $('#email').addClass('is-invalid');
                        $('#sendOtpButton').prop('disabled', true);
                    } else if (responseTrimmed == 'true') {
                        $('#email_error').text('').hide();
                        $('#email').removeClass('is-invalid');
                        $('#sendOtpButton').prop('disabled', false);
                    } else {
                        $('#email_error').text('Error validating email.').show();
                        $('#sendOtpButton').prop('disabled', true);
                    }
                }
            });
        });
    });
</script>