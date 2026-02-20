<?php
$title = "My Wishlist - JK Store";
$active_sidebar = 'wishlist';
ob_start();
?>
<style>
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0 text-white">My Wishlist <span class="text-white fs-4">(4 items)</span></h2>
    <div class="d-flex gap-2">
        <button class="btn btn-sm rounded-pill text-white" data-bs-toggle="modal" data-bs-target="#clearWishlistModal">
            <i class="fas fa-trash me-2"></i>Clear All
        </button>
        <a href="shop.php" class="btn btn-sm rounded-pill text-white">
            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Item 1 -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 product-card">
            <div class="position-relative">
                <img src="images/product-1.jpg" class="card-img-top p-4 bg-light" alt="Product"
                    style="height: 250px; object-fit: contain;">
                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-3 rounded-circle shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#removeWishlistModal" title="Remove from wishlist">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-light btn-sm position-absolute top-0 start-0 m-3 rounded-circle shadow-sm"
                    title="Move to cart">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-2 text-truncate">Wireless Headphones</h5>
                <div class="mb-2 text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <span class="text-muted ms-1">(4.5)</span>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                    <span class="text-muted text-decoration-line-through small">$350.00</span>
                    <span class="fw-bold text-primary fs-5">$299.99</span>
                </div>
                <button class="btn btn-gradient w-100 rounded-pill">
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Item 2 -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 product-card">
            <div class="position-relative">
                <img src="images/product-2.jpg" class="card-img-top p-4 bg-light" alt="Product"
                    style="height: 250px; object-fit: contain;">
                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-3 rounded-circle shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#removeWishlistModal" title="Remove from wishlist">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-light btn-sm position-absolute top-0 start-0 m-3 rounded-circle shadow-sm"
                    title="Move to cart">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-2 text-truncate">Smart Fitness Watch</h5>
                <div class="mb-2 text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <span class="text-muted ms-1">(5.0)</span>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                    <span class="fw-bold text-primary fs-5">$399.00</span>
                </div>
                <button class="btn btn-gradient w-100 rounded-pill">
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Item 3 -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 product-card">
            <div class="position-relative">
                <span class="badge bg-danger position-absolute top-0 start-0 m-3 shadow-sm"
                    style="z-index: 1;">Sale</span>
                <img src="images/product-3.jpg" class="card-img-top p-4 bg-light" alt="Product"
                    style="height: 250px; object-fit: contain;">
                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-3 rounded-circle shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#removeWishlistModal" title="Remove from wishlist">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-2 text-truncate">Leather Backpack</h5>
                <div class="mb-2 text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="far fa-star"></i>
                    <span class="text-muted ms-1">(4.0)</span>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                    <span class="text-muted text-decoration-line-through small">$200.00</span>
                    <span class="fw-bold text-primary fs-5">$149.50</span>
                </div>
                <button class="btn btn-gradient w-100 rounded-pill">
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Item 4 -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 product-card">
            <div class="position-relative">
                <img src="images/product-4.jpg" class="card-img-top p-4 bg-light" alt="Product"
                    style="height: 250px; object-fit: contain;">
                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-3 rounded-circle shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#removeWishlistModal" title="Remove from wishlist">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-light btn-sm position-absolute top-0 start-0 m-3 rounded-circle shadow-sm"
                    title="Move to cart">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-2 text-truncate">Running Shoes</h5>
                <div class="mb-2 text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <i class="far fa-star"></i>
                    <span class="text-muted ms-1">(3.5)</span>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                    <span class="fw-bold text-primary fs-5">$89.99</span>
                </div>
                <button class="btn btn-gradient w-100 rounded-pill">
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Remove from Wishlist Modal -->
<div class="modal fade" id="removeWishlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-heart-broken fa-4x text-danger"></i>
                </div>
                <h4 class="fw-bold mb-3">Remove from Wishlist?</h4>
                <p class="text-muted mb-4">Are you sure you want to remove this item from your wishlist?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Remove</button>
                    <button type="button" class="btn btn-cancel py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Wishlist Modal -->
<div class="modal fade" id="clearWishlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                </div>
                <h4 class="fw-bold mb-3">Clear Entire Wishlist?</h4>
                <p class="text-muted mb-4">This will remove all items from your wishlist. This action cannot be undone.
                </p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Clear All</button>
                    <button type="button" class="btn btn-cancel py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>