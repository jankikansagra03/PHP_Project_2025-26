<?php
$title = "Register - JK Store";
include_once 'db_config.php';
include_once 'mailer.php';
ob_start();
?>
<div class="container register-shell fade-in-up">
    <div class="register-wrap">
        <div class="row g-0">
            <div class="col-lg-4 d-none d-lg-block">
                <aside class="register-aside h-100">
                    <span class="brand-pill"><i class="fas fa-sparkles"></i> JK Store Community</span>
                    <h2 class="register-title">Build your shopping profile in under a minute.</h2>
                    <p class="mt-3 mb-0 text-white-50">Create your account to unlock wishlists, order tracking, and exclusive member offers.</p>

                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-bolt"></i>
                            <span>Faster checkout with your saved details.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-heart"></i>
                            <span>Secure profile management and password controls.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-gift"></i>
                            <span>Access to member-only deals and launch offers.</span>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="col-lg-8">
                <div class="register-form-pane">
                    <div class="mb-4 text-center text-lg-start">
                        <h3 class="register-heading">Create Account</h3>
                        <p class="text-muted mb-0">Join JK Store and start shopping smarter.</p>
                    </div>

                    <form action="register.php" method="post" id="regform" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label fw-semibold">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="John" data-validation="required min alphabetic max" data-min="2" data-max="20">
                                <span id="firstName_error" class="text-danger"></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label fw-semibold">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Doe" data-validation="required min alphabetic" data-min="2">
                                <span id="lastName_error" class="text-danger"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="john.doe@example.com" data-validation="required email">
                            <span id="email_error"></span>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your 10-digit number" data-validation="required number min max" data-min="10" data-max="10">
                            <span id="phone_error"></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="confirmPassword_confirm" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" id="confirmPassword_confirm" name="password" placeholder="Create a strong password" data-validation="required strongPassword">
                                <span id="password_error"></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" class="form-control" id="password" name="confirmPassword" placeholder="Re-enter your password" data-validation="required confirmPassword">
                                <span id="confirmPassword_error"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-semibold">Gender</label>
                                <select class="form-select" id="gender" name="gender" data-validation="required select">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <span id="gender_error"></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                                <input type="file" name="profile_picture" id="profile_picture" class="form-control" data-validation="required fileSize fileType" data-filesize="100" data-filetype="jpg,jpeg,png">
                                <span id="profile_picture_error"></span>
                            </div>
                        </div>

                        <div class="mb-4 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" data-validation="required">
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms_of_service.php" class="legal-link">Terms & Conditions</a> and <a href="privacy_policy.php" class="legal-link">Privacy Policy</a>
                                </label>
                            </div>
                            <span id="terms_error"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="reg_btn">Create Account</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Already have an account? <a href="login.php" class="auth-switch-link">Login</a></p>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-3">Or register with</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary flex-fill social-btn">
                                <i class="fab fa-google"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary flex-fill social-btn">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary flex-fill social-btn">
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
if (isset($_POST['reg_btn'])) {
    $fname = $_POST['firstName'];
    $lname = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $profile_picture = uniqid() . $_FILES['profile_picture']['name'];
    $tmp_name = $_FILES['profile_picture']['tmp_name'];
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $fullname = $fname . " " . $lname;
    $token = bin2hex(random_bytes(15));
    $insert_query = "INSERT INTO `registration`(`fullname`, `email`, `password`, `mobile`, `gender`, `profile_picture`,`token`) VALUES ('$fullname','$email','$password',$phone,'$gender','$profile_picture','$token')";
    echo $insert_query;

    if (mysqli_query($con, $insert_query)) {
        move_uploaded_file($tmp_name, $upload_dir . $profile_picture);
        $to = $email;
        $subject = "Email Verification - JK Store";
        $body = "<h2>Welcome to JK Store, $fullname!</h2>
        <p>Thank you for registering an account with us. Please click the link below to verify your email address and activate your account:</p>

        <a href='http://localhost/2025_practice/PHP_Project_2025-26/verify_email.php?token=" . $token . "&em=" . $email . "' style='display:inline-block; padding:10px 20px; background-color:#667eea; color:#fff; text-decoration:none; border-radius:5px;'>
        Click here to verify email
        </a>
        ";
        if (sendEmail($to, $subject, $body, "")) {

            setcookie("success", "Registration successful! Please check your email to verify your account.", time() + 5, "/");
        } else {
            setcookie("error", "error in sending mail", time() + 5);
        }
?>
        <script>
            window.location.href = "login.php";
        </script>
    <?php
    } else {
        setcookie("error", "Registration failed! Please try again.", time() + 5, "/");
    ?>
        <script>
            window.location.href = "login.php";
        </script>
<?php
    }
}
?>
