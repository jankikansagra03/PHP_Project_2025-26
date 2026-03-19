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
                            <label for="otp" class="form-label fw-semibold">OTP Code</label>
                            <!-- 6 single-character fields for better OTP entry UX -->
                            <div class="d-flex justify-content-between gap-2">
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                            </div>
                            <!-- Backend reads this single value as the submitted OTP -->
                            <input type="hidden" name="otp" id="otp_full">
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
    // Cache frequently used elements.
    const inputs = [...document.querySelectorAll('.otp-digit')];
    const otpFull = document.getElementById('otp_full');
    const verifyOtpForm = document.getElementById('verifyOtpForm');
    const timerDisplay = document.getElementById('timer');
    const resendButton = document.getElementById('resend_otp');
    const TIMER_STORAGE_KEY = 'otpTimerEndAt';
    const OTP_DURATION_MS = 120000;

    // Reuse existing timer in session or start a fresh 2-minute window.
    let timerEndAt = Number(sessionStorage.getItem(TIMER_STORAGE_KEY));
    if (!timerEndAt || timerEndAt <= Date.now()) {
        timerEndAt = Date.now() + OTP_DURATION_MS;
        sessionStorage.setItem(TIMER_STORAGE_KEY, String(timerEndAt));
    }

    // Build one OTP string from six input boxes.
    const syncOtpValue = () => {
        otpFull.value = inputs.map((input) => input.value).join('');
    };

    // Update countdown label and resend button state.
    const updateTimer = () => {
        const secondsLeft = Math.max(0, Math.ceil((timerEndAt - Date.now()) / 1000));
        resendButton.disabled = secondsLeft > 0;
        timerDisplay.textContent = secondsLeft ?
            `Regenerate OTP in ${secondsLeft} seconds` :
            'You can now regenerate OTP.';

        if (!secondsLeft) sessionStorage.removeItem(TIMER_STORAGE_KEY);
    };

    // Start countdown updates immediately and then every second.
    updateTimer();
    const countdown = setInterval(() => {
        updateTimer();
        if (!resendButton.disabled) clearInterval(countdown);
    }, 1000);

    // Input behavior: numeric only, auto-next, backspace-prev, and paste support.
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);
            if (e.target.value && index < inputs.length - 1) inputs[index + 1].focus();
            syncOtpValue();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) inputs[index - 1].focus();
        });

        input.addEventListener('paste', (e) => {
            const pastedOtp = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, inputs.length);

            if (!pastedOtp) return;

            e.preventDefault();
            inputs.forEach((field, fieldIndex) => {
                field.value = pastedOtp[fieldIndex] || '';
            });
            syncOtpValue();
            inputs[Math.min(pastedOtp.length, inputs.length) - 1].focus();
        });
    });

    // Ensure hidden OTP is synced just before form submission.
    verifyOtpForm.addEventListener('submit', syncOtpValue);

    // Allow resend only after timer expires.
    resendButton.addEventListener('click', (event) => {
        event.preventDefault();
        if (resendButton.disabled) return;
        sessionStorage.removeItem(TIMER_STORAGE_KEY);
        window.location.href = 'resend_otp_forgot_password.php';
    });
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>