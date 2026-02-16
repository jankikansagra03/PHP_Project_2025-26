<?php
$title = "Product Detail - JK Store";
ob_start();
?>
<div class="container fade-in-up">
    <div class="row g-5">
        <!-- Product Images -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <img src="images/product-1.jpg" class="img-fluid rounded" alt="Product Main Image" style="background-color: #f8f9fa; min-height: 400px; object-fit: contain;">
            </div>
            <div class="row g-2">
                <div class="col-3">
                    <img src="images/product-1.jpg" class="img-fluid rounded border shadow-sm" alt="Thumbnail 1" style="cursor: pointer;">
                </div>
                <div class="col-3">
                    <img src="images/product-2.jpg" class="img-fluid rounded border-0 bg-light" alt="Thumbnail 2" style="cursor: pointer;">
                </div>
                <div class="col-3">
                    <img src="images/product-3.jpg" class="img-fluid rounded border-0 bg-light" alt="Thumbnail 3" style="cursor: pointer;">
                </div>
                <div class="col-3">
                    <img src="images/product-4.jpg" class="img-fluid rounded border-0 bg-light" alt="Thumbnail 4" style="cursor: pointer;">
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-6">
            <div class="mb-4">
                <span class="badge bg-primary bg-gradient rounded-pill px-3 py-2 mb-2">New Arrival</span>
                <h1 class="fw-bold mb-2 text-dark">Wireless Noise-Canceling Headphones</h1>
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-muted small border-end pe-3 me-3">4.5 Rating</span>
                    <span class="text-muted small">120 Reviews</span>
                </div>
                <h2 class="fw-bold text-primary mb-3">$299.99 <span class="text-muted text-decoration-line-through fs-5 ms-2">$350.00</span></h2>
                <p class="text-secondary lh-lg">Experience world-class noise cancellation, clearer calls, and longer battery life. Designed for comfort and built for sound, these headphones are perfect for travel, work, and play.</p>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold mb-3">Color</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark rounded-circle border-2 border-white shadow-sm p-3 position-relative" style="width: 40px; height: 40px;"></button>
                    <button class="btn btn-secondary rounded-circle border-0 p-3" style="width: 40px; height: 40px;"></button>
                    <button class="btn btn-light border rounded-circle p-3" style="width: 40px; height: 40px; background-color: #f0f0f0;"></button>
                </div>
            </div>

            <div class="mb-5">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button"><i class="fas fa-minus"></i></button>
                            <input type="text" class="form-control text-center" value="1">
                            <button class="btn btn-outline-secondary" type="button"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <button class="btn btn-gradient w-100 py-2 shadow-sm"><i class="fas fa-shopping-cart me-2"></i> Add to Cart</button>
                    </div>
                </div>
                <button class="btn btn-outline-secondary w-100 mt-3 py-2"><i class="far fa-heart me-2"></i> Add to Wishlist</button>
            </div>

            <!-- Additional Details -->
            <div class="accordion" id="productValues">
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingDesc">
                        <button class="accordion-button fw-bold bg-white text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDesc">
                            Description
                        </button>
                    </h2>
                    <div id="collapseDesc" class="accordion-collapse collapse show" data-bs-parent="#productValues">
                        <div class="accordion-body text-secondary">
                            Immerse yourself in music with our latest noise-canceling technology. Featuring up to 30 hours of battery life and quick charging capabilities.
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingSpecs">
                        <button class="accordion-button collapsed fw-bold bg-white text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecs">
                            Specifications
                        </button>
                    </h2>
                    <div id="collapseSpecs" class="accordion-collapse collapse" data-bs-parent="#productValues">
                        <div class="accordion-body text-secondary">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><strong>Weight:</strong> 250g</li>
                                <li class="mb-2"><strong>Battery Life:</strong> 30 Hours</li>
                                <li class="mb-2"><strong>Connectivity:</strong> Bluetooth 5.0</li>
                                <li><strong>Warranty:</strong> 1 Year</li>
                            </ul>
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