<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_categories.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $name   = trim($_POST['category_name'] ?? '');
        $status = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $stmt   = mysqli_prepare($con, 'INSERT INTO categories (category_name, status) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $name, $status);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'Category created successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to create category.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id     = (int) ($_POST['id'] ?? 0);
        $name   = trim($_POST['category_name'] ?? '');
        $status = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $stmt   = mysqli_prepare($con, 'UPDATE categories SET category_name=?, status=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'Category updated successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to update category.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM categories WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'Category deleted successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to delete category.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'change_status') {
        $id        = (int) ($_POST['id'] ?? 0);
        $newStatus = ($_POST['new_status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $stmt      = mysqli_prepare($con, 'UPDATE categories SET status=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'Category status updated.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to update status.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search    = trim($_GET['search'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$perPage   = 7;
$where     = $search !== '' ? ' WHERE category_name LIKE ?' : '';
$like      = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM categories' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 's', $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM categories' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') {
    mysqli_stmt_bind_param($listStmt, 'sii', $like, $offset, $perPage);
} else {
    mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
}
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title            = 'Admin Categories - JK Store';
$admin_active     = 'categories';
$admin_page_title = 'Categories';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Category Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-1"></i>Add Category
        </button>
    </div>

    <div class="products-body">

        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search"
                placeholder="Search by category name..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php $isActive = strtolower($row['status']) === 'active'; ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="products-actions d-flex gap-1">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?= $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#statusCategoryModal<?= $row['id'] ?>" title="<?= $isActive ? 'Deactivate' : 'Activate' ?>" aria-label="Toggle Status"><i class="fas <?= $isActive ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editCategoryModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Category Name</label>
                                                    <input type="text" class="form-control" name="category_name"
                                                        value="<?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        required data-validation="required,min,max" data-min="2" data-max="150"
                                                        data-error="#edit_name_error_<?= $row['id'] ?>">
                                                    <small id="edit_name_error_<?= $row['id'] ?>"></small>
                                                </div>
                                                <div class="mb-1">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status" data-validation="required,select" data-error="#edit_status_error_<?= $row['id'] ?>">
                                                        <option value="Active" <?= $isActive ? 'selected' : '' ?>>Active</option>
                                                        <option value="Inactive" <?= !$isActive ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                    <small id="edit_status_error_<?= $row['id'] ?>"></small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteCategoryModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete category <strong><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></strong> permanently?</p>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Modal -->
                            <div class="modal fade" id="statusCategoryModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <?php $nextStatus = $isActive ? 'Inactive' : 'Active'; ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Change Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Set <strong><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></strong> as <strong><?= $nextStatus ?></strong>?</p>
                                                <input type="hidden" name="action" value="change_status">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="new_status" value="<?= $nextStatus ?>">
                                                <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Categories pagination">
            <div class="products-pagination-meta">
                Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total
            </div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">
                        <i class="fas fa-chevron-left me-1 small"></i>Prev
                    </a>
                </li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">
                        Next<i class="fas fa-chevron-right ms-1 small"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="category_name" required
                            data-validation="required,min,max" data-min="2" data-max="150"
                            data-error="#add_name_error">
                        <small id="add_name_error"></small>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" data-validation="required,select" data-error="#add_status_error">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <small id="add_status_error"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
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
        var v = searchInput.val();
        if (searchInput[0]) searchInput[0].setSelectionRange(v.length, v.length);
        var t;
        searchInput.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                window.location.href = 'admin_categories.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
