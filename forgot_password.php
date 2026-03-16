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

<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">Forgot Password?</h2>
                        <p class="text-muted">Enter your registered email to receive an OTP.</p>
                    </div>

                    <form id="forgotForm" method="post" action="forgot_password.php">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" data-validation="required email">
                            <span class="text-danger small" id="email_error"></span>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <i class="fas fa-circle-info me-2"></i>
                            OTP is valid for 2 minutes. Check spam/junk folder if needed.
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="send_otp" id="sendOtpButton">Send OTP</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Remember your password?
                                <a href="login.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Login</a>
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