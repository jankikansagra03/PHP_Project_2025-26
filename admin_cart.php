<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action       = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage   = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl  = 'admin_cart.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $userEmail = trim($_POST['user_email'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));
        if ($userEmail === '' || $productId <= 0) {
            setcookie('error', 'Please provide valid cart details.', time() + 5, '/');
        } else {
            $stmt = mysqli_prepare($con, 'INSERT INTO cart (user_email, product_id, quantity) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sii', $userEmail, $productId, $quantity);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Cart item added.', time() + 5, '/');
            else setcookie('error', 'Failed to add cart item.', time() + 5, '/');
            mysqli_stmt_close($stmt);
        }
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id        = (int) ($_POST['id'] ?? 0);
        $userEmail = trim($_POST['user_email'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));
        if ($id <= 0 || $userEmail === '' || $productId <= 0) {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
        } else {
            $stmt = mysqli_prepare($con, 'UPDATE cart SET user_email=?, product_id=?, quantity=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'siii', $userEmail, $productId, $quantity, $id);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Cart item updated.', time() + 5, '/');
            else setcookie('error', 'Failed to update cart item.', time() + 5, '/');
            mysqli_stmt_close($stmt);
        }
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM cart WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Cart item deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete cart item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$like    = '%' . $search . '%';

// Count distinct users with cart items
$whereUsers = $search !== '' ? " HAVING r.email LIKE ? OR r.fullname LIKE ?" : '';
$countSql   = "SELECT COUNT(*) AS total FROM (
    SELECT r.email
    FROM registration r
    INNER JOIN cart c ON c.user_email = r.email
    GROUP BY r.email" . $whereUsers . ") AS u";
$countStmt = mysqli_prepare($con, $countSql);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ss', $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// Get paginated users with cart summary
$havingSearch = $search !== '' ? " HAVING r.email LIKE ? OR r.fullname LIKE ?" : '';
$usersSql     = "SELECT r.email, r.fullname, r.profile_picture,
                        COUNT(c.id) AS item_count,
                        SUM(c.quantity) AS total_qty,
                        SUM(c.quantity * COALESCE(p.price, 0)) AS cart_value
                 FROM registration r
                 INNER JOIN cart c ON c.user_email = r.email
                 LEFT JOIN products p ON p.id = c.product_id
                 GROUP BY r.email, r.fullname, r.profile_picture
                 $havingSearch
                 ORDER BY MAX(c.added_at) DESC
                 LIMIT ?, ?";
$usersStmt = mysqli_prepare($con, $usersSql);
if ($search !== '') mysqli_stmt_bind_param($usersStmt, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($usersStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($usersStmt);
$usersResult = mysqli_stmt_get_result($usersStmt);
$users       = [];
while ($u = mysqli_fetch_assoc($usersResult)) $users[] = $u;

// Load each user's cart items (with product info)
$userCartItems = [];
foreach ($users as $u) {
    $em = $u['email'];
    $itemStmt = mysqli_prepare($con, "SELECT c.*, p.name AS product_name, p.image AS product_image, p.price AS unit_price FROM cart c LEFT JOIN products p ON p.id = c.product_id WHERE c.user_email = ? ORDER BY c.id DESC");
    mysqli_stmt_bind_param($itemStmt, 's', $em);
    mysqli_stmt_execute($itemStmt);
    $userCartItems[$em] = mysqli_stmt_get_result($itemStmt)->fetch_all(MYSQLI_ASSOC);
    mysqli_stmt_close($itemStmt);
}

// Load all products for FK dropdown
$products = [];
$prodRes  = mysqli_query($con, 'SELECT id, name, price FROM products ORDER BY name ASC');
if ($prodRes) while ($p = mysqli_fetch_assoc($prodRes)) $products[] = $p;

$allUsers = [];
$usersFkRes = mysqli_query($con, 'SELECT email, fullname FROM registration ORDER BY fullname ASC');
if ($usersFkRes) while ($u = mysqli_fetch_assoc($usersFkRes)) $allUsers[] = $u;

$title            = 'Admin Cart - JK Store';
$admin_active     = 'cart';
$admin_page_title = 'Cart Management';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="mb-0 fw-bold">Cart Management</h5>
            <small class="text-muted"><?= (int) $total ?> users with cart items</small>
        </div>
    </div>

    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search"
                placeholder="Search by user email or name..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <?php if (empty($users)): ?>
            <div class="text-center text-muted py-5">No cart items found.</div>
        <?php else: ?>
            <div class="accordion" id="cartAccordion">
                <?php foreach ($users as $i => $user):
                    $em        = $user['email'];
                    $accId     = 'cartUser_' . md5($em);
                    $avatar    = !empty($user['profile_picture']) ? 'images/profile_pictures/' . $user['profile_picture'] : 'images/profile_pictures/default.png';
                    $items     = $userCartItems[$em] ?? [];
                ?>
                    <div class="accordion-item border mb-2 rounded overflow-hidden">
                        <h2 class="accordion-header" id="head_<?= $accId ?>">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> py-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#body_<?= $accId ?>"
                                aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                                <div class="d-flex align-items-center gap-3 flex-wrap w-100 pe-2">
                                    <img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="avatar" class="avatar-38">
                                    <div class="flex-fill-min">
                                        <div class="fw-bold"><?= htmlspecialchars((string) $user['fullname'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="d-flex gap-3 ms-auto shrink-0">
                                        <span class="badge text-bg-primary"><?= (int) $user['item_count'] ?> items</span>
                                        <span class="badge text-bg-success">&#8377;<?= number_format((float) $user['cart_value'], 2) ?></span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="body_<?= $accId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                            data-bs-parent="#cartAccordion">
                            <div class="accordion-body p-0">
                                <div class="d-flex justify-content-end p-2 bg-light border-bottom">
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#addCartModal_<?= htmlspecialchars($accId, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-plus me-1"></i>Add Item
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Product</th>
                                                <th>Unit Price</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                                <th>Added At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($items)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-3">No items.</td>
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
                                                        <td>&#8377;<?= number_format((float) ($item['unit_price'] ?? 0), 2) ?></td>
                                                        <td><span class="badge text-bg-secondary"><?= (int) $item['quantity'] ?></span></td>
                                                        <td class="fw-semibold text-success">&#8377;<?= number_format((float) ($item['unit_price'] ?? 0) * (int) $item['quantity'], 2) ?></td>
                                                        <td><small><?= htmlspecialchars((string) ($item['added_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                                    data-bs-target="#editCartItem_<?= (int) $item['id'] ?>" title="Edit">
                                                                    <i class="fas fa-pen"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                                    data-bs-target="#deleteCartItem_<?= (int) $item['id'] ?>" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editCartItem_<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="post" novalidate>
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Edit Cart Item</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action" value="update">
                                                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                                        <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">User Email <span class="text-danger">*</span></label>
                                                                            <select class="form-select" name="user_email" required data-validation="required,select" data-error="#eci_email_<?= (int) $item['id'] ?>">
                                                                                <option value="">Select user</option><?php foreach ($allUsers as $u): ?><option value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($item['user_email'] ?? '') === (string) $u['email'] ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
                                                                            </select>
                                                                            <small id="eci_email_<?= (int) $item['id'] ?>"></small>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Product <span class="text-danger">*</span></label>
                                                                            <select class="form-select" name="product_id" required
                                                                                data-validation="required,select"
                                                                                data-error="#eci_prod_<?= (int) $item['id'] ?>">
                                                                                <option value="">Select product</option>
                                                                                <?php foreach ($products as $pr): ?>
                                                                                    <option value="<?= (int) $pr['id'] ?>"
                                                                                        <?= (int) ($item['product_id'] ?? 0) === (int) $pr['id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars((string) $pr['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                                        (&#8377;<?= number_format((float) $pr['price'], 2) ?>)
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                            <small id="eci_prod_<?= (int) $item['id'] ?>"></small>
                                                                        </div>
                                                                        <div class="mb-1">
                                                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                                            <input type="number" class="form-control" name="quantity" min="1"
                                                                                value="<?= (int) $item['quantity'] ?>" required
                                                                                data-validation="required,number" data-min="1"
                                                                                data-error="#eci_qty_<?= (int) $item['id'] ?>">
                                                                            <small id="eci_qty_<?= (int) $item['id'] ?>"></small>
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
                                                    <div class="modal fade" id="deleteCartItem_<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="post" novalidate>
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Delete Cart Item</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>Remove <strong><?= htmlspecialchars((string) ($item['product_name'] ?? 'this product'), ENT_QUOTES, 'UTF-8') ?></strong> from cart?</p>
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
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
                                            <?php endforeach;
                                            endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Cart Item Modal for this user -->
                    <div class="modal fade" id="addCartModal_<?= htmlspecialchars($accId, ENT_QUOTES, 'UTF-8') ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Add Cart Item — <?= htmlspecialchars((string) $user['fullname'], ENT_QUOTES, 'UTF-8') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="create">
                                        <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <div class="mb-3">
                                            <label class="form-label">User Email <span class="text-danger">*</span></label>
                                            <select class="form-select" name="user_email" required data-validation="required,select" data-error="#ac_email_<?= $accId ?>">
                                                <option value="">Select user</option><?php foreach ($allUsers as $u): ?><option value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $em === (string) $u['email'] ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
                                            </select>
                                            <small id="ac_email_<?= $accId ?>"></small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Product <span class="text-danger">*</span></label>
                                            <select class="form-select" name="product_id" required
                                                data-validation="required,select" data-error="#ac_prod_<?= $accId ?>">
                                                <option value="">Select product</option>
                                                <?php foreach ($products as $pr): ?>
                                                    <option value="<?= (int) $pr['id'] ?>">
                                                        <?= htmlspecialchars((string) $pr['name'], ENT_QUOTES, 'UTF-8') ?>
                                                        (&#8377;<?= number_format((float) $pr['price'], 2) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small id="ac_prod_<?= $accId ?>"></small>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="quantity" value="1" min="1" required
                                                data-validation="required,number" data-min="1" data-error="#ac_qty_<?= $accId ?>">
                                            <small id="ac_qty_<?= $accId ?>"></small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Add to Cart</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

            <nav class="products-pagination mt-4" aria-label="Cart users pagination">
                <div class="products-pagination-meta">
                    Page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> users
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
        <?php endif; ?>
    </div>
</div>

<script src="js/jquery.js"></script>
<script src="js/validate.js"></script>
<script>
    $(document).ready(function() {
        var searchInput = $('#searchInput');
        searchInput.focus();
        var v = searchInput.val() || '';
        if (searchInput[0] && typeof searchInput[0].setSelectionRange === 'function')
            searchInput[0].setSelectionRange(v.length, v.length);
        var t;
        searchInput.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                window.location.href = 'admin_cart.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($usersStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
