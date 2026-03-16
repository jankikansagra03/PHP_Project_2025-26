<?php
include_once 'user_authentication.php';
$title = "Checkout - JK Store";
$active_sidebar = 'cart';
ob_start();
?>
<style>
    .address-select-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #dee2e6 !important;
        border-radius: 14px;
    }

    .address-select-card:hover {
        border-color: #667eea !important;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.15);
    }

    .address-select-card.selected {
        border-color: #667eea !important;
        background: rgba(102, 126, 234, 0.04);
    }

    .address-select-card.selected .select-indicator {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-color: transparent;
    }

    .select-indicator {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

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
</style>

<!-- Step Indicator -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-4">
        <div class="checkout-step-wrap">
            <div class="checkout-step">
                <div class="step-dot text-white" style="background: linear-gradient(135deg,#667eea,#764ba2);">1</div>
                <span class="d-none d-sm-inline" style="color: #667eea;">Address</span>
            </div>
            <div class="step-line mx-2"></div>
            <div class="checkout-step">
                <div class="step-dot bg-light text-muted border">2</div>
                <span class="d-none d-sm-inline text-muted">Payment</span>
            </div>
            <div class="step-line mx-2"></div>
            <div class="checkout-step">
                <div class="step-dot bg-light text-muted border">3</div>
                <span class="d-none d-sm-inline text-muted">Confirm</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Address Selection -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>Select Delivery Address
                    </h5>
                    <button class="btn btn-sm btn-gradient rounded-pill px-3 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="fas fa-plus me-1"></i>New Address
                    </button>
                </div>

                <div class="d-flex flex-column gap-3" id="addressList">

                    <!-- Address 1 (selected by default) -->
                    <div class="card address-select-card selected" onclick="selectAddress(this)">
                        <div class="card-body p-3 d-flex gap-3 align-items-start">
                            <div class="select-indicator mt-1">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            <i class="fas fa-home me-2 text-primary"></i>Home
                                            <span class="badge bg-primary ms-1" style="font-size:0.7rem;">Default</span>
                                        </h6>
                                        <p class="mb-0 fw-semibold small">John Doe</p>
                                        <p class="text-muted mb-0 small">123 Street Name, Apt 4B, New York, NY 10001</p>
                                        <p class="text-muted mb-0 small"><i class="fas fa-phone me-1"></i>+1 234 567 890</p>
                                    </div>
                                    <button class="btn btn-sm btn-link text-primary p-0 ms-2"
                                        onclick="event.stopPropagation();"
                                        data-bs-toggle="modal" data-bs-target="#editAddressModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address 2 -->
                    <div class="card address-select-card" onclick="selectAddress(this)">
                        <div class="card-body p-3 d-flex gap-3 align-items-start">
                            <div class="select-indicator mt-1"></div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            <i class="fas fa-building me-2 text-success"></i>Office
                                        </h6>
                                        <p class="mb-0 fw-semibold small">John Doe</p>
                                        <p class="text-muted mb-0 small">456 Business Blvd, Suite 200, San Francisco, CA 94107</p>
                                        <p class="text-muted mb-0 small"><i class="fas fa-phone me-1"></i>+1 987 654 321</p>
                                    </div>
                                    <button class="btn btn-sm btn-link text-primary p-0 ms-2"
                                        onclick="event.stopPropagation();"
                                        data-bs-toggle="modal" data-bs-target="#editAddressModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="cart.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i>Back to Cart
                    </a>
                    <a href="order_confirm.php" class="btn btn-gradient rounded-pill px-4 shadow-sm">
                        Continue <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Add New Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="addressLabel" class="form-label fw-semibold">Address Label</label>
                            <select class="form-select" id="addressLabel" name="addressLabel">
                                <option value="home">Home</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fullName" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="fullName"
                                placeholder="Enter full name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                placeholder="Enter phone number" required>
                        </div>
                        <div class="col-md-6">
                            <label for="zipCode" class="form-label fw-semibold">ZIP Code</label>
                            <input type="text" class="form-control" id="zipCode" name="zipCode"
                                placeholder="Enter ZIP code" required>
                        </div>
                        <div class="col-12">
                            <label for="streetAddress" class="form-label fw-semibold">Street Address</label>
                            <textarea class="form-control" id="streetAddress" name="streetAddress" rows="2"
                                placeholder="Enter street address" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label fw-semibold">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                placeholder="Enter city" required>
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label fw-semibold">State</label>
                            <input type="text" class="form-control" id="state" name="state"
                                placeholder="Enter state" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="setDefault" name="setDefault">
                                <label class="form-check-label" for="setDefault">Set as default address</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-gradient py-3">
                            <i class="fas fa-save me-2"></i>Save Address
                        </button>
                        <button type="button" class="btn btn-outline-secondary py-3"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Address Modal -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editAddressLabel" class="form-label fw-semibold">Address Label</label>
                            <select class="form-select" id="editAddressLabel" name="addressLabel">
                                <option value="home" selected>Home</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editFullName" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" name="fullName"
                                value="John Doe" placeholder="Enter full name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editPhone" class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone"
                                value="+1 234 567 890" placeholder="Enter phone number" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editZipCode" class="form-label fw-semibold">ZIP Code</label>
                            <input type="text" class="form-control" id="editZipCode" name="zipCode"
                                value="10001" placeholder="Enter ZIP code" required>
                        </div>
                        <div class="col-12">
                            <label for="editStreetAddress" class="form-label fw-semibold">Street Address</label>
                            <textarea class="form-control" id="editStreetAddress" name="streetAddress" rows="2"
                                placeholder="Enter street address" required>123 Street Name, Apt 4B</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="editCity" class="form-label fw-semibold">City</label>
                            <input type="text" class="form-control" id="editCity" name="city"
                                value="New York" placeholder="Enter city" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editState" class="form-label fw-semibold">State</label>
                            <input type="text" class="form-control" id="editState" name="state"
                                value="NY" placeholder="Enter state" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-gradient py-3">
                            <i class="fas fa-save me-2"></i>Update Address
                        </button>
                        <button type="button" class="btn btn-outline-secondary py-3"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function selectAddress(card) {
        document.querySelectorAll('.address-select-card').forEach(function(c) {
            c.classList.remove('selected');
            var ind = c.querySelector('.select-indicator');
            if (ind) ind.innerHTML = '';
        });
        card.classList.add('selected');
        var indicator = card.querySelector('.select-indicator');
        if (indicator) indicator.innerHTML = '<i class="fas fa-check"></i>';
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>