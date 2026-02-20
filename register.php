<?php
include_once 'db_config.php';
$title = "Register - JK Store";
ob_start();
?>
<!-- <script src="js/jquery.js"></script> -->
<script>
    // $(document).ready(function() {
    //     $('#regform').submit(function(e) {
    //         var fname = $('#firstName').val();
    //         if (fname == "") {
    //             $('#fname_error').text("Firstname is required")
    //             $('#fname_error').addClass('text-danger')
    //             $('#firstName').addClass("is-invalid")
    //             var validate_fname = false;

    //         } else {
    //             if (fname.length < 3) {
    //                 $('#fname_error').text("Minimum length is 3 characters")
    //                 $('#fname_error').addClass('text-danger')
    //                 $('#firstName').addClass("is-invalid")
    //                 var validate_fname = false;
    //             } else {
    //                 fname_regex = /^[a-zA-Z/s]+$/;
    //                 if (fname_regex.test(fname)) {
    //                     $('#fname_error').text("")
    //                     $('#firstName').addClass("is-valid")
    //                     $('#firstName').removeClass("is-invalid");
    //                     validate_fname = true;

    //                 } else {
    //                     $('#fname_error').text("Name must conatin only letters")
    //                     $('#fname_error').addClass('text-danger')
    //                     $('#firstName').addClass("is-invalid")
    //                     validate_fname = false;

    //                 }
    //             }
    //         }
    //         var lname = $('#lastName').val();
    //         if (lname == "") {
    //             $('#lname_error').text("Lasstname is required")
    //             $('#lname_error').addClass('text-danger')
    //             $('#lastName').addClass("is-invalid")
    //             var validate_lname = false;

    //         } else {
    //             if (lname.length < 3) {
    //                 $('#lname_error').text("Minimum length is 3 characters")
    //                 $('#lname_error').addClass('text-danger')
    //                 $('#lastName').addClass("is-invalid")
    //                 var validate_lname = false;
    //             } else {
    //                 lname_regex = /^[a-zA-Z]+$/;
    //                 if (lname_regex.test(lname)) {
    //                     $('#lname_error').text("")
    //                     $('#lastName').removeClass("is-invalid");
    //                     $('#lastName').addClass("is-valid")

    //                     validate_lname = true;

    //                 } else {
    //                 $('#lname_error').text("Name must conatin only letters")
    //                 $('#lname_error').addClass('text-danger')
    //                 $('#lastName').addClass("is-invalid")
    //                 validate_lname = false;

    //             }
    //         }
    //     }
    //     if (validate_fname == false || validate_lname == false) {
    //         e.preventDefault();
    //     }
    // });
    // })
</script>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-8 col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #667eea;">
                            Create Account
                        </h2>
                        <p class="text-muted">Join us today and get started</p>
                    </div>

                    <form action="register.php" method="post" id="regform" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label for="firstName" class="form-label fw-semibold">First Name</label>
                                <input type="text" class="form-control  " id="firstName" name="firstName" placeholder="John" data-validation="required min alphabetic max" data-min="2" data-max="20">
                                <span id="firstName_error" class="text-danger"> </span>
                            </div>


                            <div class="col-lg-6 mb-4">
                                <label for="lastName" class="form-label fw-semibold">Last Name</label>
                                <input type="text" class="form-control  " id="lastName" name="lastName" placeholder="Doe" data-validation="required min alphabetic" data-min="2">
                                <span id="lastName_error" class="text-danger"> </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="text" class="form-control  " id="email" name="email" placeholder="john.doe@example.com" data-validation="required email">
                            <span id="email_error"></span>
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="text" class="form-control  " id="phone" name="phone" placeholder="Enter your phone number" data-validation="required number min max" data-min="10" data-max="10">
                            <span id="phone_error"></span>
                        </div>

                        <div class="mb-4">
                            <label for="confirmPassword_confirm" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="confirmPassword_confirm" name="password" placeholder="Create a strong password" data-validation="required strongPassword">
                            <span id="password_error"></span>

                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control  " id="password" name="confirmPassword" placeholder="Re-enter your password" data-validation="required confirmPassword">
                            <span id="confirmPassword_error"></span>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label for="gender" class="form-label fw-semibold">Gender</label>
                                <select class="form-select" id="gender" name="gender" data-validation="required select">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <span id="gender_error"></span>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                                <input type="file" name="profile_picture" id="profile_picture" class="form-control" data-validation="required fileSize fileType" data-filesize="100" data-filetype="jpg,jpeg,png">
                                <span id="profile_picture_error"></span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" data-validation="required">
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms_of_service.php" class="text-decoration-none" style="color: #667eea;">Terms & Conditions</a> and <a href="privacy_policy.php" class="text-decoration-none" style="color: #667eea;">Privacy Policy</a>
                                </label>
                            </div>
                            <span id="terms_error"></span>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3" name="reg_btn">Create Account</button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Login</a></p>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-3">Or register with</p>
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

if (isset($_POST['reg_btn'])) {
    $fname = $_POST['firstName'];
    $lname = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $profile_picture = $_FILES['profile_picture']['name'];
    $tmp_name = $_FILES['profile_picture']['tmp_name'];
    $upload_dir = "uploads/";
    $address = "Rajkot";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir);
    }
    $fullname = $fname . " " . $lname;
    $token = uniqid();
    $insert_query = "INSERT INTO `registration`(`fullname`, `email`, `password`, `gender`, `mobile`, `profile_picture`, `address`,`token`) values ('$fullname','$email','$password','$gender',$phone,'$profile_picture','$address','$token')";
    // echo $insert_query;

    if (mysqli_query($con, $insert_query)) {
        move_uploaded_file($tmp_name, $upload_dir . $profile_picture);
        echo "<script>alert('Registration successful');</script>";
    } else {
        echo "<script>alert('Error in registration');</script>";
    }
}
?>