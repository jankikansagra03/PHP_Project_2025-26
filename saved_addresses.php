<?php
$title          = "Saved Addresses - JK Store";
$active_sidebar = 'addresses';
include_once 'user_authentication.php';
$email     = $_SESSION['user'];
include_once 'db_config.php';
$esc_email = mysqli_real_escape_string($con, $email);

$addr_q    = mysqli_query($con, "SELECT * FROM addresses WHERE email='$esc_email' ORDER BY is_default DESC, id ASC");
$addresses = [];
while ($r = mysqli_fetch_assoc($addr_q)) $addresses[] = $r;

$user_q    = mysqli_query($con, "SELECT fullname, mobile FROM registration WHERE email='$esc_email' LIMIT 1");
$user_info = $user_q ? mysqli_fetch_assoc($user_q) : [];

ob_start();
?>

<!-- Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="addrToast" class="toast align-items-center text-white border-0 shadow-lg rounded-3" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold small" id="addrToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-bold mb-0 heading-primary">Saved Addresses</h4>
                <p class="text-muted small mb-0 mt-1">Manage your delivery addresses</p>
            </div>
            <button class="btn btn-gradient rounded-pill px-4 shadow-sm"
                data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="fas fa-plus me-2"></i>Add New Address
            </button>
        </div>

        <?php if (empty($addresses)): ?>
            <div style="text-align:center;padding:3rem 1rem;background:#f8fafc;border-radius:16px;border:2px dashed #e2e8f0;">
                <div style="font-size:3.5rem;margin-bottom:1rem;">📍</div>
                <h5 style="font-weight:700;color:#374151;">No saved addresses</h5>
                <p style="color:#94a3b8;font-size:.9rem;">Add a delivery address to get started.</p>
                <button class="btn btn-gradient rounded-pill px-4 mt-2"
                    data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fas fa-plus me-2"></i>Add First Address
                </button>
            </div>
        <?php else: ?>
            <div class="row g-4" id="addressGrid">
                <?php
                $iconMap  = ['home' => 'fa-home', 'office' => 'fa-building', 'other' => 'fa-map-marker-alt'];
                $colorMap = ['home' => '#3b82f6', 'office' => '#10b981', 'other' => '#f59e0b'];
                $bgMap    = ['home' => '#eff6ff', 'office' => '#f0fdf4', 'other' => '#fffbeb'];
                foreach ($addresses as $row):
                    $lbl = strtolower($row['label'] ?? 'home');
                    $ico = $iconMap[$lbl] ?? 'fa-map-marker-alt';
                    $col = $colorMap[$lbl] ?? '#64748b';
                    $bg  = $bgMap[$lbl] ?? '#f8fafc';
                ?>
                    <div class="col-md-6" id="addr-grid-<?= $row['id'] ?>">
                        <div class="card h-100 border-0 shadow-sm rounded-4 address-card-sa position-relative overflow-hidden"
                            style="border:1.5px solid <?= !empty($row['is_default']) ? 'var(--theme-primary,#1f7a8c)' : '#e2e8f0' ?>!important;">

                            <!-- Colored top bar -->
                            <div style="height:4px;background:<?= $col ?>;"></div>

                            <div class="card-body p-4">
                                <!-- Label + Default badge -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div style="width:40px;height:40px;border-radius:10px;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas <?= $ico ?>" style="color:<?= $col ?>;font-size:1rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-capitalize"><?= htmlspecialchars($row['label'] ?? 'home') ?></h6>
                                        <?php if (!empty($row['is_default'])): ?>
                                            <span style="background:#dbeafe;color:#1d4ed8;font-size:.65rem;font-weight:700;padding:1px 8px;border-radius:20px;">DEFAULT</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <p class="fw-bold mb-1" style="color:#1e293b;"><?= htmlspecialchars($row['name']) ?></p>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($row['address']) ?></p>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($row['city'] ?? '') ?>, <?= htmlspecialchars($row['state'] ?? '') ?> <?= htmlspecialchars($row['zip'] ?? '') ?></p>
                                <p class="text-muted small mb-3"><i class="fas fa-phone me-1" style="font-size:.7rem;"></i><?= htmlspecialchars($row['phone']) ?></p>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                        data-id="<?= (int)$row['id'] ?>"
                                        data-label="<?= htmlspecialchars($row['label'] ?? 'home', ENT_QUOTES) ?>"
                                        data-name="<?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES) ?>"
                                        data-phone="<?= htmlspecialchars($row['phone'] ?? '', ENT_QUOTES) ?>"
                                        data-address="<?= htmlspecialchars($row['address'] ?? '', ENT_QUOTES) ?>"
                                        data-city="<?= htmlspecialchars($row['city'] ?? '', ENT_QUOTES) ?>"
                                        data-state="<?= htmlspecialchars($row['state'] ?? '', ENT_QUOTES) ?>"
                                        data-zip="<?= htmlspecialchars($row['zip'] ?? '', ENT_QUOTES) ?>"
                                        data-is-default="<?= !empty($row['is_default']) ? '1' : '0' ?>"
                                        onclick="openEditModal(this)">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <?php if (empty($row['is_default'])): ?>
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3"
                                            onclick="setDefault(<?= $row['id'] ?>)">
                                            <i class="fas fa-star me-1"></i>Set Default
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="confirmDelete(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Label</label>
                        <select class="form-select" id="addLabel">
                            <option value="home">🏠 Home</option>
                            <option value="office">🏢 Office</option>
                            <option value="other">📍 Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Full Name *</label>
                        <input type="text" class="form-control" id="addName"
                            value="<?= htmlspecialchars($user_info['fullname'] ?? '', ENT_QUOTES) ?>" placeholder="Full name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Phone *</label>
                        <input type="tel" class="form-control" id="addPhone"
                            value="<?= htmlspecialchars($user_info['mobile'] ?? '', ENT_QUOTES) ?>" placeholder="Phone number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">ZIP / Pincode</label>
                        <input type="text" class="form-control" id="addZip" placeholder="PIN code">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Street Address *</label>
                        <textarea class="form-control" id="addAddress" rows="2" placeholder="House no., Street, Area" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">City *</label>
                        <input type="text" class="form-control" id="addCity" placeholder="City" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">State *</label>
                        <input type="text" class="form-control" id="addState" placeholder="State" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="addIsDefault">
                            <label class="form-check-label fw-semibold small" for="addIsDefault">Set as default address</label>
                        </div>
                    </div>
                </div>
                <div id="addAddrMsg" class="mt-3" style="font-size:.82rem;"></div>
                <div class="d-grid gap-2 mt-4">
                    <button type="button" class="btn btn-gradient py-3 fw-bold" id="saveAddressBtn">
                        <i class="fas fa-save me-2"></i>Save Address
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Address Modal -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <input type="hidden" id="editAddrId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Label</label>
                        <select class="form-select" id="editLabel">
                            <option value="home">🏠 Home</option>
                            <option value="office">🏢 Office</option>
                            <option value="other">📍 Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Full Name *</label>
                        <input type="text" class="form-control" id="editName" placeholder="Full name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Phone *</label>
                        <input type="tel" class="form-control" id="editPhone" placeholder="Phone number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">ZIP / Pincode</label>
                        <input type="text" class="form-control" id="editZip" placeholder="PIN code">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Street Address *</label>
                        <textarea class="form-control" id="editAddress" rows="2" placeholder="House no., Street, Area" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">City *</label>
                        <input type="text" class="form-control" id="editCity" placeholder="City" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">State *</label>
                        <input type="text" class="form-control" id="editState" placeholder="State" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editIsDefault">
                            <label class="form-check-label fw-semibold small" for="editIsDefault">Set as default address</label>
                        </div>
                    </div>
                </div>
                <div id="editAddrMsg" class="mt-3" style="font-size:.82rem;"></div>
                <div class="d-grid gap-2 mt-4">
                    <button type="button" class="btn btn-gradient py-3 fw-bold" id="updateAddressBtn">
                        <i class="fas fa-save me-2"></i>Update Address
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteAddrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div style="font-size:3rem;margin-bottom:.75rem;">🗑️</div>
                <h5 class="fw-bold mb-2">Delete Address?</h5>
                <p class="text-muted small mb-4">This address will be permanently removed.</p>
                <input type="hidden" id="deleteAddrId">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger rounded-pill fw-bold" id="confirmDeleteAddrBtn">Yes, Delete</button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showAddrToast(msg, ok) {
        var $t = $('#addrToast');
        $t.removeClass('bg-success bg-danger').addClass(ok ? 'bg-success' : 'bg-danger');
        $('#addrToastMsg').text(msg);
        new bootstrap.Toast($t.get(0), {
            delay: 3200
        }).show();
    }

    // ── Add Address ──────────────────────────────────────────
    $('#saveAddressBtn').on('click', function() {
        var name = $.trim($('#addName').val()),
            phone = $.trim($('#addPhone').val()),
            address = $.trim($('#addAddress').val()),
            city = $.trim($('#addCity').val()),
            state = $.trim($('#addState').val());
        if (!name || !phone || !address || !city || !state) {
            $('#addAddrMsg').html('<span class="text-danger">Please fill all required fields.</span>');
            return;
        }
        $(this).prop('disabled', true);
        $.post('address_handler.php', {
            action: 'add',
            label: $('#addLabel').val(),
            name,
            phone,
            address,
            city,
            state,
            zip: $.trim($('#addZip').val()),
            is_default: $('#addIsDefault').is(':checked') ? 1 : 0
        }, function(response) {
            $('#saveAddressBtn').prop('disabled', false);
            if (response == 'success') {
                bootstrap.Modal.getInstance($('#addAddressModal').get(0))?.hide();
                location.reload();
            } else {
                $('#addAddrMsg').html('<span class="text-danger">' + response + '</span>');
            }
        }, 'text');
    });

    // ── Open Edit Modal ──────────────────────────────────────
    function openEditModal(button) {
        var row = button.dataset;
        $('#editAddrId').val(row.id || '');
        $('#editLabel').val(row.label || 'home');
        $('#editName').val(row.name || '');
        $('#editPhone').val(row.phone || '');
        $('#editAddress').val(row.address || '');
        $('#editCity').val(row.city || '');
        $('#editState').val(row.state || '');
        $('#editZip').val(row.zip || '');
        $('#editIsDefault').prop('checked', row.isDefault == '1');
        $('#editAddrMsg').html('');
        new bootstrap.Modal($('#editAddressModal').get(0)).show();
    }

    // ── Update Address ───────────────────────────────────────
    $('#updateAddressBtn').on('click', function() {
        var id = $('#editAddrId').val(),
            name = $.trim($('#editName').val()),
            phone = $.trim($('#editPhone').val()),
            address = $.trim($('#editAddress').val()),
            city = $.trim($('#editCity').val()),
            state = $.trim($('#editState').val());
        if (!name || !phone || !address || !city || !state) {
            $('#editAddrMsg').html('<span class="text-danger">Please fill all required fields.</span>');
            return;
        }
        $(this).prop('disabled', true);
        $.post('address_handler.php', {
            action: 'edit',
            id,
            label: $('#editLabel').val(),
            name,
            phone,
            address,
            city,
            state,
            zip: $.trim($('#editZip').val()),
            is_default: $('#editIsDefault').is(':checked') ? 1 : 0
        }, function(response) {
            $('#updateAddressBtn').prop('disabled', false);
            if (response == 'success') {
                bootstrap.Modal.getInstance($('#editAddressModal').get(0))?.hide();
                location.reload();
            } else {
                $('#editAddrMsg').html('<span class="text-danger">' + response + '</span>');
            }
        }, 'text');
    });

    // ── Delete ───────────────────────────────────────────────
    function confirmDelete(id) {
        $('#deleteAddrId').val(id);
        new bootstrap.Modal($('#deleteAddrModal').get(0)).show();
    }
    $('#confirmDeleteAddrBtn').on('click', function() {
        var id = $('#deleteAddrId').val();
        $(this).prop('disabled', true);
        $.post('address_handler.php', {
            action: 'delete',
            id
        }, function(response) {
            bootstrap.Modal.getInstance($('#deleteAddrModal').get(0))?.hide();
            $('#confirmDeleteAddrBtn').prop('disabled', false);
            if (response == 'success') {
                location.reload();
            } else {
                showAddrToast(response.replace('error: ', ''), false);
            }
        }, 'text');
    });

    // ── Set Default ──────────────────────────────────────────
    function setDefault(id) {
        $.post('address_handler.php', {
            action: 'set_default',
            id
        }, function(response) {
            if (response == 'success') {
                location.reload();
            } else {
                showAddrToast(response.replace('error: ', ''), false);
            }
        }, 'text');
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>