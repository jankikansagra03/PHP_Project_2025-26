<?php
$title = "Terms of Service - JK Store";
ob_start();
?>
<div class="container fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h1 class="fw-bold mb-4" style="color: #667eea;">Terms of Service</h1>
                    <p class="text-muted">Last Updated: <?php echo date('F d, Y'); ?></p>
                    <hr class="mb-5">

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">1. Acceptance of Terms</h3>
                        <p class="text-secondary">By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    </div>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">2. Use License</h3>
                        <p class="text-secondary">Permission is granted to temporarily download one copy of the materials (information or software) on JK Store's website for personal, non-commercial transitory viewing only.</p>
                    </div>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">3. Disclaimer</h3>
                        <p class="text-secondary">The materials on JK Store's website are provided on an 'as is' basis. JK Store makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                    </div>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">4. Limitations</h3>
                        <p class="text-secondary">In no event shall JK Store or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on JK Store's website.</p>
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