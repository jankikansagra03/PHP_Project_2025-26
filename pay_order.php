<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title          = "Pay Order - JK Store";
$active_sidebar = 'orders';

$esc_email = mysqli_real_escape_string($con, $email);
$order_id  = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    header('Location: my_orders.php');
    exit;
}

$oq = mysqli_query($con, "SELECT * FROM orders WHERE id=$order_id AND user_email='$esc_email' LIMIT 1");
$order = $oq ? mysqli_fetch_assoc($oq) : null;

if (!$order || $order['payment_status'] === 'Paid' || $order['order_status'] === 'Cancelled') {
    header('Location: my_orders.php');
    exit;
}

// Fetch items
$iq = mysqli_query($con, "SELECT * FROM order_items WHERE order_id=$order_id");
$items = [];
while ($row = mysqli_fetch_assoc($iq)) {
    $items[] = $row;
}

include_once 'payment_config.php';
ob_start();
?>

<div class="row g-4 justify-content-center mt-2">
    <!-- LEFT: Payment Method Selection -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="font-weight:800;font-size:1.1rem;margin-bottom:0;">
                        <i class="fas fa-credit-card me-2" style="color:var(--theme-primary);"></i>Pay Online
                    </h5>
                    <span class="badge bg-warning text-dark">Order #<?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <p style="color:#64748b; font-size:.85rem; margin-bottom:1.5rem;">
                    Securely pay for your existing Cash on Delivery order online.
                </p>

                <div class="d-flex flex-column gap-3" id="paymentMethods">
                    <!-- Cashfree -->
                    <label class="payment-option-card" id="pm-cashfree" style="border:2px solid var(--theme-primary,#1f7a8c);background:rgba(31,122,140,.03);">
                        <input type="radio" name="payment_method" value="cashfree" checked style="accent-color:var(--theme-primary);">
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

                <!-- PayPal Buttons Container -->
                <div id="paypalBtnContainer" class="mt-3" style="display:none;"></div>

            </div>
        </div>
    </div>

    <!-- RIGHT: Order Summary -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px;">
            <div class="card-body p-4">
                <h5 style="font-weight:800;margin-bottom:1.25rem;">
                    <i class="fas fa-receipt me-2" style="color:var(--theme-primary);opacity:.8;"></i>Order Summary
                </h5>

                <!-- Items -->
                <div style="max-height:250px;overflow-y:auto;margin-bottom:1.25rem;">
                    <?php foreach ($items as $ci): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                            <div style="width:52px;height:52px;background:#f8fafc;border-radius:10px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;">
                                <img src="<?= htmlspecialchars($ci['product_image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                    style="width:44px;height:44px;object-fit:contain;mix-blend-mode:multiply;">
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;color:#1e293b;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($ci['product_name']) ?></div>
                                <div style="color:#94a3b8;font-size:.78rem;margin-top:2px;">Qty: <?= $ci['quantity'] ?></div>
                            </div>
                            <div style="font-weight:800;color:#1e293b;font-size:.9rem;flex-shrink:0;">₹<?= number_format($ci['subtotal'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pricing -->
                <div style="background:#f8fafc;border-radius:12px;padding:1rem;margin-bottom:1.25rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#64748b;font-size:.88rem;">Subtotal</span>
                        <span style="font-weight:600;">₹<?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color:#16a34a;font-size:.88rem;font-weight:600;"><i class="fas fa-tag me-1"></i>Discount</span>
                            <span style="color:#16a34a;font-weight:700;">-₹<?= number_format($order['discount'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#64748b;font-size:.88rem;">Shipping</span>
                        <span style="font-weight:600;">
                            <?= $order['shipping_fee'] > 0 ? '+₹' . number_format($order['shipping_fee'], 2) : '<span style="background:#dcfce7;color:#16a34a;font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:20px;">FREE</span>' ?>
                        </span>
                    </div>
                    <hr style="border-color:#e2e8f0;margin:10px 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-weight:800;font-size:1.05rem;color:#0f172a;">Total Payable</span>
                        <span style="font-weight:900;font-size:1.5rem;color:var(--theme-primary,#1f7a8c);">₹<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="d-flex gap-3 mb-4 justify-content-center">
                    <span style="font-size:.75rem;color:#64748b;"><i class="fas fa-shield-alt text-success me-1"></i>Secure Payment</span>
                    <span style="font-size:.75rem;color:#64748b;"><i class="fas fa-lock text-primary me-1"></i>256-bit SSL</span>
                </div>

                <div id="generalPayBtnContainer">
                    <button class="btn btn-gradient w-100 py-3 fw-bold rounded-3 shadow-sm mb-3"
                        id="payOrderBtn" style="font-size:1.05rem;" onclick="processPayment()">
                        <i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($order['total_amount'], 2) ?>
                    </button>
                </div>

                <a href="my_orders.php" class="btn btn-outline-secondary w-100 rounded-3 py-2">
                    <i class="fas fa-arrow-left me-2"></i>Cancel & Back to Orders
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
    const ORDER_ID = <?= (int)$order_id ?>;

    function setFlashCookie(type, msg) {
        document.cookie = type + '=' + encodeURIComponent(msg) + '; path=/; max-age=5';
    }

    function flashReload(msg, ok) {
        setFlashCookie(ok ? 'success' : 'error', msg || (ok ? 'Success' : 'Something went wrong.'));
        location.reload();
    }

    // Highlight selected card
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
        $('#generalPayBtnContainer').css('display', isPayPal ? 'none' : '');
        $('#paypalBtnContainer').css('display', isPayPal ? '' : 'none');
        if (isPayPal) renderPayPalButtons();
    });

    function setLoading(btn, busy) {
        if (!btn) return;
        btn.disabled = busy;
        btn.innerHTML = busy ?
            '<i class="fas fa-spinner fa-spin me-2"></i>Processing...' :
            '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($order['total_amount'], 2) ?>';
    }

    function processPayment() {
        const method = $('input[name="payment_method"]:checked').val();
        const btn = $('#payOrderBtn').get(0);

        setLoading(btn, true);

        $.post('init_existing_payment.php', {
            order_id: ORDER_ID,
            payment_method: method
        }, function(raw) {
            var data = parseAppReply(raw);
            if (!data.success) {
                flashReload(data.message || 'Payment initiation failed.', false);
                setLoading(btn, false);
                return;
            }

            if (method === 'cashfree') {
                const cashfree = Cashfree({
                    mode: '<?= CF_JS_ENV ?>'
                });
                cashfree.checkout({
                        paymentSessionId: data.payment_session_id
                    })
                    .then(function(result) {
                        if (result.error) {
                            flashReload(result.error.message, false);
                            setLoading(btn, false);
                        }
                    });
            } else if (method === 'razorpay') {
                const options = {
                    key: data.key_id,
                    amount: data.amount,
                    currency: data.currency,
                    name: 'JK Store',
                    description: 'Payment for Order #' + data.order_number,
                    order_id: data.rzp_order_id,
                    image: '<?= APP_URL ?>/images/logo.png',
                    handler: function(response) {
                        $.post('razorpay_verify.php', {
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature
                        }, function(reply) {
                            var vData = parseAppReply(reply);
                            if (vData.success) {
                                window.location.href = 'order_success.php?order_id=' + vData.order_id;
                            } else {
                                flashReload(vData.message || 'Verification failed.', false);
                                setLoading(btn, false);
                            }
                        }, 'text');
                    },
                    modal: {
                        ondismiss: function() {
                            setLoading(btn, false);
                        }
                    },
                    prefill: {
                        name: '<?= addslashes(htmlspecialchars($order['delivery_name'], ENT_QUOTES)) ?>',
                        email: '<?= addslashes(htmlspecialchars($order['user_email'], ENT_QUOTES)) ?>',
                        contact: '<?= preg_replace('/[^0-9]/', '', $order['delivery_mobile'] ?? '') ?>'
                    },
                    theme: {
                        color: '#1f7a8c'
                    }
                };
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function(resp) {
                    flashReload('Payment failed: ' + resp.error.description, false);
                    setLoading(btn, false);
                });
                rzp.open();
            }
        }, 'text').fail(function() {
            flashReload('Server error. Please try again.', false);
            setLoading(btn, false);
        });
    }

    // PayPal setup
    let paypalRendered = false;

    function renderPayPalButtons() {
        if (paypalRendered) return;
        paypalRendered = true;
        paypalSDK.Buttons({
            createOrder: function(data, actions) {
                return $.post('init_existing_payment.php', {
                    order_id: ORDER_ID,
                    payment_method: 'paypal'
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
                        window.location.href = 'order_success.php?order_id=' + ORDER_ID;
                    } else {
                        flashReload(d.message || 'PayPal capture failed.', false);
                    }
                });
            },
            onError: function(err) {
                flashReload('PayPal error: ' + err, false);
            },
            onCancel: function() {
                flashReload('PayPal payment cancelled.', false);
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