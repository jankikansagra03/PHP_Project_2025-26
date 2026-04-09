<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title = "Shopping Cart - JK Store";
$active_sidebar = 'cart';

$esc_email = mysqli_real_escape_string($con, $email);

// Fetch cart items joined with product info
$q = "SELECT c.id as cart_id, c.quantity,
             p.id as product_id, p.name, p.image, p.price, p.discount, p.final_price, p.stock
      FROM cart c
      JOIN products p ON c.product_id = p.id
      WHERE c.user_email='$esc_email'
      ORDER BY c.added_at DESC";
$result  = mysqli_query($con, $q);
$items   = [];
$subtotal = 0.0;
while ($row = mysqli_fetch_assoc($result)) {
    $row['line_total'] = (float)$row['final_price'] * (int)$row['quantity'];
    $subtotal += $row['line_total'];
    $items[] = $row;
}
$count = count($items);

// Calculate discount directly in PHP instead of Javascript (simpler for students)
$discount_amount = 0.0;
$coupon = $_SESSION['applied_coupon'] ?? null;
if ($coupon && $subtotal > 0) {
    if ($coupon['discount_type'] === 'percent') {
        $discount_amount = $subtotal * ((float)$coupon['discount_value'] / 100);
        if (!empty($coupon['max_discount_amount'])) {
            $discount_amount = min($discount_amount, (float)$coupon['max_discount_amount']);
        }
    } else {
        $discount_amount = (float)$coupon['discount_amount'];
    }
    $discount_amount = min(round($discount_amount, 2), $subtotal);
}
$total = round($subtotal - $discount_amount, 2);

ob_start();
?>

<!-- ── Toast ─────────────────────────────────────────── -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="cartToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="cartToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- ── Page Header ────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0 text-white">
        <i class="fas fa-shopping-cart me-2 opacity-75"></i>
        Shopping Cart
        <span class="fs-5 fw-normal ms-1 opacity-75" id="cartItemCountLabel">(<?= $count ?> item<?= $count !== 1 ? 's' : '' ?>)</span>
    </h2>
    <div class="d-flex gap-2">
        <?php if ($count > 0): ?>
        <button class="btn btn-sm rounded-pill text-white border border-white border-opacity-50 px-3" id="clearCartBtn">
            <i class="fas fa-trash me-2"></i>Clear Cart
        </button>
        <?php endif; ?>
        <a href="shop.php" class="btn btn-sm rounded-pill text-white border border-white border-opacity-50 px-3">
            <i class="fas fa-store me-2"></i>Continue Shopping
        </a>
    </div>
</div>

<?php if ($count === 0): ?>
<!-- ── Empty State ────────────────────────────────────── -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-5 text-center">
        <div class="mb-4">
            <span style="font-size:5rem; line-height:1;">🛒</span>
        </div>
        <h4 class="fw-bold mb-2">Your cart is empty</h4>
        <p class="text-muted mb-4">Looks like you haven't added anything yet. Browse our collection and find something you love!</p>
        <a href="shop.php" class="btn btn-gradient px-5 py-2 rounded-pill">
            <i class="fas fa-store me-2"></i>Browse Products
        </a>
    </div>
</div>

<?php else: ?>

<div class="row g-4" id="cartWrapper">

    <!-- ── Cart Items List ──────────────────────────────── -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <?php foreach ($items as $idx => $item): ?>
                <div class="cart-row d-flex align-items-center gap-3 p-4 <?= $idx < count($items) - 1 ? 'border-bottom' : '' ?>"
                     id="cart-item-<?= $item['cart_id'] ?>">

                    <!-- Product Image -->
                    <a href="product_detail.php?id=<?= $item['product_id'] ?>"
                       class="flex-shrink-0 bg-light rounded-3 d-flex align-items-center justify-content-center"
                       style="width:90px; height:90px; overflow:hidden; text-decoration:none;">
                        <img src="<?= htmlspecialchars($item['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             style="width:80px; height:80px; object-fit:contain; mix-blend-mode:multiply;">
                    </a>

                    <!-- Product Details -->
                    <div class="flex-grow-1 min-width-0">
                        <a href="product_detail.php?id=<?= $item['product_id'] ?>"
                           class="fw-bold text-dark text-decoration-none d-block mb-1 text-truncate">
                            <?= htmlspecialchars($item['name']) ?>
                        </a>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fw-bold text-primary">&#8377;<?= number_format((float)$item['final_price'], 2) ?></span>
                            <?php if ($item['discount'] > 0): ?>
                            <span class="text-muted text-decoration-line-through small">&#8377;<?= number_format((float)$item['price'], 2) ?></span>
                            <span class="badge bg-danger-subtle text-danger rounded-pill small">Save &#8377;<?= number_format((float)$item['discount'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Qty Stepper (inline) -->
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group input-group-sm" style="width:120px;">
                                <button class="btn btn-outline-secondary qty-dec" type="button"
                                    data-cart-id="<?= $item['cart_id'] ?>"
                                    data-max="<?= $item['stock'] ?>">
                                    <i class="fas fa-minus" style="font-size:.65rem;"></i>
                                </button>
                                <input type="text" class="form-control text-center fw-bold bg-white qty-input"
                                    id="qty-input-<?= $item['cart_id'] ?>"
                                    value="<?= $item['quantity'] ?>" readonly>
                                <button class="btn btn-outline-secondary qty-inc" type="button"
                                    data-cart-id="<?= $item['cart_id'] ?>"
                                    data-max="<?= $item['stock'] ?>">
                                    <i class="fas fa-plus" style="font-size:.65rem;"></i>
                                </button>
                            </div>
                            <span class="text-muted small">Max: <?= $item['stock'] ?></span>
                        </div>
                    </div>

                    <!-- Line Total + Remove -->
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold fs-6 mb-2" id="line-total-<?= $item['cart_id'] ?>">
                            &#8377;<?= number_format($item['line_total'], 2) ?>
                        </div>
                        <button class="btn btn-sm btn-outline-danger rounded-circle remove-item-btn"
                                data-cart-id="<?= $item['cart_id'] ?>"
                                style="width:32px; height:32px; padding:0; line-height:1;"
                                title="Remove">
                            <i class="fas fa-times" style="font-size:.75rem;"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── Order Summary ────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="fas fa-receipt me-2 text-primary opacity-75"></i>Order Summary
                </h5>

                <!-- ── Coupon Code ── -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted text-uppercase" style="letter-spacing:.5px;">
                        <i class="fas fa-tag me-1"></i>Coupon Code
                    </label>

                    <?php if ($coupon && $discount_amount > 0): ?>
                    <!-- Applied coupon display -->
                    <div id="appliedCouponRow" class="mt-1">
                        <div class="d-flex align-items-center justify-content-between rounded-3 p-2 px-3"
                             style="background:linear-gradient(135deg,#dcfce7,#bbf7d0); border:1.5px dashed #16a34a;">
                            <div>
                                <span class="badge bg-success rounded-pill me-2 fw-bold"><?= htmlspecialchars($coupon['code']) ?></span>
                                <small class="text-success fw-semibold"><?= htmlspecialchars($coupon['description'] ?? 'Discount applied') ?></small>
                            </div>
                            <button class="btn btn-sm btn-link p-0 text-danger fw-bold ms-2" id="removeCouponBtn" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div id="couponInputArea">
                        <div class="input-group input-group-sm rounded-3 overflow-hidden" style="border:2px solid #e2e8f0;">
                            <input type="text" id="couponCodeInput" class="form-control border-0 bg-transparent fw-semibold text-uppercase"
                                   placeholder="Enter promo code" maxlength="30"
                                   style="letter-spacing:1.5px; text-transform:uppercase;">
                            <button class="btn btn-gradient px-3 fw-bold" id="applyCouponBtn" type="button">
                                Apply
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div id="couponMessage" class="mt-1" style="font-size:.8rem;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Subtotal (<?= $count ?> item<?= $count !== 1 ? 's' : '' ?>)</span>
                    <span class="fw-semibold">&#8377;<?= number_format($subtotal, 2) ?></span>
                </div>
                
                <?php if ($discount_amount > 0): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-success fw-semibold"><i class="fas fa-tag me-1"></i>Discount</span>
                    <span class="fw-bold text-success">-&#8377;<?= number_format($discount_amount, 2) ?></span>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Shipping</span>
                    <span class="badge bg-success rounded-pill px-2">FREE</span>
                </div>

                <hr class="my-3 opacity-25">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-4" style="color:var(--theme-primary);">
                        &#8377;<?= number_format($total, 2) ?>
                    </span>
                </div>

                <a href="checkout.php" class="btn btn-gradient w-100 py-3 fw-bold rounded-3 mb-3 shadow-sm" id="checkoutBtn">
                    <i class="fas fa-lock me-2"></i>Proceed to Checkout
                </a>
                <a href="shop.php" class="btn btn-outline-secondary w-100 py-2 rounded-3 mb-3">
                    <i class="fas fa-store me-2"></i>Continue Shopping
                </a>
                <p class="text-center text-muted small mb-0">
                    <i class="fas fa-shield-alt me-1 text-success"></i>100% Secure Checkout
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ── Remove Item Modal ───────────────────────────────── -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <span style="font-size:3rem;">🗑️</span>
                </div>
                <h5 class="fw-bold mb-2">Remove Item?</h5>
                <p class="text-muted small mb-4">This item will be removed from your cart.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger rounded-pill" id="confirmRemoveBtn">Yes, Remove</button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Clear Cart Modal ───────────────────────────────── -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <span style="font-size:3rem;">⚠️</span>
                </div>
                <h5 class="fw-bold mb-2">Clear Entire Cart?</h5>
                <p class="text-muted small mb-4">All items will be removed. This cannot be undone.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger rounded-pill" id="confirmClearBtn">Yes, Clear All</button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let pendingRemoveCartId = null;

function showCartToast(msg, ok) {
    var t = document.getElementById('cartToast');
    t.classList.remove('bg-success','bg-danger');
    t.classList.add(ok ? 'bg-success' : 'bg-danger');
    document.getElementById('cartToastMsg').textContent = msg;
    new bootstrap.Toast(t, {delay:3000}).show();
}

function updateCartBadge(count) {
    var b = document.getElementById('navCartBadge');
    if (b) { b.textContent = count; b.style.display = count > 0 ? 'inline-flex' : 'none'; }
}

// Reusable function that silently refreshes just the Cart HTML section via AJAX
function reloadCartUI() {
    $('#cartWrapper').load(location.href + ' #cartWrapper > *');
}

// ── Coupon Apply / Remove ──
document.addEventListener('click', function(e) {
    var applyBtn = e.target.closest('#applyCouponBtn');
    if (applyBtn) {
        var code = (document.getElementById('couponCodeInput').value || '').trim();
        if (!code) return;
        applyBtn.disabled = true; applyBtn.textContent = '...';
        
        $.post('coupon_handler.php', {action:'validate', code: code}, function(data) {
            if (data.success) {
                showCartToast(data.message, true);
                reloadCartUI(); // Simple AJAX magic
            } else {
                applyBtn.disabled = false; applyBtn.textContent = 'Apply';
                document.getElementById('couponMessage').innerHTML = '<span class="text-danger">' + data.message + '</span>';
            }
        }, 'json');
    }

    var removeCouponBtn = e.target.closest('#removeCouponBtn');
    if (removeCouponBtn) {
        $.post('coupon_handler.php', {action:'remove'}, function() {
            showCartToast('Coupon removed', true);
            reloadCartUI();
        }, 'json');
    }
});

// ── Remove Item ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-item-btn');
    if (btn) { pendingRemoveCartId = btn.dataset.cartId; new bootstrap.Modal(document.getElementById('removeItemModal')).show(); }
});
document.getElementById('confirmRemoveBtn')?.addEventListener('click', function() {
    bootstrap.Modal.getInstance(document.getElementById('removeItemModal'))?.hide();
    $.post('cart_handler.php', {action:'remove', cart_id: pendingRemoveCartId}, function(data) {
        showCartToast(data.message, data.success);
        if (data.success) {
            updateCartBadge(data.cart_count);
            document.getElementById('cartItemCountLabel').textContent = '('+data.cart_count+' items)';
            if (data.cart_count === 0) location.reload(); else reloadCartUI(); // Full reload if empty to show empty state
        }
    }, 'json');
});

// ── Clear Cart ──
document.getElementById('clearCartBtn')?.addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('clearCartModal')).show();
});
document.getElementById('confirmClearBtn')?.addEventListener('click', function() {
    bootstrap.Modal.getInstance(document.getElementById('clearCartModal'))?.hide();
    $.post('cart_handler.php', {action:'clear'}, function(data) {
        if (data.success) {
            showCartToast(data.message, true);
            setTimeout(() => location.reload(), 500); // Reload to show empty state cleanly
        }
    }, 'json');
});

// ── Quantity Stepper ──
document.addEventListener('click', function(e) {
    var dec = e.target.closest('.qty-dec');
    var inc = e.target.closest('.qty-inc');
    var btn = dec || inc;
    if (!btn) return;
    
    var cid = btn.dataset.cartId, max = parseInt(btn.dataset.max);
    var cur = parseInt(document.getElementById('qty-input-' + cid).value);
    
    if (dec && cur <= 1) return;
    if (inc && cur >= max) { showCartToast('Maximum stock reached.', false); return; }
    
    var nq = dec ? cur - 1 : cur + 1;
    $.post('cart_handler.php', {action:'update_qty', cart_id:cid, quantity:nq}, function(data) {
        if (data.success) {
            updateCartBadge(data.cart_count);
            reloadCartUI(); // Instantly update view without reloading page
        } else {
            showCartToast(data.message, false);
        }
    }, 'json');
});
</script>


<style>
.cart-row { transition: background .2s; }
.cart-row:hover { background: rgba(0,0,0,.02); }
</style>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>