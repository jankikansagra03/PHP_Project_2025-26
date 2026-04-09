<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title          = "Order Confirmed! 🎉 - JK Store";
$active_sidebar = 'orders';

$order_id  = (int)($_GET['order_id'] ?? 0);
$esc_email = mysqli_real_escape_string($con, $email);

$order = null;
if ($order_id) {
    $oq    = mysqli_query($con, "SELECT * FROM orders WHERE id=$order_id AND user_email='$esc_email' LIMIT 1");
    $order = $oq ? mysqli_fetch_assoc($oq) : null;
}
if (!$order) { header('Location: my_orders.php'); exit; }

$items_q = mysqli_query($con, "SELECT * FROM order_items WHERE order_id=$order_id");
$items   = [];
while ($r = mysqli_fetch_assoc($items_q)) $items[] = $r;

$is_paid    = ($order['payment_status'] === 'Paid');
$pay_method = strtolower($order['payment_method'] ?? 'cod');
$pm_labels  = ['cod'=>'Cash on Delivery','cashfree'=>'Cashfree','razorpay'=>'Razorpay','paypal'=>'PayPal'];
$pm_icons   = ['cod'=>'fa-money-bill-wave','cashfree'=>'fa-bolt','razorpay'=>'fa-credit-card','paypal'=>'fa-paypal'];
$pm_colors  = ['cod'=>'#16a34a','cashfree'=>'#0284c7','razorpay'=>'#7c3aed','paypal'=>'#f59e0b'];

$est_delivery = date('D, M j', strtotime('+'.($pay_method==='cod'?'3':'2').' days'));
$est_delivery2 = date('D, M j', strtotime('+'.($pay_method==='cod'?'7':'5').' days'));

ob_start();
?>

<!-- ═══════════════════════ CONFETTI CANVAS ═══════════════════════ -->
<canvas id="confettiCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;"></canvas>

<style>
/* ── Keyframe Animations ─────────────────────────────────────── */
@keyframes successPop {
    0%   { transform:scale(0) rotate(-15deg); opacity:0; }
    60%  { transform:scale(1.15) rotate(3deg); }
    80%  { transform:scale(.96) rotate(-1deg); }
    100% { transform:scale(1) rotate(0); opacity:1; }
}
@keyframes ringPulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,.4); }
    50%      { box-shadow: 0 0 0 20px rgba(22,163,74,0); }
}
@keyframes slideUp {
    from { opacity:0; transform:translateY(32px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes fadeIn {
    from { opacity:0; } to { opacity:1; }
}
@keyframes trackPulse {
    0%,100% { transform:scale(1); }
    50%      { transform:scale(1.04); }
}
@keyframes shimmer {
    0%   { background-position:-200% 0; }
    100% { background-position:200% 0; }
}
@keyframes floatBadge {
    0%,100% { transform:translateY(0); }
    50%      { transform:translateY(-5px); }
}

/* ── Success Hero ────────────────────────────────────────────── */
.os-hero {
    background: linear-gradient(135deg,#f0fdf4 0%,#dcfce7 50%,#bbf7d0 100%);
    border-radius: 24px;
    padding: 2.5rem 2rem 2rem;
    text-align: center;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    border: 1.5px solid rgba(22,163,74,.15);
    animation: slideUp .6s ease both;
}
.os-hero::before {
    content:'';
    position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2316a34a' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.os-check-ring {
    width: 110px; height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg,#22c55e,#16a34a);
    display: flex; align-items:center; justify-content:center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 12px 40px rgba(22,163,74,.35);
    animation: successPop .7s cubic-bezier(.34,1.56,.64,1) both, ringPulse 2.5s ease 1s infinite;
    position: relative;
}
.os-check-ring .inner-ring {
    position: absolute; inset:-8px;
    border: 3px solid rgba(22,163,74,.25);
    border-radius: 50%;
    animation: ringPulse 2.5s ease .5s infinite;
}
.os-check-ring i { font-size: 2.8rem; color: #fff; }
.os-order-num {
    display: inline-block;
    background: rgba(22,163,74,.12);
    color: #15803d;
    font-weight: 800;
    font-size: .88rem;
    padding: 5px 18px;
    border-radius: 20px;
    letter-spacing: .5px;
    border: 1px solid rgba(22,163,74,.2);
    margin-bottom: .75rem;
    animation: floatBadge 3s ease infinite;
}

/* ── Status Steps ────────────────────────────────────────────── */
.os-steps {
    display: flex; align-items:center;
    gap: 0; margin: 1.5rem 0 0;
    background: #fff;
    border-radius: 16px;
    padding: .75rem 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.os-step { display:flex; flex-direction:column; align-items:center; gap:5px; flex:1; }
.os-step-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items:center; justify-content:center;
    font-size: .75rem; font-weight: 800;
    transition: all .3s;
}
.os-step-dot.done   { background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; box-shadow:0 4px 12px rgba(22,163,74,.3); }
.os-step-dot.active { background:linear-gradient(135deg,var(--theme-primary,#1f7a8c),#0f4c5c); color:#fff; box-shadow:0 4px 12px rgba(31,122,140,.3); animation:trackPulse 1.5s ease infinite; }
.os-step-dot.idle   { background:#f1f5f9; color:#94a3b8; border:2px solid #e2e8f0; }
.os-step-label { font-size:.65rem; font-weight:700; color:#64748b; }
.os-step-label.active { color:var(--theme-primary,#1f7a8c); }
.os-connector { flex:1; height:3px; border-radius:3px; margin-bottom:14px; }
.os-connector.done   { background:linear-gradient(90deg,#22c55e,#16a34a); }
.os-connector.idle   { background:#e2e8f0; }

/* ── Cards ───────────────────────────────────────────────────── */
.os-card {
    border: 0; border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
    transition: transform .2s, box-shadow .2s;
    animation: slideUp .5s ease both;
}
.os-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(0,0,0,.1);
}
.os-card-header {
    display:flex; align-items:center; gap:10px;
    padding: 1.1rem 1.4rem .75rem;
    border-bottom: 1px solid #f1f5f9;
}
.os-card-icon {
    width:36px; height:36px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem;
}
.os-card-title { font-weight:800; font-size:.95rem; color:#0f172a; margin:0; }

/* ── Item Row ────────────────────────────────────────────────── */
.os-item { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f8fafc; }
.os-item:last-child { border-bottom:none; }
.os-item-img {
    width:54px; height:54px; border-radius:12px;
    background:#f8fafc; border:1.5px solid #e2e8f0;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; overflow:hidden;
}
.os-item-img img { width:46px; height:46px; object-fit:contain; mix-blend-mode:multiply; }

/* ── Delivery Estimate Banner ────────────────────────────────── */
.os-estimate {
    background: linear-gradient(135deg,#eff6ff,#dbeafe);
    border: 1.5px solid rgba(59,130,246,.2);
    border-radius: 16px;
    padding: 1rem 1.2rem;
    display: flex; align-items:center; gap:14px;
    margin-bottom: 1rem;
}
.os-est-icon {
    width:46px; height:46px; border-radius:12px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; box-shadow:0 4px 12px rgba(59,130,246,.3);
}

/* ── Payment Pill ────────────────────────────────────────────── */
.os-pay-pill {
    display:flex; align-items:center; gap:8px;
    padding:.55rem 1rem;
    border-radius:12px;
    font-size:.82rem; font-weight:700;
    border:1.5px solid;
}

/* ── Total Shimmer ───────────────────────────────────────────── */
.os-total-val {
    font-size:1.8rem; font-weight:900;
    background: linear-gradient(90deg,
        var(--theme-primary,#1f7a8c) 0%,
        #10b981 40%,
        var(--theme-primary,#1f7a8c) 80%
    );
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 3s linear infinite;
}

/* ── Action Buttons ──────────────────────────────────────────── */
.os-btn-primary {
    background: linear-gradient(135deg,var(--theme-primary,#1f7a8c),#0f4c5c);
    color: #fff; border:none;
    padding:.85rem 1.5rem;
    border-radius:14px;
    font-weight:800; font-size:.92rem;
    width:100%; display:block; text-align:center;
    text-decoration:none;
    box-shadow:0 4px 16px rgba(31,122,140,.3);
    transition: transform .2s, box-shadow .2s;
}
.os-btn-primary:hover { color:#fff; transform:translateY(-2px); box-shadow:0 8px 24px rgba(31,122,140,.4); }
.os-btn-secondary {
    background:#f8fafc; border: 1.5px solid #e2e8f0;
    color:#374151; padding:.75rem 1.5rem;
    border-radius:14px; font-weight:700; font-size:.88rem;
    width:100%; display:block; text-align:center;
    text-decoration:none; margin-top:.75rem;
    transition: border-color .2s, background .2s;
}
.os-btn-secondary:hover { color:#374151; background:#f1f5f9; border-color:#d1d5db; }
</style>

<!-- ═══════ HERO SECTION ═══════ -->
<div class="os-hero">
    <div class="os-check-ring">
        <div class="inner-ring"></div>
        <i class="fas fa-check"></i>
    </div>

    <div class="os-order-num">
        <i class="fas fa-hashtag me-1"></i><?= htmlspecialchars($order['order_number']) ?>
    </div>

    <h2 style="font-weight:900;color:#0f172a;font-size:1.6rem;margin-bottom:.4rem;line-height:1.2;">
        Order Confirmed! 🎉
    </h2>
    <p style="color:#374151;font-size:.95rem;margin-bottom:.25rem;font-weight:500;">
        Thank you for your purchase, <strong><?= htmlspecialchars(explode('@',$email)[0]) ?></strong>!
    </p>
    <p style="color:#64748b;font-size:.82rem;margin-bottom:1.25rem;">
        Confirmation sent to <strong><?= htmlspecialchars($email) ?></strong>
    </p>

    <!-- Checkout Step Tracker -->
    <div class="os-steps">
        <div class="os-step">
            <div class="os-step-dot done"><i class="fas fa-check"></i></div>
            <span class="os-step-label">Address</span>
        </div>
        <div class="os-connector done"></div>
        <div class="os-step">
            <div class="os-step-dot done"><i class="fas fa-check"></i></div>
            <span class="os-step-label">Payment</span>
        </div>
        <div class="os-connector done"></div>
        <div class="os-step">
            <div class="os-step-dot active"><i class="fas fa-box"></i></div>
            <span class="os-step-label active">Confirmed</span>
        </div>
        <div class="os-connector idle"></div>
        <div class="os-step">
            <div class="os-step-dot idle"><i class="fas fa-shipping-fast"></i></div>
            <span class="os-step-label">Shipped</span>
        </div>
        <div class="os-connector idle"></div>
        <div class="os-step">
            <div class="os-step-dot idle"><i class="fas fa-home"></i></div>
            <span class="os-step-label">Delivered</span>
        </div>
    </div>
</div>

<!-- ═══════ MAIN CONTENT ═══════ -->
<div class="row g-4">

    <!-- LEFT COLUMN -->
    <div class="col-lg-7">

        <!-- Estimated Delivery -->
        <div class="os-estimate" style="animation-delay:.1s; animation: slideUp .5s .1s ease both;">
            <div class="os-est-icon">
                <i class="fas fa-shipping-fast" style="color:#fff;font-size:1.1rem;"></i>
            </div>
            <div>
                <div style="font-size:.72rem;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.5px;">Estimated Delivery</div>
                <div style="font-size:.95rem;font-weight:800;color:#1e293b;margin-top:1px;"><?= $est_delivery ?> – <?= $est_delivery2 ?></div>
                <div style="font-size:.75rem;color:#64748b;margin-top:1px;">
                    <?= $pay_method === 'cod' ? 'Standard delivery (Cash on Delivery orders)' : 'Express delivery (Online payment)' ?>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="card os-card mb-4" style="animation-delay:.15s;">
            <div class="os-card-header">
                <div class="os-card-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                    <i class="fas fa-box" style="color:#3b82f6;"></i>
                </div>
                <h6 class="os-card-title">Order Items</h6>
                <span class="ms-auto" style="font-size:.75rem;color:#94a3b8;font-weight:600;"><?= count($items) ?> item<?= count($items)>1?'s':'' ?></span>
            </div>
            <div class="card-body px-4 py-3">
                <?php foreach ($items as $idx => $item): ?>
                <div class="os-item" style="animation:slideUp .4s <?= 0.1+$idx*.08 ?>s ease both; opacity:0; animation-fill-mode:forwards;">
                    <div class="os-item-img">
                        <img src="<?= htmlspecialchars($item['product_image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                             alt="<?= htmlspecialchars($item['product_name']) ?>">
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;color:#1e293b;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
                            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:8px;">
                                Qty: <?= (int)$item['quantity'] ?>
                            </span>
                            <span style="color:#94a3b8;font-size:.75rem;">×</span>
                            <span style="color:#64748b;font-size:.78rem;font-weight:600;">₹<?= number_format($item['price'], 2) ?></span>
                        </div>
                    </div>
                    <div style="font-weight:800;color:#1e293b;font-size:.92rem;flex-shrink:0;text-align:right;">
                        ₹<?= number_format($item['subtotal'], 2) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Delivery Address Card -->
        <div class="card os-card" style="animation-delay:.25s;">
            <div class="os-card-header">
                <div class="os-card-icon" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);">
                    <i class="fas fa-map-marker-alt" style="color:#f97316;"></i>
                </div>
                <h6 class="os-card-title">Delivery Address</h6>
            </div>
            <div class="card-body px-4 py-3">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:12px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <i class="fas fa-home" style="color:#f97316;font-size:.9rem;"></i>
                    </div>
                    <div>
                        <p style="font-weight:800;margin:0 0 4px;color:#0f172a;font-size:.95rem;">
                            <?= htmlspecialchars($order['delivery_name']) ?>
                        </p>
                        <p style="color:#64748b;margin:0 0 4px;font-size:.83rem;line-height:1.5;">
                            <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                        </p>
                        <p style="color:#64748b;margin:0;font-size:.82rem;">
                            <span style="background:#f1f5f9;padding:2px 10px;border-radius:8px;font-weight:600;">
                                <i class="fas fa-phone me-1" style="font-size:.7rem;"></i><?= htmlspecialchars($order['delivery_mobile']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-5">
        <div class="card os-card sticky-top" style="top:80px;animation-delay:.2s;">
            <div class="card-body p-4">

                <!-- Payment status badge -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
                    <h6 style="font-weight:800;margin:0;font-size:.95rem;color:#0f172a;">
                        <i class="fas fa-receipt me-2" style="color:var(--theme-primary);opacity:.7;"></i>Bill Summary
                    </h6>
                    <span style="background:<?= $is_paid ? '#dcfce7' : '#fef9c3' ?>;color:<?= $is_paid ? '#15803d' : '#92400e' ?>;font-size:.7rem;font-weight:800;padding:4px 12px;border-radius:20px;border:1px solid <?= $is_paid ? 'rgba(22,163,74,.2)' : 'rgba(234,179,8,.2)' ?>;">
                        <i class="fas <?= $is_paid ? 'fa-check-circle' : 'fa-clock' ?> me-1"></i>
                        <?= htmlspecialchars($order['payment_status'] ?? 'Pending') ?>
                    </span>
                </div>

                <!-- Pricing rows -->
                <div style="background:#f8fafc;border-radius:14px;padding:1rem 1.1rem;margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span style="color:#64748b;font-size:.85rem;">Subtotal (<?= count($items) ?> items)</span>
                        <span style="font-weight:600;font-size:.85rem;">₹<?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <?php if ((float)$order['discount'] > 0): ?>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span style="color:#16a34a;font-size:.85rem;font-weight:600;">
                            <i class="fas fa-tag me-1"></i>Discount
                        </span>
                        <span style="color:#16a34a;font-weight:700;font-size:.85rem;">-₹<?= number_format($order['discount'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                        <span style="color:#64748b;font-size:.85rem;">Shipping</span>
                        <span style="background:#dcfce7;color:#16a34a;font-size:.7rem;font-weight:700;padding:2px 10px;border-radius:20px;">FREE</span>
                    </div>
                    <div style="height:1px;background:linear-gradient(90deg,transparent,#e2e8f0,transparent);margin:10px 0;"></div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-weight:800;color:#0f172a;font-size:.92rem;">Total <?= $is_paid ? 'Paid' : 'Payable' ?></span>
                        <span class="os-total-val">₹<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Payment Method -->
                <?php
                $pm_color = $pm_colors[$pay_method] ?? '#64748b';
                $pm_icon  = $pm_icons[$pay_method] ?? 'fa-credit-card';
                $pm_label = $pm_labels[$pay_method] ?? strtoupper($pay_method);
                ?>
                <div class="os-pay-pill mb-4" style="color:<?= $pm_color ?>;background:<?= $pm_color ?>15;border-color:<?= $pm_color ?>30;">
                    <i class="fas <?= $pm_icon ?>"></i>
                    <span><?= htmlspecialchars($pm_label) ?></span>
                    <span class="ms-auto" style="font-size:.72rem;color:#94a3b8;font-weight:600;">Payment Method</span>
                </div>

                <!-- Trust line -->
                <div style="display:flex;justify-content:center;gap:16px;margin-bottom:1.25rem;flex-wrap:wrap;">
                    <span style="font-size:.72rem;color:#94a3b8;display:flex;align-items:center;gap:4px;">
                        <i class="fas fa-shield-alt text-success"></i> Secure
                    </span>
                    <span style="font-size:.72rem;color:#94a3b8;display:flex;align-items:center;gap:4px;">
                        <i class="fas fa-undo text-primary"></i> 7-Day Return
                    </span>
                    <span style="font-size:.72rem;color:#94a3b8;display:flex;align-items:center;gap:4px;">
                        <i class="fas fa-headset text-warning"></i> 24/7 Support
                    </span>
                </div>

                <!-- Actions -->
                <a href="my_orders.php" class="os-btn-primary">
                    <i class="fas fa-box me-2"></i>Track My Orders
                </a>
                <a href="shop.php" class="os-btn-secondary">
                    <i class="fas fa-store me-2"></i>Continue Shopping
                </a>

            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════ CONFETTI JS ═══════════════════════ -->
<script>
(function() {
    var canvas = document.getElementById('confettiCanvas');
    var ctx    = canvas.getContext('2d');
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;

    var colors = ['#22c55e','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316'];
    var pieces = [];
    var total  = 160;

    for (var i = 0; i < total; i++) {
        pieces.push({
            x:      Math.random() * canvas.width,
            y:      Math.random() * canvas.height - canvas.height,
            w:      Math.random() * 10 + 5,
            h:      Math.random() * 6 + 3,
            color:  colors[Math.floor(Math.random() * colors.length)],
            speed:  Math.random() * 3 + 1.5,
            angle:  Math.random() * Math.PI * 2,
            spin:   (Math.random() - .5) * .15,
            drift:  (Math.random() - .5) * 1.2,
            alpha:  1
        });
    }

    var frame = 0;
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach(function(p) {
            p.y     += p.speed;
            p.x     += p.drift;
            p.angle += p.spin;
            if (frame > 180) p.alpha = Math.max(0, p.alpha - .008);

            ctx.save();
            ctx.globalAlpha = p.alpha;
            ctx.translate(p.x + p.w/2, p.y + p.h/2);
            ctx.rotate(p.angle);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
            ctx.restore();

            if (p.y > canvas.height) {
                p.y = -p.h;
                p.x = Math.random() * canvas.width;
            }
        });
        frame++;
        if (frame < 350) requestAnimationFrame(draw);
        else ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    // Start after a tiny delay so page renders first
    setTimeout(draw, 300);

    window.addEventListener('resize', function() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    });
})();
</script>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>
