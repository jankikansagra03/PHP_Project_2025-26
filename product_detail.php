<?php
include_once 'db_config.php';
@session_start();

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header('Location: shop.php');
    exit;
}

// Fetch product
$stmt = mysqli_prepare($con, "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'Active'");
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) === 0) {
    header('Location: shop.php');
    exit;
}
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Gallery
$gallery = [];
if (!empty($product['gallery_images'])) {
    $raw_gallery = trim((string)$product['gallery_images']);
    if ($raw_gallery !== '') {
        if ($raw_gallery[0] === '[' && substr($raw_gallery, -1) === ']') {
            $inner = substr($raw_gallery, 1, -1);
            $gallery = $inner === '' ? [] : str_getcsv($inner, ',', '"', '\\');
        } else {
            $gallery = preg_split('/\s*,\s*/', $raw_gallery) ?: [];
        }
        $gallery = array_values(array_filter(array_map('trim', $gallery), static function ($image) {
            return $image !== '';
        }));
    }
}
$allImages = array_values(array_unique(array_filter(array_merge([$product['image']], $gallery))));

// Reviews
$rev_stmt = mysqli_prepare($con, "SELECT * FROM reviews WHERE product_id = ? AND status = 'Approved' ORDER BY created_at DESC");
mysqli_stmt_bind_param($rev_stmt, 'i', $product_id);
mysqli_stmt_execute($rev_stmt);
$rev_res      = mysqli_stmt_get_result($rev_stmt);
$reviews      = [];
$total_rating = 0;
while ($r = mysqli_fetch_assoc($rev_res)) {
    $reviews[] = $r;
    $total_rating += $r['rating'];
}
mysqli_stmt_close($rev_stmt);
$review_count = count($reviews);
$avg_rating   = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;

// Rating distribution
$ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $rv) $ratingCounts[(int)$rv['rating']] = ($ratingCounts[(int)$rv['rating']] ?? 0) + 1;

// Has current user already reviewed?
$user_already_reviewed = false;
$user_own_review = null;
if (isset($_SESSION['user'])) {
    $ue2 = mysqli_real_escape_string($con, $_SESSION['user']);
    $urq = mysqli_query($con, "SELECT * FROM reviews WHERE product_id=$product_id AND user_email='$ue2' LIMIT 1");
    if ($urq && mysqli_num_rows($urq) > 0) {
        $user_already_reviewed = true;
        $user_own_review = mysqli_fetch_assoc($urq);
    }
}

// Cart / Wishlist state
$in_cart = false;
$in_wl   = false;
if (isset($_SESSION['user'])) {
    $ue      = mysqli_real_escape_string($con, $_SESSION['user']);
    $in_cart = (bool)(mysqli_num_rows(mysqli_query($con, "SELECT id FROM cart WHERE user_email='$ue' AND product_id=$product_id")));
    $in_wl   = (bool)(mysqli_num_rows(mysqli_query($con, "SELECT id FROM wishlist WHERE user_email='$ue' AND product_id=$product_id")));
}

// Related products
$related = [];
if (!empty($product['category_id'])) {
    $rq = mysqli_prepare($con, "SELECT id, name, image, final_price, price, discount, brand FROM products WHERE category_id=? AND id!=? AND status='Active' ORDER BY RAND() LIMIT 4");
    mysqli_stmt_bind_param($rq, 'ii', $product['category_id'], $product_id);
    mysqli_stmt_execute($rq);
    $rr = mysqli_stmt_get_result($rq);
    while ($r = mysqli_fetch_assoc($rr)) $related[] = $r;
    mysqli_stmt_close($rq);
}

$title       = htmlspecialchars($product['name']) . " - JK Store";
$final_price = (float)($product['final_price'] ?? $product['price']);
$orig_price  = (float)$product['price'];
$discount    = (float)($product['discount'] ?? 0);
$stock       = (int)$product['stock'];
$disc_pct    = ($discount > 0 && $orig_price > 0) ? round($discount / $orig_price * 100) : 0;

ob_start();
?>

<!-- ─── Inline Styles ───────────────────────────────────────────── -->
<style>
    /* ── Gallery ── */
    .pd-gallery-wrap {
        position: relative;
        display: flex;
        flex-direction: row;
        gap: 12px;
    }

    .pd-thumb-strip {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 520px;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        padding-right: 2px;
        flex-shrink: 0;
        width: 76px;
    }

    .pd-thumb-strip::-webkit-scrollbar {
        width: 4px;
    }

    .pd-thumb-strip::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .pd-thumb {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        border: 2px solid transparent;
        background: #f1f5f9;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color .2s, box-shadow .2s, background .2s;
        flex-shrink: 0;
    }

    .pd-thumb img {
        width: 85%;
        height: 85%;
        object-fit: contain;
        mix-blend-mode: multiply;
    }

    .pd-thumb.active {
        border-color: var(--theme-primary, #1f7a8c);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(31, 122, 140, .15);
    }

    .pd-thumb:hover:not(.active) {
        border-color: #94a3b8;
        background: #fff;
    }

    .pd-main-area {
        flex: 1;
        min-width: 0;
    }

    .pd-main-img {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 16px;
        overflow: hidden;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        position: relative;
    }

    .pd-main-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 1.5rem;
        transition: transform .4s cubic-bezier(.25, .8, .25, 1);
        mix-blend-mode: multiply;
    }

    .pd-main-img:hover img {
        transform: scale(1.06);
    }

    @media (max-width: 767px) {
        .pd-gallery-wrap {
            flex-direction: column-reverse;
        }

        .pd-thumb-strip {
            flex-direction: row;
            width: 100%;
            max-height: none;
            overflow-x: auto;
            overflow-y: hidden;
            padding-right: 0;
            padding-bottom: 2px;
        }

        .pd-thumb {
            width: 62px;
            height: 62px;
            flex-shrink: 0;
        }
    }

    /* ── Product Info ── */
    .pd-price-pill {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #bae6fd;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
    }

    .pd-old-price {
        text-decoration: line-through;
        color: #94a3b8;
    }

    .pd-discount-badge {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: #fff;
        border-radius: 30px;
        padding: 4px 12px;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .3px;
    }

    /* ── Status Badges ── */
    .pd-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.25rem;
    }

    .pd-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 30px;
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .2px;
    }

    .pill-category {
        background: rgba(31, 122, 140, .1);
        color: var(--theme-primary, #1f7a8c);
    }

    .pill-instock {
        background: #dcfce7;
        color: #15803d;
    }

    .pill-outstock {
        background: #fee2e2;
        color: #b91c1c;
    }

    .pill-incart {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .pill-inwl {
        background: #fce7f3;
        color: #9d174d;
    }

    /* ── Qty Stepper ── */
    .pd-qty-stepper {
        display: flex;
        align-items: center;
        gap: 0;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        width: fit-content;
    }

    .pd-qty-stepper button {
        background: none;
        border: none;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1rem;
        transition: background .15s;
        color: var(--theme-primary, #1f7a8c);
    }

    .pd-qty-stepper button:hover {
        background: rgba(31, 122, 140, .1);
    }

    .pd-qty-stepper input {
        width: 52px;
        border: none;
        text-align: center;
        font-weight: 700;
        font-size: 1rem;
        background: none;
        border-left: 2px solid #e2e8f0;
        border-right: 2px solid #e2e8f0;
        outline: none;
        height: 42px;
    }

    /* ── Action Buttons ── */
    .pd-btn-cart {
        flex: 1;
        background: linear-gradient(135deg, var(--theme-primary, #1f7a8c), var(--theme-secondary, #0f4c5c));
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 14px 28px;
        font-weight: 700;
        font-size: .95rem;
        letter-spacing: .3px;
        transition: all .25s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(31, 122, 140, .3);
    }

    .pd-btn-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(31, 122, 140, .4);
    }

    .pd-btn-cart:active {
        transform: translateY(0);
    }

    .pd-btn-cart.in-cart {
        background: linear-gradient(135deg, #16a34a, #15803d);
        box-shadow: 0 4px 15px rgba(22, 163, 74, .3);
    }

    .pd-btn-wl {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all .25s;
        flex-shrink: 0;
        color: #94a3b8;
    }

    .pd-btn-wl:hover {
        border-color: #f43f5e;
        color: #f43f5e;
        background: #fef2f2;
        transform: scale(1.08);
    }

    .pd-btn-wl.in-wl {
        border-color: #f43f5e;
        background: #f43f5e;
        color: #fff;
    }

    .pd-btn-wl.in-wl:hover {
        background: #e11d48;
        border-color: #e11d48;
    }

    /* ── Trust badges ── */
    .pd-trust-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 1.5rem;
    }

    .pd-trust-item {
        text-align: center;
        padding: 14px 8px;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
    }

    .pd-trust-item i {
        font-size: 1.4rem;
        margin-bottom: 6px;
        display: block;
    }

    .pd-trust-item strong {
        font-size: .8rem;
        display: block;
        color: #374151;
    }

    .pd-trust-item small {
        font-size: .7rem;
        color: #9ca3af;
    }

    /* ── Tabs ── */
    .pd-tabs {
        display: flex;
        gap: 0;
        background: #f1f5f9;
        border-radius: 14px;
        padding: 5px;
        margin-bottom: 1.5rem;
    }

    .pd-tab-btn {
        flex: 1;
        padding: 10px;
        font-weight: 600;
        font-size: .88rem;
        border: none;
        background: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all .2s;
        color: #64748b;
        position: relative;
    }

    .pd-tab-btn.active {
        background: #fff;
        color: var(--theme-primary, #1f7a8c);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
    }

    .pd-tab-content {
        display: none;
    }

    .pd-tab-content.active {
        display: block;
    }

    /* ── Reviews ── */
    .review-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        transition: box-shadow .2s;
    }

    .review-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
    }

    .review-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        color: #fff;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--theme-primary, #1f7a8c), var(--theme-secondary, #0f4c5c));
    }

    /* ── Related Products ── */
    .related-card {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: transform .25s, box-shadow .25s;
        text-decoration: none;
        color: inherit;
        display: block;
        background: #fff;
    }

    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
    }

    .related-img {
        aspect-ratio: 1/1;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .related-img img {
        width: 80%;
        height: 80%;
        object-fit: contain;
        mix-blend-mode: multiply;
        transition: transform .3s;
    }

    .related-card:hover .related-img img {
        transform: scale(1.07);
    }

    /* ── Toast ── */
    #pdToast {
        min-width: 260px;
    }

    /* ── Zoom overlay ── */
    .pd-zoom-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .85);
        z-index: 9998;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }

    .pd-zoom-overlay.show {
        display: flex;
    }

    .pd-zoom-overlay img {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 12px;
    }
</style>

<!-- ─── Zoom Overlay ─────────────────────────────────────────────── -->
<div class="pd-zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
    <img id="zoomImg" src="" alt="Zoom">
</div>

<!-- ─── Toast ────────────────────────────────────────────────────── -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="pdToast" class="toast align-items-center text-white border-0 shadow-lg rounded-3" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold small" id="pdToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="container py-4 py-lg-5" style="max-width:1200px;">

    <!-- ── Breadcrumb ──────────────────────────────────────────── -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none fw-medium">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php" class="text-decoration-none fw-medium">Shop</a></li>
            <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item">
                    <a href="shop.php?category=<?= $product['category_id'] ?>" class="text-decoration-none fw-medium">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active fw-semibold text-truncate" style="max-width:220px;">
                <?= htmlspecialchars($product['name']) ?>
            </li>
        </ol>
    </nav>

    <!-- ── Main Product Grid ───────────────────────────────────── -->
    <div style="background:#fff; border-radius:20px; box-shadow:0 2px 24px rgba(0,0,0,.07); border:1px solid #e2e8f0; padding:2rem; margin-bottom:2.5rem;">
        <div class="row g-5 align-items-start">

            <!-- LEFT: Image Gallery -->
            <div class="col-lg-6">
                <div class="pd-gallery-wrap">

                    <!-- Vertical Thumbnail Strip -->
                    <div class="pd-thumb-strip">
                        <?php foreach (array_slice($allImages, 0, 6) as $idx => $img): ?>
                            <div class="pd-thumb <?= $idx === 0 ? 'active' : '' ?>"
                                onclick="switchImage(this, '<?= htmlspecialchars($img, ENT_QUOTES) ?>')"
                                title="Image <?= $idx + 1 ?>">
                                <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="Thumb <?= $idx + 1 ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Main Image Area -->
                    <div class="pd-main-area">
                        <!-- Discount Ribbon -->
                        <?php if ($disc_pct > 0): ?>
                            <div style="position:absolute; top:12px; right:12px; z-index:10;">
                                <div style="background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; font-size:.75rem; font-weight:800; padding:5px 12px; border-radius:30px; letter-spacing:.5px; box-shadow:0 4px 12px rgba(239,68,68,.4);">
                                    <?= $disc_pct ?>% OFF
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="pd-main-img" id="mainImgWrap"
                            onclick="openZoom(document.getElementById('mainProductImage').src)"
                            style="cursor:zoom-in;" title="Click to zoom">
                            <img id="mainProductImage"
                                src="<?= htmlspecialchars($allImages[0] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>">
                            <div style="position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,.25); color:#fff; border-radius:8px; padding:4px 10px; font-size:.68rem; font-weight:600; display:flex; align-items:center; gap:5px; backdrop-filter:blur(4px);">
                                <i class="fas fa-search-plus"></i> Zoom
                            </div>
                        </div>

                        <!-- Image Counter -->
                        <?php if (count($allImages) > 1): ?>
                            <div style="text-align:center; margin-top:10px; color:#94a3b8; font-size:.75rem; font-weight:600;">
                                <i class="fas fa-images me-1"></i>
                                <?= count($allImages) ?> image<?= count($allImages) !== 1 ? 's' : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Product Information -->
            <div class="col-lg-6">

                <!-- Status Pills -->
                <div class="pd-status-row">
                    <?php if (!empty($product['category_name'])): ?>
                        <span class="pd-status-pill pill-category">
                            <i class="fas fa-tag" style="font-size:.65rem;"></i>
                            <?= htmlspecialchars($product['category_name']) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($stock > 0): ?>
                        <span class="pd-status-pill pill-instock">
                            <i class="fas fa-check-circle" style="font-size:.65rem;"></i> In Stock
                        </span>
                    <?php else: ?>
                        <span class="pd-status-pill pill-outstock">
                            <i class="fas fa-times-circle" style="font-size:.65rem;"></i> Out of Stock
                        </span>
                    <?php endif; ?>

                    <span class="pd-status-pill pill-incart" id="inCartBadge" style="<?= $in_cart ? '' : 'display:none;' ?>">
                        <i class="fas fa-shopping-cart" style="font-size:.65rem;"></i> Added to Cart
                    </span>
                    <span class="pd-status-pill pill-inwl" id="inWlBadge" style="<?= $in_wl ? '' : 'display:none;' ?>">
                        <i class="fas fa-heart" style="font-size:.65rem;"></i> In Wishlist
                    </span>
                </div>

                <!-- Product Name -->
                <h1 style="font-size:1.75rem; font-weight:800; color:#0f172a; line-height:1.2; margin-bottom:.5rem;">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>

                <?php if (!empty($product['brand'])): ?>
                    <p style="color:#64748b; margin-bottom:.75rem; font-size:.9rem;">
                        by <a href="shop.php?search=<?= urlencode($product['brand']) ?>" class="text-decoration-none fw-semibold" style="color:var(--theme-primary);">
                            <?= htmlspecialchars($product['brand']) ?>
                        </a>
                    </p>
                <?php endif; ?>

                <!-- Star Rating -->
                <?php if ($review_count > 0): ?>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="d-flex gap-1">
                            <?php
                            $full = floor($avg_rating);
                            $half = ($avg_rating - $full) >= 0.5 ? 1 : 0;
                            $empty = 5 - $full - $half;
                            for ($i = 0; $i < $full; $i++) echo '<i class="fas fa-star text-warning" style="font-size:.85rem;"></i>';
                            if ($half) echo '<i class="fas fa-star-half-alt text-warning" style="font-size:.85rem;"></i>';
                            for ($i = 0; $i < $empty; $i++) echo '<i class="far fa-star text-warning" style="font-size:.85rem;"></i>';
                            ?>
                        </div>
                        <span style="font-weight:700; color:#0f172a; font-size:.9rem;"><?= $avg_rating ?></span>
                        <span style="color:#94a3b8; font-size:.85rem;">(<?= $review_count ?> review<?= $review_count !== 1 ? 's' : '' ?>)</span>
                        <button class="btn btn-link p-0 small text-decoration-none" style="color:var(--theme-primary); font-size:.82rem;"
                            onclick="document.getElementById('reviewTabBtn').click(); document.getElementById('reviewTabBtn').scrollIntoView({behavior:'smooth', block:'start'});">
                            See all →
                        </button>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <span style="color:#94a3b8; font-size:.85rem;">No reviews yet — be the first!</span>
                    </div>
                <?php endif; ?>

                <!-- Price Block -->
                <div class="pd-price-pill mb-4">
                    <div class="d-flex align-items-baseline flex-wrap gap-3">
                        <span style="font-size:2.2rem; font-weight:900; color:var(--theme-primary, #1f7a8c); line-height:1;">
                            &#8377;<?= number_format($final_price, 2) ?>
                        </span>
                        <?php if ($discount > 0): ?>
                            <span class="pd-old-price" style="font-size:1.1rem;">&#8377;<?= number_format($orig_price, 2) ?></span>
                            <span class="pd-discount-badge">Save <?= $disc_pct ?>%</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($stock > 0 && $stock <= 10): ?>
                        <div class="d-flex align-items-center gap-2 mt-2" style="color:#dc2626; font-size:.82rem; font-weight:600;">
                            <i class="fas fa-fire-alt"></i>
                            Only <?= $stock ?> left — order soon!
                        </div>
                    <?php elseif ($stock > 0): ?>
                        <div class="d-flex align-items-center gap-2 mt-2" style="color:#16a34a; font-size:.82rem; font-weight:600;">
                            <i class="fas fa-circle" style="font-size:.5rem;"></i> In stock and ready to ship
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($product['description'])): ?>
                    <p style="color:#475569; line-height:1.8; margin-bottom:1.5rem; font-size:.93rem;">
                        <?= htmlspecialchars($product['description']) ?>
                    </p>
                <?php endif; ?>

                <!-- Divider -->
                <hr style="border-color:#e2e8f0; margin: 1.5rem 0;">

                <!-- Add to Cart Section -->
                <?php if ($stock > 0): ?>
                    <div class="mb-4">
                        <!-- Qty + Cart CTA -->
                        <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                            <div>
                                <label style="font-size:.78rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.7px; display:block; margin-bottom:6px;">Quantity</label>
                                <div class="pd-qty-stepper">
                                    <button type="button" id="qtyDecBtn" title="Decrease"><i class="fas fa-minus" style="font-size:.75rem;"></i></button>
                                    <input type="number" id="productQty" value="1" min="1" max="<?= $stock ?>" readonly>
                                    <button type="button" id="qtyIncBtn" title="Increase"><i class="fas fa-plus" style="font-size:.75rem;"></i></button>
                                </div>
                            </div>
                            <div style="color:#94a3b8; font-size:.8rem; align-self:flex-end; padding-bottom:6px;">
                                <i class="fas fa-box me-1"></i><?= $stock ?> available
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-stretch">
                            <button class="pd-btn-cart <?= $in_cart ? 'in-cart' : '' ?>" id="addToCartBtn"
                                data-product-id="<?= $product['id'] ?>">
                                <i class="fas <?= $in_cart ? 'fa-check' : 'fa-shopping-cart' ?>"></i>
                                <?= $in_cart ? 'Added to Cart' : 'Add to Cart' ?>
                            </button>
                            <button class="pd-btn-wl <?= $in_wl ? 'in-wl' : '' ?>" id="wishlistBtn"
                                data-product-id="<?= $product['id'] ?>"
                                data-in-wishlist="<?= $in_wl ? '1' : '0' ?>"
                                title="<?= $in_wl ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                                <i class="<?= $in_wl ? 'fas' : 'far' ?> fa-heart"></i>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background:#fef2f2; border:1.5px solid #fecaca; border-radius:14px; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:12px;">
                        <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:1.25rem;"></i>
                        <div>
                            <strong style="color:#7f1d1d;">Out of Stock</strong>
                            <div style="color:#b91c1c; font-size:.82rem;">This product is currently unavailable.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Trust Badges -->
                <div class="pd-trust-grid">
                    <div class="pd-trust-item">
                        <i class="fas fa-shipping-fast" style="color:#3b82f6;"></i>
                        <strong>Free Delivery</strong>
                        <small>On all orders</small>
                    </div>
                    <div class="pd-trust-item">
                        <i class="fas fa-lock" style="color:#8b5cf6;"></i>
                        <strong>Secure Pay</strong>
                        <small>100% Protected</small>
                    </div>
                    <div class="pd-trust-item">
                        <i class="fas fa-undo" style="color:#f59e0b;"></i>
                        <strong>Easy Returns</strong>
                        <small>Within 7 days</small>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /white card -->

    <!-- ── Details & Reviews Tabs ──────────────────────────────── -->
    <div class="mb-5" id="reviewTabBtn" style="scroll-margin-top:100px;">
        <div class="pd-tabs" role="tablist">
            <button class="pd-tab-btn active" onclick="switchTab('descTab', this)">
                <i class="fas fa-align-left me-2" style="font-size:.8rem;"></i>Description
            </button>
            <button class="pd-tab-btn" onclick="switchTab('specTab', this)">
                <i class="fas fa-list-ul me-2" style="font-size:.8rem;"></i>Specifications
            </button>
            <button class="pd-tab-btn" onclick="switchTab('reviewTab', this)" id="reviewTabBtn">
                <i class="fas fa-star me-2" style="font-size:.8rem;"></i>
                Reviews<?= $review_count > 0 ? " ($review_count)" : "" ?>
            </button>
        </div>

        <!-- Description Tab -->
        <div class="pd-tab-content active" id="descTab">
            <div style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:2rem; line-height:1.9; color:#475569;">
                <?php if (!empty($product['long_description'])): ?>
                    <?= nl2br(htmlspecialchars($product['long_description'])) ?>
                <?php elseif (!empty($product['description'])): ?>
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                <?php else: ?>
                    <span style="color:#94a3b8;">No description available for this product.</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Specifications Tab -->
        <div class="pd-tab-content" id="specTab">
            <div style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:1.5rem;">
                <div class="row g-3">
                    <?php
                    $specs = [
                        ['fas fa-tag',        'Brand',    $product['brand'] ?? null],
                        ['fas fa-th-large',   'Category', $product['category_name'] ?? null],
                        ['fas fa-boxes',      'Stock',    $stock . ' units'],
                        ['fas fa-rupee-sign', 'MRP',      '₹' . number_format($orig_price, 2)],
                        ['fas fa-percent',    'Discount', $discount > 0 ? '₹' . number_format($discount, 2) . " ($disc_pct%)" : null],
                        ['fas fa-check',      'Status',   $product['status']],
                    ];
                    foreach ($specs as $s):
                        if (empty($s[2])) continue;
                    ?>
                        <div class="col-md-6">
                            <div style="display:flex; align-items:center; gap:14px; padding:14px 16px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
                                <div style="width:36px; height:36px; border-radius:9px; background:linear-gradient(135deg,var(--theme-primary,#1f7a8c),#0f4c5c); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="<?= $s[0] ?>" style="color:#fff; font-size:.8rem;"></i>
                                </div>
                                <div>
                                    <div style="font-size:.72rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.5px;"><?= $s[1] ?></div>
                                    <div style="font-weight:700; color:#1e293b; font-size:.9rem;"><?= htmlspecialchars($s[2]) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div class="pd-tab-content" id="reviewTab">

            <div class="row g-4">

                <!-- LEFT: Rating Summary + Write Review Form -->
                <div class="col-lg-4">

                    <!-- Rating Summary Card -->
                    <div style="background:#fff;border-radius:20px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.25rem;">
                        <?php if ($review_count > 0): ?>
                            <div class="text-center mb-3">
                                <div style="font-size:4rem;font-weight:900;color:var(--theme-primary,#1f7a8c);line-height:1;"><?= $avg_rating ?></div>
                                <div class="d-flex gap-1 justify-content-center my-2">
                                    <?php
                                    $full2 = floor($avg_rating);
                                    $half2 = ($avg_rating - $full2) >= 0.5 ? 1 : 0;
                                    $emp2 = 5 - $full2 - $half2;
                                    for ($i = 0; $i < $full2; $i++) echo '<i class="fas fa-star text-warning" style="font-size:1.1rem;"></i>';
                                    if ($half2) echo '<i class="fas fa-star-half-alt text-warning" style="font-size:1.1rem;"></i>';
                                    for ($i = 0; $i < $emp2; $i++) echo '<i class="far fa-star text-warning" style="font-size:1.1rem;"></i>';
                                    ?>
                                </div>
                                <div style="color:#64748b;font-size:.82rem;font-weight:600;"><?= $review_count ?> Review<?= $review_count !== 1 ? 's' : '' ?></div>
                            </div>
                            <!-- Rating Bars -->
                            <?php foreach ([5, 4, 3, 2, 1] as $star):
                                $cnt = $ratingCounts[$star];
                                $pct = $review_count > 0 ? ($cnt / $review_count * 100) : 0;
                            ?>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span style="width:10px;text-align:right;font-size:.78rem;font-weight:700;color:#374151;"><?= $star ?></span>
                                    <i class="fas fa-star text-warning" style="font-size:.65rem;"></i>
                                    <div style="flex:1;height:7px;background:#f1f5f9;border-radius:30px;overflow:hidden;">
                                        <div style="height:100%;width:<?= round($pct) ?>%;background:linear-gradient(90deg,#fbbf24,#f59e0b);border-radius:30px;"></div>
                                    </div>
                                    <span style="width:18px;font-size:.75rem;color:#94a3b8;"><?= $cnt ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-2">
                                <div style="font-size:3rem;margin-bottom:.75rem;">⭐</div>
                                <h6 style="font-weight:800;color:#0f172a;">No Reviews Yet</h6>
                                <p style="color:#94a3b8;font-size:.82rem;">Be the first to review this product!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Write Review Form -->
                    <?php if (!isset($_SESSION['user'])): ?>
                        <div style="background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1.5px solid #bae6fd;border-radius:16px;padding:1.25rem;text-align:center;">
                            <i class="fas fa-lock" style="font-size:1.5rem;color:#0284c7;margin-bottom:.5rem;"></i>
                            <p style="font-weight:700;color:#0f172a;margin-bottom:.25rem;">Login to Write a Review</p>
                            <p style="color:#64748b;font-size:.8rem;margin-bottom:.75rem;">Share your experience with others</p>
                            <a href="login.php" class="btn btn-sm btn-gradient rounded-pill px-4">Login / Register</a>
                        </div>

                    <?php elseif ($user_already_reviewed): ?>
                        <!-- Already Reviewed -->
                        <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:16px;padding:1.25rem;">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#16a34a);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-check" style="color:#fff;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight:800;color:#15803d;margin:0;">Review Submitted!</h6>
                                    <small style="color:#16a34a;">Thank you for your feedback</small>
                                </div>
                            </div>
                            <?php if ($user_own_review): ?>
                                <div style="background:rgba(255,255,255,.6);border-radius:12px;padding:.75rem .9rem;">
                                    <div class="d-flex gap-1 mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++) echo '<i class="' . ($i <= (int)$user_own_review['rating'] ? 'fas' : 'far') . ' fa-star text-warning" style="font-size:.8rem;"></i>'; ?>
                                    </div>
                                    <p style="font-weight:700;font-size:.85rem;color:#1e293b;margin-bottom:3px;"><?= htmlspecialchars($user_own_review['title']) ?></p>
                                    <p style="font-size:.8rem;color:#64748b;margin:0;"><?= htmlspecialchars(mb_strimwidth($user_own_review['review'], 0, 80, '...')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <!-- Write Review Form -->
                        <div style="background:#fff;border-radius:20px;border:1px solid #e2e8f0;padding:1.5rem;">
                            <h6 style="font-weight:800;color:#0f172a;margin-bottom:1.1rem;">
                                <i class="fas fa-pen-alt me-2" style="color:var(--theme-primary);"></i>Write a Review
                            </h6>

                            <!-- Interactive Star Picker -->
                            <div class="mb-3">
                                <label style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:8px;">Your Rating *</label>
                                <div id="starPicker" class="d-flex gap-2">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <button type="button" class="star-pick-btn" data-val="<?= $s ?>"
                                            style="background:none;border:none;cursor:pointer;padding:3px;font-size:1.6rem;color:#e2e8f0;transition:color .15s,transform .15s;">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" id="ratingInput" value="0">
                                <div id="ratingLabel" style="font-size:.75rem;color:#94a3b8;margin-top:4px;">Select a rating</div>
                            </div>

                            <!-- Title -->
                            <div class="mb-3">
                                <label style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">Review Title *</label>
                                <input type="text" id="reviewTitle" placeholder="Summarise your experience..."
                                    maxlength="100"
                                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:.6rem .85rem;font-size:.88rem;outline:none;transition:border-color .2s;"
                                    onfocus="this.style.borderColor='var(--theme-primary)'" onblur="this.style.borderColor='#e2e8f0'">
                            </div>

                            <!-- Review Body -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Your Review *</label>
                                    <span id="reviewCharCount" style="font-size:.7rem;color:#94a3b8;">0 / 1000</span>
                                </div>
                                <textarea id="reviewBody" rows="4" maxlength="1000"
                                    placeholder="Tell others what you think about this product. Is it worth buying?"
                                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:.6rem .85rem;font-size:.85rem;line-height:1.6;resize:vertical;outline:none;transition:border-color .2s;"
                                    onfocus="this.style.borderColor='var(--theme-primary)'" onblur="this.style.borderColor='#e2e8f0'"
                                    oninput="document.getElementById('reviewCharCount').textContent=this.value.length+' / 1000'"></textarea>
                            </div>

                            <div id="reviewFormMsg" style="font-size:.8rem;margin-bottom:.75rem;"></div>

                            <button type="button" id="submitReviewBtn"
                                style="width:100%;background:linear-gradient(135deg,var(--theme-primary,#1f7a8c),#0f4c5c);color:#fff;border:none;border-radius:12px;padding:.75rem;font-weight:700;font-size:.9rem;cursor:pointer;transition:opacity .2s;">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- RIGHT: Review Cards -->
                <div class="col-lg-8">

                    <?php if ($review_count > 0): ?>
                        <div id="reviewCardsList" class="d-flex flex-column gap-3">
                            <?php
                            $avatarColors = ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#06b6d4', '#f97316', '#ec4899'];
                            foreach ($reviews as $ridx => $rv):
                                $ac = $avatarColors[$ridx % count($avatarColors)];
                                $initial = strtoupper(substr($rv['user_name'], 0, 1));
                            ?>
                                <div class="review-card">
                                    <div class="d-flex align-items-start gap-3 mb-2">
                                        <!-- Avatar -->
                                        <div style="width:44px;height:44px;border-radius:50%;background:<?= $ac ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff;flex-shrink:0;box-shadow:0 3px 10px <?= $ac ?>55;">
                                            <?= $initial ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                                <div>
                                                    <span style="font-weight:700;color:#0f172a;font-size:.9rem;"><?= htmlspecialchars($rv['user_name']) ?></span>
                                                    <?php if ($user_already_reviewed && $user_own_review && $user_own_review['id'] == $rv['id']): ?>
                                                        <span style="background:#dbeafe;color:#1d4ed8;font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:20px;margin-left:6px;">Your Review</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex gap-0">
                                                    <?php for ($i = 1; $i <= 5; $i++) echo '<i class="' . ($i <= (int)$rv['rating'] ? 'fas' : 'far') . ' fa-star text-warning" style="font-size:.78rem;"></i>'; ?>
                                                </div>
                                            </div>
                                            <div style="color:#94a3b8;font-size:.72rem;margin-top:1px;">
                                                <i class="fas fa-calendar-alt me-1" style="font-size:.65rem;"></i><?= date('d M Y', strtotime($rv['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 style="font-weight:700;color:#1e293b;font-size:.88rem;margin-bottom:5px;"><?= htmlspecialchars($rv['title']) ?></h6>
                                    <p style="color:#64748b;font-size:.83rem;line-height:1.75;margin:0;"><?= nl2br(htmlspecialchars($rv['review'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div id="reviewCardsList">
                            <div style="background:#f8fafc;border-radius:16px;border:2px dashed #e2e8f0;padding:3.5rem;text-align:center;">
                                <div style="font-size:3.5rem;margin-bottom:1rem;">💬</div>
                                <h5 style="font-weight:800;color:#1e293b;margin-bottom:.5rem;">No Reviews Yet</h5>
                                <p style="color:#94a3b8;font-size:.88rem;">Be the first to share your experience!</p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- ── Related Products ──────────────────────────────────────── -->
    <?php if (!empty($related)): ?>
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 style="font-weight:800; color:#0f172a; margin-bottom:4px; font-size:1.4rem;">You Might Also Like</h3>
                    <p style="color:#94a3b8; font-size:.85rem; margin:0;">Similar products from <?= htmlspecialchars($product['category_name'] ?? 'this category') ?></p>
                </div>
                <a href="shop.php?category=<?= $product['category_id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-4" style="font-weight:600; white-space:nowrap;">
                    View All <i class="fas fa-arrow-right ms-1" style="font-size:.75rem;"></i>
                </a>
            </div>
            <div class="row g-4">
                <?php foreach ($related as $rp): ?>
                    <div class="col-6 col-md-3">
                        <a href="product_detail.php?id=<?= $rp['id'] ?>" class="related-card">
                            <div class="related-img p-3">
                                <img src="<?= htmlspecialchars($rp['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                    alt="<?= htmlspecialchars($rp['name']) ?>">
                            </div>
                            <div style="padding:14px 16px;">
                                <?php if (!empty($rp['brand'])): ?>
                                    <div style="font-size:.7rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">
                                        <?= htmlspecialchars($rp['brand']) ?>
                                    </div>
                                <?php endif; ?>
                                <div style="font-weight:700; color:#1e293b; font-size:.88rem; margin-bottom:6px; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                    <?= htmlspecialchars($rp['name']) ?>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span style="font-weight:800; color:var(--theme-primary,#1f7a8c); font-size:1rem;">
                                        &#8377;<?= number_format((float)$rp['final_price'], 2) ?>
                                    </span>
                                    <?php if ($rp['discount'] > 0): ?>
                                        <span style="text-decoration:line-through; color:#94a3b8; font-size:.78rem;">
                                            &#8377;<?= number_format((float)$rp['price'], 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
    const maxQty = <?= $stock ?>;

    // ── Toast ────────────────────────────────────────────
    function showPdToast(msg, ok) {
        var t = document.getElementById('pdToast');
        t.classList.remove('bg-success', 'bg-danger');
        t.classList.add(ok ? 'bg-success' : 'bg-danger');
        document.getElementById('pdToastMsg').textContent = msg;
        new bootstrap.Toast(t, {
            delay: 3200
        }).show();
    }

    function updateCartBadge(count) {
        var b = document.getElementById('navCartBadge');
        if (b) {
            b.textContent = count;
            b.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    function updateWishlistBadge(delta) {
        var b = document.getElementById('navWishlistBadge');
        if (!b) return;
        var c = Math.max(0, parseInt(b.textContent || 0) + delta);
        b.textContent = c;
        b.style.display = c > 0 ? 'inline-flex' : 'none';
    }

    // ── Image Gallery ────────────────────────────────────
    function switchImage(thumb, src) {
        document.getElementById('mainProductImage').src = src;
        document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }

    // ── Zoom ─────────────────────────────────────────────
    function openZoom(src) {
        document.getElementById('zoomImg').src = src;
        document.getElementById('zoomOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeZoom() {
        document.getElementById('zoomOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeZoom();
    });

    // ── Tab Switcher ─────────────────────────────────────
    function switchTab(id, btn) {
        document.querySelectorAll('.pd-tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.pd-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        btn.classList.add('active');
    }

    // ── Qty Stepper ──────────────────────────────────────
    var qtyInput = document.getElementById('productQty');
    document.getElementById('qtyDecBtn')?.addEventListener('click', function() {
        if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
    });
    document.getElementById('qtyIncBtn')?.addEventListener('click', function() {
        if (parseInt(qtyInput.value) < maxQty) {
            qtyInput.value = parseInt(qtyInput.value) + 1;
        } else {
            showPdToast('Maximum available: ' + maxQty + ' units', false);
        }
    });

    // ── Add to Cart ──────────────────────────────────────
    document.getElementById('addToCartBtn')?.addEventListener('click', function() {
        var btn = this,
            pid = btn.dataset.productId,
            qty = parseInt(qtyInput?.value || 1);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:16px;height:16px;"></span>&nbsp; Adding...';
        $.post('cart_handler.php', {
            action: 'add',
            product_id: pid,
            quantity: qty
        }, function(response) {
            btn.disabled = false;
            if (response == 'success') {
                showPdToast('Added to cart! Redirecting...', true);
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Added to Cart';
                btn.classList.add('btn-success');
                setTimeout(function() {
                    window.location.href = 'cart.php';
                }, 1000);
            } else {
                btn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
                showPdToast(response.replace('error: ', ''), false);
            }
        }, 'text');
    });

    // ── Wishlist Toggle ──────────────────────────────────
    document.getElementById('wishlistBtn')?.addEventListener('click', function() {
        var btn = this,
            pid = btn.dataset.productId,
            inWl = btn.dataset.inWishlist === '1';
        btn.disabled = true;
        var origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:16px;height:16px;border-width:2px;"></span>';
        $.post('wishlist_handler.php', {
            action: inWl ? 'remove' : 'add',
            product_id: pid
        }, function(response) {
            btn.disabled = false;
            if (response == 'success') {
                if (inWl) {
                    showPdToast('Removed from wishlist.', true);
                    btn.dataset.inWishlist = '0';
                    btn.innerHTML = '<i class="far fa-heart"></i>';
                } else {
                    showPdToast('Saved to wishlist!', true);
                    btn.dataset.inWishlist = '1';
                    btn.innerHTML = '<i class="fas fa-heart"></i>';
                }
            } else {
                btn.innerHTML = origHtml;
                showPdToast(response.replace('error: ', ''), false);
            }
        }, 'text');
    });

    // ── Star Picker ──────────────────────────────────────────────
    var starBtns = document.querySelectorAll('.star-pick-btn');
    var ratingLabels = ['', 'Terrible 😞', 'Poor 😕', 'Okay 😐', 'Good 😊', 'Excellent 🤩'];
    if (starBtns.length) {
        starBtns.forEach(function(btn) {
            btn.addEventListener('mouseenter', function() {
                var val = parseInt(this.dataset.val);
                starBtns.forEach(function(b) {
                    var bv = parseInt(b.dataset.val);
                    b.style.color = bv <= val ? '#f59e0b' : '#e2e8f0';
                    b.style.transform = bv <= val ? 'scale(1.2)' : 'scale(1)';
                });
            });
            btn.addEventListener('mouseleave', function() {
                var selected = parseInt(document.getElementById('ratingInput').value);
                starBtns.forEach(function(b) {
                    var bv = parseInt(b.dataset.val);
                    b.style.color = bv <= selected ? '#f59e0b' : '#e2e8f0';
                    b.style.transform = 'scale(1)';
                });
            });
            btn.addEventListener('click', function() {
                var val = parseInt(this.dataset.val);
                document.getElementById('ratingInput').value = val;
                var lbl = document.getElementById('ratingLabel');
                lbl.textContent = ratingLabels[val];
                lbl.style.color = '#f59e0b';
                lbl.style.fontWeight = '700';
                starBtns.forEach(function(b) {
                    b.style.color = parseInt(b.dataset.val) <= val ? '#f59e0b' : '#e2e8f0';
                });
            });
        });
    }

    // ── Submit Review ────────────────────────────────────────────
    document.getElementById('submitReviewBtn')?.addEventListener('click', function() {
        var rating = parseInt(document.getElementById('ratingInput')?.value || 0);
        var title = document.getElementById('reviewTitle')?.value.trim();
        var review = document.getElementById('reviewBody')?.value.trim();
        var msgBox = document.getElementById('reviewFormMsg');
        var btn = this;

        function showMsg(msg, ok) {
            msgBox.innerHTML = '<div style="padding:.5rem .85rem;border-radius:10px;font-weight:600;background:' +
                (ok ? '#dcfce7' : '#fee2e2') + ';color:' + (ok ? '#15803d' : '#b91c1c') + ';">' + msg + '</div>';
        }

        if (!rating || rating < 1) {
            showMsg('⭐ Please select a star rating.', false);
            return;
        }
        if (!title || title.length < 3) {
            showMsg('Please enter a review title (min 3 characters).', false);
            return;
        }
        if (!review || review.length < 10) {
            showMsg('Review must be at least 10 characters.', false);
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:16px;height:16px;"></span>&nbsp;Submitting...';
        msgBox.innerHTML = '';

        $.post('review_handler.php', {
                product_id: <?= $product_id ?>,
                rating: rating,
                title: title,
                review: review
            },
            function(response) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Review';
                if (response == 'success') {
                    showMsg('✅ Review submitted! Awaiting approval.', true);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showMsg('❌ ' + response, false);
                }
            }, 'text');
    });
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>