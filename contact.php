<?php
$title = "Contact Us - JK Store";
ob_start();
?>

<div class="container">


    <div class="row mb-5 fade-in-up">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3 text-white">
                Get In Touch
            </h1>
            <p class="lead text-white">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </div>

    <div class="row g-4 fade-in-up" style="animation-delay: 0.1s;">
        <div class="col-lg-7">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4">Send us a Message</h3>
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="name" class="form-label fw-semibold">Your Name</label>
                                <input type="text" class="form-control" id="name" name="fname" placeholder="Enter your name" data-validation="required min alphabetic" data-min="2">
                                <span id="fname_error"></span>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" data-validation="required email">
                                <span id="email_error"></span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="subject" class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" data-validation="required min" data-min="3">
                            <span id="subject_error"></span>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="form-label fw-semibold">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" placeholder="Enter your message" data-validation="required min" data-min="10"></textarea>
                            <span id="message_error"></span>
                        </div>
                        <button type="submit" class="btn btn-gradient btn-lg w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4">Contact Info</h3>
                    <p class="mb-5">Fill out the form and our team will get back to you within 24 hours.</p>
                    <div class="mb-4">
                        <h5 class="fw-bold mb-2">Address</h5>
                        <p class="mb-4 opacity-75">123 Business Street, Suite 100, New York, NY 10001</p>
                        <h5 class="fw-bold mb-2">Phone</h5>
                        <p class="mb-4 opacity-75">+1 (555) 123-4567</p>
                        <h5 class="fw-bold mb-2">Email</h5>
                        <p class="mb-0 opacity-75">support@JK Store.com</p>
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