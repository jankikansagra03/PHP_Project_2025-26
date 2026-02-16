<?php
$title = "Privacy Policy - JK Store";
ob_start();
?>
<div class="container fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h1 class="fw-bold mb-4" style="color: #667eea;">Privacy Policy</h1>
                    <p class="text-muted">Last Updated: <?php echo date('F d, Y'); ?></p>
                    <hr class="mb-5">

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">1. Information We Collect</h3>
                        <p class="text-secondary">We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support.</p>
                    </div>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">2. How We Use Your Information</h3>
                        <p class="text-secondary">We use the information we collect to provide, maintain, and improve our services, process transactions, and communicate with you.</p>
                    </div>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">3. Information Sharing</h3>
                        <p class="text-secondary">We do not share your personal information with third parties except as described in this policy or with your consent.</p>
                    </div>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">4. Security</h3>
                        <p class="text-secondary">We take reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access, disclosure, alteration, and destruction.</p>
                    </div>

                    <div class="text-center mt-5">
                        <a href="index.php" class="btn btn-primary btn-gradient">Back to Home</a>
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