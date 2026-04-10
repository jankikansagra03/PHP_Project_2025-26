<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title          = "Review Order - JK Store";
$active_sidebar = 'cart';

$esc_email = mysqli_real_escape_string($con, $email);

$address_id = (int)($_GET['address_id'] ?? 0);
if (!$address_id) {
    header('Location: checkout.php');
    exit;
}

$addr_q = mysqli_query($con, "SELECT * FROM addresses WHERE id=$address_id AND email='$esc_email' LIMIT 1");
$addr   = $addr_q ? mysqli_fetch_assoc($addr_q) : null;
if (!$addr) {
    header('Location: checkout.php');
    exit;
}

$cart_q = mysqli_query($con, "
    SELECT c.id as cart_id, c.quantity,
           p.id as product_id, p.name, p.image, p.final_price, p.price, p.discount, p.stock
    FROM cart c JOIN products p ON c.product_id=p.id
    WHERE c.user_email='$esc_email' ORDER BY c.added_at DESC
");
$cart_items = [];
$cart_subtotal = 0.0;
while ($row = mysqli_fetch_assoc($cart_q)) {
    $row['line_total'] = (float)$row['final_price'] * (int)$row['quantity'];
    $cart_subtotal    += $row['line_total'];
    $cart_items[]      = $row;
}
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$coupon = $_SESSION['applied_coupon'] ?? null;
$discount_amount = 0.0;
$coupon_code = '';
if ($coupon) {
    $coupon_code = $coupon['code'];
    if ($coupon['discount_type'] === 'percent') {
        $discount_amount = $cart_subtotal * ((float)$coupon['discount_value'] / 100);
        if (!empty($coupon['max_discount_amount']))
            $discount_amount = min($discount_amount, (float)$coupon['max_discount_amount']);
    } else {
        $discount_amount = (float)$coupon['discount_amount'];
    }
    $discount_amount = min(round($discount_amount, 2), $cart_subtotal);
}
$total = round($cart_subtotal - $discount_amount, 2);

$icons  = ['home' => 'fa-home', 'office' => 'fa-building', 'other' => 'fa-map-marker-alt'];
$colors = ['home' => '#3b82f6', 'office' => '#10b981', 'other' => '#f59e0b'];
$lbl    = strtolower($addr['label'] ?? 'home');

// Payment error from gateway redirect
$payment_error = $_SESSION['payment_error'] ?? null;
unset($_SESSION['payment_error']);

// Cashfree JS env
include_once 'payment_config.php';

ob_start();
?>

<?php if ($payment_error): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($payment_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Step Indicator -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3 px-4">
        <div style="display:flex;align-items:center;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                <div style="width:36px;height:36px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;color:#0284c7;">
                    <i class="fas fa-check" style="font-size:.75rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:600;color:#94a3b8;">Address</span>
            </div>
            <div style="flex:1;height:3px;background:linear-gradient(90deg,#0284c7,var(--theme-primary,#1f7a8c));margin:0 8px;border-radius:3px;margin-bottom:18px;"></div>
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--theme-primary,#1f7a8c),#0f4c5c);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;box-shadow:0 4px 12px rgba(31,122,140,.3);">2</div>
                <span style="font-size:.72rem;font-weight:700;color:var(--theme-primary,#1f7a8c);">Payment</span>
            </div>
            <div style="flex:1;height:3px;background:#e2e8f0;margin:0 8px;border-radius:3px;margin-bottom:18px;"></div>
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                <div style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;color:#94a3b8;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;border:2px solid #e2e8f0;">3</div>
                <span style="font-size:.72rem;font-weight:600;color:#94a3b8;">Done</span>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="ocToast" class="toast align-items-center text-white border-0 shadow-lg rounded-3" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold small" id="ocToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- LEFT: Delivery Summary + Payment Methods -->
    <div class="col-lg-5">

        <!-- Delivering To -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-size:1rem;">
                        <i class="fas fa-map-marker-alt me-2" style="color:var(--theme-primary);"></i>Delivering To
                    </h5>
                    <a href="checkout.php" style="font-size:.8rem;color:var(--theme-primary);text-decoration:none;font-weight:600;">
                        <i class="fas fa-edit me-1"></i>Change
                    </a>
                </div>
                <div style="border:2px solid var(--theme-primary,#1f7a8c);border-radius:14px;padding:1rem 1.25rem;background:rgba(31,122,140,.03);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas <?= $icons[$lbl] ?? 'fa-map-marker-alt' ?>" style="color:<?= $colors[$lbl] ?? '#64748b' ?>;"></i>
                        <span style="font-weight:800;text-transform:capitalize;color:#0f172a;"><?= htmlspecialchars($addr['label'] ?? 'Home') ?></span>
                        <?php if (!empty($addr['is_default'])): ?>
                            <span style="background:#dbeafe;color:#1d4ed8;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:20px;">DEFAULT</span>
                        <?php endif; ?>
                    </div>
                    <p style="font-weight:700;margin:0 0 4px;color:#1e293b;"><?= htmlspecialchars($addr['name']) ?></p>
                    <p style="color:#64748b;margin:0 0 4px;font-size:.85rem;">
                        <?= htmlspecialchars($addr['address']) ?>,
                        <?= htmlspecialchars($addr['city'] ?? '') ?>,
                        <?= htmlspecialchars($addr['state'] ?? '') ?>
                        <?= !empty($addr['zip']) ? '- ' . htmlspecialchars($addr['zip']) : '' ?>
                    </p>
                    <p style="color:#64748b;margin:0;font-size:.85rem;">
                        <i class="fas fa-phone me-1" style="font-size:.7rem;"></i><?= htmlspecialchars($addr['phone']) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Coupon Applied -->
        <?php if ($coupon && $discount_amount > 0): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 style="font-weight:800;font-size:1rem;margin-bottom:.75rem;">
                        <i class="fas fa-tag me-2" style="color:#16a34a;"></i>Coupon Applied
                    </h5>
                    <div style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);border:1.5px dashed #16a34a;border-radius:14px;padding:.75rem 1rem;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <span style="background:#16a34a;color:#fff;font-weight:800;font-size:.8rem;padding:3px 12px;border-radius:20px;letter-spacing:1px;"><?= htmlspecialchars($coupon_code) ?></span>
                            <p style="color:#16a34a;font-size:.82rem;margin:4px 0 0;font-weight:600;"><?= htmlspecialchars($coupon['description'] ?? '') ?></p>
                        </div>
                        <span style="font-size:1.1rem;font-weight:900;color:#15803d;">-₹<?= number_format($discount_amount, 2) ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Method Selection -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 style="font-weight:800;font-size:1rem;margin-bottom:1rem;">
                    <i class="fas fa-credit-card me-2" style="color:var(--theme-primary);"></i>Choose Payment Method
                </h5>
                <div class="d-flex flex-column gap-3" id="paymentMethods">

                    <!-- COD -->
                    <label class="payment-option-card" id="pm-cod" style="border:2px solid var(--theme-primary,#1f7a8c);background:rgba(31,122,140,.03);">
                        <input type="radio" name="payment_method" value="cod" checked style="accent-color:var(--theme-primary);">
                        <div class="pm-icon" style="background:#dcfce7;"><i class="fas fa-money-bill-wave" style="color:#16a34a;"></i></div>
                        <div>
                            <div class="pm-title">Cash on Delivery</div>
                            <div class="pm-sub">Pay when your order arrives</div>
                        </div>
                    </label>

                    <!-- Cashfree -->
                    <label class="payment-option-card" id="pm-cashfree" style="border:2px solid #e2e8f0;background:#fff;">
                        <input type="radio" name="payment_method" value="cashfree" style="accent-color:var(--theme-primary);">
                        <div class="pm-icon" style="background:#e0f2fe;"><i class="fas fa-bolt" style="color:#0284c7;"></i></div>
                        <div>
                            <div class="pm-title">Cashfree</div>
                            <div class="pm-sub">UPI, Cards, Net Banking via Cashfree</div>
                        </div>
                        <span class="pm-badge" style="background:#0284c7;">Popular</span>
                    </label>

                    <!-- Razorpay -->
                    <label class="payment-option-card" id="pm-razorpay" style="border:2px solid #e2e8f0;background:#fff;">
                        <input type="radio" name="payment_method" value="razorpay" style="accent-color:var(--theme-primary);">
                        <div class="pm-icon" style="background:#ede9fe;"><i class="fas fa-credit-card" style="color:#7c3aed;"></i></div>
                        <div>
                            <div class="pm-title">Razorpay</div>
                            <div class="pm-sub">UPI, Cards, Wallets via Razorpay</div>
                        </div>
                    </label>

                    <!-- PayPal -->
                    <label class="payment-option-card" id="pm-paypal" style="border:2px solid #e2e8f0;background:#fff;">
                        <input type="radio" name="payment_method" value="paypal" style="accent-color:var(--theme-primary);">
                        <div class="pm-icon" style="background:#fef3c7;"><i class="fab fa-paypal" style="color:#f59e0b;"></i></div>
                        <div>
                            <div class="pm-title">PayPal</div>
                            <div class="pm-sub">Pay securely via PayPal</div>
                        </div>
                    </label>

                </div>
                <!-- PayPal Buttons Container (shown only when PayPal selected) -->
                <div id="paypalBtnContainer" class="mt-3" style="display:none;"></div>
            </div>
        </div>

    </div>

    <!-- RIGHT: Order Summary + Place Order -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px;">
            <div class="card-body p-4">
                <h5 style="font-weight:800;margin-bottom:1.25rem;">
                    <i class="fas fa-receipt me-2" style="color:var(--theme-primary);opacity:.8;"></i>Order Summary
                </h5>

                <!-- Items -->
                <div style="max-height:300px;overflow-y:auto;margin-bottom:1.25rem;">
                    <?php foreach ($cart_items as $ci): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                            <div style="width:52px;height:52px;background:#f8fafc;border-radius:10px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;">
                                <img src="<?= htmlspecialchars($ci['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                    style="width:44px;height:44px;object-fit:contain;mix-blend-mode:multiply;">
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;color:#1e293b;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($ci['name']) ?></div>
                                <div style="color:#94a3b8;font-size:.78rem;margin-top:2px;">Qty: <?= $ci['quantity'] ?> × ₹<?= number_format($ci['final_price'], 2) ?></div>
                            </div>
                            <div style="font-weight:800;color:#1e293b;font-size:.9rem;flex-shrink:0;">₹<?= number_format($ci['line_total'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pricing -->
                <div style="background:#f8fafc;border-radius:12px;padding:1rem;margin-bottom:1.25rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#64748b;font-size:.88rem;">Subtotal (<?= count($cart_items) ?> items)</span>
                        <span style="font-weight:600;">₹<?= number_format($cart_subtotal, 2) ?></span>
                    </div>
                    <?php if ($discount_amount > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color:#16a34a;font-size:.88rem;font-weight:600;"><i class="fas fa-tag me-1"></i>Discount (<?= htmlspecialchars($coupon_code) ?>)</span>
                            <span style="color:#16a34a;font-weight:700;">-₹<?= number_format($discount_amount, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#64748b;font-size:.88rem;">Shipping</span>
                        <span style="background:#dcfce7;color:#16a34a;font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:20px;">FREE</span>
                    </div>
                    <hr style="border-color:#e2e8f0;margin:10px 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-weight:800;font-size:1.05rem;color:#0f172a;">Total Payable</span>
                        <span style="font-weight:900;font-size:1.5rem;color:var(--theme-primary,#1f7a8c);">₹<?= number_format($total, 2) ?></span>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="d-flex gap-3 mb-4 flex-wrap">
                    <span style="font-size:.75rem;color:#64748b;"><i class="fas fa-shield-alt text-success me-1"></i>Secure Payment</span>
                    <span style="font-size:.75rem;color:#64748b;"><i class="fas fa-undo text-primary me-1"></i>7-Day Returns</span>
                    <span style="font-size:.75rem;color:#64748b;"><i class="fas fa-shipping-fast text-warning me-1"></i>Free Shipping</span>
                </div>

                <!-- Place Order Button (hidden when PayPal selected) -->
                <button class="btn btn-gradient w-100 py-3 fw-bold rounded-3 shadow-sm mb-3"
                    id="placeOrderBtn" style="font-size:1.05rem;" onclick="placeOrder()">
                    <i class="fas fa-lock me-2"></i>Place Order
                </button>
                <a href="checkout.php" class="btn btn-outline-secondary w-100 rounded-3 py-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Checkout
                </a>
            </div>
        </div>
    </div>

</div>

<style>
    .payment-option-card {
        display: flex;
        align-items: center;
        gap: 14px;
        border-radius: 14px;
        padding: .85rem 1rem;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
    }

    .payment-option-card input[type=radio] {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
    }

    .pm-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .pm-title {
        font-weight: 700;
        font-size: .9rem;
        color: #1e293b;
    }

    .pm-sub {
        font-size: .75rem;
        color: #94a3b8;
        margin-top: 1px;
    }

    .pm-badge {
        position: absolute;
        top: 10px;
        right: 12px;
        font-size: .6rem;
        font-weight: 700;
        color: #fff;
        padding: 2px 8px;
        border-radius: 20px;
    }
</style>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=<?= PP_CLIENT_ID ?>&currency=INR" data-namespace="paypalSDK"></script>

<script>
    const ADDRESS_ID = <?= (int)$address_id ?>;
    const COUPON_CODE = '<?= addslashes(htmlspecialchars($coupon_code, ENT_QUOTES)) ?>';
    const ORDER_TOTAL = <?= $total ?>;

    function showToast(msg, ok) {
        var $t = $('#ocToast');
        $t.removeClass('bg-success bg-danger').addClass(ok ? 'bg-success' : 'bg-danger');
        $('#ocToastMsg').text(msg);
        new bootstrap.Toast($t.get(0), {
            delay: 4000
        }).show();
    }

    // Highlight selected payment card
    $(document).on('change', 'input[name="payment_method"]', function() {
        $('.payment-option-card').css({
            borderColor: '#e2e8f0',
            background: '#fff'
        });

        $(this).closest('.payment-option-card').css({
            borderColor: 'var(--theme-primary,#1f7a8c)',
            background: 'rgba(31,122,140,.03)'
        });

        const isPayPal = $(this).val() === 'paypal';
        $('#placeOrderBtn').css('display', isPayPal ? 'none' : '');
        $('#paypalBtnContainer').css('display', isPayPal ? '' : 'none');
        if (isPayPal) renderPayPalButtons();
    });

    // ── Place Order dispatcher ───────────────────────────────
    function placeOrder() {
        const method = $('input[name="payment_method"]:checked').val();
        const btn = $('#placeOrderBtn').get(0);

        if (method === 'cod') placeCOD(btn);
        else if (method === 'cashfree') initCashfree(btn);
        else if (method === 'razorpay') initRazorpay(btn);
    }

    function setLoading(btn, busy, label) {
        if (!btn) return;
        btn.disabled = busy;
        btn.innerHTML = busy ?
            '<i class="fas fa-spinner fa-spin me-2"></i>Processing...' :
            '<i class="fas fa-lock me-2"></i>' + (label || 'Place Order');
    }

    // ── COD ──────────────────────────────────────────────────
    function placeCOD(btn) {
        setLoading(btn, true);
        $.post('place_order.php', {
            address_id: ADDRESS_ID,
            payment_method: 'cod',
            coupon_code: COUPON_CODE
        }, function(raw) {
            var data = parseAppReply(raw);
            if (data.success) {
                window.location.href = 'order_success.php?order_id=' + data.order_id;
            } else {
                showToast(data.message || 'Failed to place order.', false);
                setLoading(btn, false);
            }
        }, 'text').fail(function() {
            showToast('Server error. Please try again.', false);
            setLoading(btn, false);
        });
    }

    // ── Cashfree ─────────────────────────────────────────────
    function initCashfree(btn) {
        setLoading(btn, true);
        $.post('cashfree_create_order.php', {
            address_id: ADDRESS_ID,
            coupon_code: COUPON_CODE
        }, function(raw) {
            var data = parseAppReply(raw);
            if (!data.success) {
                showToast(data.message || 'Cashfree error.', false);
                setLoading(btn, false);
                return;
            }
            const cashfree = Cashfree({
                mode: '<?= CF_JS_ENV ?>'
            });
            cashfree.checkout({
                    paymentSessionId: data.payment_session_id
                })
                .then(function(result) {
                    if (result.error) {
                        showToast(result.error.message, false);
                        setLoading(btn, false);
                    }
                    // redirect happens automatically via return_url
                });
        }, 'text').fail(function() {
            showToast('Server error. Please try again.', false);
            setLoading(btn, false);
        });
    }

    // ── Razorpay ─────────────────────────────────────────────
    function initRazorpay(btn) {
        setLoading(btn, true);
        $.post('razorpay_create_order.php', {
            address_id: ADDRESS_ID,
            coupon_code: COUPON_CODE
        }, function(raw) {
            var data = parseAppReply(raw);
            if (!data.success) {
                showToast(data.message || 'Razorpay error.', false);
                setLoading(btn, false);
                return;
            }
            const options = {
                key: data.key_id,
                amount: data.amount,
                currency: data.currency,
                name: 'JK Store',
                description: 'Order #' + data.order_number,
                order_id: data.rzp_order_id,
                image: '<?= APP_URL ?>/images/logo.png',
                handler: function(response) {
                    // Verify signature
                    $.post('razorpay_verify.php', {
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    }, function(reply) {
                        var vData = parseAppReply(reply);
                        if (vData.success) {
                            window.location.href = 'order_success.php?order_id=' + vData.order_id;
                        } else {
                            showToast(vData.message || 'Verification failed.', false);
                            setLoading(btn, false, 'Place Order');
                        }
                    }, 'text');
                },
                modal: {
                    ondismiss: function() {
                        setLoading(btn, false);
                    }
                },
                prefill: {
                    name: '<?= addslashes(htmlspecialchars($addr['name'], ENT_QUOTES)) ?>',
                    email: '<?= addslashes(htmlspecialchars($email, ENT_QUOTES)) ?>',
                    contact: '<?= preg_replace('/[^0-9]/', '', $addr['phone'] ?? '') ?>'
                },
                theme: {
                    color: '#1f7a8c'
                }
            };
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function(resp) {
                showToast('Payment failed: ' + resp.error.description, false);
                setLoading(btn, false);
            });
            rzp.open();
        }, 'text').fail(function() {
            showToast('Server error. Please try again.', false);
            setLoading(btn, false);
        });
    }

    // ── PayPal ───────────────────────────────────────────────
    let paypalRendered = false;

    function renderPayPalButtons() {
        if (paypalRendered) return;
        paypalRendered = true;
        paypalSDK.Buttons({
            createOrder: function(data, actions) {
                return $.post('paypal_create_order.php', {
                    address_id: ADDRESS_ID,
                    coupon_code: COUPON_CODE
                }).then(function(resp) {
                    const d = parseAppReply(resp);
                    if (!d.success) throw new Error(d.message);
                    return d.paypal_order_id;
                });
            },
            onApprove: function(data, actions) {
                return $.post('paypal_capture.php', {
                    paypal_order_id: data.orderID
                }).then(function(resp) {
                    const d = parseAppReply(resp);
                    if (d.success) {
                        window.location.href = 'order_success.php?order_id=' + d.order_id;
                    } else {
                        showToast(d.message || 'PayPal capture failed.', false);
                    }
                });
            },
            onError: function(err) {
                showToast('PayPal error: ' + err, false);
            },
            onCancel: function() {
                showToast('PayPal payment cancelled.', false);
            },
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'pay'
            }
        }).render('#paypalBtnContainer');
    }
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>