<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_order_items.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $productName = trim($_POST['product_name'] ?? '');
        $productImage = trim($_POST['product_image'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $discount = (float) ($_POST['discount'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $subtotal = (float) ($_POST['subtotal'] ?? 0);

        if ($orderId <= 0 || $productId <= 0 || $productName === '' || $quantity <= 0) {
            setcookie('error', 'Please enter valid order item details.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'INSERT INTO order_items (order_id, product_id, product_name, product_image, price, discount, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iissddid', $orderId, $productId, $productName, $productImage, $price, $discount, $quantity, $subtotal);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Order item created.', time() + 5, '/');
        else setcookie('error', 'Failed to create order item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $productName = trim($_POST['product_name'] ?? '');
        $productImage = trim($_POST['product_image'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $discount = (float) ($_POST['discount'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $subtotal = (float) ($_POST['subtotal'] ?? 0);

        if ($id <= 0 || $orderId <= 0 || $productId <= 0 || $productName === '' || $quantity <= 0) {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'UPDATE order_items SET order_id=?, product_id=?, product_name=?, product_image=?, price=?, discount=?, quantity=?, subtotal=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'iissddidi', $orderId, $productId, $productName, $productImage, $price, $discount, $quantity, $subtotal, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Order item updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update order item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM order_items WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Order item deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete order item.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE oi.product_name LIKE ? OR oi.order_id LIKE ? OR oi.product_id LIKE ?' : '';
$like = '%' . $search . '%';

$countSql = 'SELECT COUNT(*) AS total FROM order_items oi' . $where;
$countStmt = mysqli_prepare($con, $countSql);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'sss', $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT oi.*, o.order_number FROM order_items oi LEFT JOIN orders o ON o.id = oi.order_id' . $where . ' ORDER BY oi.id DESC LIMIT ?, ?';
$listStmt = mysqli_prepare($con, $listSql);
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'sssii', $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Order Items - JK Store';
$admin_active = 'order_items';
$admin_page_title = 'Order Items';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Order Items</h5><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrderItemModal"><i class="fas fa-plus me-1"></i>Add Order Item</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by product, order id, product id..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php if ($result && mysqli_num_rows($result) > 0): ?><?php while ($row = mysqli_fetch_assoc($result)): ?><tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td>
                            <div class="small fw-semibold">#<?= (int) ($row['order_id'] ?? 0) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars((string) ($row['order_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2"><?php if (!empty($row['product_image'])): ?><img src="<?= htmlspecialchars((string) $row['product_image'], ENT_QUOTES, 'UTF-8') ?>" class="small-preview border" alt="product"><?php endif; ?><div>
                                    <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-muted small">Product ID: <?= (int) ($row['product_id'] ?? 0) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>&#8377;<?= number_format((float) ($row['price'] ?? 0), 2) ?></td>
                        <td>&#8377;<?= number_format((float) ($row['discount'] ?? 0), 2) ?></td>
                        <td><?= (int) ($row['quantity'] ?? 0) ?></td>
                        <td>&#8377;<?= number_format((float) ($row['subtotal'] ?? 0), 2) ?></td>
                        <td>
                            <div class="products-actions d-flex gap-1"><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editOrderItemModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOrderItemModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                        </td>
                    </tr>
                    <div class="modal fade" id="editOrderItemModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Order Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <div class="mb-2"><label class="form-label">Order ID</label><input type="number" class="form-control" name="order_id" value="<?= (int) ($row['order_id'] ?? 0) ?>" required></div>
                                        <div class="mb-2"><label class="form-label">Product ID</label><input type="number" class="form-control" name="product_id" value="<?= (int) ($row['product_id'] ?? 0) ?>" required></div>
                                        <div class="mb-2"><label class="form-label">Product Name</label><input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars((string) ($row['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                        <div class="mb-2"><label class="form-label">Product Image</label><input type="text" class="form-control" name="product_image" value="<?= htmlspecialchars((string) ($row['product_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                        <div class="row g-2">
                                            <div class="col-md-6"><label class="form-label">Price</label><input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars((string) ($row['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                            <div class="col-md-6"><label class="form-label">Discount</label><input type="number" step="0.01" class="form-control" name="discount" value="<?= htmlspecialchars((string) ($row['discount'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></div>
                                        </div>
                                        <div class="row g-2 mt-1">
                                            <div class="col-md-6"><label class="form-label">Quantity</label><input type="number" class="form-control" name="quantity" value="<?= (int) ($row['quantity'] ?? 1) ?>" min="1" required></div>
                                            <div class="col-md-6"><label class="form-label">Subtotal</label><input type="number" step="0.01" class="form-control" name="subtotal" value="<?= htmlspecialchars((string) ($row['subtotal'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="deleteOrderItemModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Order Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Delete this order item permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                </form>
                            </div>
                        </div>
                    </div><?php endwhile; ?><?php else: ?><tr>
                        <td colspan="8" class="text-center text-muted py-4">No order items found.</td>
                    </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <nav class="products-pagination" aria-label="Order items pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addOrderItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Order Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-2"><label class="form-label">Order ID</label><input type="number" class="form-control" name="order_id" required></div>
                    <div class="mb-2"><label class="form-label">Product ID</label><input type="number" class="form-control" name="product_id" required></div>
                    <div class="mb-2"><label class="form-label">Product Name</label><input type="text" class="form-control" name="product_name" required></div>
                    <div class="mb-2"><label class="form-label">Product Image</label><input type="text" class="form-control" name="product_image"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Price</label><input type="number" step="0.01" class="form-control" name="price" value="0" required></div>
                        <div class="col-md-6"><label class="form-label">Discount</label><input type="number" step="0.01" class="form-control" name="discount" value="0"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6"><label class="form-label">Quantity</label><input type="number" class="form-control" name="quantity" value="1" min="1" required></div>
                        <div class="col-md-6"><label class="form-label">Subtotal</label><input type="number" step="0.01" class="form-control" name="subtotal" value="0" required></div>
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
        var searchInput = $('#searchInput');
        searchInput.focus();
        var v = searchInput.val() || '';
        if (searchInput[0] && typeof searchInput[0].setSelectionRange === 'function') searchInput[0].setSelectionRange(v.length, v.length);
        var t;
        searchInput.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                window.location.href = 'admin_order_items.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
