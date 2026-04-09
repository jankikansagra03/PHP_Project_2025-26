<?php
include_once 'db_config.php';

function user_has_ordered_product(mysqli $con, string $userEmail, int $productId): bool
{
    $stmt = mysqli_prepare(
        $con,
        'SELECT 1 FROM orders o INNER JOIN order_items oi ON oi.order_id = o.id WHERE o.user_email = ? AND oi.product_id = ? LIMIT 1'
    );
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 'si', $userEmail, $productId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok = $res && mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

function get_user_fullname_by_email(mysqli $con, string $userEmail): string
{
    $stmt = mysqli_prepare($con, 'SELECT fullname FROM registration WHERE email = ? LIMIT 1');
    if (!$stmt) return '';
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $name = '';
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $name = trim((string) ($row['fullname'] ?? ''));
    }
    mysqli_stmt_close($stmt);
    return $name;
}

function is_valid_review_status(string $status): bool
{
    return in_array($status, ['Pending', 'Approved', 'Rejected'], true);
}

function product_exists(mysqli $con, int $productId): bool
{
    $stmt = mysqli_prepare($con, 'SELECT 1 FROM products WHERE id = ? LIMIT 1');
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok = $res && mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

function review_exists_for_user_product(mysqli $con, string $userEmail, int $productId, int $ignoreId = 0): bool
{
    if ($ignoreId > 0) {
        $stmt = mysqli_prepare($con, 'SELECT 1 FROM reviews WHERE user_email = ? AND product_id = ? AND id <> ? LIMIT 1');
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 'sii', $userEmail, $productId, $ignoreId);
    } else {
        $stmt = mysqli_prepare($con, 'SELECT 1 FROM reviews WHERE user_email = ? AND product_id = ? LIMIT 1');
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 'si', $userEmail, $productId);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok = $res && mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

function normalize_datetime_local(string $value): ?string
{
    $value = trim($value);
    if ($value === '') return null;
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
    if (!$dt) return null;
    return $dt->format('Y-m-d H:i:s');
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnStatus = trim($_POST['return_status'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_reviews.php?page=' . $returnPage
        . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '')
        . ($returnStatus !== '' ? '&status=' . urlencode($returnStatus) : '');

    if ($action === 'create') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $review = trim($_POST['review'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $createdAtInput = trim($_POST['created_at'] ?? '');
        $updatedAtInput = trim($_POST['updated_at'] ?? '');

        if ($productId <= 0 || $userEmail === '' || !filter_var($userEmail, FILTER_VALIDATE_EMAIL) || $rating < 1 || $rating > 5 || $title === '' || $review === '' || !is_valid_review_status($status)) {
            setcookie('error', 'Please provide valid review details.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (!product_exists($con, $productId)) {
            setcookie('error', 'Selected product does not exist.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (!user_has_ordered_product($con, $userEmail, $productId)) {
            setcookie('error', 'Selected user has not ordered this product.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $userName = get_user_fullname_by_email($con, $userEmail);
        if ($userName === '') {
            setcookie('error', 'Unable to fetch username for selected email.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (review_exists_for_user_product($con, $userEmail, $productId)) {
            setcookie('error', 'A review by this user for the selected product already exists.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $createdAt = normalize_datetime_local($createdAtInput);
        $updatedAt = normalize_datetime_local($updatedAtInput);
        if (($createdAtInput !== '' && $createdAt === null) || ($updatedAtInput !== '' && $updatedAt === null)) {
            setcookie('error', 'Invalid date-time format.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if ($createdAt !== null && $updatedAt !== null && strtotime($updatedAt) < strtotime($createdAt)) {
            setcookie('error', 'Updated time cannot be earlier than created time.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'INSERT INTO reviews (product_id, user_email, user_name, rating, title, review, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            setcookie('error', 'Failed to create review.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'ississsss', $productId, $userEmail, $userName, $rating, $title, $review, $status, $createdAt, $updatedAt);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Review created successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to create review.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $review = trim($_POST['review'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $createdAtInput = trim($_POST['created_at'] ?? '');
        $updatedAtInput = trim($_POST['updated_at'] ?? '');

        if ($id <= 0 || $productId <= 0 || $userEmail === '' || !filter_var($userEmail, FILTER_VALIDATE_EMAIL) || $rating < 1 || $rating > 5 || $title === '' || $review === '' || !is_valid_review_status($status)) {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (!product_exists($con, $productId)) {
            setcookie('error', 'Selected product does not exist.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (!user_has_ordered_product($con, $userEmail, $productId)) {
            setcookie('error', 'Selected user has not ordered this product.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $userName = get_user_fullname_by_email($con, $userEmail);
        if ($userName === '') {
            setcookie('error', 'Unable to fetch username for selected email.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (review_exists_for_user_product($con, $userEmail, $productId, $id)) {
            setcookie('error', 'A review by this user for the selected product already exists.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $createdAt = normalize_datetime_local($createdAtInput);
        $updatedAt = normalize_datetime_local($updatedAtInput);
        if (($createdAtInput !== '' && $createdAt === null) || ($updatedAtInput !== '' && $updatedAt === null)) {
            setcookie('error', 'Invalid date-time format.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if ($createdAt !== null && $updatedAt !== null && strtotime($updatedAt) < strtotime($createdAt)) {
            setcookie('error', 'Updated time cannot be earlier than created time.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'UPDATE reviews SET product_id=?, user_email=?, user_name=?, rating=?, title=?, review=?, status=?, created_at=?, updated_at=? WHERE id=?');
        if (!$stmt) {
            setcookie('error', 'Failed to update review.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'ississsssi', $productId, $userEmail, $userName, $rating, $title, $review, $status, $createdAt, $updatedAt, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Review updated successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to update review.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            setcookie('error', 'Invalid delete request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'DELETE FROM reviews WHERE id=?');
        if (!$stmt) {
            setcookie('error', 'Failed to delete review.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Review deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete review.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'set_status') {
        $id = (int) ($_POST['id'] ?? 0);
        $newStatus = trim($_POST['new_status'] ?? 'Pending');
        if (!in_array($newStatus, ['Approved', 'Rejected'], true) || $id <= 0) {
            setcookie('error', 'Invalid status request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'UPDATE reviews SET status=? WHERE id=?');
        if (!$stmt) {
            setcookie('error', 'Failed to update review status.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Review marked as ' . $newStatus . '.', time() + 5, '/');
        else setcookie('error', 'Failed to update review status.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$whereParts = [];
$params = [];
$types = '';

if ($search !== '') {
    $whereParts[] = '(r.title LIKE ? OR r.review LIKE ? OR r.user_name LIKE ? OR r.user_email LIKE ? OR p.name LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sssss';
}
if ($statusFilter !== '') {
    $whereParts[] = 'r.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
$whereSql = !empty($whereParts) ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

$countSql = 'SELECT COUNT(*) AS total FROM reviews r LEFT JOIN products p ON p.id = r.product_id' . $whereSql;
$countStmt = mysqli_prepare($con, $countSql);
if (!empty($params)) mysqli_stmt_bind_param($countStmt, $types, ...$params);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT r.*, p.name AS product_name, p.image AS product_image FROM reviews r LEFT JOIN products p ON p.id = r.product_id'
    . $whereSql . ' ORDER BY r.id DESC LIMIT ?, ?';
$listStmt = mysqli_prepare($con, $listSql);
if (!empty($params)) {
    $listTypes = $types . 'ii';
    $listParams = $params;
    $listParams[] = $offset;
    $listParams[] = $perPage;
    mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams);
} else {
    mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
}
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$products = [];
$productsRes = mysqli_query($con, 'SELECT id, name FROM products ORDER BY name ASC');
if ($productsRes) while ($p = mysqli_fetch_assoc($productsRes)) $products[] = $p;

$users = [];
$usersRes = mysqli_query($con, 'SELECT email, fullname FROM registration ORDER BY fullname ASC');
if ($usersRes) while ($u = mysqli_fetch_assoc($usersRes)) $users[] = $u;

$eligibleUsersByProduct = [];
$eligibleUsersRes = mysqli_query(
    $con,
    'SELECT oi.product_id, r.email, r.fullname
     FROM order_items oi
     INNER JOIN orders o ON o.id = oi.order_id
     INNER JOIN registration r ON r.email = o.user_email
     GROUP BY oi.product_id, r.email, r.fullname
     ORDER BY oi.product_id, r.fullname'
);
if ($eligibleUsersRes) {
    while ($eu = mysqli_fetch_assoc($eligibleUsersRes)) {
        $pid = (int) ($eu['product_id'] ?? 0);
        if ($pid <= 0) continue;
        if (!isset($eligibleUsersByProduct[$pid])) $eligibleUsersByProduct[$pid] = [];
        $eligibleUsersByProduct[$pid][] = [
            'email' => (string) ($eu['email'] ?? ''),
            'fullname' => (string) ($eu['fullname'] ?? ''),
        ];
    }
}

$title = 'Admin Reviews - JK Store';
$admin_active = 'reviews';
$admin_page_title = 'Reviews';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Reviews</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReviewModal"><i class="fas fa-plus me-1"></i>Add Review</button>
    </div>
    <div class="products-body">
        <form method="get" class="row g-2 mb-3" novalidate>
            <div class="col-md-8"><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by title, review, user, product..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-4"><select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                </select></div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $status = (string) ($row['status'] ?? 'Pending');
                            $badge = $status === 'Approved' ? 'success' : ($status === 'Rejected' ? 'danger' : 'warning');
                            ?>
                            <tr>
                                <td><?= (int) ($row['id'] ?? 0) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2"><?php if (!empty($row['product_image'])): ?><img src="<?= htmlspecialchars((string) $row['product_image'], ENT_QUOTES, 'UTF-8') ?>" class="small-preview border" alt="product"><?php endif; ?><div>
                                            <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-muted small">Product ID: <?= (int) ($row['product_id'] ?? 0) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['user_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars((string) ($row['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><span class="badge text-bg-secondary"><?= (int) ($row['rating'] ?? 0) ?>/5</span></td>
                                <td><?= htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                    <div class="products-actions d-flex flex-wrap gap-1"><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewReviewModal<?= (int) $row['id'] ?>" title="View" aria-label="View"><i class="fas fa-eye"></i></button><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editReviewModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><?php if ($status !== 'Approved'): ?><form method="post" class="d-inline" novalidate><input type="hidden" name="action" value="set_status"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="new_status" value="Approved"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>"><button type="submit" class="btn btn-sm btn-success" title="Approve" aria-label="Approve"><i class="fas fa-check"></i></button></form><?php endif; ?><?php if ($status !== 'Rejected'): ?><form method="post" class="d-inline" novalidate><input type="hidden" name="action" value="set_status"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="new_status" value="Rejected"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>"><button type="submit" class="btn btn-sm btn-secondary" title="Reject" aria-label="Reject"><i class="fas fa-times"></i></button></form><?php endif; ?><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteReviewModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                                </td>
                            </tr>

                            <div class="modal fade" id="viewReviewModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Review #<?= (int) $row['id'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Title:</strong> <?= htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p><strong>Review:</strong><br><?= nl2br(htmlspecialchars((string) ($row['review'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                                            <p><strong>Created At:</strong> <?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p class="mb-0"><strong>Updated At:</strong> <?= htmlspecialchars((string) ($row['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="editReviewModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Review</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="row g-2">
                                                    <div class="col-md-6"><label class="form-label">Product <span class="text-danger">*</span></label><select class="form-select review-product-select" data-target-user="er_uem_sel_<?= (int) $row['id'] ?>" name="product_id" required data-validation="required,select" data-error="#er_pid_<?= (int) $row['id'] ?>">
                                                            <option value="">Select product</option><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) ($row['product_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                                                        </select><small id="er_pid_<?= (int) $row['id'] ?>"></small></div>
                                                    <div class="col-md-6"><label class="form-label">User Email <span class="text-danger">*</span></label><select class="form-select review-user-select" id="er_uem_sel_<?= (int) $row['id'] ?>" name="user_email" required data-validation="required,select" data-error="#er_uem_<?= (int) $row['id'] ?>" data-selected="<?= htmlspecialchars((string) ($row['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                            <option value="">Select user</option><?php $rowPid = (int) ($row['product_id'] ?? 0);
                                                                                                    $eligibleRowUsers = $eligibleUsersByProduct[$rowPid] ?? [];
                                                                                                    foreach ($eligibleRowUsers as $u): ?><option value="<?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($row['user_email'] ?? '') === (string) ($u['email'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
                                                        </select><small id="er_uem_<?= (int) $row['id'] ?>"></small></div>
                                                </div>
                                                <div class="row g-2 mt-1">
                                                    <div class="col-md-6"><label class="form-label">Rating <span class="text-danger">*</span></label><select class="form-select" name="rating" required data-validation="required,select" data-error="#er_rat_<?= (int) $row['id'] ?>"><?php for ($r = 1; $r <= 5; $r++): ?><option value="<?= $r ?>" <?= (int) ($row['rating'] ?? 0) === $r ? 'selected' : '' ?>><?= $r ?></option><?php endfor; ?></select><small id="er_rat_<?= (int) $row['id'] ?>"></small></div>
                                                    <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status">
                                                            <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                            <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                                            <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                                        </select></div>
                                                </div>
                                                <div class="mb-2 mt-2"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" class="form-control" name="title" value="<?= htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="3" data-max="200" data-error="#er_tit_<?= (int) $row['id'] ?>"><small id="er_tit_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Review <span class="text-danger">*</span></label><textarea class="form-control" name="review" rows="3" required data-validation="required,min" data-min="5" data-error="#er_rev_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['review'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small id="er_rev_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="row g-2">
                                                    <div class="col-md-6"><label class="form-label">Created At</label><input type="datetime-local" class="form-control" name="created_at" value="<?= !empty($row['created_at']) ? date('Y-m-d\\TH:i', strtotime((string) $row['created_at'])) : '' ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Updated At</label><input type="datetime-local" class="form-control" name="updated_at" value="<?= !empty($row['updated_at']) ? date('Y-m-d\\TH:i', strtotime((string) $row['updated_at'])) : '' ?>"></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="deleteReviewModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Review</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this review permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No reviews found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination mt-3" aria-label="Reviews pagination">
            <div class="products-pagination-meta">Page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?><?= $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?><?= $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?><?= $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Review</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Product <span class="text-danger">*</span></label><select class="form-select review-product-select" id="ar_pid_sel" data-target-user="ar_uem_sel" name="product_id" required data-validation="required,select" data-error="#ar_pid">
                                <option value="">Select product</option><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                            </select><small id="ar_pid"></small></div>
                        <div class="col-md-6"><label class="form-label">User Email <span class="text-danger">*</span></label><select class="form-select review-user-select" id="ar_uem_sel" name="user_email" required data-validation="required,select" data-error="#ar_uem">
                                <option value="">Select product first</option>
                            </select><small id="ar_uem"></small></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6"><label class="form-label">Rating <span class="text-danger">*</span></label><select class="form-select" name="rating" required data-validation="required,select" data-error="#ar_rat">
                                <option value="">Select</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select><small id="ar_rat"></small></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status">
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select></div>
                    </div>
                    <div class="mb-2 mt-2"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" class="form-control" name="title" required data-validation="required,min,max" data-min="3" data-max="200" data-error="#ar_tit"><small id="ar_tit"></small></div>
                    <div class="mb-2"><label class="form-label">Review <span class="text-danger">*</span></label><textarea class="form-control" name="review" rows="3" required data-validation="required,min" data-min="5" data-error="#ar_rev"></textarea><small id="ar_rev"></small></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Created At</label><input type="datetime-local" class="form-control" name="created_at"></div>
                        <div class="col-md-6"><label class="form-label">Updated At</label><input type="datetime-local" class="form-control" name="updated_at"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Create</button></div>
            </form>
        </div>
    </div>
</div>

<script src="js/jquery.js"></script>
<script src="js/validate.js"></script>
<script>
    $(document).ready(function() {
        var eligibleUsersByProduct = <?= json_encode($eligibleUsersByProduct, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        function populateUserSelect(productSelectEl) {
            var targetId = productSelectEl.getAttribute('data-target-user');
            if (!targetId) return;
            var userSelectEl = document.getElementById(targetId);
            if (!userSelectEl) return;

            var productId = productSelectEl.value;
            var selectedEmail = userSelectEl.getAttribute('data-selected') || '';
            var users = (productId && eligibleUsersByProduct[productId]) ? eligibleUsersByProduct[productId] : [];

            userSelectEl.innerHTML = '';
            var defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = users.length > 0 ? 'Select user' : (productId ? 'No eligible users for this product' : 'Select product first');
            userSelectEl.appendChild(defaultOption);

            users.forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = u.email;
                opt.textContent = (u.fullname || '') + ' (' + u.email + ')';
                if (selectedEmail !== '' && selectedEmail === u.email) {
                    opt.selected = true;
                }
                userSelectEl.appendChild(opt);
            });

            userSelectEl.setAttribute('data-selected', '');
        }

        $('.review-product-select').each(function() {
            populateUserSelect(this);
        }).on('change', function() {
            var targetId = this.getAttribute('data-target-user');
            var userSelectEl = targetId ? document.getElementById(targetId) : null;
            if (userSelectEl) userSelectEl.setAttribute('data-selected', '');
            populateUserSelect(this);
        });

        var searchInput = $('#searchInput');
        searchInput.focus();
        var v = searchInput.val() || '';
        if (searchInput[0] && typeof searchInput[0].setSelectionRange === 'function') searchInput[0].setSelectionRange(v.length, v.length);
        var t;
        searchInput.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                var url = 'admin_reviews.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
                var st = '<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>';
                if (st) url += '&status=' + encodeURIComponent(st);
                window.location.href = url;
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
