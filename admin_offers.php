<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_offers.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create' || $action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discountType = (($_POST['discount_type'] ?? 'percent') === 'fixed') ? 'fixed' : 'percent';
        $discountValue = (float) ($_POST['discount_value'] ?? 0);
        $minOrderAmount = ($_POST['min_order_amount'] ?? '') === '' ? null : (float) $_POST['min_order_amount'];
        $maxApplicableAmount = ($_POST['max_applicable_amount'] ?? '') === '' ? null : (float) $_POST['max_applicable_amount'];
        $maxDiscountAmount = ($_POST['max_discount_amount'] ?? '') === '' ? null : (float) $_POST['max_discount_amount'];
        $validFrom = trim($_POST['valid_from'] ?? '');
        $validTo = trim($_POST['valid_to'] ?? '');
        $usageLimit = ($_POST['usage_limit'] ?? '') === '' ? null : (int) $_POST['usage_limit'];
        $perUserLimit = ($_POST['per_user_limit'] ?? '') === '' ? null : (int) $_POST['per_user_limit'];
        $status = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';
        $description = trim($_POST['description'] ?? '');

        if ($action === 'create') {
            $stmt = mysqli_prepare($con, 'INSERT INTO offers (code, discount_type, discount_value, min_order_amount, max_applicable_amount, max_discount_amount, valid_from, valid_to, usage_limit, per_user_limit, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssddddssiiss', $code, $discountType, $discountValue, $minOrderAmount, $maxApplicableAmount, $maxDiscountAmount, $validFrom, $validTo, $usageLimit, $perUserLimit, $status, $description);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Offer created successfully.', time() + 5, '/');
            else setcookie('error', 'Failed to create offer.', time() + 5, '/');
        } else {
            $stmt = mysqli_prepare($con, 'UPDATE offers SET code=?, discount_type=?, discount_value=?, min_order_amount=?, max_applicable_amount=?, max_discount_amount=?, valid_from=?, valid_to=?, usage_limit=?, per_user_limit=?, status=?, description=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'ssddddssiissi', $code, $discountType, $discountValue, $minOrderAmount, $maxApplicableAmount, $maxDiscountAmount, $validFrom, $validTo, $usageLimit, $perUserLimit, $status, $description, $id);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Offer updated successfully.', time() + 5, '/');
            else setcookie('error', 'Failed to update offer.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM offers WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Offer deleted successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to delete offer.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE code LIKE ? OR description LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM offers' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ss', $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM offers' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Offers - JK Store';
$admin_active = 'offers';
$admin_page_title = 'Offers';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Offers Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOfferModal"><i class="fas fa-plus me-1"></i>Add Offer</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by code, description..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Validity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php $isActive = strtolower((string) ($row['status'] ?? '')) === 'active'; ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><span class="fw-semibold"><?= htmlspecialchars((string) ($row['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars((string) ($row['discount_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (float) ($row['discount_value'] ?? 0) ?></td>
                                <td><small><?= htmlspecialchars((string) ($row['valid_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?> to <?= htmlspecialchars((string) ($row['valid_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td><span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <div class="products-actions d-flex gap-1">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editOfferModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOfferModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editOfferModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Offer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="row g-2">
                                                    <div class="col-md-6"><label class="form-label">Code</label><input type="text" class="form-control" name="code" value="<?= htmlspecialchars((string) ($row['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                                    <div class="col-md-6"><label class="form-label">Discount Type</label><select class="form-select" name="discount_type">
                                                            <option value="percent" <?= ($row['discount_type'] ?? '') === 'percent' ? 'selected' : '' ?>>Percent</option>
                                                            <option value="fixed" <?= ($row['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                                                        </select></div>
                                                </div>
                                                <div class="row g-2 mt-1">
                                                    <div class="col-md-4"><label class="form-label">Discount Value</label><input type="number" step="0.01" class="form-control" name="discount_value" value="<?= (float) ($row['discount_value'] ?? 0) ?>"></div>
                                                    <div class="col-md-4"><label class="form-label">Min Order</label><input type="number" step="0.01" class="form-control" name="min_order_amount" value="<?= htmlspecialchars((string) ($row['min_order_amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    <div class="col-md-4"><label class="form-label">Max Discount</label><input type="number" step="0.01" class="form-control" name="max_discount_amount" value="<?= htmlspecialchars((string) ($row['max_discount_amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                </div>
                                                <div class="row g-2 mt-1">
                                                    <div class="col-md-6"><label class="form-label">Valid From</label><input type="datetime-local" class="form-control" name="valid_from" value="<?= !empty($row['valid_from']) ? date('Y-m-d\\TH:i', strtotime((string) $row['valid_from'])) : '' ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Valid To</label><input type="datetime-local" class="form-control" name="valid_to" value="<?= !empty($row['valid_to']) ? date('Y-m-d\\TH:i', strtotime((string) $row['valid_to'])) : '' ?>"></div>
                                                </div>
                                                <div class="row g-2 mt-1">
                                                    <div class="col-md-4"><label class="form-label">Usage Limit</label><input type="number" class="form-control" name="usage_limit" value="<?= htmlspecialchars((string) ($row['usage_limit'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    <div class="col-md-4"><label class="form-label">Per User Limit</label><input type="number" class="form-control" name="per_user_limit" value="<?= htmlspecialchars((string) ($row['per_user_limit'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status">
                                                            <option value="Active" <?= $isActive ? 'selected' : '' ?>>Active</option>
                                                            <option value="Inactive" <?= !$isActive ? 'selected' : '' ?>>Inactive</option>
                                                        </select></div>
                                                </div>
                                                <div class="mt-2"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?= htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="deleteOfferModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Offer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete offer <strong><?= htmlspecialchars((string) ($row['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No offers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Offers pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?>
                <li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addOfferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Offer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Code</label><input type="text" class="form-control" name="code" required></div>
                        <div class="col-md-6"><label class="form-label">Discount Type</label><select class="form-select" name="discount_type">
                                <option value="percent" selected>Percent</option>
                                <option value="fixed">Fixed</option>
                            </select></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4"><label class="form-label">Discount Value</label><input type="number" step="0.01" class="form-control" name="discount_value" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Min Order</label><input type="number" step="0.01" class="form-control" name="min_order_amount"></div>
                        <div class="col-md-4"><label class="form-label">Max Discount</label><input type="number" step="0.01" class="form-control" name="max_discount_amount"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6"><label class="form-label">Valid From</label><input type="datetime-local" class="form-control" name="valid_from"></div>
                        <div class="col-md-6"><label class="form-label">Valid To</label><input type="datetime-local" class="form-control" name="valid_to"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4"><label class="form-label">Usage Limit</label><input type="number" class="form-control" name="usage_limit"></div>
                        <div class="col-md-4"><label class="form-label">Per User Limit</label><input type="number" class="form-control" name="per_user_limit"></div>
                        <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select></div>
                    </div>
                    <div class="mt-2"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
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
        var searchInput = $('#searchInput');
        searchInput.focus();
        var v = searchInput.val() || '';
        if (searchInput[0] && typeof searchInput[0].setSelectionRange === 'function') searchInput[0].setSelectionRange(v.length, v.length);
        var t;
        searchInput.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                window.location.href = 'admin_offers.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
