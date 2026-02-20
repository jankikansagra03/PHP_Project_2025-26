<?php
$title = "Change Password - JK Store";
$active_sidebar = 'password';
ob_start();
?>
<style>
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }

    .password-strength {
        height: 5px;
        border-radius: 3px;
        background: #e0e0e0;
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        transition: width 0.3s ease, background 0.3s ease;
        width: 0%;
    }
</style>

<div class="card border-0 shadow-lg mb-4">
    <div class="card-body p-5">
        <h2 class="fw-bold mb-2" style="color: #667eea;"><i class="fas fa-shield-alt me-2"></i>Security Settings</h2>
        <p class="text-muted mb-4">Manage your password and account security</p>



        <form action="change_password_action.php" method="POST" id="changePasswordForm">
            <div class="mb-4">
                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                    <input type="password" class="form-control" id="current_password" name="current_password"
                        placeholder="Enter current password" data-validation="required">
                    <button class="btn btn-outline-secondary border-0" type="button"
                        onclick="togglePassword('current_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <span id="current_password_error" class="text-danger small"></span>
            </div>

            <div class="mb-4">
                <label for="new_password" class="form-label fw-semibold">New Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control" id="confirm_password_confirm" name="new_password"
                        placeholder="Enter new password" data-validation="required strongPassword">
                    <button class="btn btn-outline-secondary border-0" type="button"
                        onclick="togglePassword('confirm_password_confirm')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <span id="new_password_error" class="text-danger small"></span>


            </div>

            <div class="mb-5">
                <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i
                            class="fas fa-check-circle text-muted"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        placeholder="Confirm new password" data-validation="required confirmPassword">
                    <button class="btn btn-outline-secondary border-0" type="button"
                        onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <span id="confirm_password_error" class="text-danger small"></span>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-gradient btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
                <button type="button" class="btn btn-cancel btn-lg"
                    onclick="window.location.href='user_dashboard.php'">
                    Cancel
                </button>
            </div>
        </form>

    </div>
</div>



<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = event.currentTarget.querySelector('i');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>