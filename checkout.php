<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title          = "Checkout - JK Store";
$active_sidebar = 'cart';

$esc_email = mysqli_real_escape_string($con, $email);

// ── Fetch cart items ─────────────────────────────────────
$cart_q  = mysqli_query($con, "
    SELECT c.id as cart_id, c.quantity,
           p.id as product_id, p.name, p.image, p.final_price, p.price, p.discount, p.stock, p.category_id
    FROM cart c JOIN products p ON c.product_id=p.id
    WHERE c.user_email='$esc_email'
    ORDER BY c.added_at DESC
");
$cart_items = [];
$cart_subtotal = 0.0;
while ($row = mysqli_fetch_assoc($cart_q)) {
    $row['line_total'] = (float)$row['final_price'] * (int)$row['quantity'];
    $cart_subtotal += $row['line_total'];
    $cart_items[] = $row;
}
if (empty($cart_items)) { header('Location: cart.php'); exit; }

// ── Applied coupon from session ──────────────────────────
$coupon = $_SESSION['applied_coupon'] ?? null;
$discount_amount = 0.0;
if ($coupon) {
    // Re-validate amount (safety)
    if ($coupon['discount_type'] === 'percent') {
        $discount_amount = $cart_subtotal * ((float)$coupon['discount_value'] / 100);
        if (!empty($coupon['max_discount_amount'])) {
            $discount_amount = min($discount_amount, (float)$coupon['max_discount_amount']);
        }
    } else {
        $discount_amount = (float)$coupon['discount_amount'];
    }
    $discount_amount = min(round($discount_amount, 2), $cart_subtotal);
}
$total = round($cart_subtotal - $discount_amount, 2);

// ── Fetch saved addresses ────────────────────────────────
$addr_q    = mysqli_query($con, "SELECT * FROM addresses WHERE email='$esc_email' ORDER BY is_default DESC, id ASC");
$addresses = [];
while ($r = mysqli_fetch_assoc($addr_q)) $addresses[] = $r;

// ── User profile (pre-fill new address) ─────────────────
$user_q    = mysqli_query($con, "SELECT fullname, mobile FROM registration WHERE email='$esc_email' LIMIT 1");
$user_info = $user_q ? mysqli_fetch_assoc($user_q) : [];

ob_start();
?>

<!-- Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="coToast" class="toast align-items-center text-white border-0 shadow-lg rounded-3" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold small" id="coToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Step Indicator -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3 px-4">
        <div style="display:flex; align-items:center; gap:0;">
            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,var(--theme-primary,#1f7a8c),#0f4c5c); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem; box-shadow:0 4px 12px rgba(31,122,140,.3);">1</div>
                <span style="font-size:.72rem; font-weight:700; color:var(--theme-primary,#1f7a8c);">Address</span>
            </div>
            <div style="flex:1; height:3px; background:linear-gradient(90deg,var(--theme-primary,#1f7a8c),#e2e8f0); margin:0 8px; border-radius:3px; margin-bottom:18px;"></div>
            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                <div style="width:36px; height:36px; border-radius:50%; background:#f1f5f9; color:#94a3b8; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem; border:2px solid #e2e8f0;">2</div>
                <span style="font-size:.72rem; font-weight:600; color:#94a3b8;">Payment</span>
            </div>
            <div style="flex:1; height:3px; background:#e2e8f0; margin:0 8px; border-radius:3px; margin-bottom:18px;"></div>
            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                <div style="width:36px; height:36px; border-radius:50%; background:#f1f5f9; color:#94a3b8; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem; border:2px solid #e2e8f0;">3</div>
                <span style="font-size:.72rem; font-weight:600; color:#94a3b8;">Confirm</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- ── LEFT: Address + Coupon ────────────────────────── -->
    <div class="col-lg-8">

        <!-- Address Selection Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-map-marker-alt me-2" style="color:var(--theme-primary);"></i>
                        Delivery Address
                    </h5>
                    <button class="btn btn-gradient btn-sm rounded-pill px-3 shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="fas fa-plus me-1"></i>Add New
                    </button>
                </div>

                <?php if (empty($addresses)): ?>
                <div style="text-align:center; padding:2rem; background:#f8fafc; border-radius:14px; border:2px dashed #e2e8f0;">
                    <div style="font-size:3rem; margin-bottom:.75rem;">📍</div>
                    <h6 style="font-weight:700; color:#374151;">No saved addresses</h6>
                    <p style="color:#94a3b8; font-size:.85rem; margin:0;">Add a delivery address to continue.</p>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-3" id="addressList">
                    <?php foreach ($addresses as $idx => $addr): ?>
                    <?php $selected = ($idx === 0); ?>
                    <div class="address-card-co <?= $selected ? 'addr-selected' : '' ?>"
                         id="addr-card-<?= $addr['id'] ?>"
                         onclick="selectAddress(this, <?= $addr['id'] ?>)"
                         style="border:2px solid <?= $selected ? 'var(--theme-primary,#1f7a8c)' : '#e2e8f0' ?>; border-radius:14px; padding:1rem 1.25rem; cursor:pointer; transition:border-color .2s,background .2s; background:<?= $selected ? 'rgba(31,122,140,.03)' : '#fff' ?>;">
                        <div style="display:flex; align-items:flex-start; gap:14px;">
                            <!-- Radio indicator -->
                            <div class="addr-radio" style="width:22px; height:22px; border-radius:50%; border:2px solid <?= $selected ? 'var(--theme-primary,#1f7a8c)' : '#cbd5e1' ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px; background:<?= $selected ? 'var(--theme-primary,#1f7a8c)' : '#fff' ?>; transition:all .2s;">
                                <?php if ($selected): ?>
                                <i class="fas fa-check" style="color:#fff; font-size:.6rem;"></i>
                                <?php endif; ?>
                            </div>
                            <!-- Address details -->
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                    <?php
                                    $icons = ['home'=>'fa-home','office'=>'fa-building','other'=>'fa-map-marker-alt'];
                                    $colors = ['home'=>'#3b82f6','office'=>'#10b981','other'=>'#f59e0b'];
                                    $lbl = strtolower($addr['label'] ?? 'home');
                                    ?>
                                    <i class="fas <?= $icons[$lbl] ?? 'fa-map-marker-alt' ?>"
                                       style="color:<?= $colors[$lbl] ?? '#64748b' ?>; font-size:.9rem;"></i>
                                    <span style="font-weight:800; font-size:.9rem; color:#0f172a; text-transform:capitalize;">
                                        <?= htmlspecialchars($addr['label'] ?? 'Home') ?>
                                    </span>
                                    <?php if (!empty($addr['is_default'])): ?>
                                    <span style="background:#dbeafe; color:#1d4ed8; font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:.3px;">DEFAULT</span>
                                    <?php endif; ?>
                                </div>
                                <p style="font-weight:700; color:#1e293b; margin:0 0 2px; font-size:.88rem;"><?= htmlspecialchars($addr['name']) ?></p>
                                <p style="color:#64748b; margin:0 0 2px; font-size:.82rem;">
                                    <?= htmlspecialchars($addr['address']) ?>,
                                    <?= htmlspecialchars($addr['city'] ?? '') ?>,
                                    <?= htmlspecialchars($addr['state'] ?? '') ?>
                                    <?= !empty($addr['zip']) ? '- ' . htmlspecialchars($addr['zip']) : '' ?>
                                </p>
                                <p style="color:#64748b; margin:0; font-size:.82rem;">
                                    <i class="fas fa-phone me-1" style="font-size:.7rem;"></i>
                                    <?= htmlspecialchars($addr['phone']) ?>
                                </p>
                            </div>
                            <!-- Edit / Delete actions -->
                            <div style="display:flex; gap:6px; flex-shrink:0;">
                                <button class="btn btn-sm btn-outline-primary rounded-pill"
                                        style="font-size:.72rem; padding:3px 10px;"
                                        onclick="editAddress(event, <?= $addr['id'] ?>, '<?= addslashes(htmlspecialchars($addr['label'] ?? 'home', ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars($addr['name'], ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars($addr['phone'], ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars($addr['address'], ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars($addr['city'] ?? '', ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars($addr['state'] ?? '', ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars($addr['zip'] ?? '', ENT_QUOTES)) ?>', <?= !empty($addr['is_default']) ? 1 : 0 ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill"
                                        style="font-size:.72rem; padding:3px 10px;"
                                        onclick="deleteAddress(event, <?= $addr['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coupon Code Card -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-ticket-alt me-2" style="color:var(--theme-primary);"></i>
                    Promo / Coupon Code
                </h5>

                <?php if ($coupon): ?>
                <!-- Coupon already applied from cart session -->
                <div style="background:linear-gradient(135deg,#dcfce7,#bbf7d0); border:1.5px dashed #16a34a; border-radius:14px; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <i class="fas fa-check-circle" style="color:#16a34a;"></i>
                            <span style="font-weight:800; color:#15803d; letter-spacing:1.5px; font-size:.9rem;"><?= htmlspecialchars($coupon['code']) ?></span>
                        </div>
                        <p style="color:#16a34a; font-size:.82rem; margin:0;"><?= htmlspecialchars($coupon['description'] ?? '') ?></p>
                        <p style="color:#15803d; font-weight:700; font-size:.9rem; margin:0;">
                            Saving ₹<?= number_format($discount_amount, 2) ?>
                        </p>
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-pill" id="removeCouponCheckout">
                        <i class="fas fa-times me-1"></i>Remove
                    </button>
                </div>
                <?php else: ?>
                <div id="couponInputAreaCo">
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="couponCodeCo" class="form-control fw-semibold"
                               placeholder="Enter coupon code" maxlength="30"
                               style="text-transform:uppercase; letter-spacing:1.5px;">
                        <button class="btn btn-gradient fw-bold px-4 rounded-3" id="applyCouponCo" type="button" style="white-space:nowrap;">
                            Apply Code
                        </button>
                    </div>
                    <div id="couponMsgCo" class="mt-2" style="font-size:.82rem;"></div>
                </div>
                <div id="couponAppliedCo" style="display:none; background:linear-gradient(135deg,#dcfce7,#bbf7d0); border:1.5px dashed #16a34a; border-radius:14px; padding:1rem 1.25rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <span class="badge bg-success rounded-pill me-2 fw-bold" id="coAppliedCode" style="letter-spacing:.5px;"></span>
                            <span style="color:#16a34a; font-size:.85rem; font-weight:600;" id="coAppliedDesc"></span>
                            <div style="color:#15803d; font-weight:700; font-size:.9rem; margin-top:2px;" id="coAppliedSaving"></div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger rounded-pill" id="removeCouponCo">
                            <i class="fas fa-times me-1"></i>Remove
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ── RIGHT: Order Summary ──────────────────────────── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="fas fa-receipt me-2" style="color:var(--theme-primary); opacity:.8;"></i>
                    Order Summary
                </h5>

                <!-- Items list (compact) -->
                <div style="max-height:220px; overflow-y:auto; margin-bottom:1rem;" class="pe-1">
                    <?php foreach ($cart_items as $ci): ?>
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <div style="width:44px; height:44px; background:#f8fafc; border-radius:10px; overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            <img src="<?= htmlspecialchars($ci['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                 style="width:38px; height:38px; object-fit:contain; mix-blend-mode:multiply;">
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; color:#1e293b; font-size:.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?= htmlspecialchars($ci['name']) ?>
                            </div>
                            <div style="color:#94a3b8; font-size:.75rem;">Qty: <?= $ci['quantity'] ?></div>
                        </div>
                        <div style="font-weight:700; color:#1e293b; font-size:.85rem; flex-shrink:0;">
                            ₹<?= number_format($ci['line_total'], 2) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="color:#64748b; font-size:.88rem;">Subtotal (<?= count($cart_items) ?> items)</span>
                    <span style="font-weight:600;">₹<?= number_format($cart_subtotal, 2) ?></span>
                </div>

                <?php if ($coupon && $discount_amount > 0): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; " id="summaryDiscountRow">
                    <span style="color:#16a34a; font-size:.88rem; font-weight:600;">
                        <i class="fas fa-tag me-1"></i>Discount (<?= htmlspecialchars($coupon['code']) ?>)
                    </span>
                    <span style="font-weight:700; color:#16a34a;" id="summaryDiscountAmt">-₹<?= number_format($discount_amount, 2) ?></span>
                </div>
                <?php else: ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;" id="summaryDiscountRow" style="display:none;">
                    <span style="color:#16a34a; font-size:.88rem; font-weight:600;">
                        <i class="fas fa-tag me-1"></i>Discount
                    </span>
                    <span style="font-weight:700; color:#16a34a;" id="summaryDiscountAmt">-₹0.00</span>
                </div>
                <?php endif; ?>

                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="color:#64748b; font-size:.88rem;">Shipping</span>
                    <span style="background:#dcfce7; color:#16a34a; font-size:.75rem; font-weight:700; padding:2px 10px; border-radius:20px;">FREE</span>
                </div>

                <hr style="border-color:#e2e8f0; margin:14px 0;">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <span style="font-weight:800; font-size:1.1rem; color:#0f172a;">Total</span>
                    <span style="font-weight:900; font-size:1.5rem; color:var(--theme-primary, #1f7a8c);" id="coTotal">
                        ₹<?= number_format($total, 2) ?>
                    </span>
                </div>

                <!-- Hidden inputs pass to order_confirm -->
                <input type="hidden" id="selectedAddressId" value="<?= !empty($addresses) ? $addresses[0]['id'] : '' ?>">
                <input type="hidden" id="appliedCouponCode" value="<?= htmlspecialchars($coupon['code'] ?? '', ENT_QUOTES) ?>">
                <input type="hidden" id="discountAmountHidden" value="<?= number_format($discount_amount, 2) ?>">

                <button class="btn btn-gradient w-100 py-3 fw-bold rounded-3 shadow-sm mb-3" id="proceedToPayBtn"
                        style="font-size:1rem;" onclick="proceedToPayment()">
                    <i class="fas fa-lock me-2"></i>Proceed to Payment
                </button>
                <a href="cart.php" class="btn btn-outline-secondary w-100 rounded-3 py-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                </a>
                <p style="text-align:center; color:#94a3b8; font-size:.75rem; margin-top:.75rem; margin-bottom:0;">
                    <i class="fas fa-shield-alt me-1 text-success"></i>100% Secure &amp; Encrypted Checkout
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ── Add Address Modal ──────────────────────────────── -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2 text-primary"></i>Add New Address</h5>
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
                        <input type="text" class="form-control" id="addName" value="<?= htmlspecialchars($user_info['fullname'] ?? '', ENT_QUOTES) ?>" placeholder="Full name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Phone *</label>
                        <input type="tel" class="form-control" id="addPhone" value="<?= htmlspecialchars($user_info['mobile'] ?? '', ENT_QUOTES) ?>" placeholder="Phone number" required>
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

<!-- ── Edit Address Modal ─────────────────────────────── -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
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

<!-- ── Delete Confirm Modal ───────────────────────────── -->
<div class="modal fade" id="deleteAddrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div style="font-size:3rem; margin-bottom:.75rem;">🗑️</div>
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
var selectedAddrId = <?= !empty($addresses) ? $addresses[0]['id'] : 'null' ?>;
var checkoutSubtotal = <?= $cart_subtotal ?>;
var checkoutDiscount = <?= $discount_amount ?>;

function showCoToast(msg, ok) {
    var t = document.getElementById('coToast');
    t.classList.remove('bg-success','bg-danger');
    t.classList.add(ok ? 'bg-success' : 'bg-danger');
    document.getElementById('coToastMsg').textContent = msg;
    new bootstrap.Toast(t, {delay:3200}).show();
}

// ── Address Selection ───────────────────────────────────
function selectAddress(el, id) {
    document.querySelectorAll('.address-card-co').forEach(function(c) {
        c.style.borderColor = '#e2e8f0';
        c.style.background  = '#fff';
        var radio = c.querySelector('.addr-radio');
        if (radio) {
            radio.style.borderColor = '#cbd5e1';
            radio.style.background  = '#fff';
            radio.innerHTML = '';
        }
    });
    el.style.borderColor = 'var(--theme-primary, #1f7a8c)';
    el.style.background  = 'rgba(31,122,140,.03)';
    var radio = el.querySelector('.addr-radio');
    if (radio) {
        radio.style.borderColor = 'var(--theme-primary, #1f7a8c)';
        radio.style.background  = 'var(--theme-primary, #1f7a8c)';
        radio.innerHTML = '<i class="fas fa-check" style="color:#fff; font-size:.6rem;"></i>';
    }
    selectedAddrId = id;
    document.getElementById('selectedAddressId').value = id;
}

// ── Edit Address ────────────────────────────────────────
function editAddress(e, id, label, name, phone, address, city, state, zip, isDef) {
    e.stopPropagation();
    document.getElementById('editAddrId').value = id;
    document.getElementById('editLabel').value  = label;
    document.getElementById('editName').value   = name;
    document.getElementById('editPhone').value  = phone;
    document.getElementById('editAddress').value= address;
    document.getElementById('editCity').value   = city;
    document.getElementById('editState').value  = state;
    document.getElementById('editZip').value    = zip;
    document.getElementById('editIsDefault').checked = (isDef == 1);
    document.getElementById('editAddrMsg').innerHTML = '';
    new bootstrap.Modal(document.getElementById('editAddressModal')).show();
}

// ── Delete Address ──────────────────────────────────────
function deleteAddress(e, id) {
    e.stopPropagation();
    document.getElementById('deleteAddrId').value = id;
    new bootstrap.Modal(document.getElementById('deleteAddrModal')).show();
}

function addAddrCardHTML(a) {
    var icons = {home:'fa-home', office:'fa-building', other:'fa-map-marker-alt'};
    var colors = {home:'#3b82f6', office:'#10b981', other:'#f59e0b'};
    var lbl = (a.label||'home').toLowerCase();
    return '<div class="address-card-co" id="addr-card-'+a.id+'" onclick="selectAddress(this,'+a.id+')"'+
        ' style="border:2px solid #e2e8f0; border-radius:14px; padding:1rem 1.25rem; cursor:pointer; transition:border-color .2s,background .2s; background:#fff;">'+
        '<div style="display:flex; align-items:flex-start; gap:14px;">'+
        '<div class="addr-radio" style="width:22px; height:22px; border-radius:50%; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px; background:#fff;"></div>'+
        '<div style="flex:1;"><div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">'+
        '<i class="fas '+(icons[lbl]||'fa-map-marker-alt')+'" style="color:'+(colors[lbl]||'#64748b')+'; font-size:.9rem;"></i>'+
        '<span style="font-weight:800; font-size:.9rem; color:#0f172a; text-transform:capitalize;">'+a.label+'</span>'+
        (a.is_default ? '<span style="background:#dbeafe; color:#1d4ed8; font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px;">DEFAULT</span>' : '')+
        '</div>'+
        '<p style="font-weight:700; color:#1e293b; margin:0 0 2px; font-size:.88rem;">'+a.name+'</p>'+
        '<p style="color:#64748b; margin:0 0 2px; font-size:.82rem;">'+a.address+', '+a.city+', '+a.state+(a.zip?' - '+a.zip:'')+
        '</p><p style="color:#64748b; margin:0; font-size:.82rem;"><i class="fas fa-phone me-1" style="font-size:.7rem;"></i>'+a.phone+'</p></div>'+
        '<div style="display:flex; gap:6px; flex-shrink:0;">'+
        '<button class="btn btn-sm btn-outline-primary rounded-pill" style="font-size:.72rem; padding:3px 10px;"'+
        ' onclick="editAddress(event,'+a.id+',\''+a.label+'\',\''+a.name+'\',\''+a.phone+'\',\''+a.address+'\',\''+a.city+'\',\''+a.state+'\',\''+a.zip+'\','+a.is_default+')">[edit]</button>'+
        '<button class="btn btn-sm btn-outline-danger rounded-pill" style="font-size:.72rem; padding:3px 10px;" onclick="deleteAddress(event,'+a.id+')"><i class="fas fa-trash"></i></button>'+
        '</div></div></div>';
}

// ── Save New Address ─────────────────────────────────────
document.getElementById('saveAddressBtn')?.addEventListener('click', function() {
    var name=document.getElementById('addName').value.trim(),
        phone=document.getElementById('addPhone').value.trim(),
        address=document.getElementById('addAddress').value.trim(),
        city=document.getElementById('addCity').value.trim(),
        state=document.getElementById('addState').value.trim();
    var msg = document.getElementById('addAddrMsg');
    if (!name||!phone||!address||!city||!state) {
        msg.innerHTML='<span class="text-danger">Please fill all required fields.</span>'; return;
    }
    this.disabled=true;
    $.post('address_handler.php', {
        action:'add',
        label:document.getElementById('addLabel').value,
        name, phone, address, city, state,
        zip:document.getElementById('addZip').value.trim(),
        is_default:document.getElementById('addIsDefault').checked ? 1 : 0
    }, function(data) {
        document.getElementById('saveAddressBtn').disabled=false;
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addAddressModal'))?.hide();
            showCoToast(data.message, true);
            var list = document.getElementById('addressList');
            if (!list) {
                location.reload(); return;
            }
            list.insertAdjacentHTML('beforeend', addAddrCardHTML(data));
            // auto-select new
            selectAddress(document.getElementById('addr-card-'+data.id), data.id);
        } else {
            msg.innerHTML='<span class="text-danger">'+data.message+'</span>';
        }
    }, 'json').fail(function() {
        document.getElementById('saveAddressBtn').disabled=false;
        msg.innerHTML='<span class="text-danger">Error. Try again.</span>';
    });
});

// ── Update Address ───────────────────────────────────────
document.getElementById('updateAddressBtn')?.addEventListener('click', function() {
    var id=document.getElementById('editAddrId').value,
        name=document.getElementById('editName').value.trim(),
        phone=document.getElementById('editPhone').value.trim(),
        address=document.getElementById('editAddress').value.trim(),
        city=document.getElementById('editCity').value.trim(),
        state=document.getElementById('editState').value.trim();
    var msg = document.getElementById('editAddrMsg');
    if (!name||!phone||!address||!city||!state) {
        msg.innerHTML='<span class="text-danger">Please fill all required fields.</span>'; return;
    }
    this.disabled=true;
    $.post('address_handler.php', {
        action:'edit', id,
        label:document.getElementById('editLabel').value,
        name, phone, address, city, state,
        zip:document.getElementById('editZip').value.trim(),
        is_default:document.getElementById('editIsDefault').checked ? 1 : 0
    }, function(data) {
        document.getElementById('updateAddressBtn').disabled=false;
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editAddressModal'))?.hide();
            showCoToast(data.message, true);
            location.reload();
        } else {
            msg.innerHTML='<span class="text-danger">'+data.message+'</span>';
        }
    }, 'json').fail(function() {
        document.getElementById('updateAddressBtn').disabled=false;
        msg.innerHTML='<span class="text-danger">Error. Try again.</span>';
    });
});

// ── Confirm Delete ───────────────────────────────────────
document.getElementById('confirmDeleteAddrBtn')?.addEventListener('click', function() {
    var id = document.getElementById('deleteAddrId').value;
    this.disabled=true;
    $.post('address_handler.php', {action:'delete', id}, function(data) {
        bootstrap.Modal.getInstance(document.getElementById('deleteAddrModal'))?.hide();
        document.getElementById('confirmDeleteAddrBtn').disabled=false;
        if (data.success) {
            showCoToast(data.message, true);
            document.getElementById('addr-card-'+id)?.remove();
            // Select first remaining address
            var first = document.querySelector('.address-card-co');
            if (first) {
                var m = first.id.match(/addr-card-(\d+)/);
                if (m) selectAddress(first, parseInt(m[1]));
            }
        } else showCoToast(data.message, false);
    }, 'json');
});

// ── Coupon on Checkout Page ──────────────────────────────
<?php if (!$coupon): ?>
document.getElementById('applyCouponCo')?.addEventListener('click', function() {
    var code = (document.getElementById('couponCodeCo').value || '').trim().toUpperCase();
    if (!code) return;
    var btn = this, msg = document.getElementById('couponMsgCo');
    btn.disabled=true; btn.textContent='...';
    msg.innerHTML='';
    $.post('coupon_handler.php', {action:'validate', code, cart_subtotal: checkoutSubtotal.toFixed(2)}, function(data) {
        btn.disabled=false; btn.textContent='Apply Code';
        if (data.success) {
            checkoutDiscount = parseFloat(data.discount_amount.replace(/,/g,'')) || 0;
            document.getElementById('couponInputAreaCo').style.display='none';
            document.getElementById('couponAppliedCo').style.display='';
            document.getElementById('coAppliedCode').textContent = data.code;
            document.getElementById('coAppliedDesc').textContent = data.discount_label + ' applied';
            document.getElementById('coAppliedSaving').textContent = 'Saving ₹' + checkoutDiscount.toFixed(2);
            document.getElementById('appliedCouponCode').value = data.code;
            document.getElementById('discountAmountHidden').value = checkoutDiscount.toFixed(2);
            updateCoTotal();
            showCoToast(data.message, true);
        } else {
            msg.innerHTML='<span class="text-danger"><i class="fas fa-times-circle me-1"></i>'+data.message+'</span>';
        }
    }, 'json').fail(function() {
        btn.disabled=false; btn.textContent='Apply Code';
        msg.innerHTML='<span class="text-danger">Error. Try again.</span>';
    });
});
document.getElementById('removeCouponCo')?.addEventListener('click', function() {
    $.post('coupon_handler.php', {action:'remove'}, function() {
        checkoutDiscount=0;
        document.getElementById('couponAppliedCo').style.display='none';
        document.getElementById('couponInputAreaCo').style.display='';
        document.getElementById('couponCodeCo').value='';
        document.getElementById('couponMsgCo').innerHTML='';
        document.getElementById('appliedCouponCode').value='';
        document.getElementById('discountAmountHidden').value='0.00';
        updateCoTotal();
    }, 'json');
});
document.getElementById('couponCodeCo')?.addEventListener('keydown', function(e) {
    if (e.key==='Enter') document.getElementById('applyCouponCo').click();
});
<?php else: ?>
document.getElementById('removeCouponCheckout')?.addEventListener('click', function() {
    $.post('coupon_handler.php', {action:'remove'}, function() {
        showCoToast('Coupon removed.', true);
        setTimeout(function() { location.reload(); }, 800);
    }, 'json');
});
<?php endif; ?>

function updateCoTotal() {
    var total = Math.max(0, checkoutSubtotal - checkoutDiscount);
    document.getElementById('coTotal').textContent = '₹' + total.toFixed(2);
    var dr = document.getElementById('summaryDiscountRow');
    if (dr) {
        document.getElementById('summaryDiscountAmt').textContent = '-₹' + checkoutDiscount.toFixed(2);
        dr.style.display = checkoutDiscount > 0 ? '' : 'none';
    }
}

// ── Proceed to Payment ───────────────────────────────────
function proceedToPayment() {
    if (!selectedAddrId) {
        showCoToast('Please select a delivery address.', false); return;
    }
    var addrId    = document.getElementById('selectedAddressId').value;
    var coupon    = document.getElementById('appliedCouponCode').value;
    var discount  = document.getElementById('discountAmountHidden').value;
    var url = 'order_confirm.php?address_id='+addrId;
    if (coupon) url += '&coupon='+encodeURIComponent(coupon)+'&discount='+discount;
    window.location.href = url;
}
</script>

<style>
.address-card-co:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
</style>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>