<?php
$title = "Shopping Cart - JK Store";
ob_start();
?>
<style>
.cart-item {
    transition: background 0.3s ease;
}

.cart-item:hover {
    background: rgba(102, 126, 234, 0.02);
}

.quantity-btn {
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<div class="container fade-in-up">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0 text-white">Shopping Cart <span class="fs-4 text-white">(3
                        items)</span></h2>
                <button class="btn text-white btn-sm rounded-pill" data-bs-toggle="modal"
                    data-bs-target="#clearCartModal">
                    <i class="fas fa-trash me-2"></i>Clear Cart
                </button>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <!-- Item 1 -->
                    <div class="cart-item p-4 border-bottom">
                        <div class="row align-items-center g-3">
                            <div class="col-md-2">
                                <img src="images/product-1.jpg" alt="Product" class="img-fluid rounded shadow-sm"
                                    style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-4">
                                <h5 class="fw-bold mb-1">Wireless Noise-Canceling Headphones</h5>
                                <p class="text-muted small mb-2">Color: Black</p>
                                <button class="btn btn-link text-danger p-0 small" data-bs-toggle="modal"
                                    data-bs-target="#removeItemModal">
                                    <i class="fas fa-trash-alt me-1"></i>Remove
                                </button>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm" style="width: 130px;">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="form-control text-center bg-white" value="1" readonly>
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <h5 class="fw-bold mb-0 text-dark">$299.99</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="cart-item p-4 border-bottom">
                        <div class="row align-items-center g-3">
                            <div class="col-md-2">
                                <img src="images/product-2.jpg" alt="Product" class="img-fluid rounded shadow-sm"
                                    style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-4">
                                <h5 class="fw-bold mb-1">Smart Fitness Watch Series 5</h5>
                                <p class="text-muted small mb-2">Color: Silver | Size: 44mm</p>
                                <button class="btn btn-link text-danger p-0 small" data-bs-toggle="modal"
                                    data-bs-target="#removeItemModal">
                                    <i class="fas fa-trash-alt me-1"></i>Remove
                                </button>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm" style="width: 130px;">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="form-control text-center bg-white" value="1" readonly>
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <h5 class="fw-bold mb-0 text-dark">$399.00</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="cart-item p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-2">
                                <img src="images/product-3.jpg" alt="Product" class="img-fluid rounded shadow-sm"
                                    style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-4">
                                <h5 class="fw-bold mb-1">Premium Leather Backpack</h5>
                                <p class="text-muted small mb-2">Color: Brown</p>
                                <button class="btn btn-link text-danger p-0 small" data-bs-toggle="modal"
                                    data-bs-target="#removeItemModal">
                                    <i class="fas fa-trash-alt me-1"></i>Remove
                                </button>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm" style="width: 130px;">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="form-control text-center bg-white" value="1" readonly>
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <h5 class="fw-bold mb-0 text-dark">$149.50</h5>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="shop.php" class="btn btn-outline-secondary rounded-pill px-4"><i
                        class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-lg sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Order Summary</h4>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal (3 items)</span>
                        <span class="fw-semibold">$848.49</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success fw-semibold">Free</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax (Estimated)</span>
                        <span class="fw-semibold">$0.00</span>
                    </div>
                    <hr class="my-4">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-4 text-primary">$848.49</span>
                    </div>

                    <div class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Promo code">
                            <button class="btn btn-secondary px-4" type="button">Apply</button>
                        </div>
                    </div>

                    <button class="btn btn-gradient w-100 py-3 fw-bold shadow-sm mb-2" data-bs-toggle="modal"
                        data-bs-target="#checkoutModal">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </button>
                    <p class="text-center text-muted small mb-0">
                        <i class="fas fa-shield-alt me-1"></i>Secure checkout
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Item Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-circle fa-4x text-warning"></i>
                </div>
                <h4 class="fw-bold mb-3">Remove Item?</h4>
                <p class="text-muted mb-4">Are you sure you want to remove this item from your cart?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Remove Item</button>
                    <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Cart Modal -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-trash-alt fa-4x text-danger"></i>
                </div>
                <h4 class="fw-bold mb-3">Clear Entire Cart?</h4>
                <p class="text-muted mb-4">This will remove all items from your cart. This action cannot be undone.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Clear Cart</button>
                    <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Please select a delivery address to continue</p>
                <div class="d-grid gap-2">
                    <a href="saved_addresses.php" class="btn btn-gradient py-3">
                        <i class="fas fa-map-marker-alt me-2"></i>Select Delivery Address
                    </a>
                    <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>