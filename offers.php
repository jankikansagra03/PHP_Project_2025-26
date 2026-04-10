<?php
include_once 'db_config.php';
$title = "Special Offers - JK Store";

// ==== COUPONS LOGIC ====
$q = "select * from offers where status ='Active'";
$data = mysqli_query($con, $q);

// ==== HOT DEALS FILTERS & PAGINATION ====
$catQuery = "SELECT id, category_name FROM categories WHERE status = 'Active'";
$catResult = mysqli_query($con, $catQuery);
$categoriesList = [];
if ($catResult) {
    while ($cRow = mysqli_fetch_assoc($catResult)) {
        $categoriesList[] = $cRow;
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$min_price = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Tab State Management
$activeTab = 'coupons';
if (isset($_GET['tab']) && $_GET['tab'] === 'deals') {
    $activeTab = 'deals';
}
if ($search !== '' || $category_id !== 0 || $min_price !== '' || $max_price !== '' || isset($_GET['page'])) {
    $activeTab = 'deals';
}

// Build WHERE Clause for Hot Deals (discount > 0)
$whereParts = ["discount > 0 AND status = 'Active'"];
$params = [];
$types = '';

if ($search !== '') {
    $whereParts[] = "(name LIKE ? OR description LIKE ? OR brand LIKE ?)";
    $searchWild = "%$search%";
    array_push($params, $searchWild, $searchWild, $searchWild);
    $types .= 'sss';
}
if ($category_id > 0) {
    $whereParts[] = "category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}
if ($min_price !== '' && is_numeric($min_price)) {
    $whereParts[] = "(price - discount) >= ?";
    $params[] = (float)$min_price;
    $types .= 'd';
}
if ($max_price !== '' && is_numeric($max_price)) {
    $whereParts[] = "(price - discount) <= ?";
    $params[] = (float)$max_price;
    $types .= 'd';
}

$whereClause = "WHERE " . implode(" AND ", $whereParts);

$countSql = "SELECT COUNT(*) as total FROM products $whereClause";
$stmtCount = mysqli_prepare($con, $countSql);
if ($types) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$countRes = mysqli_stmt_get_result($stmtCount);
$countRow = mysqli_fetch_assoc($countRes);
$totalProducts = $countRow['total'];
$totalPages = ceil($totalProducts / $limit);
mysqli_stmt_close($stmtCount);

$sql = "SELECT * FROM products $whereClause ORDER BY id DESC LIMIT ?, ?";
$stmtProd = mysqli_prepare($con, $sql);
$typesLimit = $types . 'ii';
$paramsLimit = array_merge($params, [$offset, $limit]);
mysqli_stmt_bind_param($stmtProd, $typesLimit, ...$paramsLimit);
mysqli_stmt_execute($stmtProd);
$data1 = mysqli_stmt_get_result($stmtProd);

// Utility for building query strings in pagination
$queryParamsArray = ['tab' => 'deals'];
if ($search !== '') $queryParamsArray['search'] = $search;
if ($category_id > 0) $queryParamsArray['category'] = $category_id;
if ($min_price !== '') $queryParamsArray['min_price'] = $min_price;
if ($max_price !== '') $queryParamsArray['max_price'] = $max_price;

if (!function_exists('build_query_string')) {
    function build_query_string($params, $p = null)
    {
        $q = $params;
        if ($p !== null) $q['page'] = $p;
        return '?' . http_build_query($q);
    }
}

ob_start();
?>

<div class="container-fluid mb-5 fade-in-up">
    <!-- Header Section -->
    <div class="row mb-5 rounded-4 text-white">
        <div class="col-12 text-center py-4">
            <h1 class="display-4 fw-bold mb-2">Special Offers</h1>
            <p class="lead mb-0 opacity-75">Don't miss out on our amazing deals and exclusive discounts!</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="row px-md-3 mb-4 fade-in-up">
        <div class="col-12">
            <ul class="nav nav-tabs nav-fill border-bottom-0 gap-3" id="offersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= ($activeTab === 'coupons') ? 'active' : '' ?> rounded-pill fw-bold border shadow-sm py-3"
                        id="coupons-tab" data-bs-toggle="tab" data-bs-target="#coupons-tab-pane" type="button"
                        role="tab" aria-controls="coupons-tab-pane"
                        aria-selected="<?= ($activeTab === 'coupons') ? 'true' : 'false' ?>"
                        style="color: var(--theme-primary);">
                        <i class="fas fa-ticket-alt me-2" style="color: var(--theme-accent);"></i>Coupon Codes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= ($activeTab === 'deals') ? 'active' : '' ?> rounded-pill fw-bold border shadow-sm py-3"
                        id="deals-tab" data-bs-toggle="tab" data-bs-target="#deals-tab-pane" type="button" role="tab"
                        aria-controls="deals-tab-pane"
                        aria-selected="<?= ($activeTab === 'deals') ? 'true' : 'false' ?>"
                        style="color: var(--theme-primary);">
                        <i class="fas fa-fire me-2 text-danger"></i>Hot Deals
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="offersTabContent">
        <!-- Active Coupons Section (Tab Pane) -->
        <div class="tab-pane fade <?= ($activeTab === 'coupons') ? 'show active' : '' ?> px-md-3" id="coupons-tab-pane"
            role="tabpanel" aria-labelledby="coupons-tab" tabindex="0">
            <?php if (mysqli_num_rows($data) > 0): ?>
                <div class="row g-4 mb-5">
                    <?php while ($coupon = mysqli_fetch_assoc($data)): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                                <div class="card-body p-4 position-relative overflow-hidden">
                                    <!-- Inner Ticket Dashed Border -->
                                    <div class="position-absolute top-0 bottom-0 start-0 end-0 m-2 border border-2 rounded"
                                        style="border-style: dashed !important; border-color: var(--theme-primary) !important; opacity: 0.5; pointer-events: none;">
                                    </div>

                                    <div class="position-relative z-1 d-flex flex-column h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="badge px-3 py-2 rounded-pill shadow-sm fw-bold"
                                                style="background: var(--primary-gradient); color: white;">Limited
                                                Time</span>
                                            <i class="fas fa-tags fa-2x"
                                                style="color: var(--theme-primary); opacity: 0.25;"></i>
                                        </div>

                                        <div class="text-center my-3">
                                            <h2 class="fw-bold mb-1 font-monospace text-dark" style="letter-spacing: 2px;">
                                                <?php echo htmlspecialchars($coupon['code']); ?>
                                            </h2>
                                        </div>

                                        <p class="mb-4 text-center text-muted mx-auto" style="max-width: 250px;">
                                            <?= htmlspecialchars($coupon['description']); ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3"
                                            style="border-color: rgba(0,0,0,0.1) !important;">
                                            <small class="text-muted fw-semibold pt-1">
                                                <i class="far fa-calendar-alt me-1"></i>Ends:
                                                <?php echo date('M d, Y', strtotime($coupon['valid_to'])); ?>
                                            </small>
                                            <button
                                                class="btn btn-outline-gradient btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm"
                                                onclick="copyCode('<?php echo htmlspecialchars($coupon['code'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-copy me-1"></i>Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm bg-light text-center py-5 rounded-4 mb-5">
                    <i class="fas fa-frown fa-3x text-muted opacity-25 mb-3"></i>
                    <h5 class="text-secondary fw-semibold">No active coupons available right now</h5>
                </div>
            <?php endif; ?>
        </div>

        <!-- Discounted Products Section (Tab Pane) -->
        <div class="tab-pane fade <?= ($activeTab === 'deals') ? 'show active' : '' ?> px-md-3" id="deals-tab-pane"
            role="tabpanel" aria-labelledby="deals-tab" tabindex="0">
            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px; z-index: 10;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0" style="color: var(--theme-primary);"><i
                                        class="fas fa-filter me-2"></i>Filters</h5>
                                <span class="badge"
                                    style="background: var(--primary-gradient);"><?php echo $totalProducts; ?>
                                    Deals</span>
                            </div>

                            <form action="offers.php" method="GET">
                                <input type="hidden" name="tab" value="deals">
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
                                        style="letter-spacing: 1px;">Max Price (&#8377;)</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="number" step="0.01" class="form-control bg-light border-0"
                                                name="min_price" placeholder="Min"
                                                value="<?php echo htmlspecialchars($min_price, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" step="0.01" class="form-control bg-light border-0"
                                                name="max_price" placeholder="Max"
                                                value="<?php echo htmlspecialchars($max_price, ENT_QUOTES); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-gradient py-2">Apply Filters</button>
                                    <?php if (count($queryParamsArray) > 1): ?>
                                        <a href="offers.php?tab=deals"
                                            class="btn btn-outline-gradient py-2 text-center text-decoration-none">Clear
                                            All</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="col-lg-9 col-md-8">
                    <!-- Active Filters Breadcrumb -->
                    <?php if (count($queryParamsArray) > 1): ?>
                        <div
                            class="d-flex flex-wrap gap-2 mb-4 align-items-center bg-white rounded-4 p-3 shadow-sm border-0">
                            <span class="text-muted small fw-semibold me-2">Active Filters:</span>
                            <?php if ($search !== ''): ?>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">Search:
                                    <?php echo htmlspecialchars($search); ?></span>
                            <?php endif; ?>
                            <?php if ($category_id > 0): ?>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">Category
                                    Applied</span>
                            <?php endif; ?>
                            <?php if ($min_price !== '' || $max_price !== ''): ?>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">Price
                                    Filtered</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4 mb-5">
                        <?php if (mysqli_num_rows($data1) > 0): ?>
                            <?php while ($offer = mysqli_fetch_assoc($data1)):
                                $finalPrice = (float)$offer['price'] - (float)$offer['discount'];
                            ?>
                                <div class="col-sm-6 col-lg-4 col-xl-3 fade-in-up">
                                    <div class="card h-100 border-0 p-0 overflow-hidden shadow-sm">
                                        <div class="position-relative bg-white d-flex align-items-center justify-content-center border-bottom"
                                            style="aspect-ratio: 1/1;">
                                            <img src="<?= htmlspecialchars($offer['image'] ?? 'images/placeholder.png', ENT_QUOTES) ?>"
                                                alt="" class="img-fluid p-4"
                                                style="max-height: 100%; object-fit: contain; mix-blend-mode: multiply;">

                                            <button
                                                class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3 d-flex align-items-center justify-content-center"
                                                style="width: 36px; height: 36px; z-index: 5;" title="Add to Wishlist">
                                                <i class="fas fa-heart text-danger"></i>
                                            </button>

                                            <span
                                                class="badge position-absolute top-0 start-0 m-3 px-3 py-2 shadow-sm rounded-pill fw-bold"
                                                style="background: var(--theme-accent); font-size: 0.85rem;">
                                                Save &#8377;<?= number_format((float)$offer['discount'], 2) ?>
                                            </span>
                                        </div>

                                        <div class="card-body p-4 d-flex flex-column position-relative bg-light">
                                            <?php if (!empty($offer['brand'])): ?>
                                                <div class="mb-2">
                                                    <span class="badge bg-white text-dark border px-2 py-1 fw-semibold shadow-sm"
                                                        style="color: var(--theme-primary) !important;">
                                                        <?php echo htmlspecialchars($offer['brand']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <h6 class="fw-bold mb-2 text-truncate"
                                                title="<?php echo htmlspecialchars($offer['name'] ?? '', ENT_QUOTES); ?>">
                                                <a href="product_detail.php?id=<?php echo $offer['id']; ?>"
                                                    class="text-decoration-none text-dark stretched-link">
                                                    <?php echo htmlspecialchars($offer['name'] ?? ''); ?>
                                                </a>
                                            </h6>

                                            <p class="text-muted small flex-grow-1 mb-4"
                                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                                <?php echo htmlspecialchars($offer['description'] ?? ''); ?>
                                            </p>

                                            <div class="d-flex justify-content-between align-items-center mt-auto position-relative"
                                                style="z-index: 2;">
                                                <div>
                                                    <div
                                                        class="text-muted small text-decoration-line-through mb-1 lh-1 fw-semibold opacity-75">
                                                        &#8377;<?php echo number_format((float)($offer['price'] ?? 0), 2); ?>
                                                    </div>
                                                    <div class="fs-5 fw-black"
                                                        style="color: var(--theme-accent); font-weight: 900;">
                                                        &#8377;<?php echo number_format($finalPrice, 2); ?>
                                                    </div>
                                                </div>
                                                <button
                                                    class="btn btn-gradient rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                                    style="width: 44px; height: 44px; padding: 0; flex-shrink: 0;"
                                                    title="Add to Cart">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 py-5 text-center bg-white rounded-4 border-0 shadow-sm">
                                <i class="fas fa-box-open fa-3x text-muted opacity-25 mb-3"></i>
                                <h5 class="text-secondary fw-semibold">No products found matching your deals criteria.</h5>
                                <a href="offers.php?tab=deals" class="btn btn-outline-gradient mt-3">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4 mb-5">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link shadow-sm border-0 bg-white" style="color: var(--theme-primary);"
                                        href="<?php echo build_query_string($queryParamsArray, $page - 1); ?>"
                                        tabindex="-1">
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
    </div>

</div>

<style>
    /* Stylize the Bootstrap Tab component specifically for modern GUI */
    .nav-tabs .nav-link {
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.7);
    }

    .nav-tabs .nav-link:hover {
        border-color: #dee2e6;
        background: #fff;
        transform: translateY(-2px);
    }

    .nav-tabs .nav-link.active {
        background: #fff;
        border-color: var(--theme-primary) !important;
        border-width: 2px !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05) !important;
    }
</style>

<!-- Toast for Coupon Copy Feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
    <div id="copyToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold">
                <i class="fas fa-check-circle me-2"></i> Coupon code copied successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    function copyCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            // Find Bootstrap Toast constructor globally or fallback to alert
            if (typeof bootstrap !== 'undefined') {
                const toastElement = $('#copyToast').get(0);
                const toast = new bootstrap.Toast(toastElement);
                toast.show();
            } else {
                alert('Coupon code "' + code + '" copied to clipboard!');
            }
        });
    }
</script>

<?php
mysqli_stmt_close($stmtProd);
$content = ob_get_clean();
include 'layout.php';
?>