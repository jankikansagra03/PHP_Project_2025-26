<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_wishlist.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $userEmail = trim($_POST['user_email'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($userEmail === '' || $productId <= 0) {
            setcookie('error', 'Please enter valid wishlist details.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'INSERT INTO wishlist (user_email, product_id) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'si', $userEmail, $productId);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Wishlist item created.', time() + 5, '/');
        else setcookie('error', 'Failed to create wishlist item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($id <= 0 || $userEmail === '' || $productId <= 0) {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'UPDATE wishlist SET user_email=?, product_id=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sii', $userEmail, $productId, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Wishlist item updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update wishlist item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM wishlist WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Wishlist item deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete wishlist item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE w.user_email LIKE ? OR p.name LIKE ?' : '';
$like = '%' . $search . '%';

$countSql = 'SELECT COUNT(*) AS total FROM wishlist w LEFT JOIN products p ON p.id = w.product_id' . $where;
$countStmt = mysqli_prepare($con, $countSql);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ss', $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$products = [];
$productsRes = mysqli_query($con, 'SELECT id, name FROM products ORDER BY name ASC');
if ($productsRes) {
    while ($p = mysqli_fetch_assoc($productsRes)) {
        $products[] = $p;
    }
}

$listSql = 'SELECT w.*, p.name AS product_name, p.image AS product_image FROM wishlist w LEFT JOIN products p ON p.id = w.product_id' . $where . ' ORDER BY w.id DESC LIMIT ?, ?';
$listStmt = mysqli_prepare($con, $listSql);
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Wishlist - JK Store';
$admin_active = 'wishlist';
$admin_page_title = 'Wishlist';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Wishlist Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addWishlistModal"><i class="fas fa-plus me-1"></i>Add Wishlist Item</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by user email or product..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>User Email</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2"><?php if (!empty($row['product_image'])): ?><img src="<?= htmlspecialchars((string) $row['product_image'], ENT_QUOTES, 'UTF-8') ?>" class="small-preview border" alt="product"><?php endif; ?><div>
                                            <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['product_name'] ?? ('#' . ($row['product_id'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-muted small">Product ID: <?= (int) ($row['product_id'] ?? 0) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><small><?= htmlspecialchars((string) ($row['added_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td>
                                    <div class="products-actions d-flex gap-1"><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editWishlistModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteWishlistModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editWishlistModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Wishlist Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="mb-3"><label class="form-label">User Email</label><input type="email" class="form-control" name="user_email" value="<?= htmlspecialchars((string) ($row['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                                <div class="mb-1"><label class="form-label">Product</label><select class="form-select" name="product_id" required>
                                                        <option value="">Select product</option><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) ($row['product_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                                                    </select></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="deleteWishlistModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Wishlist Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this wishlist item permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No wishlist items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Wishlist pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addWishlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Wishlist Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-3"><label class="form-label">User Email</label><input type="email" class="form-control" name="user_email" required></div>
                    <div class="mb-1"><label class="form-label">Product</label><select class="form-select" name="product_id" required>
                            <option value="">Select product</option><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                        </select></div>
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
                window.location.href = 'admin_wishlist.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
