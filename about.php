<?php
$title = "About Us - JK Store";
ob_start();
?>

<div class="row mb-5 fade-in-up">
    <div class="col-12 text-center">
        <h1 class="display-4 fw-bold mb-3 text-white">
            About JK Store
        </h1>
        <p class="lead text-white">Delivering excellence since 2020</p>
    </div>
</div>

<div class="row mb-5 fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-lg-6 mb-4">
        <div class="card border-0 h-100">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-4">Our Story</h2>
                <p class="text-muted mb-3">Founded in 2020, JK Store has been at the forefront of innovation, delivering exceptional products and services to customers worldwide.</p>
                <p class="text-muted mb-3">Our mission is to empower individuals and businesses through cutting-edge solutions that make a real difference in their daily lives.</p>
                <p class="text-muted">With a team of dedicated professionals and a commitment to excellence, we continue to push boundaries and set new standards in our industry.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card border-0 h-100">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-4">Our Values</h2>
                <div class="mb-3">
                    <h5 class="fw-bold">Innovation</h5>
                    <p class="text-muted">We constantly evolve and adapt to meet changing needs.</p>
                </div>
                <div class="mb-3">
                    <h5 class="fw-bold">Quality</h5>
                    <p class="text-muted">Excellence is not an option, it's our standard.</p>
                </div>
                <div class="mb-3">
                    <h5 class="fw-bold">Customer Focus</h5>
                    <p class="text-muted">Your success is our success.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5 fade-in-up" style="animation-delay: 0.2s;">
    <div class="col-12">
        <div class="card border-0 text-center p-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <h2 class="fw-bold mb-4">Our Team</h2>
                <p class="lead mb-5">Meet the talented individuals behind JK Store</p>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <span class="display-6" style="color: #667eea;">JD</span>
                                </div>
                            </div>
                            <h5 class="fw-bold">John Doe</h5>
                            <p class="opacity-75">CEO & Founder</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <span class="display-6" style="color: #667eea;">JS</span>
                                </div>
                            </div>
                            <h5 class="fw-bold">Jane Smith</h5>
                            <p class="opacity-75">CTO</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <span class="display-6" style="color: #667eea;">MJ</span>
                                </div>
                            </div>
                            <h5 class="fw-bold">Mike Johnson</h5>
                            <p class="opacity-75">Head of Design</p>
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
?>