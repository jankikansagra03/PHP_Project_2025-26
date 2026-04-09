<?php
// Final step of forgot-password flow: set new password after OTP verification.
@session_start();
include_once 'db_config.php';

$title = "Reset Password - JK Store";

if (!isset($_SESSION['forgot_email'])) {
    // Missing session email means flow context is gone.
    setcookie('error', 'Session expired. Please start the reset process again.', time() + 5, '/');
    echo "<script>window.location.href = 'forgot_password.php';</script>";
    exit();
}

if (empty($_SESSION['forgot_otp_verified'])) {
    // Prevent password reset unless OTP has been verified.
    setcookie('error', 'Please verify your OTP before resetting the password.', time() + 5, '/');
    echo "<script>window.location.href = 'verify_otp.php';</script>";
    exit();
}

if (isset($_POST['reset_pwd_btn'])) {
    if (isset($_SESSION['forgot_email'])) {
        // Sanitize session/form values before composing SQL.
        $email = mysqli_real_escape_string($con, $_SESSION['forgot_email']);
        $new_password = mysqli_real_escape_string($con, $_POST['new_password'] ?? ($_POST['npwd'] ?? ''));

        // Update user password for the verified account.
        $update_sql = "UPDATE registration SET password = '$new_password' WHERE email = '$email'";
        if (mysqli_query($con, $update_sql)) {
            // Clear OTP token so it cannot be reused.
            $delete_sql = "DELETE FROM password_token WHERE email = '$email'";
            mysqli_query($con, $delete_sql);

            // Reset forgot-password session flags and return to login.
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
            // Password update failed.
            setcookie('error', 'Failed to update password. Please try again.', time() + 5, '/');
        }
    } else {
        // Defensive fallback when session email vanishes mid-request.
        setcookie('error', 'Session expired. Please start the reset process again.', time() + 5, '/');
    }

    // Return to reset page after failure paths.
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
                        <h2 class="fw-bold heading-primary">Reset Password</h2>
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