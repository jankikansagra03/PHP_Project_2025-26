<?php
$title = "Contact Us - JK Store";
include 'db_config.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fname'])) {
    $name = mysqli_real_escape_string($con, $_POST['fname']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $message = mysqli_real_escape_string($con, $_POST['message']);

    $insert_query = "INSERT INTO contact_us (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
    if (mysqli_query($con, $insert_query)) {
        $success_msg = "Your message has been sent successfully. We will get back to you soon!";
    } else {
        $error_msg = "Failed to send message. Please try again later.";
    }
}

// Fetch contact info
$info_query = "SELECT * FROM contact_info LIMIT 1";
$info_res = mysqli_query($con, $info_query);
$contact_info = mysqli_fetch_assoc($info_res) ?: [];

$company_name = $contact_info['company_name'] ?? 'JK Store';
$tagline = $contact_info['tagline'] ?? '';
$disp_email = $contact_info['email'] ?? 'support@jkstore.com';
$disp_phone = $contact_info['phone'] ?? '+1 (555) 123-4567';
$alt_phone = $contact_info['alternate_phone'] ?? '';
$whatsapp = $contact_info['whatsapp_number'] ?? '';

$address_parts = [
    trim($contact_info['address'] ?? '123 Business Street, Suite 100'),
    trim($contact_info['city'] ?? 'New York'),
    trim(trim($contact_info['state'] ?? 'NY') . ' ' . trim($contact_info['postal_code'] ?? '10001')),
    trim($contact_info['country'] ?? '')
];
$address_full = implode(', ', array_filter($address_parts));

$socials = [
    'facebook' => ['url' => $contact_info['facebook_url'] ?? '', 'icon' => 'fab fa-facebook-f', 'bg' => 'bg-primary'],
    'twitter' => ['url' => $contact_info['twitter_url'] ?? '', 'icon' => 'fab fa-twitter', 'bg' => 'bg-info'],
    'instagram' => ['url' => $contact_info['instagram_url'] ?? '', 'icon' => 'fab fa-instagram', 'bg' => 'bg-danger'],
    'linkedin' => ['url' => $contact_info['linkedin_url'] ?? '', 'icon' => 'fab fa-linkedin-in', 'bg' => 'bg-primary'],
    'youtube' => ['url' => $contact_info['youtube_url'] ?? '', 'icon' => 'fab fa-youtube', 'bg' => 'bg-danger']
];

$map_url = $contact_info['map_embed_url'] ?? '';

ob_start();
?>

<div class="container">


    <div class="row mb-5 fade-in-up">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3 text-white">
                Get In Touch
            </h1>
            <p class="lead text-white">We'd love to hear from you. Send us a message and we'll respond as soon as
                possible.</p>
        </div>
    </div>

    <div class="row g-4 fade-in-up fade-delay-1">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-body p-5 d-flex flex-column">
                    <h3 class="fw-bold mb-4">Send us a Message</h3>
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="d-flex flex-column grow">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label for="name" class="form-label fw-semibold">Your Name</label>
                                <input type="text" class="form-control" id="name" name="fname"
                                    placeholder="Enter your name" data-validation="required min alphabetic"
                                    data-min="2">
                                <span id="fname_error"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Enter your email" data-validation="required email">
                                <span id="email_error"></span>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label for="email" class="form-label fw-semibold">Mobile Number</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Enter your email" data-validation="required email">
                                <span id="email_error"></span>
                            </div>

                        </div>
                        <div class="mb-4">
                            <label for="subject" class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject"
                                placeholder="Enter subject" data-validation="required min" data-min="3">
                            <span id="subject_error"></span>
                        </div>
                        <div class="mb-4 grow d-flex flex-column">
                            <label for="message" class="form-label fw-semibold">Message</label>
                            <textarea class="form-control grow" id="message" name="message"
                                placeholder="Enter your message" data-validation="required min" data-min="10"
                                style="min-height: 150px;"></textarea>
                            <span id="message_error"></span>
                        </div>
                        <button type="submit" class="btn btn-gradient btn-lg w-100 mt-auto">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg h-100 overflow-hidden">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-1 border-bottom pb-3"><?php echo htmlspecialchars($company_name); ?></h3>
                    <?php if (!empty($tagline)): ?>
                        <p class="text-muted mb-4 fst-italic"><?php echo htmlspecialchars($tagline); ?></p>
                    <?php endif; ?>

                    <p class="mb-4">Reach out to us directly via the details below, or visit us at our office.</p>

                    <ul class="list-unstyled mb-5">
                        <li class="d-flex mb-4 align-items-start fade-in-up fade-delay-2">
                            <div class="bg-light rounded-circle p-3 me-3 text-primary d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-map-marker-alt fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Address</h6>
                                <p class="mb-0 opacity-75"><?php echo htmlspecialchars($address_full); ?></p>
                            </div>
                        </li>

                        <li class="d-flex mb-4 align-items-start fade-in-up fade-delay-3">
                            <div class="bg-light rounded-circle p-3 me-3 text-primary d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-phone-alt fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Phone Number</h6>
                                <p class="mb-0 opacity-75">
                                    <a href="tel:<?php echo htmlspecialchars($disp_phone); ?>"
                                        class="text-decoration-none text-dark"><?php echo htmlspecialchars($disp_phone); ?></a>
                                </p>
                                <?php if (!empty($alt_phone)): ?>
                                    <p class="mb-0 opacity-75 small">Alt: <a
                                            href="tel:<?php echo htmlspecialchars($alt_phone); ?>"
                                            class="text-decoration-none text-dark"><?php echo htmlspecialchars($alt_phone); ?></a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </li>

                        <?php if (!empty($whatsapp)): ?>
                            <li class="d-flex mb-4 align-items-start fade-in-up fade-delay-4">
                                <div class="bg-light rounded-circle p-3 me-3 text-success d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 50px; height: 50px;">
                                    <i class="fab fa-whatsapp fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">WhatsApp</h6>
                                    <p class="mb-0 opacity-75">
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>"
                                            target="_blank"
                                            class="text-decoration-none text-dark"><?php echo htmlspecialchars($whatsapp); ?></a>
                                    </p>
                                </div>
                            </li>
                        <?php endif; ?>

                        <li class="d-flex mb-0 align-items-start fade-in-up fade-delay-5">
                            <div class="bg-light rounded-circle p-3 me-3 text-danger d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-envelope fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email Address</h6>
                                <p class="mb-0 opacity-75">
                                    <a href="mailto:<?php echo htmlspecialchars($disp_email); ?>"
                                        class="text-decoration-none text-dark"><?php echo htmlspecialchars($disp_email); ?></a>
                                </p>
                            </div>
                        </li>
                    </ul>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Connect With Us</h6>
                        <div class="d-flex gap-2">
                            <?php foreach ($socials as $network => $social): ?>
                                <?php if (!empty($social['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank"
                                        class="btn btn-light rounded-circle d-flex align-items-center justify-content-center text-dark shadow-sm border-2 border-white"
                                        style="width: 45px; height: 45px; transition: 0.3s;"
                                        onmouseover="this.classList.replace('btn-light', '<?php echo $social['bg']; ?>'); this.classList.replace('text-dark', 'text-white');"
                                        onmouseout="this.classList.replace('<?php echo $social['bg']; ?>', 'btn-light'); this.classList.replace('text-white', 'text-dark');">
                                        <i class="<?php echo $social['icon']; ?>"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($map_url)): ?>
                        <div class="rounded overflow-hidden shadow-sm border" style="height: 200px;">
                            <iframe src="<?php echo htmlspecialchars($map_url); ?>" width="100%" height="100%"
                                style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include 'layout.php';
?>