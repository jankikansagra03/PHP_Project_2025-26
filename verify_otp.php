<?php
// OTP verification step in forgot-password flow.
@session_start();
include_once 'db_config.php';

$title = "Verify OTP - JK Store";

if (!isset($_SESSION['forgot_email'])) {
    // Block access when forgot-password flow is not initialized.
    setcookie('error', 'Please request a password reset OTP first.', time() + 5, '/');
    echo "<script>window.location.href = 'forgot_password.php';</script>";
    exit();
}

if (isset($_POST['verify_otp_btn'])) {
    // Normalize OTP to digits only and validate expected length.
    $email = mysqli_real_escape_string($con, $_SESSION['forgot_email']);
    $otp = preg_replace('/\D/', '', $_POST['otp'] ?? '');

    if (strlen($otp) !== 6) {
        setcookie('error', 'Please enter the 6-digit OTP sent to your email.', time() + 5, '/');
        echo "<script>window.location.href = 'verify_otp.php';</script>";
        exit();
    }

    $sql = "SELECT otp, expires_at FROM password_token WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($con, $sql);
    if ($result === false) {
        die('Query failed: ' . mysqli_error($con));
    }
    $token_data = $result->fetch_assoc();
    $result->free();

    // Token row must exist for the session email.
    if (!$token_data) {
        setcookie('error', 'No OTP request found. Please request a new OTP.', time() + 5, '/');
        echo "<script>window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    // Reject expired OTP and require a fresh one.
    if (strtotime($token_data['expires_at']) < time()) {
        unset($_SESSION['forgot_otp_verified']);
        setcookie('error', 'OTP expired. Please request a new OTP.', time() + 5, '/');
        echo "<script>window.location.href = 'verify_otp.php';</script>";
        exit();
    }

    // Reject incorrect OTP values.
    if ((string) $token_data['otp'] !== $otp) {
        unset($_SESSION['forgot_otp_verified']);
        setcookie('error', 'Invalid OTP. Please try again.', time() + 5, '/');
        echo "<script>window.location.href = 'verify_otp.php';</script>";
        exit();
    }

    // Mark OTP check as verified so reset-password page can proceed.
    $_SESSION['forgot_otp_verified'] = true;
    echo "<script>window.location.href = 'reset_password.php';</script>";
    exit();
}

ob_start();
?>
<!-- OTP verification card UI -->
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">
                            Verify OTP
                        </h2>
                        <p class="text-muted">Enter the 6-digit code sent to your email.</p>
                    </div>

                    <!-- Submits the combined 6-digit OTP via hidden input -->
                    <form action="verify_otp.php" method="POST" id="verifyOtpForm">
                        <div class="mb-4">
                            <label for="otp_input" class="form-label fw-semibold">OTP Code</label>
                            <input type="text" class="form-control text-center fs-5" id="otp_input" name="otp" maxlength="6" inputmode="numeric" placeholder="Enter 6-digit OTP" autocomplete="one-time-code" required>
                            <span id="otp_error" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="verify_otp_btn">Verify</button>

                        <!-- Countdown text to show when resend becomes available -->
                        <div class="text-danger small mb-3" id="timer"></div>

                        <div class="text-center">
                            <button type="button" id="resend_otp" class="btn btn-outline-secondary" disabled>Regenerate OTP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const otpInput = document.getElementById('otp_input');      
    const timerDisplay = document.getElementById('timer');
    const resendButton = document.getElementById('resend_otp');

    let timeLeft = Number(sessionStorage.getItem('otpTimeLeft')) || 60;

    // Allow digits only.   
    otpInput.addEventListener('input', () => {
        otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
    });

    const countdown = setInterval(() => {
        timeLeft--;
        sessionStorage.setItem('otpTimeLeft', timeLeft);
        resendButton.disabled = timeLeft > 0;
        timerDisplay.textContent = timeLeft > 0 ?
            `Regenerate OTP in ${timeLeft} seconds` :
            'You can now regenerate OTP.';
        if (timeLeft <= 0) {
            sessionStorage.removeItem('otpTimeLeft');
            clearInterval(countdown);
        }
    }, 1000);

    resendButton.addEventListener('click', (event) => {
        event.preventDefault();
        if (resendButton.disabled) return;
        window.location.href = 'resend_otp_forgot_password.php';
    });
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>