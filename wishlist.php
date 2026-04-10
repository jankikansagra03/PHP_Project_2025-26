<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$esc_email = mysqli_real_escape_string($con, $email);

$q      = "SELECT w.id as wishlist_id, w.product_id,
                  p.name, p.image, p.price, p.discount, p.final_price, p.stock, p.brand, p.description
           FROM wishlist w
           JOIN products p ON w.product_id = p.id
           WHERE w.user_email='$esc_email'
           ORDER BY w.added_at DESC";
$result = mysqli_query($con, $q);
$count  = mysqli_num_rows($result);
$items  = [];
while ($row = mysqli_fetch_assoc($result)) $items[] = $row;

// Cart ids for "In Cart" badge
$cart_q   = mysqli_query($con, "SELECT product_id FROM cart WHERE user_email='$esc_email'");
$cart_ids = [];
while ($r = mysqli_fetch_assoc($cart_q)) $cart_ids[] = (int)$r['product_id'];

$title          = "My Wishlist - JK Store";
$active_sidebar = 'wishlist';

ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0 text-white">
        <i class="fas fa-heart me-2 opacity-75"></i>
        My Wishlist
        <span class="fs-5 fw-normal ms-1 opacity-75" id="wlCountLabel">(<?= $count ?> item<?= $count !== 1 ? 's' : '' ?>)</span>
    </h2>
    <div class="d-flex gap-2">
        <?php if ($count > 0): ?>
            <button class="btn btn-sm rounded-pill text-white border border-white border-opacity-50 px-3" id="clearWlBtn">
                <i class="fas fa-trash me-2"></i>Clear All
            </button>
        <?php endif; ?>
        <a href="shop.php" class="btn btn-sm rounded-pill text-white border border-white border-opacity-50 px-3">
            <i class="fas fa-store me-2"></i>Continue Shopping
        </a>
    </div>
</div>

<?php if ($count === 0): ?>
    <!-- Empty State -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <div class="mb-4" style="font-size:5rem; line-height:1;">💝</div>
            <h4 class="fw-bold mb-2">Your wishlist is empty</h4>
            <p class="text-muted mb-4">Save items you love and revisit them anytime!</p>
            <a href="shop.php" class="btn btn-gradient px-5 py-2 rounded-pill">
                <i class="fas fa-store me-2"></i>Start Browsing
            </a>
        </div>
    </div>

<?php else: ?>

    <div class="row g-4" id="wlGrid">
        <?php foreach ($items as $item):
            $in_cart = in_array((int)$item['product_id'], $cart_ids);
        ?>
            <div class="col-sm-6 col-xl-4" id="wl-item-<?= $item['wishlist_id'] ?>">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100" style="transition: transform .2s, box-shadow .2s;">

                    <!-- Image -->
                    <div class="position-relative bg-white d-flex align-items-center justify-content-center border-bottom" style="aspect-ratio:1/1;">
                        <a href="product_detail.php?id=<?= $item['product_id'] ?>">
                            <img src="<?= htmlspecialchars($item['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                                class="img-fluid p-4"
                                style="max-height:100%; object-fit:contain; mix-blend-mode:multiply;">
                        </a>

                        <!-- Remove Button (top right) -->
                        <button class="btn btn-danger btn-sm rounded-circle shadow-sm position-absolute top-0 end-0 m-3 remove-wl-btn d-flex align-items-center justify-content-center"
                            style="width:34px; height:34px; padding:0;"
                            data-wishlist-id="<?= $item['wishlist_id'] ?>"
                            data-product-id="<?= $item['product_id'] ?>"
                            title="Remove from Wishlist">
                            <i class="fas fa-heart-broken" style="font-size:.8rem;"></i>
                        </button>

                        <!-- In Cart Badge (bottom left) -->
                        <span class="badge bg-success position-absolute bottom-0 start-0 m-3 px-2 py-1 shadow-sm"
                            id="wl-cart-badge-<?= $item['product_id'] ?>"
                            style="font-size:.72rem; <?= $in_cart ? '' : 'display:none;' ?>">
                            <i class="fas fa-check me-1"></i>In Cart
                        </span>

                        <!-- Discount Badge -->
                        <?php if (!empty($item['discount']) && $item['discount'] > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 px-2 py-1">
                                -&#8377;<?= number_format((float)$item['discount'], 0) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4 d-flex flex-column">
                        <?php if (!empty($item['brand'])): ?>
                            <span class="badge bg-light text-dark border small mb-2 align-self-start">
                                <?= htmlspecialchars($item['brand']) ?>
                            </span>
                        <?php endif; ?>

                        <h6 class="fw-bold mb-1 text-truncate">
                            <a href="product_detail.php?id=<?= $item['product_id'] ?>"
                                class="text-decoration-none text-dark">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                        </h6>

                        <p class="text-muted small mb-3"
                            style="display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            <?= htmlspecialchars($item['description'] ?? '') ?>
                        </p>

                        <!-- Price -->
                        <div class="d-flex align-items-center gap-2 mb-4 mt-auto">
                            <span class="fw-bold fs-5" style="color:var(--theme-primary);">
                                &#8377;<?= number_format((float)$item['final_price'], 2) ?>
                            </span>
                            <?php if ($item['discount'] > 0): ?>
                                <span class="text-muted text-decoration-line-through small">
                                    &#8377;<?= number_format((float)$item['price'], 2) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($item['stock'] <= 0): ?>
                                <span class="badge bg-danger ms-auto">Out of Stock</span>
                            <?php elseif ($item['stock'] <= 5): ?>
                                <span class="badge bg-warning text-dark ms-auto">Only <?= $item['stock'] ?> left</span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <?php if ($item['stock'] > 0): ?>
                                <button class="btn <?= $in_cart ? 'btn-success' : 'btn-gradient' ?> rounded-3 add-to-cart-wl-btn fw-semibold"
                                    data-product-id="<?= $item['product_id'] ?>"
                                    id="wl-cart-btn-<?= $item['product_id'] ?>">
                                    <i class="fas <?= $in_cart ? 'fa-check' : 'fa-shopping-cart' ?> me-2"></i>
                                    <?= $in_cart ? 'In Cart' : 'Add to Cart' ?>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary rounded-3 fw-semibold" disabled>
                                    <i class="fas fa-ban me-2"></i>Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<!-- Confirm Modal -->
<div class="modal fade" id="wlConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3" style="font-size:2.5rem;" id="wlConfirmIcon">⚠️</div>
                <h6 class="fw-bold mb-2" id="wlConfirmText"></h6>
                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-gradient rounded-pill" id="wlConfirmOkBtn">Yes, Confirm</button>
                    <button type="button" class="btn btn-outline-gradient rounded-pill" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="wlAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3" style="font-size:2.5rem;">ℹ️</div>
                <p class="fw-semibold mb-3" id="wlAlertText"></p>
                <button type="button" class="btn btn-gradient rounded-pill px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showWlAlert(msg) {
        document.getElementById('wlAlertText').textContent = msg;
        new bootstrap.Modal(document.getElementById('wlAlertModal')).show();
    }

    function showWlConfirm(msg, icon, onConfirm) {
        document.getElementById('wlConfirmText').textContent = msg;
        document.getElementById('wlConfirmIcon').textContent = icon;
        var modal = new bootstrap.Modal(document.getElementById('wlConfirmModal'));
        var btn = document.getElementById('wlConfirmOkBtn');
        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener('click', function() {
            modal.hide();
            onConfirm();
        });
        modal.show();
    }

    // ── Remove from Wishlist ──
    $(document).on('click', '.remove-wl-btn', function() {
        var pid = $(this).data('productId');
        showWlConfirm('Remove this item from wishlist?', '💔', function() {
            $.post('wishlist_handler.php', {
                action: 'remove',
                product_id: pid
            }, function(response) {
                if (response == 'success') {
                    location.reload();
                } else {
                    showWlAlert(response.replace('error: ', ''));
                }
            });
        });
    });

    // ── Clear Wishlist ──
    $(document).on('click', '#clearWlBtn', function() {
        showWlConfirm('Clear your entire wishlist?', '🗑️', function() {
            window.location.href = 'clear_wishlist.php';
        });
    });

    // ── Add to Cart from Wishlist ──
    $(document).on('click', '.add-to-cart-wl-btn', function() {
        var pid = $(this).data('productId');
        $.post('cart_handler.php', {
            action: 'add',
            product_id: pid,
            quantity: 1
        }, function(response) {
            if (response == 'success') {
                location.reload();
            } else {
                showWlAlert(response.replace('error: ', ''));
            }
        });
    });
</script>

<style>
    #wlGrid .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, .12) !important;
    }
</style>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>