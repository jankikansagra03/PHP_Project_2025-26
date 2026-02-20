<?php
$title = "Saved Addresses - JK Store";
$active_sidebar = 'addresses';
ob_start();
?>
<style>
    .address-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .address-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="card border-0 shadow-lg mb-4">
    <div class="card-body p-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h2 class="fw-bold mb-0" style="color: #667eea;">Saved Addresses</h2>
            <button class="btn btn-gradient btn-sm rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="fas fa-plus me-2"></i>Add New Address
            </button>
        </div>

        <div class="row g-4">
            <!-- Address 1 -->
            <div class="col-md-6">
                <div class="card h-100 border shadow-sm address-card">
                    <div class="card-body p-4 position-relative">
                        <span class="badge bg-primary position-absolute top-0 end-0 m-3">Default</span>
                        <div class="mb-3">
                            <h5 class="fw-bold mb-1"><i class="fas fa-home me-2 text-primary"></i>Home</h5>
                        </div>
                        <p class="mb-1 fw-semibold">John Doe</p>
                        <p class="text-muted mb-1 small">123 Street Name, Apt 4B</p>
                        <p class="text-muted mb-1 small">New York, NY 10001, USA</p>
                        <p class="text-muted mb-3 small"><i class="fas fa-phone me-2"></i>+1 234 567 890</p>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#editAddressModal">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteAddressModal">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border shadow-sm address-card">
                    <div class="card-body p-4 position-relative">
                        <span class="badge bg-primary position-absolute top-0 end-0 m-3">Default</span>
                        <div class="mb-3">
                            <h5 class="fw-bold mb-1"><i class="fas fa-home me-2 text-primary"></i>Home</h5>
                        </div>
                        <p class="mb-1 fw-semibold">John Doe</p>
                        <p class="text-muted mb-1 small">123 Street Name, Apt 4B</p>
                        <p class="text-muted mb-1 small">New York, NY 10001, USA</p>
                        <p class="text-muted mb-3 small"><i class="fas fa-phone me-2"></i>+1 234 567 890</p>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#editAddressModal">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteAddressModal">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address 2 -->
            <div class="col-md-6">
                <div class="card h-100 border shadow-sm address-card">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h5 class="fw-bold mb-1"><i class="fas fa-building me-2 text-success"></i>Office</h5>
                        </div>
                        <p class="mb-1 fw-semibold">John Doe</p>
                        <p class="text-muted mb-1 small">456 Business Blvd, Suite 200</p>
                        <p class="text-muted mb-1 small">San Francisco, CA 94107, USA</p>
                        <p class="text-muted mb-3 small"><i class="fas fa-phone me-2"></i>+1 987 654 321</p>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#editAddressModal">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteAddressModal">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                            <button class="btn btn-sm btn-link text-secondary text-decoration-none">Set as Default</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="add_address_action.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="addressLabel" class="form-label fw-semibold">Address Label</label>
                            <select class="form-select" id="addressLabel" name="addressLabel" data-validation="required select" data-error-selector="#add_addressLabel_error">
                                <option value="">Select Label</option>
                                <option value="home">Home</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                            <span id="add_addressLabel_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="fullName" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Enter full name" data-validation="required min alphabetic" data-min="3" data-error-selector="#add_fullName_error">
                            <span id="add_fullName_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number" data-validation="required number min max" data-min="10" data-max="10" data-error-selector="#add_phone_error">
                            <span id="add_phone_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="zipCode" class="form-label fw-semibold">ZIP Code</label>
                            <input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="Enter ZIP code" data-validation="required number min max" data-min="5" data-max="6" data-error-selector="#add_zipCode_error">
                            <span id="add_zipCode_error" class="text-danger small"></span>
                        </div>
                        <div class="col-12">
                            <label for="streetAddress" class="form-label fw-semibold">Street Address</label>
                            <textarea class="form-control" id="streetAddress" name="streetAddress" rows="2" placeholder="Enter street address" data-validation="required min" data-min="5" data-error-selector="#add_streetAddress_error"></textarea>
                            <span id="add_streetAddress_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label fw-semibold">City</label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="Enter city" data-validation="required alphabetic" data-error-selector="#add_city_error">
                            <span id="add_city_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label fw-semibold">State</label>
                            <input type="text" class="form-control" id="state" name="state" placeholder="Enter state" data-validation="required alphabetic" data-error-selector="#add_state_error">
                            <span id="add_state_error" class="text-danger small"></span>
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
                        <button type="button" class="btn btn-cancel py-3" data-bs-dismiss="modal">Cancel</button>
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
                <form action="edit_address_action.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editAddressLabel" class="form-label fw-semibold">Address Label</label>
                            <select class="form-select" id="editAddressLabel" name="addressLabel" data-validation="required select" data-error-selector="#edit_addressLabel_error">
                                <option value="">Select Label</option>
                                <option value="home" selected>Home</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                            <span id="edit_addressLabel_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="editFullName" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" name="fullName" value="John Doe" placeholder="Enter full name" data-validation="required min alphabetic" data-min="3" data-error-selector="#edit_fullName_error">
                            <span id="edit_fullName_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="editPhone" class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone" value="+1 234 567 890" placeholder="Enter phone number" data-validation="required number min max" data-min="10" data-max="10" data-error-selector="#edit_phone_error">
                            <span id="edit_phone_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="editZipCode" class="form-label fw-semibold">ZIP Code</label>
                            <input type="text" class="form-control" id="editZipCode" name="zipCode" value="10001" placeholder="Enter ZIP code" data-validation="required number min max" data-min="5" data-max="6" data-error-selector="#edit_zipCode_error">
                            <span id="edit_zipCode_error" class="text-danger small"></span>
                        </div>
                        <div class="col-12">
                            <label for="editStreetAddress" class="form-label fw-semibold">Street Address</label>
                            <textarea class="form-control" id="editStreetAddress" name="streetAddress" rows="2" placeholder="Enter street address" data-validation="required min" data-min="5" data-error-selector="#edit_streetAddress_error">123 Street Name, Apt 4B</textarea>
                            <span id="edit_streetAddress_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="editCity" class="form-label fw-semibold">City</label>
                            <input type="text" class="form-control" id="editCity" name="city" value="New York" placeholder="Enter city" data-validation="required alphabetic" data-error-selector="#edit_city_error">
                            <span id="edit_city_error" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="editState" class="form-label fw-semibold">State</label>
                            <input type="text" class="form-control" id="editState" name="state" value="NY" placeholder="Enter state" data-validation="required alphabetic" data-error-selector="#edit_state_error">
                            <span id="edit_state_error" class="text-danger small"></span>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-gradient py-3">
                            <i class="fas fa-save me-2"></i>Update Address
                        </button>
                        <button type="button" class="btn btn-cancel py-3" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Address Modal -->
<div class="modal fade" id="deleteAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                </div>
                <h4 class="fw-bold mb-3">Delete Address?</h4>
                <p class="text-muted mb-4">Are you sure you want to delete this address? This action cannot be undone.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Delete Address</button>
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