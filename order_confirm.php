<?php
include_once 'user_authentication.php';
$title = "Order Confirmation - JK Store";
$active_sidebar = 'cart';
ob_start();
?>
<style>
    .checkout-step-wrap {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .checkout-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .step-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #dee2e6;
        min-width: 30px;
    }

    .step-line.done {
        background: linear-gradient(90deg, #667eea, #764ba2);
    }

    .order-item-thumb {
        width: 62px;
        height: 62px;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 10px;
        flex-shrink: 0;
    }
</style>

<!-- Step Indicator -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-4">
        <div class="checkout-step-wrap">
            <div class="checkout-step">
                <div class="step-dot text-white" style="background: linear-gradient(135deg,#667eea,#764ba2);">
                    <i class="fas fa-check" style="font-size:0.75rem;"></i>
                </div>
                <span class="d-none d-sm-inline text-muted">Address</span>
            </div>
            <div class="step-line done mx-2"></div>
            <div class="checkout-step">
                <div class="step-dot text-white" style="background: linear-gradient(135deg,#667eea,#764ba2);">2</div>
                <span class="d-none d-sm-inline" style="color: #667eea;">Confirm</span>
            </div>
            <div class="step-line mx-2"></div>
            <div class="checkout-step">
                <div class="step-dot bg-light text-muted border">3</div>
                <span class="d-none d-sm-inline text-muted">Done</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Left: Delivery Address Summary -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Delivering To
                </h5>
                <div class="card border rounded-3 p-3 mb-3" style="border-color: #667eea !important; background: rgba(102,126,234,0.04);">
                    <h6 class="fw-bold mb-1">
                        <i class="fas fa-home me-2 text-primary"></i>Home
                        <span class="badge bg-primary ms-1" style="font-size:0.7rem;">Default</span>
                    </h6>
                    <p class="mb-1 fw-semibold">John Doe</p>
                    <p class="text-muted mb-1 small">123 Street Name, Apt 4B</p>
                    <p class="text-muted mb-1 small">New York, NY 10001, USA</p>
                    <p class="text-muted mb-0 small"><i class="fas fa-phone me-1"></i>+1 234 567 890</p>
                </div>
                <a href="checkout.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-edit me-1"></i>Change Address
                </a>
            </div>
        </div>
    </div>

    <!-- Right: Order Summary -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="fas fa-receipt me-2 text-primary"></i>Order Summary
                </h5>

                <!-- Items -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="images/product-1.jpg" class="order-item-thumb" alt="Headphones">
                        <div class="flex-grow-1">
                            <p class="fw-semibold mb-0">Wireless Noise-Canceling Headphones</p>
                            <span class="text-muted small">Color: Black &bull; Qty: 1</span>
                        </div>
                        <span class="fw-bold text-dark">$299.99</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="images/product-2.jpg" class="order-item-thumb" alt="Smart Watch">
                        <div class="flex-grow-1">
                            <p class="fw-semibold mb-0">Smart Fitness Watch Series 5</p>
                            <span class="text-muted small">Color: Silver &bull; Qty: 1</span>
                        </div>
                        <span class="fw-bold text-dark">$399.00</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="images/product-3.jpg" class="order-item-thumb" alt="Backpack">
                        <div class="flex-grow-1">
                            <p class="fw-semibold mb-0">Premium Leather Backpack</p>
                            <span class="text-muted small">Color: Brown &bull; Qty: 1</span>
                        </div>
                        <span class="fw-bold text-dark">$149.50</span>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal (3 items)</span>
                    <span class="fw-semibold">$848.49</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Shipping</span>
                    <span class="text-success fw-semibold">Free</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Tax (Estimated)</span>
                    <span class="fw-semibold">$0.00</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-4 text-primary">$848.49</span>
                </div>

                <button class="btn btn-gradient w-100 py-3 fw-bold shadow-sm mb-2">
                    <i class="fas fa-check-circle me-2"></i>Confirm Order
                </button>
                <a href="checkout.php" class="btn btn-outline-secondary w-100 py-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Address
                </a>
            </div>
        </div>
    </div>

</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>