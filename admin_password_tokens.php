<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_password_tokens.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $email = trim($_POST['email'] ?? '');
        $otp = ($_POST['otp'] ?? '') === '' ? null : (int) $_POST['otp'];
        $createdAt = trim($_POST['created_at'] ?? '');
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $otpAttempts = (int) ($_POST['otp_attempts'] ?? 0);

        if ($email === '' || $createdAt === '' || $expiresAt === '') {
            setcookie('error', 'Please fill required token fields.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'INSERT INTO password_token (email, otp, created_at, expires_at, otp_attempts) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sissi', $email, $otp, $createdAt, $expiresAt, $otpAttempts);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Token created.', time() + 5, '/');
        else setcookie('error', 'Failed to create token.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $otp = ($_POST['otp'] ?? '') === '' ? null : (int) $_POST['otp'];
        $createdAt = trim($_POST['created_at'] ?? '');
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $otpAttempts = (int) ($_POST['otp_attempts'] ?? 0);
        $lastResend = trim($_POST['last_resend'] ?? '');

        if ($id <= 0 || $email === '' || $createdAt === '' || $expiresAt === '') {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'UPDATE password_token SET email=?, otp=?, created_at=?, expires_at=?, otp_attempts=?, last_resend=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sissisi', $email, $otp, $createdAt, $expiresAt, $otpAttempts, $lastResend, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Token updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update token.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM password_token WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Token deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete token.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE email LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM password_token' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 's', $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM password_token' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'sii', $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Password Tokens - JK Store';
$admin_active = 'password_token';
$admin_page_title = 'Password Tokens';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Password Tokens</h5><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTokenModal"><i class="fas fa-plus me-1"></i>Add Token</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate><input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by email..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>OTP</th>
                        <th>Attempts</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Last Resend</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php if ($result && mysqli_num_rows($result) > 0): ?><?php while ($row = mysqli_fetch_assoc($result)): ?><tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($row['otp'] ?? 'NULL'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) ($row['otp_attempts'] ?? 0) ?></td>
                        <td><small><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><small><?= htmlspecialchars((string) ($row['expires_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><small><?= htmlspecialchars((string) ($row['last_resend'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td>
                            <div class="products-actions d-flex gap-1"><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTokenModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTokenModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                        </td>
                    </tr>
                    <div class="modal fade" id="editTokenModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Token</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                                        <div class="mb-2"><label class="form-label">OTP</label><input type="number" class="form-control" name="otp" value="<?= htmlspecialchars((string) ($row['otp'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                        <div class="mb-2"><label class="form-label">Created At</label><input type="datetime-local" class="form-control" name="created_at" value="<?= !empty($row['created_at']) ? date('Y-m-d\\TH:i', strtotime((string) $row['created_at'])) : '' ?>" required></div>
                                        <div class="mb-2"><label class="form-label">Expires At</label><input type="datetime-local" class="form-control" name="expires_at" value="<?= !empty($row['expires_at']) ? date('Y-m-d\\TH:i', strtotime((string) $row['expires_at'])) : '' ?>" required></div>
                                        <div class="mb-2"><label class="form-label">OTP Attempts</label><input type="number" class="form-control" name="otp_attempts" value="<?= (int) ($row['otp_attempts'] ?? 0) ?>"></div>
                                        <div class="mb-1"><label class="form-label">Last Resend</label><input type="datetime-local" class="form-control" name="last_resend" value="<?= !empty($row['last_resend']) ? date('Y-m-d\\TH:i', strtotime((string) $row['last_resend'])) : '' ?>"></div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="deleteTokenModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Token</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Delete token for <strong><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                </form>
                            </div>
                        </div>
                    </div><?php endwhile; ?><?php else: ?><tr>
                        <td colspan="8" class="text-center text-muted py-4">No tokens found.</td>
                    </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <nav class="products-pagination" aria-label="Tokens pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addTokenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Token</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-2"><label class="form-label">OTP</label><input type="number" class="form-control" name="otp"></div>
                    <div class="mb-2"><label class="form-label">Created At</label><input type="datetime-local" class="form-control" name="created_at" required></div>
                    <div class="mb-2"><label class="form-label">Expires At</label><input type="datetime-local" class="form-control" name="expires_at" required></div>
                    <div class="mb-1"><label class="form-label">OTP Attempts</label><input type="number" class="form-control" name="otp_attempts" value="0"></div>
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
                window.location.href = 'admin_password_tokens.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
