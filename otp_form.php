<?php
include_once 'db_config.php';
$title = "Verify OTP - JK Store";
if (isset($_POST['otp_btn'])) {
    if (isset($_SESSION['forgot_email'])) {
        $email = $_SESSION['forgot_email'];
        $otp = $_POST['otp'];
        $stmt = $con->prepare("CALL PasswordToken_Select(?)");
        if ($stmt == false) {
            die('Prepare failed: ' . $con->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result1 = $stmt->get_result();
        $users = $result1->fetch_all(MYSQLI_ASSOC);

        if ($result1->num_rows > 0) {
            $db_otp = $users[0]['otp'];
            if (!$db_otp) {
                setcookie('error', 'OTP has expired. Regenerate New OTP', time() + 5, '/');
?>
                <script>
                    window.location.href = 'forgot_password.php';
                </script>
                <?php
            } else {
                if ($otp == $db_otp) {
                ?>
                    <script>
                        window.location.href = 'reset_password.php';
                    </script>
                <?php
                } else {
                    setcookie('error', 'Incorrect OTP', time() + 5, '/');
                ?>
                    <script>
                        window.location.href = 'otp_form.php';
                    </script>
            <?php
                }
            }
        } else {
            setcookie('error', 'OTP has expired. Regenerate New OTP', time() + 5, '/');
            ?>
            <script>
                window.location.href = 'forgot_password.php';
            </script>
<?php
        }
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
                        <h2 class="fw-bold" style="color: #667eea;">Verify OTP</h2>
                        <p class="text-muted">Enter the OTP sent to your email.</p>
                    </div>

                    <form id="otpForm" method="post" action="otp_form.php">
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold">One-Time Password (OTP)</label>
                            <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter 6-digit OTP" data-validation="required number min max" data-min="6" data-max="6">
                            <span class="text-danger small" id="otp_error"></span>
                        </div>

                        <div class="text-danger small mb-3" id="timer"></div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-gradient btn-lg" name="otp_btn">Verify OTP</button>
                        </div>

                        <div class="text-center">
                            <button type="button" id="resend_otp" class="btn btn-outline-secondary">Resend OTP</button>
                        </div>

                        <script>
                            let timeLeft = 120;
                            const timerDisplay = document.getElementById('timer');
                            const resendButton = document.getElementById('resend_otp');

                            let countdown;

                            function isPageRefresh() {
                                return !!sessionStorage.getItem('otpTimer');
                            }

                            if (isPageRefresh()) {
                                timeLeft = parseInt(sessionStorage.getItem('otpTimer'), 10);
                            } else {
                                sessionStorage.setItem('otpTimer', 120);
                                timeLeft = 120;
                            }

                            function startCountdown() {
                                resendButton.disabled = true;
                                timerDisplay.innerHTML = `Resend OTP in ${timeLeft} seconds`;

                                countdown = setInterval(() => {
                                    if (timeLeft <= 0) {
                                        clearInterval(countdown);
                                        timerDisplay.innerHTML = "You can now resend the OTP.";
                                        resendButton.disabled = false;
                                        sessionStorage.removeItem('otpTimer');
                                    } else {
                                        timerDisplay.innerHTML = `Resend OTP in ${timeLeft} seconds`;
                                        timeLeft -= 1;
                                        sessionStorage.setItem('otpTimer', timeLeft);
                                    }
                                }, 1000);
                            }

                            if (timeLeft > 0) {
                                startCountdown();
                            } else {
                                resendButton.disabled = false;
                                timerDisplay.innerHTML = "You can now resend the OTP.";
                            }

                            resendButton.onclick = function(event) {
                                event.preventDefault();
                                clearInterval(countdown);
                                sessionStorage.setItem('otpTimer', 120);
                                window.location.href = 'resend_otp_forgot_password.php';
                            };
                        </script>
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