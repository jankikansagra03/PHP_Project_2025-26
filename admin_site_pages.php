<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $slug = trim($_POST['current_slug'] ?? '');
    $redirectUrl = 'admin_site_pages.php' . ($slug !== '' ? '?slug=' . urlencode($slug) : '');

    if ($action === 'create') {
        $pageSlug = trim($_POST['page_slug'] ?? '');
        $pageTitle = trim($_POST['page_title'] ?? '');
        $pageContent = trim($_POST['page_content'] ?? '');
        $status = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';
        $updatedBy = $_SESSION['email'] ?? 'admin';

        $stmt = mysqli_prepare($con, 'INSERT INTO site_pages (page_slug, page_title, page_content, status, updated_by) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssss', $pageSlug, $pageTitle, $pageContent, $status, $updatedBy);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Site page created.', time() + 5, '/');
        else setcookie('error', 'Failed to create page.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $pageSlug = trim($_POST['page_slug'] ?? '');
        $pageTitle = trim($_POST['page_title'] ?? '');
        $pageContent = trim($_POST['page_content'] ?? '');
        $status = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';
        $updatedBy = $_SESSION['email'] ?? 'admin';

        $stmt = mysqli_prepare($con, 'UPDATE site_pages SET page_slug=?, page_title=?, page_content=?, status=?, updated_by=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssssi', $pageSlug, $pageTitle, $pageContent, $status, $updatedBy, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Site page updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update page.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM site_pages WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Site page deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete page.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$slugFilter = trim($_GET['slug'] ?? '');
$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$whereParts = [];
$params = [];
$types = '';
if ($slugFilter !== '') {
    $whereParts[] = 'page_slug = ?';
    $types .= 's';
    $params[] = $slugFilter;
}
if ($search !== '') {
    $whereParts[] = '(page_slug LIKE ? OR page_title LIKE ? OR page_content LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$whereSql = !empty($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM site_pages' . $whereSql);
if ($types !== '') mysqli_stmt_bind_param($countStmt, $types, ...$params);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT * FROM site_pages' . $whereSql . ' ORDER BY id DESC LIMIT ?, ?';
$listStmt = mysqli_prepare($con, $listSql);
if ($types !== '') {
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$offset, $perPage]);
    mysqli_stmt_bind_param($listStmt, $bindTypes, ...$bindParams);
} else {
    mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
}
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Site Pages - JK Store';
$admin_active = 'site_pages';
$admin_page_title = 'Site Pages';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Site Pages</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPageModal"><i class="fas fa-plus me-1"></i>Add Page</button>
    </div>
    <div class="products-body">
        <form method="get" class="row g-2 mb-3" novalidate>
            <div class="col-md-8"><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by slug, title, content..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-4"><input type="text" class="form-control" name="slug" placeholder="Filter by slug" value="<?= htmlspecialchars($slugFilter, ENT_QUOTES, 'UTF-8') ?>"></div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Slug</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php $isActive = strtolower((string) ($row['status'] ?? '')) === 'active'; ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><code><?= htmlspecialchars((string) ($row['page_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td><?= htmlspecialchars((string) ($row['page_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span></td>
                                <td><small><?= htmlspecialchars((string) ($row['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td>
                                    <div class="products-actions d-flex gap-1">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewPageModal<?= (int) $row['id'] ?>" title="View" aria-label="View"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPageModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deletePageModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <div class="modal fade" id="viewPageModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars((string) ($row['page_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body modal-body-scroll">
                                            <p><strong>Slug:</strong> <?= htmlspecialchars((string) ($row['page_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <hr>
                                            <div><?= nl2br(htmlspecialchars((string) ($row['page_content'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="editPageModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Site Page</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="current_slug" value="<?= htmlspecialchars($slugFilter, ENT_QUOTES, 'UTF-8') ?>">
                                                <div class="mb-2"><label class="form-label">Slug <span class="text-danger">*</span></label><input type="text" class="form-control" name="page_slug" value="<?= htmlspecialchars((string) ($row['page_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="50" data-error="#ep_slug_<?= (int) $row['id'] ?>"><small id="ep_slug_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" class="form-control" name="page_title" value="<?= htmlspecialchars((string) ($row['page_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="200" data-error="#ep_title_<?= (int) $row['id'] ?>"><small id="ep_title_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Content <span class="text-danger">*</span></label><textarea class="form-control" name="page_content" rows="10" required data-validation="required,min" data-min="10" data-error="#ep_content_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['page_content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small id="ep_content_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Status</label><select class="form-select" name="status">
                                                        <option value="Active" <?= $isActive ? 'selected' : '' ?>>Active</option>
                                                        <option value="Inactive" <?= !$isActive ? 'selected' : '' ?>>Inactive</option>
                                                    </select></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="deletePageModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Site Page</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete page <strong><?= htmlspecialchars((string) ($row['page_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="current_slug" value="<?= htmlspecialchars($slugFilter, ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No pages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Site pages pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?><?= $slugFilter !== '' ? '&slug=' . urlencode($slugFilter) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?><?= $slugFilter !== '' ? '&slug=' . urlencode($slugFilter) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?><?= $slugFilter !== '' ? '&slug=' . urlencode($slugFilter) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addPageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Site Page</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="current_slug" value="<?= htmlspecialchars($slugFilter, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-2"><label class="form-label">Slug <span class="text-danger">*</span></label><input type="text" class="form-control" name="page_slug" required data-validation="required,min,max" data-min="2" data-max="50" data-error="#ap_slug"><small id="ap_slug"></small></div>
                    <div class="mb-2"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" class="form-control" name="page_title" required data-validation="required,min,max" data-min="2" data-max="200" data-error="#ap_title"><small id="ap_title"></small></div>
                    <div class="mb-2"><label class="form-label">Content <span class="text-danger">*</span></label><textarea class="form-control" name="page_content" rows="10" required data-validation="required,min" data-min="10" data-error="#ap_content"></textarea><small id="ap_content"></small></div>
                    <div class="mb-2"><label class="form-label">Status</label><select class="form-select" name="status">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
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
                window.location.href = 'admin_site_pages.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '') + '<?= $slugFilter !== '' ? '&slug=' . urlencode($slugFilter) : '' ?>';
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
