<?php
// Resend OTP handler for forgot-password flow.
@session_start();
include_once 'db_config.php';
include 'mailer.php';
include 'mail_content.php';

// This endpoint requires remembered email from prior forgot-password step.
if (isset($_SESSION['forgot_email'])) {
    $email = $_SESSION['forgot_email'];
    $safe_email = mysqli_real_escape_string($con, $email);

    // Load recipient display name for personalized email template.
    $user_query = "SELECT fullname FROM registration WHERE email = '$safe_email' LIMIT 1";
    $user_result = mysqli_query($con, $user_query);
    if ($user_result === false) {
        die("Query failed (User): " . mysqli_error($con));
    }

    $user_data = mysqli_fetch_assoc($user_result);
    mysqli_free_result($user_result);

    // If account no longer exists, clear flow state and restart.
    if (!$user_data) {
        unset($_SESSION['forgot_email'], $_SESSION['forgot_otp_verified']);
        setcookie('error', 'Account not found. Please start the reset process again.', time() + 5, "/");
        echo "<script>window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    // Fetch current attempt count from token table to enforce resend cap.
    $token_query = "SELECT otp_attempts FROM password_token WHERE email = '$safe_email' LIMIT 1";
    $result = mysqli_query($con, $token_query);
    if ($result === false) {
        die("Query failed (GetToken): " . mysqli_error($con));
    }

    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    // No token row means forgot-password flow was not initialized.
    if (!$row) {
        setcookie('error', "No password reset request found. Please try again.", time() + 5, "/");
        echo "<script>window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    $attempts = $row['otp_attempts'];

    // Enforce max resend attempts before forcing user to restart later.
    if ($attempts >= 3) {
        setcookie('error', "OTP resend limit reached. You can generate a new OTP after 24 hours.", time() + 5, "/");
?>
        <script>
            window.location.href = 'login.php';
        </script>
        <?php
        exit();
    }

    $email_time = date("Y-m-d H:i:s");
    $expiry_time = date("Y-m-d H:i:s", strtotime('+2 minutes'));
    $new_otp = rand(100000, 999999);

    // Increment attempts and rotate OTP + expiry metadata.
    $attempts += 1;
    $safe_email_time = mysqli_real_escape_string($con, $email_time);
    $safe_expiry_time = mysqli_real_escape_string($con, $expiry_time);
    $update_query = "UPDATE password_token SET otp = $new_otp, created_at = '$safe_email_time', expires_at = '$safe_expiry_time', otp_attempts = $attempts, last_resend = CURRENT_TIMESTAMP WHERE email = '$safe_email'";

    if (mysqli_query($con, $update_query)) {

        // Compose and send the fresh OTP email.
        $to = $email;
        $subject = "Reset password";
        $body = getForgotPasswordOtpEmailBody($new_otp, $user_data['fullname'] ?? 'User');

        if (sendEmail($to, $subject, $body, "")) {
            // Keep session email, clear verification flag to require fresh OTP check.
            unset($_SESSION['forgot_otp_verified']);
            setcookie("success", "A new OTP has been sent successfully.", time() + 5, "/");
        ?>
            <script>
                window.location.href = "verify_otp.php";
            </script>
        <?php
        } else {
            setcookie("error", "Error in sending the new OTP.", time() + 5, "/");
        ?>
            <script>
                window.location.href = "forgot_password.php";
            </script>
        <?php
        }
    } else {
        // Token update failed.
        setcookie("error", "Error updating token in database.", time() + 5, "/");
        ?>
        <script>
            window.location.href = "forgot_password.php";
        </script>
<?php
    }

    exit();
} else {
    // Direct access without session should not proceed.
    header("Location: login.php");
    exit();
}
?>