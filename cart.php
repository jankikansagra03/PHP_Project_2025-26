<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title = "Shopping Cart - JK Store";
$active_sidebar = 'cart';

$esc_email = mysqli_real_escape_string($con, $email);

// Fetch cart items
$q = "SELECT c.id as cart_id, c.quantity,
             p.id as product_id, p.name, p.image, p.price, p.discount, p.final_price, p.stock
      FROM cart c
      JOIN products p ON c.product_id = p.id
      WHERE c.user_email='$esc_email'
      ORDER BY c.added_at DESC";
$result   = mysqli_query($con, $q);
$items    = [];
$subtotal = 0.0;
while ($row = mysqli_fetch_assoc($result)) {
    $row['line_total'] = (float)$row['final_price'] * (int)$row['quantity'];
    $subtotal += $row['line_total'];
    $items[] = $row;
}
$count = count($items);

// Calculate coupon discount
$discount_amount = 0.0;
$coupon = $_SESSION['applied_coupon'] ?? null;
if ($coupon && $subtotal > 0) {
    if ($coupon['discount_type'] == 'percent') {
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

<!-- ── Page Header ────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0 text-white">
        <i class="fas fa-shopping-cart me-2 opacity-75"></i>
        Shopping Cart
        <span class="fs-5 fw-normal ms-1 opacity-75">(<?= $count ?> item<?= $count !== 1 ? 's' : '' ?>)</span>
    </h2>
    <div class="d-flex gap-2">
        <?php if ($count > 0): ?>
            <button class="btn btn-sm rounded-pill text-white border border-white border-opacity-50 px-3"
                onclick="clearCart()">
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
            <div class="mb-4"><span style="font-size:5rem; line-height:1;">🛒</span></div>
            <h4 class="fw-bold mb-2">Your cart is empty</h4>
            <p class="text-muted mb-4">Looks like you haven't added anything yet. Browse our collection!</p>
            <a href="shop.php" class="btn btn-gradient px-5 py-2 rounded-pill">
                <i class="fas fa-store me-2"></i>Browse Products
            </a>
        </div>
    </div>

<?php else: ?>

    <div class="row g-4">

        <!-- ── Cart Items List ──────────────────────────────── -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <?php foreach ($items as $idx => $item): ?>
                        <div class="cart-row d-flex align-items-center gap-3 p-4 <?= $idx < count($items) - 1 ? 'border-bottom' : '' ?>">

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
                                    <?php endif; ?>
                                </div>

                                <!-- Quantity Stepper -->
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2"
                                        onclick="changeQty(<?= $item['cart_id'] ?>, -1, <?= $item['stock'] ?>)">−</button>
                                    <span id="qty-<?= $item['cart_id'] ?>" class="fw-bold px-2" style="min-width:28px;text-align:center;"><?= $item['quantity'] ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2"
                                        onclick="changeQty(<?= $item['cart_id'] ?>, +1, <?= $item['stock'] ?>)">+</button>
                                </div>
                            </div>

                            <!-- Line Total + Remove -->
                            <div class="text-end flex-shrink-0">
                                <div class="fw-bold fs-6 mb-2">&#8377;<?= number_format($item['line_total'], 2) ?></div>
                                <button type="button"
                                    onclick="removeFromCart(<?= $item['cart_id'] ?>)"
                                    class="btn btn-sm btn-outline-danger rounded-circle"
                                    style="width:32px; height:32px; padding:0; line-height:1;"
                                    title="Remove">
                                    <i class="fas fa-trash" style="font-size:.75rem;"></i>
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
                            <div class="d-flex align-items-center justify-content-between rounded-3 p-2 px-3"
                                style="background:linear-gradient(135deg,#dcfce7,#bbf7d0); border:1.5px dashed #16a34a;">
                                <div>
                                    <span class="badge bg-success rounded-pill me-2 fw-bold"><?= htmlspecialchars($coupon['code']) ?></span>
                                    <small class="text-success fw-semibold"><?= htmlspecialchars($coupon['description'] ?? 'Discount applied') ?></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-link p-0 text-danger fw-bold ms-2"
                                    onclick="removeCoupon()" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="input-group input-group-sm rounded-3 overflow-hidden" style="border:2px solid #e2e8f0;">
                                <input type="text" id="couponCodeInput"
                                    class="form-control border-0 bg-transparent fw-semibold text-uppercase"
                                    placeholder="Enter promo code" maxlength="30"
                                    style="letter-spacing:1.5px; text-transform:uppercase;">
                                <button type="button" class="btn btn-gradient px-3 fw-bold"
                                    onclick="applyCoupon()">Apply</button>
                            </div>
                        <?php endif; ?>
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

                    <a href="checkout.php" class="btn btn-gradient w-100 py-3 fw-bold rounded-3 mb-3 shadow-sm">
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

<!-- Confirm Modal -->
<div class="modal fade" id="cartConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3" style="font-size:2.5rem;" id="cartConfirmIcon">⚠️</div>
                <h6 class="fw-bold mb-2" id="cartConfirmText"></h6>
                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-gradient rounded-pill" id="cartConfirmOkBtn">Yes, Confirm</button>
                    <button type="button" class="btn btn-outline-gradient rounded-pill" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="cartAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3" style="font-size:2.5rem;">ℹ️</div>
                <p class="fw-semibold mb-3" id="cartAlertText"></p>
                <button type="button" class="btn btn-gradient rounded-pill px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
    .cart-row {
        transition: background .2s;
    }

    .cart-row:hover {
        background: rgba(0, 0, 0, .02);
    }
</style>

<script>
    function showCartAlert(msg) {
        document.getElementById('cartAlertText').textContent = msg;
        new bootstrap.Modal(document.getElementById('cartAlertModal')).show();
    }

    function showCartConfirm(msg, icon, onConfirm) {
        document.getElementById('cartConfirmText').textContent = msg;
        document.getElementById('cartConfirmIcon').textContent = icon;
        var modal = new bootstrap.Modal(document.getElementById('cartConfirmModal'));
        var btn = document.getElementById('cartConfirmOkBtn');
        // Remove previous listener
        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener('click', function() {
            modal.hide();
            onConfirm();
        });
        modal.show();
    }

    function removeFromCart(cartId) {
        showCartConfirm('Remove this item from cart?', '🗑️', function() {
            $.post('cart_handler.php', {
                action: 'remove',
                cart_id: cartId
            }, function(response) {
                if (response == 'success') {
                    location.reload();
                } else {
                    showCartAlert(response.replace('error: ', ''));
                }
            });
        });
    }

    function changeQty(cartId, delta, maxStock) {
        var $span = $('#qty-' + cartId);
        var current = parseInt($span.text());
        var newQty = current + delta;
        if (newQty < 1) return;
        if (newQty > maxStock) {
            showCartAlert('Maximum available: ' + maxStock + ' units.');
            return;
        }
        $span.text(newQty);
        $.post('cart_handler.php', {
            action: 'update_qty',
            cart_id: cartId,
            quantity: newQty
        }, function(response) {
            if (response == 'success') {
                location.reload();
            } else {
                showCartAlert(response.replace('error: ', ''));
            }
        });
    }

    function clearCart() {
        showCartConfirm('Clear all items from cart?', '🛒', function() {
            $.post('cart_handler.php', {
                action: 'clear'
            }, function(response) {
                if (response == 'success') {
                    location.reload();
                } else {
                    showCartAlert(response.replace('error: ', ''));
                }
            });
        });
    }

    function applyCoupon() {
        var code = $('#couponCodeInput').val().trim();
        if (!code) {
            showCartAlert('Please enter a coupon code.');
            return;
        }
        $.post('coupon_handler.php', {
            action: 'apply',
            coupon_code: code
        }, function(response) {
            if (response == 'success') {
                location.reload();
            } else {
                showCartAlert(response.replace('error: ', ''));
            }
        });
    }

    function removeCoupon() {
        $.post('coupon_handler.php', {
            action: 'remove'
        }, function(response) {
            if (response == 'success') {
                location.reload();
            } else {
                showCartAlert(response.replace('error: ', ''));
            }
        });
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>