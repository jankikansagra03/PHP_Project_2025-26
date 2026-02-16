<?php
$title = "Reset Password - JK Store";
ob_start();
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">
                            Reset Password
                        </h2>
                        <p class="text-muted">Enter your new password below.</p>
                    </div>

                    <form action="reset_password_final_action.php" method="POST">
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control" id="confirm_password_confirm" name="new_password" placeholder="Enter new password" required data-validation="required strongPassword">
                            <span id="new_password_error" class="text-danger small"></span>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required data-validation="required confirmPassword">
                            <span id="confirm_password_error" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3">Reset Password</button>
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