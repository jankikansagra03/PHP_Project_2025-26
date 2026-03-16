<?php
@session_start();
include_once 'db_config.php';
include 'mailer.php';
include 'mail_content.php';

if (isset($_SESSION['forgot_email'])) {
    $email = $_SESSION['forgot_email'];

    $stmt_user = $con->prepare("SELECT fullname FROM registration WHERE email = ? LIMIT 1");
    if ($stmt_user === false) {
        die("Prepare failed (User): " . $con->error);
    }

    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_result->free();
    $stmt_user->close();

    if (!$user_data) {
        unset($_SESSION['forgot_email'], $_SESSION['forgot_otp_verified']);
        setcookie('error', 'Account not found. Please start the reset process again.', time() + 5, "/");
        echo "<script>window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    $stmt_get = $con->prepare("SELECT otp_attempts FROM password_token WHERE email = ? LIMIT 1");
    if ($stmt_get === false) {
        die("Prepare failed (GetToken): " . $con->error);
    }

    $stmt_get->bind_param("s", $email);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();

    $result->free();
    $stmt_get->close();

    if (!$row) {
        setcookie('error', "No password reset request found. Please try again.", time() + 5, "/");
        echo "<script>window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    $attempts = $row['otp_attempts'];

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

    $stmt_update = $con->prepare("UPDATE password_token SET otp = ?, created_at = ?, expires_at = ?, otp_attempts = ?, last_resend = CURRENT_TIMESTAMP WHERE email = ?");
    if ($stmt_update === false) {
        die("Prepare failed (Update): " . $con->error);
    }
    $attempts += 1;
    $stmt_update->bind_param("issis", $new_otp, $email_time, $expiry_time, $attempts, $email);

    if ($stmt_update->execute()) {
        $stmt_update->close();

        $to = $email;
        $subject = "Reset password";
        $body = getForgotPasswordOtpEmailBody($new_otp, $user_data['fullname'] ?? 'User');

        if (sendEmail($to, $subject, $body, "")) {
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
        setcookie("error", "Error updating token in database.", time() + 5, "/");
        ?>
        <script>
            window.location.href = "forgot_password.php";
        </script>
<?php
    }

    exit();
} else {
    header("Location: login.php");
    exit();
}
?>