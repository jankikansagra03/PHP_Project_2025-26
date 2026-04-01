<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_offer_usage.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $offerId = (int) ($_POST['offer_id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $orderId = ($_POST['order_id'] ?? '') === '' ? null : (int) $_POST['order_id'];
        if ($offerId <= 0 || $userEmail === '') {
            setcookie('error', 'Please enter valid offer usage details.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'INSERT INTO offer_usage (offer_id, user_email, order_id) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'isi', $offerId, $userEmail, $orderId);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Offer usage created.', time() + 5, '/');
        else setcookie('error', 'Failed to create offer usage.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $offerId = (int) ($_POST['offer_id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $orderId = ($_POST['order_id'] ?? '') === '' ? null : (int) $_POST['order_id'];
        $usedAt = trim($_POST['used_at'] ?? '');
        if ($id <= 0 || $offerId <= 0 || $userEmail === '') {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'UPDATE offer_usage SET offer_id=?, user_email=?, order_id=?, used_at=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'isisi', $offerId, $userEmail, $orderId, $usedAt, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Offer usage updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update offer usage.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM offer_usage WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Offer usage deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete offer usage.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE ou.user_email LIKE ? OR o.code LIKE ? OR ou.order_id LIKE ?' : '';
$like = '%' . $search . '%';

$countSql = 'SELECT COUNT(*) AS total FROM offer_usage ou LEFT JOIN offers o ON o.id = ou.offer_id' . $where;
$countStmt = mysqli_prepare($con, $countSql);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'sss', $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$offers = [];
$offersRes = mysqli_query($con, 'SELECT id, code FROM offers ORDER BY code ASC');
if ($offersRes) {
    while ($o = mysqli_fetch_assoc($offersRes)) {
        $offers[] = $o;
    }
}

$listSql = 'SELECT ou.*, o.code AS offer_code FROM offer_usage ou LEFT JOIN offers o ON o.id = ou.offer_id' . $where . ' ORDER BY ou.id DESC LIMIT ?, ?';
$listStmt = mysqli_prepare($con, $listSql);
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'sssii', $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Offer Usage - JK Store';
$admin_active = 'offer_usage';
$admin_page_title = 'Offer Usage';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Offer Usage</h5><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOfferUsageModal"><i class="fas fa-plus me-1"></i>Add Usage</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by user email, code, order id..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Offer</th>
                        <th>User Email</th>
                        <th>Order ID</th>
                        <th>Used At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php if ($result && mysqli_num_rows($result) > 0): ?><?php while ($row = mysqli_fetch_assoc($result)): ?><tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><span class="fw-semibold"><?= htmlspecialchars((string) ($row['offer_code'] ?? ('#' . ($row['offer_id'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) ($row['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($row['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><small><?= htmlspecialchars((string) ($row['used_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td>
                            <div class="products-actions d-flex gap-1"><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editOfferUsageModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOfferUsageModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                        </td>
                    </tr>
                    <div class="modal fade" id="editOfferUsageModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Offer Usage</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <div class="mb-2"><label class="form-label">Offer</label><select class="form-select" name="offer_id" required>
                                                <option value="">Select offer</option><?php foreach ($offers as $o): ?><option value="<?= (int) $o['id'] ?>" <?= (int) ($row['offer_id'] ?? 0) === (int) $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $o['code'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="mb-2"><label class="form-label">User Email</label><input type="email" class="form-control" name="user_email" value="<?= htmlspecialchars((string) ($row['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                        <div class="mb-2"><label class="form-label">Order ID</label><input type="number" class="form-control" name="order_id" value="<?= htmlspecialchars((string) ($row['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                        <div class="mb-1"><label class="form-label">Used At</label><input type="datetime-local" class="form-control" name="used_at" value="<?= !empty($row['used_at']) ? date('Y-m-d\\TH:i', strtotime((string) $row['used_at'])) : '' ?>"></div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="deleteOfferUsageModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Offer Usage</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Delete this usage record permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                </form>
                            </div>
                        </div>
                    </div><?php endwhile; ?><?php else: ?><tr>
                        <td colspan="6" class="text-center text-muted py-4">No offer usage records found.</td>
                    </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <nav class="products-pagination" aria-label="Offer usage pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addOfferUsageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Offer Usage</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-2"><label class="form-label">Offer</label><select class="form-select" name="offer_id" required>
                            <option value="">Select offer</option><?php foreach ($offers as $o): ?><option value="<?= (int) $o['id'] ?>"><?= htmlspecialchars((string) $o['code'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">User Email</label><input type="email" class="form-control" name="user_email" required></div>
                    <div class="mb-1"><label class="form-label">Order ID</label><input type="number" class="form-control" name="order_id"></div>
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
                window.location.href = 'admin_offer_usage.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
