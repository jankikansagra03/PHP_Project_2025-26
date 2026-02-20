<?php
$title = "Edit Profile - JK Store";
$active_sidebar = 'profile';
ob_start();
?>
<style>
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
</style>

<div class="card border-0 shadow-lg mb-4">
    <div class="card-body p-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h2 class="fw-bold mb-0" style="color: #667eea;">Edit Profile Information</h2>
            <a href="user_profile.php" class="btn btn-cancel btn-sm px-3"><i
                    class="fas fa-arrow-left me-2"></i>Back</a>
        </div>

        <form action="update_profile_action.php" method="POST" id="editProfileForm">
            <h5 class="fw-bold mb-3">Personal Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="firstName" class="form-label fw-semibold">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" value="John"
                        placeholder="Enter first name" data-validation="required min alphabetic" data-min="2">
                    <span id="firstName_error" class="text-danger small"></span>
                </div>
                <div class="col-md-6">
                    <label for="lastName" class="form-label fw-semibold">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" value="Doe"
                        placeholder="Enter last name" data-validation="required min alphabetic" data-min="2">
                    <span id="lastName_error" class="text-danger small"></span>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="john.doe@example.com"
                        readonly disabled style="opacity: 0.7;">
                    <small class="text-muted">Email cannot be changed</small>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="+1 234 567 890"
                        placeholder="Enter phone number" data-validation="required number min max" data-min="10"
                        data-max="10">
                    <span id="phone_error" class="text-danger small"></span>
                </div>
                <div class="col-md-6">
                    <label for="gender" class="form-label fw-semibold">Gender</label>
                    <select class="form-select" id="gender" name="gender" data-validation="required select">
                        <option value="male" selected>Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <span id="gender_error" class="text-danger small"></span>
                </div>
                <div class="col-md-6">
                    <label for="dob" class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" value="1990-01-01">
                </div>
            </div>

            <h5 class="fw-bold mb-3">Address</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label for="address" class="form-label fw-semibold">Street Address</label>
                    <textarea class="form-control p-3 bg-light border-0" id="address" name="address" rows="3"
                        data-validation="required">123 Street Name, City, New York, USA</textarea>
                    <span id="address_error" class="text-danger small"></span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-gradient btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>

    </div>
</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>