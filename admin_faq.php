<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_faq.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $status = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';

        $stmt = mysqli_prepare($con, 'INSERT INTO faq (question, answer, category, display_order, status) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssis', $question, $answer, $category, $displayOrder, $status);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'FAQ created successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to create FAQ.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $status = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';

        $stmt = mysqli_prepare($con, 'UPDATE faq SET question=?, answer=?, category=?, display_order=?, status=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssisi', $question, $answer, $category, $displayOrder, $status, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'FAQ updated successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to update FAQ.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM faq WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'FAQ deleted successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to delete FAQ.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE question LIKE ? OR answer LIKE ? OR category LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM faq' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'sss', $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM faq' . $where . ' ORDER BY display_order ASC, id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'sssii', $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin FAQ - JK Store';
$admin_active = 'faq';
$admin_page_title = 'FAQ';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">FAQ Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFaqModal"><i class="fas fa-plus me-1"></i>Add FAQ</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search question, answer, category..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Question</th>
                        <th>Category</th>
                        <th>Order</th>
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
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($row['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars(mb_substr((string) ($row['answer'] ?? ''), 0, 120), ENT_QUOTES, 'UTF-8') ?>...</div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['category'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) ($row['display_order'] ?? 0) ?></td>
                                <td><span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <div class="products-actions d-flex gap-1">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewFaqModal<?= (int) $row['id'] ?>" title="View" aria-label="View"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editFaqModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteFaqModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="viewFaqModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">FAQ #<?= (int) $row['id'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Question:</strong> <?= htmlspecialchars((string) ($row['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p><strong>Category:</strong> <?= htmlspecialchars((string) ($row['category'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p class="mb-0"><strong>Answer:</strong><br><?= nl2br(htmlspecialchars((string) ($row['answer'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="editFaqModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="mb-3"><label class="form-label">Question</label><input type="text" class="form-control" name="question" value="<?= htmlspecialchars((string) ($row['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="5" data-max="500" data-error="#edit_faq_q_<?= (int) $row['id'] ?>"><small id="edit_faq_q_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-3"><label class="form-label">Answer</label><textarea class="form-control" name="answer" rows="4" required data-validation="required,max" data-max="5000" data-error="#edit_faq_a_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['answer'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small id="edit_faq_a_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="row g-2">
                                                    <div class="col-md-6"><label class="form-label">Category</label><input type="text" class="form-control" name="category" value="<?= htmlspecialchars((string) ($row['category'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?>" data-validation="max" data-max="100" data-error="#edit_faq_c_<?= (int) $row['id'] ?>"><small id="edit_faq_c_<?= (int) $row['id'] ?>"></small></div>
                                                    <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" class="form-control" name="display_order" min="0" value="<?= (int) ($row['display_order'] ?? 0) ?>" data-validation="number" data-error="#edit_faq_o_<?= (int) $row['id'] ?>"><small id="edit_faq_o_<?= (int) $row['id'] ?>"></small></div>
                                                </div>
                                                <div class="mt-3"><label class="form-label">Status</label><select class="form-select" name="status" data-validation="required,select" data-error="#edit_faq_s_<?= (int) $row['id'] ?>">
                                                        <option value="Active" <?= $isActive ? 'selected' : '' ?>>Active</option>
                                                        <option value="Inactive" <?= !$isActive ? 'selected' : '' ?>>Inactive</option>
                                                    </select><small id="edit_faq_s_<?= (int) $row['id'] ?>"></small></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="deleteFaqModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this FAQ permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No FAQs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="FAQ pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?>
                <li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-3"><label class="form-label">Question</label><input type="text" class="form-control" name="question" required data-validation="required,min,max" data-min="5" data-max="500" data-error="#add_faq_q"><small id="add_faq_q"></small></div>
                    <div class="mb-3"><label class="form-label">Answer</label><textarea class="form-control" name="answer" rows="4" required data-validation="required,max" data-max="5000" data-error="#add_faq_a"></textarea><small id="add_faq_a"></small></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Category</label><input type="text" class="form-control" name="category" value="General" data-validation="max" data-max="100" data-error="#add_faq_c"><small id="add_faq_c"></small></div>
                        <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" class="form-control" name="display_order" min="0" value="0" data-validation="number" data-error="#add_faq_o"><small id="add_faq_o"></small></div>
                    </div>
                    <div class="mt-3"><label class="form-label">Status</label><select class="form-select" name="status" data-validation="required,select" data-error="#add_faq_s">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select><small id="add_faq_s"></small></div>
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
                window.location.href = 'admin_faq.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
