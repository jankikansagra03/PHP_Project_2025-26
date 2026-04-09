<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action       = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage   = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl  = 'admin_wishlist.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $userEmail = trim($_POST['user_email'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($userEmail === '' || $productId <= 0) {
            setcookie('error', 'Please provide valid wishlist details.', time() + 5, '/');
        } else {
            $stmt = mysqli_prepare($con, 'INSERT INTO wishlist (user_email, product_id) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'si', $userEmail, $productId);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Wishlist item added.', time() + 5, '/');
            else setcookie('error', 'Failed to add wishlist item.', time() + 5, '/');
            mysqli_stmt_close($stmt);
        }
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id        = (int) ($_POST['id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($id <= 0 || $userEmail === '' || $productId <= 0) {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
        } else {
            $stmt = mysqli_prepare($con, 'UPDATE wishlist SET user_email=?, product_id=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'sii', $userEmail, $productId, $id);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Wishlist item updated.', time() + 5, '/');
            else setcookie('error', 'Failed to update wishlist item.', time() + 5, '/');
            mysqli_stmt_close($stmt);
        }
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM wishlist WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Wishlist item deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete wishlist item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$like    = '%' . $search . '%';

$havingSearch = $search !== '' ? " HAVING r.email LIKE ? OR r.fullname LIKE ?" : '';
$countSql     = "SELECT COUNT(*) AS total FROM (
    SELECT r.email FROM registration r INNER JOIN wishlist w ON w.user_email = r.email
    GROUP BY r.email $havingSearch) AS u";
$countStmt = mysqli_prepare($con, $countSql);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ss', $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$usersSql = "SELECT r.email, r.fullname, r.profile_picture, COUNT(w.id) AS item_count
             FROM registration r
             INNER JOIN wishlist w ON w.user_email = r.email
             GROUP BY r.email, r.fullname, r.profile_picture
             $havingSearch
             ORDER BY MAX(w.added_at) DESC
             LIMIT ?, ?";
$usersStmt = mysqli_prepare($con, $usersSql);
if ($search !== '') mysqli_stmt_bind_param($usersStmt, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($usersStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($usersStmt);
$users = mysqli_stmt_get_result($usersStmt)->fetch_all(MYSQLI_ASSOC);

$userWishItems = [];
foreach ($users as $u) {
    $em       = $u['email'];
    $iStmt    = mysqli_prepare($con, "SELECT w.*, p.name AS product_name, p.image AS product_image, p.price FROM wishlist w LEFT JOIN products p ON p.id = w.product_id WHERE w.user_email = ? ORDER BY w.id DESC");
    mysqli_stmt_bind_param($iStmt, 's', $em);
    mysqli_stmt_execute($iStmt);
    $userWishItems[$em] = mysqli_stmt_get_result($iStmt)->fetch_all(MYSQLI_ASSOC);
    mysqli_stmt_close($iStmt);
}

$products = [];
$prodRes  = mysqli_query($con, 'SELECT id, name, price FROM products ORDER BY name ASC');
if ($prodRes) while ($p = mysqli_fetch_assoc($prodRes)) $products[] = $p;

$allUsers = [];
$usersFkRes = mysqli_query($con, 'SELECT email, fullname FROM registration ORDER BY fullname ASC');
if ($usersFkRes) while ($u = mysqli_fetch_assoc($usersFkRes)) $allUsers[] = $u;

$title            = 'Admin Wishlist - JK Store';
$admin_active     = 'wishlist';
$admin_page_title = 'Wishlist Management';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="mb-0 fw-bold">Wishlist Management</h5>
            <small class="text-muted"><?= (int) $total ?> users with wishlist items</small>
        </div>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search"
                placeholder="Search by user email or name..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <?php if (empty($users)): ?>
            <div class="text-center text-muted py-5">No wishlist items found.</div>
        <?php else: ?>
            <div class="accordion" id="wishlistAccordion">
                <?php foreach ($users as $i => $user):
                    $em    = $user['email'];
                    $accId = 'wlUser_' . md5($em);
                    $avatar = !empty($user['profile_picture']) ? 'images/profile_pictures/' . $user['profile_picture'] : 'images/profile_pictures/default.png';
                    $items = $userWishItems[$em] ?? [];
                ?>
                    <div class="accordion-item border mb-2 rounded overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> py-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#wlbody_<?= $accId ?>">
                                <div class="d-flex align-items-center gap-3 flex-wrap w-100 pe-2">
                                    <img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="avatar" class="avatar-38">
                                    <div class="grow">
                                        <div class="fw-bold"><?= htmlspecialchars((string) $user['fullname'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <span class="badge text-bg-danger ms-auto shrink-0">
                                        <i class="fas fa-heart me-1"></i><?= (int) $user['item_count'] ?> saved
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="wlbody_<?= $accId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                            data-bs-parent="#wishlistAccordion">
                            <div class="accordion-body p-0">
                                <div class="d-flex justify-content-end p-2 bg-light border-bottom">
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#addWlModal_<?= $accId ?>">
                                        <i class="fas fa-plus me-1"></i>Add Item
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Added At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($items)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">No items.</td>
                                                </tr>
                                                <?php else: foreach ($items as $item): ?>
                                                    <tr>
                                                        <td><?= (int) $item['id'] ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <?php if (!empty($item['product_image'])): ?>
                                                                    <img src="<?= htmlspecialchars((string) $item['product_image'], ENT_QUOTES, 'UTF-8') ?>" class="thumb-40" alt="">
                                                                <?php endif; ?>
                                                                <div>
                                                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($item['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
                                                                    <div class="text-muted small">ID: <?= (int) $item['product_id'] ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>&#8377;<?= number_format((float) ($item['price'] ?? 0), 2) ?></td>
                                                        <td><small><?= htmlspecialchars((string) ($item['added_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                                    data-bs-target="#editWlItem_<?= (int) $item['id'] ?>"><i class="fas fa-pen"></i></button>
                                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                                    data-bs-target="#deleteWlItem_<?= (int) $item['id'] ?>"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editWlItem_<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="post" novalidate>
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Edit Wishlist Item</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action" value="update">
                                                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                                        <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">User Email <span class="text-danger">*</span></label>
                                                                            <select class="form-select" name="user_email" required data-validation="required,select" data-error="#ewl_email_<?= (int) $item['id'] ?>">
                                                                                <option value="">Select user</option><?php foreach ($allUsers as $u): ?><option value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($item['user_email'] ?? '') === (string) $u['email'] ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
                                                                            </select>
                                                                            <small id="ewl_email_<?= (int) $item['id'] ?>"></small>
                                                                        </div>
                                                                        <div class="mb-1">
                                                                            <label class="form-label">Product <span class="text-danger">*</span></label>
                                                                            <select class="form-select" name="product_id" required
                                                                                data-validation="required,select" data-error="#ewl_prod_<?= (int) $item['id'] ?>">
                                                                                <option value="">Select product</option>
                                                                                <?php foreach ($products as $pr): ?>
                                                                                    <option value="<?= (int) $pr['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $pr['id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars((string) $pr['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                                        (&#8377;<?= number_format((float) $pr['price'], 2) ?>)
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                            <small id="ewl_prod_<?= (int) $item['id'] ?>"></small>
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
                                                    <div class="modal fade" id="deleteWlItem_<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="post" novalidate>
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Remove from Wishlist</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>Remove <strong><?= htmlspecialchars((string) ($item['product_name'] ?? 'this item'), ENT_QUOTES, 'UTF-8') ?></strong> from wishlist?</p>
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                                        <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-danger">Remove</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php endforeach;
                                            endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Wishlist Item Modal -->
                    <div class="modal fade" id="addWlModal_<?= $accId ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Add to Wishlist — <?= htmlspecialchars((string) $user['fullname'], ENT_QUOTES, 'UTF-8') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="create">
                                        <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <div class="mb-3">
                                            <label class="form-label">User Email <span class="text-danger">*</span></label>
                                            <select class="form-select" name="user_email" required data-validation="required,select" data-error="#awl_email_<?= $accId ?>">
                                                <option value="">Select user</option><?php foreach ($allUsers as $u): ?><option value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $em === (string) $u['email'] ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
                                            </select>
                                            <small id="awl_email_<?= $accId ?>"></small>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label">Product <span class="text-danger">*</span></label>
                                            <select class="form-select" name="product_id" required
                                                data-validation="required,select" data-error="#awl_prod_<?= $accId ?>">
                                                <option value="">Select product</option>
                                                <?php foreach ($products as $pr): ?>
                                                    <option value="<?= (int) $pr['id'] ?>">
                                                        <?= htmlspecialchars((string) $pr['name'], ENT_QUOTES, 'UTF-8') ?>
                                                        (&#8377;<?= number_format((float) $pr['price'], 2) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small id="awl_prod_<?= $accId ?>"></small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Add to Wishlist</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

            <nav class="products-pagination mt-4" aria-label="Wishlist users pagination">
                <div class="products-pagination-meta">Page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> users</div>
                <ul class="products-pagination-list">
                    <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li>
                    <?php endfor; ?>
                    <li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script src="js/jquery.js"></script>
<script src="js/validate.js"></script>
<script>
    $(document).ready(function() {
        var s = $('#searchInput');
        s.focus();
        var v = s.val() || '';
        if (s[0] && s[0].setSelectionRange) s[0].setSelectionRange(v.length, v.length);
        var t;
        s.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                window.location.href = 'admin_wishlist.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($usersStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
