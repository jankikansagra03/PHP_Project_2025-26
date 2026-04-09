<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_team_members.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create' || $action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $photo = trim($_POST['photo'] ?? 'images/team/default.jpg');
        $bio = trim($_POST['bio'] ?? '');
        $facebookUrl = trim($_POST['facebook_url'] ?? '');
        $twitterUrl = trim($_POST['twitter_url'] ?? '');
        $linkedinUrl = trim($_POST['linkedin_url'] ?? '');
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $status = (($_POST['status'] ?? 'Active') === 'Inactive') ? 'Inactive' : 'Active';

        if ($action === 'create') {
            $stmt = mysqli_prepare($con, 'INSERT INTO team_members (name, designation, photo, bio, facebook_url, twitter_url, linkedin_url, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sssssssis', $name, $designation, $photo, $bio, $facebookUrl, $twitterUrl, $linkedinUrl, $displayOrder, $status);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Team member added.', time() + 5, '/');
            else setcookie('error', 'Failed to add team member.', time() + 5, '/');
        } else {
            $stmt = mysqli_prepare($con, 'UPDATE team_members SET name=?, designation=?, photo=?, bio=?, facebook_url=?, twitter_url=?, linkedin_url=?, display_order=?, status=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'sssssssisi', $name, $designation, $photo, $bio, $facebookUrl, $twitterUrl, $linkedinUrl, $displayOrder, $status, $id);
            if (mysqli_stmt_execute($stmt)) setcookie('success', 'Team member updated.', time() + 5, '/');
            else setcookie('error', 'Failed to update team member.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM team_members WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Team member deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete team member.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE name LIKE ? OR designation LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM team_members' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ss', $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM team_members' . $where . ' ORDER BY display_order ASC, id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Team Members - JK Store';
$admin_active = 'team_members';
$admin_page_title = 'Team Members';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Team Members</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeamModal"><i class="fas fa-plus me-1"></i>Add Member</button>
    </div>
    <div class="products-body">
        <form method="get" class="mb-3" novalidate>
            <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by name or designation..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle products-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Designation</th>
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
                                <td><img src="<?= htmlspecialchars((string) ($row['photo'] ?? 'images/team/default.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="member" class="small-preview border"></td>
                                <td><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['designation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) ($row['display_order'] ?? 0) ?></td>
                                <td><span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <div class="products-actions d-flex gap-1">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTeamModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTeamModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editTeamModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                <div class="mb-2"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#et_name_<?= (int) $row['id'] ?>"><small id="et_name_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Designation <span class="text-danger">*</span></label><input type="text" class="form-control" name="designation" value="<?= htmlspecialchars((string) ($row['designation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#et_des_<?= (int) $row['id'] ?>"><small id="et_des_<?= (int) $row['id'] ?>"></small></div>
                                                <div class="mb-2"><label class="form-label">Photo URL</label><input type="text" class="form-control" name="photo" value="<?= htmlspecialchars((string) ($row['photo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                <div class="mb-2"><label class="form-label">Bio</label><textarea class="form-control" name="bio" rows="3"><?= htmlspecialchars((string) ($row['bio'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                                                <div class="row g-2">
                                                    <div class="col-md-4"><label class="form-label">Facebook URL</label><input type="text" class="form-control" name="facebook_url" value="<?= htmlspecialchars((string) ($row['facebook_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    <div class="col-md-4"><label class="form-label">Twitter URL</label><input type="text" class="form-control" name="twitter_url" value="<?= htmlspecialchars((string) ($row['twitter_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    <div class="col-md-4"><label class="form-label">LinkedIn URL</label><input type="text" class="form-control" name="linkedin_url" value="<?= htmlspecialchars((string) ($row['linkedin_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                </div>
                                                <div class="row g-2 mt-1">
                                                    <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" class="form-control" name="display_order" value="<?= (int) ($row['display_order'] ?? 0) ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status">
                                                            <option value="Active" <?= $isActive ? 'selected' : '' ?>>Active</option>
                                                            <option value="Inactive" <?= !$isActive ? 'selected' : '' ?>>Inactive</option>
                                                        </select></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="deleteTeamModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" novalidate>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete <strong><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> permanently?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No team members found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="products-pagination" aria-label="Team pagination">
            <div class="products-pagination-meta">Showing page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
            <ul class="products-pagination-list">
                <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?>
                <li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="addTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                    <div class="mb-2"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#at_name"><small id="at_name"></small></div>
                    <div class="mb-2"><label class="form-label">Designation <span class="text-danger">*</span></label><input type="text" class="form-control" name="designation" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#at_des"><small id="at_des"></small></div>
                    <div class="mb-2"><label class="form-label">Photo URL</label><input type="text" class="form-control" name="photo" value="images/team/default.jpg"></div>
                    <div class="mb-2"><label class="form-label">Bio</label><textarea class="form-control" name="bio" rows="3"></textarea></div>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Facebook URL</label><input type="text" class="form-control" name="facebook_url"></div>
                        <div class="col-md-4"><label class="form-label">Twitter URL</label><input type="text" class="form-control" name="twitter_url"></div>
                        <div class="col-md-4"><label class="form-label">LinkedIn URL</label><input type="text" class="form-control" name="linkedin_url"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" class="form-control" name="display_order" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select></div>
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
                window.location.href = 'admin_team_members.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
            }, 400);
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
