<?php
$title = "Forgot Password - JK Store";
ob_start();
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">
                            Forgot Password?
                        </h2>
                        <p class="text-muted">Enter your email to reset your password.</p>
                    </div>

                    <form action="verify_otp.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required data-validation="required email">
                            <span id="email_error" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3">Send Reset Link</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Remember your password? <a href="login.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Login</a></p>
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