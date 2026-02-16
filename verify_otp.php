<?php
$title = "Verify OTP - JK Store";
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
                        <p class="text-muted">Enter the 4-digit code sent to your email.</p>
                    </div>

                    <form action="reset_password.php" method="POST">
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold">OTP Code</label>
                            <div class="d-flex justify-content-between gap-2">
                                <input type="text" class="form-control text-center fs-4" maxlength="1" required>
                                <input type="text" class="form-control text-center fs-4" maxlength="1" required>
                                <input type="text" class="form-control text-center fs-4" maxlength="1" required>
                                <input type="text" class="form-control text-center fs-4" maxlength="1" required>
                            </div>
                            <!-- Hidden input to store full OTP if needed, or handle individual inputs via JS -->
                            <input type="hidden" name="otp" id="otp_full">
                            <span id="otp_error" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3">Verify</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Didn't receive code? <a href="#" class="text-decoration-none fw-semibold" style="color: #667eea;">Resend</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple script to auto-focus next input
    const inputs = document.querySelectorAll('.form-control.text-center');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0) {
                if (index > 0) {
                    inputs[index - 1].focus();
                }
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>