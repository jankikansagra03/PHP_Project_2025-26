<?php
$title = "Login - JK Store";
ob_start();
?>
<div class="container">


    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">
                            Welcome Back
                        </h2>
                        <p class="text-muted">Login to your account</p>
                    </div>

                    <form action="test.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="text" class="form-control  " id="email" name="email" placeholder="Enter your email" data-validation="required email">
                            <span id="email_error"></span>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control  " id="password" name="password" placeholder="Enter your password" data-validation="required">
                            <span id="password_error"></span>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="forgot_password.php" class="text-decoration-none" style="color: #667eea;">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3">Login</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Sign Up</a></p>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-3">Or login with</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-outline-secondary flex-fill">
                                <i class="fab fa-google"></i>
                            </button>
                            <button class="btn btn-outline-secondary flex-fill">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button class="btn btn-outline-secondary flex-fill">
                                <i class="fab fa-github"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>