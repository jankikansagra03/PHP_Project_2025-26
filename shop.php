<?php
include_once 'db_config.php';
$title = "Shop - JK Store";

// Initialize filter variables
$search = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$min_price = trim($_GET['min_price'] ?? '');
$max_price = trim($_GET['max_price'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;

// Fetch categories for the sidebar filter
$catQuery = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$catResult = mysqli_query($con, $catQuery);
$categoriesList = [];
if ($catResult) {
    while ($row = mysqli_fetch_assoc($catResult)) {
        $categoriesList[] = $row;
    }
}

// Build query conditions
$whereClauses = ["status = 'Active'"];
$params = [];
$types = '';

if ($search !== '') {
    $whereClauses[] = '(name LIKE ? OR brand LIKE ? OR description LIKE ?)';
    $searchWildcard = '%' . $search . '%';
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= 'sss';
}

if ($category_id > 0) {
    $whereClauses[] = 'category_id = ?';
    $params[] = $category_id;
    $types .= 'i';
}

if ($min_price !== '' && is_numeric($min_price)) {
    $whereClauses[] = 'price >= ?';
    $params[] = (float) $min_price;
    $types .= 'd';
}

if ($max_price !== '' && is_numeric($max_price)) {
    $whereClauses[] = 'price <= ?';
    $params[] = (float) $max_price;
    $types .= 'd';
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = ' WHERE ' . implode(' AND ', $whereClauses);
}

// Count total products for pagination
$countQuery = 'SELECT COUNT(*) as total FROM products' . $whereSql;
$stmtCount = mysqli_prepare($con, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$countRes = mysqli_stmt_get_result($stmtCount);
$totalProducts = (int) (($countRes ? mysqli_fetch_assoc($countRes)['total'] : 0) ?? 0);
mysqli_stmt_close($stmtCount);

$totalPages = max(1, (int) ceil($totalProducts / $limit));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Fetch paginated products
$productQuery = 'SELECT * FROM products' . $whereSql . ' ORDER BY id DESC LIMIT ?, ?';
$stmtProd = mysqli_prepare($con, $productQuery);
$prodTypes = $types . 'ii';
$prodParams = array_merge($params, [$offset, $limit]);
if (!empty($prodParams)) {
    mysqli_stmt_bind_param($stmtProd, $prodTypes, ...$prodParams);
}
mysqli_stmt_execute($stmtProd);
$data = mysqli_stmt_get_result($stmtProd);

// Query params array for pagination links
$queryParamsArray = [];
if ($search !== '') $queryParamsArray['search'] = $search;
if ($category_id > 0) $queryParamsArray['category'] = $category_id;
if ($min_price !== '') $queryParamsArray['min_price'] = $min_price;
if ($max_price !== '') $queryParamsArray['max_price'] = $max_price;

function build_query_string($params, $page = null)
{
    $q = $params;
    if ($page !== null) $q['page'] = $page;
    return '?' . http_build_query($q);
}

// ── User cart & wishlist product ID sets (for status badges) ──
$user_cart_ids     = [];
$user_wishlist_ids = [];
@session_start();
if (isset($_SESSION['user'])) {
    $se = mysqli_real_escape_string($con, $_SESSION['user']);
    $cr = mysqli_query($con, "SELECT product_id FROM cart WHERE user_email='$se'");
    while ($r = mysqli_fetch_assoc($cr)) $user_cart_ids[] = (int)$r['product_id'];
    $wr = mysqli_query($con, "SELECT product_id FROM wishlist WHERE user_email='$se'");
    while ($r = mysqli_fetch_assoc($wr)) $user_wishlist_ids[] = (int)$r['product_id'];
}

ob_start();
?>

<div class="container-fluid mb-5 fade-in-up">
    <!-- Header Section -->
    <div class="row mb-5 rounded-4 p-3 text-white">
        <div class="col-12 text-center py-4">
            <h1 class="display-4 fw-bold mb-2">Our Collection</h1>
            <p class="lead mb-0 opacity-75">Discover our amazing collection of premium products</p>
        </div>
    </div>

    <div class="row px-md-3">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card sticky-top" style="top: 80px; z-index: 10;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0" style="color: var(--theme-primary);"><i
                                class="fas fa-filter me-2"></i>Filters</h5>
                        <?php if (!empty($queryParamsArray)): ?>
                            <span class="badge" style="background: var(--primary-gradient);"><?php echo $totalProducts; ?>
                                Results</span>
                        <?php endif; ?>
                    </div>

                    <form action="shop.php" method="GET">
                        <!-- Search Box -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small text-uppercase"
                                style="letter-spacing: 1px;">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control bg-light border-0" name="search"
                                    value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>"
                                    placeholder="Keywords...">
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small text-uppercase"
                                style="letter-spacing: 1px;">Categories</label>
                            <select name="category" class="form-select bg-light border-0">
                                <option value="">All Categories</option>
                                <?php foreach ($categoriesList as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                        <?php echo ($category_id === (int)$cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small text-uppercase"
                                style="letter-spacing: 1px;">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" step="0.01" class="form-control bg-light border-0"
                                        name="min_price" placeholder="Min &#8377;"
                                        value="<?php echo htmlspecialchars($min_price, ENT_QUOTES); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" step="0.01" class="form-control bg-light border-0"
                                        name="max_price" placeholder="Max &#8377;"
                                        value="<?php echo htmlspecialchars($max_price, ENT_QUOTES); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-gradient py-2">Apply Filters</button>
                            <?php if (!empty($queryParamsArray)): ?>
                                <a href="shop.php"
                                    class="btn btn-outline-gradient py-2 text-center text-decoration-none">Clear All</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Product Grid -->
        <div class="col-lg-9 col-md-8">
            <!-- Active Filters Breadcrumb -->
            <?php if (!empty($queryParamsArray)): ?>
                <div class="d-flex flex-wrap gap-2 mb-4 align-items-center bg-white rounded-4 p-3">
                    <span class="text-muted small fw-semibold me-2">Active Filters:</span>
                    <?php if ($search !== ''): ?>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">Search:
                            <?php echo htmlspecialchars($search); ?></span>
                    <?php endif; ?>
                    <?php if ($category_id > 0): ?>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">Category Applied</span>
                    <?php endif; ?>
                    <?php if ($min_price !== '' || $max_price !== ''): ?>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">Price Filtered</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 mb-5">
                <?php if (mysqli_num_rows($data) > 0): ?>
                    <?php while ($products = mysqli_fetch_assoc($data)):
                        $pid     = (int)$products['id'];
                        $in_cart = in_array($pid, $user_cart_ids);
                        $in_wl   = in_array($pid, $user_wishlist_ids);
                    ?>
                        <div class="col-sm-6 col-lg-4 col-xl-3 fade-in-up">
                            <div class="card h-100 border-0 p-0 overflow-hidden" style="border-radius:16px; box-shadow:0 2px 16px rgba(0,0,0,.07);">
                                <!-- Image Container -->
                                <div class="position-relative bg-white d-flex align-items-center justify-content-center border-bottom"
                                    style="aspect-ratio: 1/1;">
                                    <img src="<?= htmlspecialchars($products['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                        alt="" class="img-fluid p-4"
                                        style="max-height: 100%; object-fit: contain; mix-blend-mode: multiply;">

                                    <!-- Wishlist Toggle Button -->
                                    <button class="btn <?= $in_wl ? 'btn-danger' : 'btn-light' ?> rounded-circle shadow-sm position-absolute top-0 end-0 m-3 d-flex align-items-center justify-content-center wishlist-shop-btn"
                                        style="width: 38px; height: 38px; z-index: 5; transition:all .2s;"
                                        title="<?= $in_wl ? 'Remove from Wishlist' : 'Add to Wishlist' ?>"
                                        data-product-id="<?= $pid ?>"
                                        data-in-wishlist="<?= $in_wl ? '1' : '0' ?>">
                                        <i class="<?= $in_wl ? 'fas fa-heart text-white' : 'far fa-heart text-danger' ?>"></i>
                                    </button>

                                    <!-- In Cart Status Badge -->
                                    <span class="badge bg-success position-absolute bottom-0 start-0 m-3 px-2 py-1 shadow-sm"
                                        id="cart-badge-<?= $pid ?>"
                                        style="font-size:.72rem; <?= $in_cart ? '' : 'display:none;' ?>">
                                        <i class="fas fa-check me-1"></i>In Cart
                                    </span>

                                    <!-- Discount Badge -->
                                    <?php if (!empty($products['discount']) && $products['discount'] > 0): ?>
                                        <span class="badge position-absolute top-0 start-0 m-3 px-2 py-1 shadow-sm"
                                            style="background: var(--theme-accent);">
                                            -&#8377;<?= number_format((float)$products['discount'], 0) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Card Body -->
                                <div class="card-body p-4 d-flex flex-column position-relative">
                                    <?php if (!empty($products['brand'])): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark border px-2 py-1 fw-semibold"
                                                style="color: var(--theme-primary) !important;">
                                                <?= htmlspecialchars($products['brand']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <h6 class="fw-bold mb-2 text-truncate"
                                        title="<?= htmlspecialchars($products['name'] ?? '', ENT_QUOTES) ?>">
                                        <a href="product_detail.php?id=<?= $pid ?>"
                                            class="text-decoration-none text-dark stretched-link">
                                            <?= htmlspecialchars($products['name'] ?? '') ?>
                                        </a>
                                    </h6>

                                    <p class="text-muted small mb-4"
                                        style="display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5;">
                                        <?= htmlspecialchars($products['description'] ?? '') ?>
                                    </p>

                                    <!-- Price and Add to Cart -->
                                    <div class="d-flex justify-content-between align-items-center mt-auto position-relative" style="z-index:2;">
                                        <div>
                                            <div class="fs-5 fw-bold" style="color: var(--theme-primary);">
                                                &#8377;<?= number_format((float)($products['final_price'] ?? $products['price']), 2) ?>
                                            </div>
                                            <?php if (!empty($products['discount']) && $products['discount'] > 0): ?>
                                                <small class="text-muted text-decoration-line-through" style="font-size:.75rem;">
                                                    &#8377;<?= number_format((float)$products['price'], 2) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn <?= $in_cart ? 'btn-success' : 'btn-gradient' ?> rounded-circle shadow-sm d-flex align-items-center justify-content-center add-to-cart-shop-btn"
                                            style="width: 42px; height: 42px; padding: 0; flex-shrink: 0; transition:all .2s;"
                                            title="<?= $in_cart ? 'In Cart' : 'Add to Cart' ?>"
                                            data-product-id="<?= $pid ?>"
                                            id="cart-btn-<?= $pid ?>">
                                            <i class="fas <?= $in_cart ? 'fa-check' : 'fa-shopping-cart' ?>"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4">
                        <div class="mb-3">
                            <i class="fas fa-box-open fa-3x text-muted opacity-50"></i>
                        </div>
                        <h4 class="text-secondary fw-bold">No Products Found</h4>
                        <p class="text-muted">We couldn't find any products matching your current filters.</p>
                        <a href="shop.php" class="btn btn-outline-gradient mt-2 py-2 text-decoration-none">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4 mb-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link shadow-sm border-0 bg-white" style="color: var(--theme-primary);"
                                href="<?php echo build_query_string($queryParamsArray, $page - 1); ?>" tabindex="-1">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link shadow-sm border-0 bg-white" style="color: var(--theme-primary);" href="' . build_query_string($queryParamsArray, 1) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link shadow-sm border-0 bg-white">...</span></li>';
                            }
                        }

                        for ($p = $startPage; $p <= $endPage; $p++):
                        ?>
                            <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                                <?php if ($p === $page): ?>
                                    <span class="page-link shadow-sm border-0"
                                        style="background: var(--primary-gradient); color: #fff;"><?php echo $p; ?></span>
                                <?php else: ?>
                                    <a class="page-link shadow-sm border-0 bg-white" style="color: var(--theme-primary);"
                                        href="<?php echo build_query_string($queryParamsArray, $p); ?>"><?php echo $p; ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link shadow-sm border-0 bg-white">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link shadow-sm border-0 bg-white" style="color: var(--theme-primary);" href="' . build_query_string($queryParamsArray, $totalPages) . '">' . $totalPages . '</a></li>';
                        }
                        ?>

                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link shadow-sm border-0 bg-white" style="color: var(--theme-primary);"
                                href="<?php echo build_query_string($queryParamsArray, $page + 1); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
mysqli_stmt_close($stmtProd);

$shop_js = <<<'SHOPJS'
<!-- Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999" id="shopToastContainer">
    <div id="shopToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="shopToastMsg">Done!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
function showShopToast(msg, success) {
    var t = document.getElementById('shopToast');
    t.classList.remove('bg-success','bg-danger');
    t.classList.add(success ? 'bg-success' : 'bg-danger');
    document.getElementById('shopToastMsg').textContent = msg;
    new bootstrap.Toast(t, {delay:3000}).show();
}
function updateCartBadge(count) {
    var b = document.getElementById('navCartBadge');
    if (b) { b.textContent = count; b.style.display = count > 0 ? 'inline-flex' : 'none'; }
}
function updateWishlistBadge(delta) {
    var b = document.getElementById('navWishlistBadge');
    if (!b) return;
    var c = Math.max(0, parseInt(b.textContent || 0) + delta);
    b.textContent = c;
    b.style.display = c > 0 ? 'inline-flex' : 'none';
}
document.addEventListener('click', function(e) {
    // ── Add to Cart ──────────────────────────────────────
    var cartBtn = e.target.closest('.add-to-cart-shop-btn');
    if (cartBtn) {
        e.preventDefault(); e.stopPropagation();
        var pid = cartBtn.dataset.productId;
        cartBtn.disabled = true;
        $.post('cart_handler.php', {action:'add', product_id:pid, quantity:1}, function(response) {
            cartBtn.disabled = false;
            if (response == 'success') {
                showShopToast('Added to cart!', true);
                cartBtn.classList.remove('btn-gradient');
                cartBtn.classList.add('btn-success');
                cartBtn.querySelector('i').className = 'fas fa-check';
                cartBtn.title = 'In Cart';
                var badge = document.getElementById('cart-badge-' + pid);
                if (badge) badge.style.display = '';
            } else {
                showShopToast(response.replace('error: ', ''), false);
            }
        }, 'text').fail(function() { cartBtn.disabled=false; showShopToast('Error. Try again.', false); });
        return;
    }
    // ── Wishlist Toggle ──────────────────────────────────
    var wlBtn = e.target.closest('.wishlist-shop-btn');
    if (wlBtn) {
        e.preventDefault(); e.stopPropagation();
        var pid  = wlBtn.dataset.productId;
        var inWl = wlBtn.dataset.inWishlist === '1';
        var icon = wlBtn.querySelector('i');
        wlBtn.disabled = true;
        $.post('wishlist_handler.php', {action: inWl ? 'remove' : 'add', product_id: pid}, function(response) {
            wlBtn.disabled = false;
            if (response == 'success') {
                if (inWl) {
                    showShopToast('Removed from wishlist.', true);
                    wlBtn.dataset.inWishlist = '0';
                    wlBtn.classList.remove('btn-danger'); wlBtn.classList.add('btn-light');
                    icon.className = 'far fa-heart text-danger';
                    wlBtn.title = 'Add to Wishlist';
                } else {
                    showShopToast('Saved to wishlist!', true);
                    wlBtn.dataset.inWishlist = '1';
                    wlBtn.classList.remove('btn-light'); wlBtn.classList.add('btn-danger');
                    icon.className = 'fas fa-heart text-white';
                    wlBtn.title = 'Remove from Wishlist';
                }
            } else {
                showShopToast(response.replace('error: ', ''), false);
            }
        }, 'text').fail(function() { wlBtn.disabled=false; showShopToast('Error. Try again.', false); });
    }
});
</script>
SHOPJS;

$content = ob_get_clean() . $shop_js;
include 'layout.php';
?>