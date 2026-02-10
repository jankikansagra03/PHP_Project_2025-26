<?php
$title = "Register - JK Store";
ob_start();
?>
<script src="js/jquery.js"></script>
<script>
    $(document).ready(function() {


        $('#regform').submit(function(e) {

            var fname = $('#firstName').val();
            if (fname == "") {
                $('#fname_error').text("Firstname is required")
                $('#fname_error').addClass('text-danger')
                $('#firstName').addClass("is-invalid")
                var validate_fname = false;

            } else {
                if (fname.length < 3) {
                    $('#fname_error').text("Minimum length is 3 characters")
                    $('#fname_error').addClass('text-danger')
                    $('#firstName').addClass("is-invalid")
                    var validate_fname = false;
                } else {
                    fname_regex = /^[a-zA-Z]+$/;
                    if (fname_regex.test(fname)) {
                        $('#fname_error').text("")

                        $('#firstName').addClass("is-valid")
                        validate_fname = true;

                    } else {
                        $('#fname_error').text("NAme must conatin only letters")
                        $('#fname_error').addClass('text-danger')
                        $('#firstName').addClass("is-invalid")
                        validate_fname = false;

                    }
                }
            }

            if (validate_fname == false) {
                e.preventDefault();
            }


        });
    })
</script>
<div class="row justify-content-center fade-in-up">
    <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold" style="color: #667eea;">
                        Create Account
                    </h2>
                    <p class="text-muted">Join us today and get started</p>
                </div>

                <form action="" method="get" id="regform" onsubmit="validateForm()">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="firstName" class="form-label fw-semibold">First Name</label>
                            <input type="text" class="form-control form-control-lg" id="firstName" name="firstName" placeholder="John">
                            <span id="fname_error"> </span>
                        </div>


                        <div class="col-md-6 mb-4">
                            <label for="lastName" class="form-label fw-semibold">Last Name</label>
                            <input type="text" class="form-control form-control-lg" id="lastName" name="lastName" placeholder="Doe">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="text" class="form-control form-control-lg" id="email" name="email" placeholder="john.doe@example.com">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Create a strong password">
                        <div class="form-text">Password must be at least 8 characters long</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control form-control-lg" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password">
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms">
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none" style="color: #667eea;">Terms & Conditions</a> and <a href="#" class="text-decoration-none" style="color: #667eea;">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gradient w-100 btn-lg mb-3">Create Account</button>

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

<?php
$content = ob_get_clean();
include 'layout.php';
?>