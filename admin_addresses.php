<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_addresses.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $userId = trim($_POST['user_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($userId === '' || $name === '' || $email === '' || $mobile === '' || $address === '') {
            setcookie('error', 'Please fill all address fields.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'INSERT INTO addresses (user_id, name, email, mobile, address) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssss', $userId, $name, $email, $mobile, $address);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Address created.', time() + 5, '/');
        else setcookie('error', 'Failed to create address.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $userId = trim($_POST['user_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($id <= 0 || $userId === '' || $name === '' || $email === '' || $mobile === '' || $address === '') {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'UPDATE addresses SET user_id=?, name=?, email=?, mobile=?, address=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssssi', $userId, $name, $email, $mobile, $address, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Address updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update address.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM addresses WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Address deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete address.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE user_id LIKE ? OR name LIKE ? OR email LIKE ? OR mobile LIKE ? OR address LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM addresses' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'sssss', $like, $like, $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM addresses' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'sssssii', $like, $like, $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Addresses - JK Store';
$admin_active = 'addresses';
$admin_page_title = 'Addresses';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Address Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAddressModal"><i class="fas fa-plus me-1"></i>Add Address</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search user, name, email, mobile, address..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= htmlspecialchars((string) ($row['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['mobile'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><small><?= nl2br(htmlspecialchars((string) ($row['address'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></small></td>
                                <td>
                                    <div class="products-actions d-flex gap-1"><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAddressModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAddressModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editAddressModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Address</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="mb-2"><label class="form-label">User ID (email)</label><input type="text" class="form-control" name="user_id" value="<?= htmlspecialchars((string) ($row['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                                <div class="mb-2"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                                <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                                <div class="mb-2"><label class="form-label">Mobile</label><input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars((string) ($row['mobile'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                                <div class="mb-1"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3" required><?= htmlspecialchars((string) ($row['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="deleteAddressModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Address</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this address permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No addresses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Addresses pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Address</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-2"><label class="form-label">User ID (email)</label><input type="text" class="form-control" name="user_id" required></div>
                    <div class="mb-2"><label class="form-label">Name</label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-2"><label class="form-label">Mobile</label><input type="text" class="form-control" name="mobile" required></div>
                    <div class="mb-1"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3" required></textarea></div>
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
                window.location.href = 'admin_addresses.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
