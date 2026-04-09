<?php
$title = "My Profile - JK Store";
include_once 'user_authentication.php';
include_once 'db_config.php';

$email = $_SESSION['user'];
$query = "SELECT * FROM registration WHERE email='$email'";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);
$active_sidebar = 'profile';

if (isset($_POST['update_picture'])) {
    $user_email = $user_data['email'];
    $current_picture = $user_data['profile_picture'];
    $upload_dir = 'images/profile_pictures/';

    $new_profile_picture = uniqid() . $_FILES['profile_picture']['name'];
    $new_temp_location = $_FILES['profile_picture']['tmp_name'];

    $update = "update registration set profile_picture = '$new_profile_picture' where email='$user_email'";

    if (mysqli_query($con, $update)) {
        //delete old profile_picture

        unlink("images/profile_pictures/" . $current_picture);
        move_uploaded_file($new_temp_location, $upload_dir . $new_profile_picture);
        setcookie('success', "Profile Picture updated successfully", time() + 5);
    } else {
        setcookie('error', "Error in updating profile picture", time() + 5);
    }
?>
    <script>
        window.location.href = "profile.php";
    </script>
<?php

}
ob_start();
?>
<div class="profile-header-card bg-white mb-4">
    <!-- Cover Image -->
    <div class="profile-cover"></div>

    <!-- Profile Header Content -->
    <div class="text-center px-4 pb-4">
        <div class="profile-avatar-wrapper">
            <img src="images/profile_pictures/<?= $user_data['profile_picture'] ?>" alt="Profile" class="profile-avatar">
            <button class="btn-camera" data-bs-toggle="modal" data-bs-target="#editPictureModal"
                title="Change Profile Picture">
                <i class="fas fa-camera"></i>
            </button>
        </div>

        <h2 class="fw-bold mb-1"><?= $user_data['fullname'] ?></h2>
        <p class="text-muted mb-3"><i class="fas fa-envelope me-2"></i><?= $user_data['email'] ?></p>

        <div class="d-flex justify-content-center gap-2 mb-3">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                <i class="fas fa-star me-1"></i> Member since 2025
            </span>
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                <i class="fas fa-check-circle me-1"></i> Verified Account
            </span>
        </div>

        <div class="d-flex gap-2 justify-content-center mt-3">
            <a href="edit_profile.php" class="btn btn-gradient rounded-pill px-4 shadow-sm">
                <i class="fas fa-user-edit me-2"></i>Edit Profile
            </a>
            <a href="change_password.php" class="btn btn-outline-gradient rounded-pill px-4">
                <i class="fas fa-key me-2"></i>Change Password
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Personal Information -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white/50 backdrop-blur">
            <h4 class="section-title">Personal Information</h4>

            <div class="row g-4">
                <!-- First Name -->
                <div class="col-md-6 col-lg-6">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="detail-label">Fullname</p>
                                <p class="detail-value"><?= $user_data['fullname'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Name -->
                <div class="col-md-6 col-lg-6">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="detail-label">Email</p>
                                <p class="detail-value"><?= $user_data['email'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="col-md-6 col-lg-6">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <p class="detail-label">Phone Number</p>
                                <p class="detail-value text-break"><?= $user_data['mobile'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="col-md-6 col-lg-6">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <p class="detail-label">Gender</p>
                                <p class="detail-value"><?= $user_data['gender'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gender -->

            </div>
        </div>
    </div>
</div>

<!-- Edit Picture Modal -->
<div class="modal fade" id="editPictureModal" tabindex="-1" aria-labelledby="editPictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg modal-radius-20">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editPictureModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="overflow-hidden rounded-circle shadow-sm mx-auto profile-preview-wrap">
                                <img src="images/profile_pictures/<?= $user_data['profile_picture'] ?>" id="modalAvatarPreview" alt="Profile Picture"
                                    class="profile-preview-img">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="profile_picture" class="form-label fw-semibold">Choose New Picture</label>
                        <input type="file" class="form-control form-control-lg" id="profile_picture"
                            name="profile_picture" accept="image/*" onchange="previewModalImage(this)" data-validation="required fileType fileSize" data-filetype="jpg,jpeg,png" data-filesize="3">
                        <span id="profile_picture_error"></span>
                        <p class=" text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Allowed: *.jpeg,
                            *.jpg, *.png (Max 3MB)</p>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-gradient py-3 shadow-sm rounded-pill fw-bold" name="update_picture">
                            <i class="fas fa-upload me-2"></i>Upload Picture
                        </button>
                        <button type="button" class="btn btn-light py-3 rounded-pill fw-bold"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewModalImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('modalAvatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>