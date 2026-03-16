<?php
@session_start();
include_once 'db_config.php';

$title = "Reset Password - JK Store";

if (!isset($_SESSION['forgot_email'])) {
    setcookie('error', 'Session expired. Please start the reset process again.', time() + 5, '/');
    echo "<script>window.location.href = 'forgot_password.php';</script>";
    exit();
}

if (empty($_SESSION['forgot_otp_verified'])) {
    setcookie('error', 'Please verify your OTP before resetting the password.', time() + 5, '/');
    echo "<script>window.location.href = 'verify_otp.php';</script>";
    exit();
}

if (isset($_POST['reset_pwd_btn'])) {
    if (isset($_SESSION['forgot_email'])) {
        $email = $_SESSION['forgot_email'];
        $new_password = $_POST['new_password'] ?? ($_POST['npwd'] ?? '');

        $stmt = $con->prepare("UPDATE registration SET password = ? WHERE email = ?");
        if ($stmt == false) {
            die('Prepare failed: ' . $con->error);
        }

        $stmt->bind_param("ss", $new_password, $email);

        if ($stmt->execute()) {
            $stmt->close();

            $stmt_del = $con->prepare("DELETE FROM password_token WHERE email = ?");
            if ($stmt_del == false) {
                die('Prepare failed (Delete): ' . $con->error);
            }

            $stmt_del->bind_param("s", $email);
            $stmt_del->execute();
            $stmt_del->close();
            // flush_stored_results($con);

            setcookie('success', 'Password has been reset successfully. You can now log in.', time() + 5, '/');
            unset($_SESSION['forgot_email']);
            unset($_SESSION['forgot_otp_verified']);
?>
            <script>
                sessionStorage.removeItem('otpTimer');
                window.location.href = 'login.php';
            </script>
<?php
            exit();
        } else {
            setcookie('error', 'Failed to update password. Please try again.', time() + 5, '/');
        }
        $stmt->close();
    } else {
        setcookie('error', 'Session expired. Please start the reset process again.', time() + 5, '/');
    }

    echo "<script>window.location.href = 'reset_password.php';</script>";
    exit();
}

ob_start();
?>

<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">Reset Password</h2>
                        <p class="text-muted">Create a new secure password for your account.</p>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <i class="fas fa-shield-alt me-2"></i>
                        Use at least 8 characters with uppercase, lowercase, number, and special character.
                    </div>

                    <form id="reset_password_form" method="post" action="reset_password.php">
                        <div class="mb-4">
                            <label for="confirm_password_confirm" class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control" id="confirm_password_confirm" name="new_password"
                                placeholder="Enter new password" data-validation="required strongPassword">
                            <span id="new_password_error" class="text-danger small"></span>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                placeholder="Confirm new password" data-validation="required confirmPassword">
                            <span id="confirm_password_error" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg" name="reset_pwd_btn">Reset Password</button>
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