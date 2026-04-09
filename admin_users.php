<?php
include_once 'db_config.php';
session_start();

function valid_user_role(string $role): bool
{
    return in_array($role, ['Admin', 'User'], true);
}

function valid_user_status(string $status): bool
{
    return in_array($status, ['Active', 'Inactive'], true);
}

function registration_email_exists(mysqli $con, string $email, int $excludeId = 0): bool
{
    if ($excludeId > 0) {
        $stmt = mysqli_prepare($con, 'SELECT 1 FROM registration WHERE email = ? AND id <> ? LIMIT 1');
        if (!$stmt) return true;
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare($con, 'SELECT 1 FROM registration WHERE email = ? LIMIT 1');
        if (!$stmt) return true;
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $exists = $res && mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function get_registration_by_id(mysqli $con, int $id): ?array
{
    $stmt = mysqli_prepare($con, 'SELECT id, email, role, status FROM registration WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function count_admin_users(mysqli $con): int
{
    $res = mysqli_query($con, "SELECT COUNT(*) AS c FROM registration WHERE role = 'Admin'");
    if (!$res) return 0;
    $row = mysqli_fetch_assoc($res);
    return (int) ($row['c'] ?? 0);
}

function delete_user_with_dependencies(mysqli $con, string $email): bool
{
    mysqli_begin_transaction($con);
    try {
        $statements = [
            ['DELETE FROM orders WHERE user_email = ?', 's'],
            ['DELETE FROM offer_usage WHERE user_email = ?', 's'],
            ['DELETE FROM reviews WHERE user_email = ?', 's'],
            ['DELETE FROM password_token WHERE email = ?', 's'],
            ['DELETE FROM registration WHERE email = ?', 's'],
        ];

        foreach ($statements as $def) {
            $stmt = mysqli_prepare($con, $def[0]);
            if (!$stmt) throw new Exception('Prepare failed');
            mysqli_stmt_bind_param($stmt, $def[1], $email);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('Execute failed');
            }
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($con);
        return true;
    } catch (Exception) {
        mysqli_rollback($con);
        return false;
    }
}

if (isset($_POST['action'])) {
    $action       = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage   = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl  = 'admin_users.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $fullname  = trim($_POST['fullname'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $gender    = trim($_POST['gender'] ?? '');
        $mobile    = trim($_POST['mobile'] ?? '');
        $address   = trim($_POST['address'] ?? '');
        $role      = (($_POST['role'] ?? 'User') === 'Admin') ? 'Admin' : 'User';
        $status    = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';
        $token     = bin2hex(random_bytes(16));

        if ($fullname === '' || mb_strlen($fullname) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || ($mobile !== '' && mb_strlen($mobile) > 15) || mb_strlen($address) > 500 || !valid_user_role($role) || !valid_user_status($status)) {
            setcookie('error', 'Please provide valid user details.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if (registration_email_exists($con, $email)) {
            setcookie('error', 'Email already exists.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($con, 'INSERT INTO registration (fullname, email, password, gender, mobile, address, role, status, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            setcookie('error', 'Failed to create user.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'sssssssss', $fullname, $email, $hashed, $gender, $mobile, $address, $role, $status, $token);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'User created successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to create user. Email may already exist.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id       = (int) ($_POST['id'] ?? 0);
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $gender   = trim($_POST['gender'] ?? '');
        $mobile   = trim($_POST['mobile'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $role     = (($_POST['role'] ?? 'User') === 'Admin') ? 'Admin' : 'User';
        $status   = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';

        if ($id <= 0 || $fullname === '' || mb_strlen($fullname) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || ($mobile !== '' && mb_strlen($mobile) > 15) || mb_strlen($address) > 500 || !valid_user_role($role) || !valid_user_status($status)) {
            setcookie('error', 'Invalid user update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if (registration_email_exists($con, $email, $id)) {
            setcookie('error', 'Email is already used by another account.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $existing = get_registration_by_id($con, $id);
        if (!$existing) {
            setcookie('error', 'User not found.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $isDemotingLastAdmin = (($existing['role'] ?? '') === 'Admin') && $role !== 'Admin' && count_admin_users($con) <= 1;
        if ($isDemotingLastAdmin) {
            setcookie('error', 'Cannot remove role from the last admin account.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = mysqli_prepare($con, 'UPDATE registration SET fullname=?, email=?, gender=?, mobile=?, address=?, role=?, status=? WHERE id=?');
        if (!$stmt) {
            setcookie('error', 'Failed to update user.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'sssssssi', $fullname, $email, $gender, $mobile, $address, $role, $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'User updated successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to update user.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'reset_password') {
        $id       = (int) ($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if ($id <= 0 || strlen($password) < 8) {
            setcookie('error', 'Password must be at least 8 characters.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $hashed   = password_hash($password, PASSWORD_DEFAULT);
        $stmt     = mysqli_prepare($con, 'UPDATE registration SET password=? WHERE id=?');
        if (!$stmt) {
            setcookie('error', 'Failed to reset password.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'si', $hashed, $id);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'Password reset successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to reset password.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            setcookie('error', 'Invalid delete request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $target = get_registration_by_id($con, $id);
        if (!$target) {
            setcookie('error', 'User not found.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if (($target['role'] ?? '') === 'Admin' && count_admin_users($con) <= 1) {
            setcookie('error', 'Cannot delete the last admin account.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if (($target['email'] ?? '') === ($_SESSION['admin'] ?? '')) {
            setcookie('error', 'You cannot delete your own signed-in account.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (delete_user_with_dependencies($con, (string) ($target['email'] ?? ''))) {
            setcookie('success', 'User deleted with related records.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to delete user.', time() + 5, '/');
        }
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'change_status') {
        $id        = (int) ($_POST['id'] ?? 0);
        $newStatus = (($_POST['new_status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';

        if ($id <= 0 || !valid_user_status($newStatus)) {
            setcookie('error', 'Invalid status request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $target = get_registration_by_id($con, $id);
        if (!$target) {
            setcookie('error', 'User not found.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        if (($target['role'] ?? '') === 'Admin' && $newStatus === 'Inactive' && count_admin_users($con) <= 1) {
            setcookie('error', 'Cannot deactivate the last admin account.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt      = mysqli_prepare($con, 'UPDATE registration SET status=? WHERE id=?');
        if (!$stmt) {
            setcookie('error', 'Failed to update status.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);
        if (mysqli_stmt_execute($stmt)) {
            setcookie('success', 'User status updated.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to update status.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where   = $search !== '' ? " WHERE fullname LIKE ? OR email LIKE ? OR mobile LIKE ? OR role LIKE ? OR status LIKE ?" : '';
$like    = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM registration' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'sssss', $like, $like, $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT id, fullname, email, gender, mobile, profile_picture, address, role, status FROM registration' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'sssssii', $like, $like, $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title            = 'Admin Users - JK Store';
$admin_active     = 'users';
$admin_page_title = 'User Management';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">User Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-1"></i>Add User
        </button>
    </div>

    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search"
                placeholder="Search by name, email, mobile, role, status..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Mobile</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="action-col-220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $isActive = strtolower((string) ($row['status'] ?? '')) === 'active';
                            $isAdmin  = strtolower((string) ($row['role'] ?? '')) === 'admin';
                            $avatar   = !empty($row['profile_picture']) ? 'images/profile_pictures/' . $row['profile_picture'] : 'images/profile_pictures/default.png';
                            ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="avatar" class="avatar-38">
                                        <div>
                                            <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['mobile'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['gender'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= $isAdmin ? 'text-bg-primary' : 'text-bg-secondary' ?>">
                                        <?= $isAdmin ? 'Admin' : 'User' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="products-actions d-flex flex-wrap gap-1">
                                        <button class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal"
                                            data-bs-target="#viewUserModal<?= (int) $row['id'] ?>" title="View" aria-label="View user details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                            data-bs-target="#editUserModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit user">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info mb-1" data-bs-toggle="modal"
                                            data-bs-target="#resetPwdModal<?= (int) $row['id'] ?>" title="Reset Password" aria-label="Reset password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary mb-1" data-bs-toggle="modal"
                                            data-bs-target="#statusUserModal<?= (int) $row['id'] ?>"
                                            title="<?= $isActive ? 'Deactivate' : 'Activate' ?>" aria-label="<?= $isActive ? 'Deactivate user' : 'Activate user' ?>">
                                            <i class="fas <?= $isActive ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewUserModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">User Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="d-flex align-items-center gap-3 mb-4">
                                                <img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="avatar" class="avatar-70">
                                                <div>
                                                    <h5 class="mb-1"><?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h5>
                                                    <div class="text-muted"><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Mobile:</strong> <?= htmlspecialchars((string) ($row['mobile'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
                                                    <p class="mb-1"><strong>Gender:</strong> <?= htmlspecialchars((string) ($row['gender'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
                                                    <p class="mb-1"><strong>Role:</strong> <span class="badge <?= $isAdmin ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= $isAdmin ? 'Admin' : 'User' ?></span></p>
                                                    <p class="mb-0"><strong>Status:</strong> <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-0"><strong>Address:</strong><br><?= nl2br(htmlspecialchars((string) ($row['address'] ?? '—'), ENT_QUOTES, 'UTF-8')) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editUserModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit User - <?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="return_page" value="<?= (int) $page ?>">

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="fullname"
                                                            value="<?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            required data-validation="required,min,max" data-min="2" data-max="100"
                                                            data-error="#eu_name_<?= (int) $row['id'] ?>">
                                                        <small id="eu_name_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                                        <input type="email" class="form-control" name="email"
                                                            value="<?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            required data-validation="required,email"
                                                            data-error="#eu_email_<?= (int) $row['id'] ?>">
                                                        <small id="eu_email_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Mobile</label>
                                                        <input type="text" class="form-control" name="mobile"
                                                            value="<?= htmlspecialchars((string) ($row['mobile'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            data-validation="max" data-max="15"
                                                            data-error="#eu_mobile_<?= (int) $row['id'] ?>">
                                                        <small id="eu_mobile_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Gender</label>
                                                        <select class="form-select" name="gender" data-error="#eu_gender_<?= (int) $row['id'] ?>">
                                                            <option value="">-- Select --</option>
                                                            <option value="Male" <?= (($row['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                                            <option value="Female" <?= (($row['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                                            <option value="Other" <?= (($row['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                                                        </select>
                                                        <small id="eu_gender_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Role <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="role" required
                                                            data-validation="required,select"
                                                            data-error="#eu_role_<?= (int) $row['id'] ?>">
                                                            <option value="User" <?= ($isAdmin ? '' : 'selected') ?>>User</option>
                                                            <option value="Admin" <?= ($isAdmin ? 'selected' : '') ?>>Admin</option>
                                                        </select>
                                                        <small id="eu_role_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="status" required
                                                            data-validation="required,select"
                                                            data-error="#eu_status_<?= (int) $row['id'] ?>">
                                                            <option value="Active" <?= ($isActive ? 'selected' : '') ?>>Active</option>
                                                            <option value="Inactive" <?= (!$isActive ? 'selected' : '') ?>>Inactive</option>
                                                        </select>
                                                        <small id="eu_status_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Address</label>
                                                        <textarea class="form-control" name="address" rows="3"
                                                            data-validation="max" data-max="500"
                                                            data-error="#eu_address_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        <small id="eu_address_<?= (int) $row['id'] ?>"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update User</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reset Password Modal -->
                            <div class="modal fade" id="resetPwdModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reset Password</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <p class="text-muted small mb-3">Setting a new password for <strong><?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label">New Password <span class="text-danger">*</span></label>
                                                    <input type="password" class="form-control" name="password"
                                                        required data-validation="required,min,strongpassword" data-min="8"
                                                        data-error="#rp_pwd_<?= (int) $row['id'] ?>">
                                                    <small id="rp_pwd_<?= (int) $row['id'] ?>"></small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Toggle Modal -->
                            <div class="modal fade" id="statusUserModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <?php $nextStatus = $isActive ? 'Inactive' : 'Active'; ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Change Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Set <strong><?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> as <strong><?= $nextStatus ?></strong>?</p>
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

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteUserModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete User</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete <strong><?= htmlspecialchars((string) ($row['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> permanently? This will also delete all their orders, cart items, addresses, and wishlist items.</p>
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

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Users pagination">
            <div class="products-pagination-meta">
                Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total users
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_page" value="<?= (int) $page ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="fullname" required
                                data-validation="required,min,max" data-min="2" data-max="100"
                                data-error="#au_name">
                            <small id="au_name"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required
                                data-validation="required,email" data-error="#au_email">
                            <small id="au_email"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required
                                data-validation="required,min,strongpassword" data-min="8"
                                data-error="#au_pwd">
                            <small id="au_pwd"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" name="mobile"
                                data-validation="max" data-max="15" data-error="#au_mobile">
                            <small id="au_mobile"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">-- Select --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required data-validation="required,select" data-error="#au_role">
                                <option value="User" selected>User</option>
                                <option value="Admin">Admin</option>
                            </select>
                            <small id="au_role"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required data-validation="required,select" data-error="#au_status">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <small id="au_status"></small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"
                                data-validation="max" data-max="500" data-error="#au_address"></textarea>
                            <small id="au_address"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create User</button>
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
        var v = searchInput.val() || '';
        if (searchInput[0] && typeof searchInput[0].setSelectionRange === 'function')
            searchInput[0].setSelectionRange(v.length, v.length);
        var t;
        searchInput.on('input', function() {
            clearTimeout(t);
            var val = $(this).val().trim();
            t = setTimeout(function() {
                window.location.href = 'admin_users.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
