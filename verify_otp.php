<?php
@session_start();
include_once 'db_config.php';

$title = "Verify OTP - JK Store";

if (!isset($_SESSION['forgot_email'])) {
    setcookie('error', 'Please request a password reset OTP first.', time() + 5, '/');
    echo "<script>window.location.href = 'forgot_password.php';</script>";
    exit();
}

if (isset($_POST['verify_otp_btn'])) {
    $email = $_SESSION['forgot_email'];
    $otp = preg_replace('/\D/', '', $_POST['otp'] ?? '');

    if (strlen($otp) !== 6) {
        setcookie('error', 'Please enter the 6-digit OTP sent to your email.', time() + 5, '/');
        echo "<script>window.location.href = 'verify_otp.php';</script>";
        exit();
    }

    $stmt = $con->prepare("SELECT otp, expires_at FROM password_token WHERE email = ? LIMIT 1");
    if ($stmt === false) {
        die('Prepare failed: ' . $con->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $token_data = $result->fetch_assoc();
    $result->free();
    $stmt->close();

    if (!$token_data) {
        setcookie('error', 'No OTP request found. Please request a new OTP.', time() + 5, '/');
        echo "<script>window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    if (strtotime($token_data['expires_at']) < time()) {
        unset($_SESSION['forgot_otp_verified']);
        setcookie('error', 'OTP expired. Please request a new OTP.', time() + 5, '/');
        echo "<script>window.location.href = 'verify_otp.php';</script>";
        exit();
    }

    if ((string) $token_data['otp'] !== $otp) {
        unset($_SESSION['forgot_otp_verified']);
        setcookie('error', 'Invalid OTP. Please try again.', time() + 5, '/');
        echo "<script>window.location.href = 'verify_otp.php';</script>";
        exit();
    }

    $_SESSION['forgot_otp_verified'] = true;
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
                        <h2 class="fw-bold" style="color: #667eea;">
                            Verify OTP
                        </h2>
                        <p class="text-muted">Enter the 6-digit code sent to your email.</p>
                    </div>

                    <form action="verify_otp.php" method="POST" id="verifyOtpForm">
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold">OTP Code</label>
                            <div class="d-flex justify-content-between gap-2">
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                                <input type="text" class="form-control text-center fs-4 otp-digit" maxlength="1" inputmode="numeric" required>
                            </div>
                            <input type="hidden" name="otp" id="otp_full">
                            <span id="otp_error" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="verify_otp_btn">Verify</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Didn't receive code? <a href="resend_otp_forgot_password.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Resend</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const inputs = document.querySelectorAll('.otp-digit');
    const otpFull = document.getElementById('otp_full');
    const verifyOtpForm = document.getElementById('verifyOtpForm');

    function syncOtpValue() {
        otpFull.value = Array.from(inputs).map((input) => input.value).join('');
    }

    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '');
            if (e.target.value.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
            syncOtpValue();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0) {
                if (index > 0) {
                    inputs[index - 1].focus();
                }
            }
        });

        input.addEventListener('paste', (e) => {
            const pastedOtp = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, inputs.length);

            if (!pastedOtp) {
                return;
            }

            e.preventDefault();
            inputs.forEach((field, fieldIndex) => {
                field.value = pastedOtp[fieldIndex] || '';
            });
            syncOtpValue();
            const nextIndex = Math.min(pastedOtp.length, inputs.length - 1);
            inputs[nextIndex].focus();
        });
    });

    verifyOtpForm.addEventListener('submit', () => {
        syncOtpValue();
    });
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>