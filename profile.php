<?php
$title = "My Profile - JK Store";
$active_sidebar = 'profile';
ob_start();
?>
<style>
.profile-header-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.0));
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    border-radius: 20px;
    overflow: hidden;
}

.profile-cover {
    height: 200px;
    background: var(--primary-gradient);
    position: relative;
}

.profile-cover::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.3));
}

.profile-avatar-wrapper {
    position: relative;
    margin-top: -75px;
    margin-bottom: 1rem;
    display: inline-block;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    object-fit: cover;
    background: #fff;
}

.btn-camera {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-gradient);
    color: white;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.btn-camera:hover {
    transform: scale(1.1);
}

.info-card {
    background: rgba(255, 255, 255, 0.7);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.15);
}

.info-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.detail-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.detail-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0;
}

.section-title {
    position: relative;
    padding-bottom: 10px;
    margin-bottom: 25px;
    color: #2d3748;
    font-weight: 700;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--primary-gradient);
    border-radius: 3px;
}
</style>

<div class="profile-header-card bg-white mb-4">
    <!-- Cover Image -->
    <div class="profile-cover"></div>

    <!-- Profile Header Content -->
    <div class="text-center px-4 pb-4">
        <div class="profile-avatar-wrapper">
            <img src="images/default-avatar.png" alt="Profile" class="profile-avatar">
            <button class="btn-camera" data-bs-toggle="modal" data-bs-target="#editPictureModal"
                title="Change Profile Picture">
                <i class="fas fa-camera"></i>
            </button>
        </div>

        <h2 class="fw-bold mb-1">John Doe</h2>
        <p class="text-muted mb-3"><i class="fas fa-envelope me-2"></i>john.doe@example.com</p>

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
                <div class="col-md-6 col-lg-4">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="detail-label">First Name</p>
                                <p class="detail-value">John</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Name -->
                <div class="col-md-6 col-lg-4">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="detail-label">Last Name</p>
                                <p class="detail-value">Doe</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="col-md-6 col-lg-4">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <p class="detail-label">Email Address</p>
                                <p class="detail-value text-break">john.doe@example.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="col-md-6 col-lg-4">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <p class="detail-label">Phone Number</p>
                                <p class="detail-value">+1 234 567 890</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gender -->
                <div class="col-md-6 col-lg-4">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-venus-mars"></i>
                            </div>
                            <div>
                                <p class="detail-label">Gender</p>
                                <p class="detail-value">Male</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address (Keeping existing functionality) -->
                <div class="col-md-6 col-lg-4">
                    <div class="info-card p-4 h-100">
                        <div class="d-flex align-items-center mb-0">
                            <div class="info-icon me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="detail-label">Address</p>
                                <p class="detail-value text-break" style="word-wrap: break-word;">123 Street Name, City,
                                    New York, USA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Picture Modal -->
<div class="modal fade" id="editPictureModal" tabindex="-1" aria-labelledby="editPictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editPictureModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="update_profile_picture.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="overflow-hidden rounded-circle shadow-sm mx-auto"
                                style="width: 150px; height: 150px; border: 4px solid #f8f9fa;">
                                <img src="images/default-avatar.png" id="modalAvatarPreview" alt="Profile Picture"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="profile_picture" class="form-label fw-semibold">Choose New Picture</label>
                        <input type="file" class="form-control form-control-lg" id="profile_picture"
                            name="profile_picture" accept="image/*" onchange="previewModalImage(this)">
                        <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Allowed: *.jpeg,
                            *.jpg, *.png (Max 3MB)</p>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-gradient py-3 shadow-sm rounded-pill fw-bold">
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