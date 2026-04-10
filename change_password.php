<?php
include 'user_authentication.php';
include_once 'db_config.php';
$user_email = $_SESSION['user'];
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $q = "select password from registration where email='$user_email'";
    $result = mysqli_query($con, $q);
    $data = mysqli_fetch_assoc($result);
    if ($current_password === $data['password']) {
        $update_query = "UPDATE registration SET password='$new_password' WHERE email='$user_email'";
        if (mysqli_query($con, $update_query)) {
            setcookie('success', "Password changed successfully", time() + 5);
        } else {
            setcookie('error', "Failed to change password. Please try again.", time() + 5);
        }
    } else {
        setcookie('error', "Current password is incorrect", time() + 5);
    }
?>
    <script>
        window.location.href = "change_password.php";
    </script>
<?php
    // Fetch current password from database
    $query = "SELECT password FROM registration WHERE email='$user_email'";
    $result = mysqli_query($con, $query);
    $user_data = mysqli_fetch_assoc($result);

    if ($current_password === $user_data['password']) {
        if ($new_password === $confirm_password) {
            // Update password in database
            $update_query = "UPDATE registration SET password='$new_password' WHERE email='$user_email'";
            if (mysqli_query($con, $update_query)) {
                setcookie('success', "Password changed successfully", time() + 5);
            } else {
                setcookie('error', "Failed to change password. Please try again.", time() + 5);
            }
        } else {
            setcookie('error', "New password and confirm password do not match", time() + 5);
        }
    } else {
        setcookie('error', "Current password is incorrect", time() + 5);
    }
}
$title = "Change Password - JK Store";
$active_sidebar = 'password';
ob_start();
?>
<div class="card border-0 shadow-lg mb-4">
    <div class="card-body p-5">
        <h2 class="fw-bold mb-2 heading-primary"><i class="fas fa-shield-alt me-2"></i>Security Settings</h2>
        <p class="text-muted mb-4">Manage your password and account security</p>

        <div class="alert alert-info border-0 shadow-sm mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Password Requirements:</strong> Minimum 8 characters, including uppercase, lowercase, number, and
            special character.
        </div>

        <form action="change_password.php" method="POST" id="changePasswordForm">
            <div class="mb-4">
                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                    <input type="password" class="form-control" id="current_password" name="current_password"
                        placeholder="Enter current password" data-validation="required">
                    <button class="btn btn-outline-secondary border-0" type="button"
                        onclick="togglePassword(this, 'current_password')">
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
                        onclick="togglePassword(this, 'confirm_password_confirm')">
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
                        onclick="togglePassword(this, 'confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <span id="confirm_password_error" class="text-danger small"></span>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="change_password" class="btn btn-gradient btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg"
                    onclick="window.location.href='dashboard.php'">
                    Cancel
                </button>
            </div>
        </form>

    </div>
</div>



<script>
    function togglePassword(btn, fieldId) {
        var $field = $('#' + fieldId);
        var $icon = $(btn).find('i');

        if ($field.attr('type') === 'password') {
            $field.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $field.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>