<?php
$title = "Home - Welcome to JK Store";
ob_start();
?>

<!-- Hero Section -->
<div class="row mb-5 fade-in-up">
    <div class="col-12">
        <div class="card border-0 text-center p-5">
            <div class="card-body">
                <h1 class="display-3 fw-bold mb-4" style="color: #667eea;">
                    Welcome to JK Store
                </h1>
                <p class="lead text-muted mb-4">
                    Discover amazing products and services tailored just for you. Experience excellence with every interaction.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="shop.php" class="btn btn-gradient btn-lg">Explore Shop</a>
                    <a href="about.php" class="btn btn-outline-gradient btn-lg">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100 border-0 text-center p-4 fade-in-up" style="animation-delay: 0.1s;">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-bolt fa-3x" style="color: #667eea;"></i>
                </div>
                <h4 class="fw-bold mb-3">Fast & Reliable</h4>
                <p class="text-muted">Lightning-fast performance with 99.9% uptime guarantee. Your satisfaction is our priority.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 text-center p-4 fade-in-up" style="animation-delay: 0.2s;">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-shield-alt fa-3x" style="color: #667eea;"></i>
                </div>
                <h4 class="fw-bold mb-3">Secure & Safe</h4>
                <p class="text-muted">Bank-level security with end-to-end encryption. Your data is always protected.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 text-center p-4 fade-in-up" style="animation-delay: 0.3s;">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-headset fa-3x" style="color: #667eea;"></i>
                </div>
                <h4 class="fw-bold mb-3">24/7 Support</h4>
                <p class="text-muted">Round-the-clock customer support ready to help you anytime, anywhere.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="row fade-in-up" style="animation-delay: 0.4s;">
    <div class="col-12">
        <div class="card border-0 text-center p-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
                <p class="lead mb-4">Join thousands of satisfied customers today!</p>
                <a href="register.php" class="btn btn-light btn-lg px-5">Sign Up Now</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>