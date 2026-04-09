<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_contact_us.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $reply = trim($_POST['reply'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $replyDate = trim($_POST['reply_date'] ?? '');
        $submittedAt = trim($_POST['submitted_at'] ?? '');

        if ($name === '' || $email === '' || $subject === '' || $message === '') {
            setcookie('error', 'Please fill all required fields.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $replyDate = $replyDate !== '' ? $replyDate : null;
        $submittedAt = $submittedAt !== '' ? $submittedAt : null;

        $stmt = mysqli_prepare($con, 'INSERT INTO contact_us (name, email, subject, message, reply, status, reply_date, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssssssss', $name, $email, $subject, $message, $reply, $status, $replyDate, $submittedAt);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Message created successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to create message.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $reply = trim($_POST['reply'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $replyDate = trim($_POST['reply_date'] ?? '');
        $submittedAt = trim($_POST['submitted_at'] ?? '');

        if ($id <= 0 || $name === '' || $email === '' || $subject === '' || $message === '') {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }

        $replyDate = $replyDate !== '' ? $replyDate : null;
        $submittedAt = $submittedAt !== '' ? $submittedAt : null;

        $stmt = mysqli_prepare($con, 'UPDATE contact_us SET name=?, email=?, subject=?, message=?, reply=?, status=?, reply_date=?, submitted_at=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssssssssi', $name, $email, $subject, $message, $reply, $status, $replyDate, $submittedAt, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Message updated successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to update message.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'reply') {
        $id = (int) ($_POST['id'] ?? 0);
        $reply = trim($_POST['reply'] ?? '');
        $status = trim($_POST['status'] ?? 'Replied');
        $stmt = mysqli_prepare($con, 'UPDATE contact_us SET reply=?, status=?, reply_date=NOW() WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssi', $reply, $status, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Message updated successfully.', time() + 5, '/');
        else setcookie('error', 'Failed to update message.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM contact_us WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Message deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete message.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM contact_us' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ssss', $like, $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM contact_us' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'ssssii', $like, $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Contact Messages - JK Store';
$admin_active = 'contact_us';
$admin_page_title = 'Contact Messages';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Contact Messages</h5>
        <div class="d-flex gap-2 align-items-center"><span class="badge text-bg-primary fs-6"><?= (int) $total ?> total messages</span><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addContactModal"><i class="fas fa-plus me-1"></i>Add Message</button></div>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by name, email, subject, message..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $status = (string) ($row['status'] ?? 'Pending');
                            $badge = strtolower($status) === 'replied' ? 'success' : 'warning';
                            ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td>
                                    <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><small><?= htmlspecialchars((string) ($row['submitted_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td>
                                    <div class="products-actions d-flex gap-1">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewContactModal<?= (int) $row['id'] ?>" title="View" aria-label="View"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editContactModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteContactModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="viewContactModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Contact Message #<?= (int) $row['id'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Name:</strong> <?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p><strong>Subject:</strong> <?= htmlspecialchars((string) ($row['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                            <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars((string) ($row['message'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p><?php if (!empty($row['reply'])): ?>
                                                <hr>
                                                <p><strong>Reply:</strong><br><?= nl2br(htmlspecialchars((string) $row['reply'], ENT_QUOTES, 'UTF-8')) ?></p><?php endif; ?>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="editContactModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Message</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="mb-2"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#cu_name_<?= (int) $row['id'] ?>"><small id="cu_name_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,email" data-error="#cu_email_<?= (int) $row['id'] ?>"><small id="cu_email_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Subject <span class="text-danger">*</span></label><input type="text" class="form-control" name="subject" value="<?= htmlspecialchars((string) ($row['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="3" data-max="150" data-error="#cu_sub_<?= (int) $row['id'] ?>"><small id="cu_sub_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Message <span class="text-danger">*</span></label><textarea class="form-control" name="message" rows="3" required data-validation="required,min" data-min="5" data-error="#cu_msg_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small id="cu_msg_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Status</label><select class="form-select" name="status">
                                                        <option value="Pending" <?= strtolower($status) === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="Replied" <?= strtolower($status) === 'replied' ? 'selected' : '' ?>>Replied</option>
                                                    </select></div>
                                                <div class="mb-2"><label class="form-label">Reply</label><textarea class="form-control" name="reply" rows="3" data-validation="max" data-max="2000" data-error="#cu_reply_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['reply'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small id="cu_reply_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="row g-2">
                                                    <div class="col-md-6"><label class="form-label">Reply Date</label><input type="datetime-local" class="form-control" name="reply_date" value="<?= !empty($row['reply_date']) ? date('Y-m-d\\TH:i', strtotime((string) $row['reply_date'])) : '' ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Submitted At</label><input type="datetime-local" class="form-control" name="submitted_at" value="<?= !empty($row['submitted_at']) ? date('Y-m-d\\TH:i', strtotime((string) $row['submitted_at'])) : '' ?>"></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="deleteContactModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Message</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this message permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No contact messages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Contact messages pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Contact Message</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-2"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#ca_name"><small id="ca_name"></small></div>
                    <div class="mb-2"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" required data-validation="required,email" data-error="#ca_email"><small id="ca_email"></small></div>
                    <div class="mb-2"><label class="form-label">Subject <span class="text-danger">*</span></label><input type="text" class="form-control" name="subject" required data-validation="required,min,max" data-min="3" data-max="150" data-error="#ca_sub"><small id="ca_sub"></small></div>
                    <div class="mb-2"><label class="form-label">Message <span class="text-danger">*</span></label><textarea class="form-control" name="message" rows="3" required data-validation="required,min" data-min="5" data-error="#ca_msg"></textarea><small id="ca_msg"></small></div>
                    <div class="mb-2"><label class="form-label">Status</label><select class="form-select" name="status">
                            <option value="Pending">Pending</option>
                            <option value="Replied">Replied</option>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Reply</label><textarea class="form-control" name="reply" rows="2" data-validation="max" data-max="2000" data-error="#ca_reply"></textarea><small id="ca_reply"></small></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Reply Date</label><input type="datetime-local" class="form-control" name="reply_date"></div>
                        <div class="col-md-6"><label class="form-label">Submitted At</label><input type="datetime-local" class="form-control" name="submitted_at"></div>
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
                window.location.href = 'admin_contact_us.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
