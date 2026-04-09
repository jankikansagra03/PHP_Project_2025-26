<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
  $action = $_POST['action'];
  $rs = trim($_POST['return_search'] ?? '');
  $rp = max(1, (int)($_POST['return_page'] ?? 1));
  $to = 'admin_addresses.php?page=' . $rp . ($rs !== '' ? '&search=' . urlencode($rs) : '');

  if ($action === 'create') {
    $uid = $_POST['user_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $mob = trim($_POST['mobile'] ?? '');
    $addr = trim($_POST['address'] ?? '');
    $s = mysqli_prepare($con, 'INSERT INTO addresses (user_id,name,email,mobile,address) VALUES (?,?,?,?,?)');
    mysqli_stmt_bind_param($s, 'sssss', $uid, $name, $em, $mob, $addr);
    if (mysqli_stmt_execute($s)) setcookie('success', 'Address added.', time() + 5, '/');
    else setcookie('error', 'Failed.', time() + 5, '/');
    mysqli_stmt_close($s);
    header('Location:' . $to);
    exit();
  }
  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $uid = $_POST['user_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $mob = trim($_POST['mobile'] ?? '');
    $addr = trim($_POST['address'] ?? '');
    $s = mysqli_prepare($con, 'UPDATE addresses SET user_id=?,name=?,email=?,mobile=?,address=? WHERE id=?');
    mysqli_stmt_bind_param($s, 'sssssi', $uid, $name, $em, $mob, $addr, $id);
    if (mysqli_stmt_execute($s)) setcookie('success', 'Address updated.', time() + 5, '/');
    else setcookie('error', 'Failed.', time() + 5, '/');
    mysqli_stmt_close($s);
    header('Location:' . $to);
    exit();
  }
  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $s = mysqli_prepare($con, 'DELETE FROM addresses WHERE id=?');
    mysqli_stmt_bind_param($s, 'i', $id);
    if (mysqli_stmt_execute($s)) setcookie('success', 'Address deleted.', time() + 5, '/');
    else setcookie('error', 'Failed.', time() + 5, '/');
    mysqli_stmt_close($s);
    header('Location:' . $to);
    exit();
  }
}

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$like = '%' . $search . '%';

$hs = $search !== '' ? " HAVING r.email LIKE ? OR r.fullname LIKE ?" : '';
$cs = mysqli_prepare($con, "SELECT COUNT(*) AS t FROM (SELECT r.email FROM registration r INNER JOIN addresses a ON a.user_id=r.email GROUP BY r.email $hs) AS u");
if ($search !== '') mysqli_stmt_bind_param($cs, 'ss', $like, $like);
mysqli_stmt_execute($cs);
$total = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['t'] ?? 0);
mysqli_stmt_close($cs);
$tp = max(1, (int)ceil($total / $perPage));
if ($page > $tp) $page = $tp;
$offset = ($page - 1) * $perPage;

$us = mysqli_prepare($con, "SELECT r.email,r.fullname,r.profile_picture,COUNT(a.id) AS ac FROM registration r INNER JOIN addresses a ON a.user_id=r.email GROUP BY r.email,r.fullname,r.profile_picture $hs ORDER BY r.fullname LIMIT ?,?");
if ($search !== '') mysqli_stmt_bind_param($us, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($us, 'ii', $offset, $perPage);
mysqli_stmt_execute($us);
$users = mysqli_stmt_get_result($us)->fetch_all(MYSQLI_ASSOC);

$uAddrs = [];
foreach ($users as $u) {
  $em = $u['email'];
  $is = mysqli_prepare($con, 'SELECT * FROM addresses WHERE user_id=? ORDER BY id DESC');
  mysqli_stmt_bind_param($is, 's', $em);
  mysqli_stmt_execute($is);
  $uAddrs[$em] = mysqli_stmt_get_result($is)->fetch_all(MYSQLI_ASSOC);
  mysqli_stmt_close($is);
}

$allUsers = [];
$ar = mysqli_query($con, 'SELECT email,fullname FROM registration ORDER BY fullname ASC');
if ($ar) while ($u = mysqli_fetch_assoc($ar)) $allUsers[] = $u;

$title = 'Admin Addresses - JK Store';
$admin_active = 'addresses';
$admin_page_title = 'Address Management';
ob_start();
?>
<div class="page-card">
  <div class="products-header d-flex align-items-center justify-content-between gap-2">
    <div>
      <h5 class="mb-0 fw-bold">Address Management</h5>
      <small class="text-muted"><?= (int)$total ?> users with saved addresses</small>
    </div>
  </div>
  <div class="products-body">
    <form method="get" class="mb-3" novalidate>
      <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by user email or name..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </form>
    <?php if (empty($users)): ?><div class="text-center text-muted py-5">No addresses found.</div>
    <?php else: ?>
      <div class="accordion" id="addrAccordion">
        <?php foreach ($users as $i => $user): $em = $user['email'];
          $aid = 'addr_' . md5($em);
          $av = !empty($user['profile_picture']) ? 'images/profile_pictures/' . $user['profile_picture'] : 'images/profile_pictures/default.png';
          $items = $uAddrs[$em] ?? []; ?>
          <div class="accordion-item border mb-2 rounded overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ab_<?= $aid ?>">
                <div class="d-flex align-items-center gap-3 w-100 pe-2">
                  <img src="<?= htmlspecialchars($av, ENT_QUOTES, 'UTF-8') ?>" class="avatar-36" alt="">
                  <div class="flex-fill-min">
                    <div class="fw-bold"><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></div>
                  </div>
                  <span class="badge text-bg-info ms-auto shrink-0"><i class="fas fa-map-marker-alt me-1"></i><?= (int)$user['ac'] ?> addr</span>
                </div>
              </button>
            </h2>
            <div id="ab_<?= $aid ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#addrAccordion">
              <div class="accordion-body p-0">
                <div class="d-flex justify-content-end p-2 bg-light border-bottom">
                  <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAM_<?= $aid ?>"><i class="fas fa-plus me-1"></i>Add Address</button>
                </div>
                <div class="p-3">
                  <div class="row g-3">
                    <?php if (empty($items)): ?><div class="col-12 text-center text-muted">No addresses.</div>
                      <?php else: foreach ($items as $a): ?>
                        <div class="col-md-6">
                          <div class="border rounded p-3 h-100">
                            <div class="fw-semibold"><?= htmlspecialchars($a['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($a['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small"><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($a['mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small mt-1"><i class="fas fa-map-marker-alt text-muted me-1"></i><?= nl2br(htmlspecialchars($a['address'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
                            <div class="d-flex gap-1 mt-2">
                              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#eaM_<?= (int)$a['id'] ?>"><i class="fas fa-pen"></i></button>
                              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#daM_<?= (int)$a['id'] ?>"><i class="fas fa-trash"></i></button>
                            </div>
                          </div>
                        </div>
                        <div class="modal fade" id="eaM_<?= (int)$a['id'] ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="post" novalidate>
                                <div class="modal-header">
                                  <h5 class="modal-title">Edit Address</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                  <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int)$page ?>">
                                  <div class="mb-2"><label class="form-label">User <span class="text-danger">*</span></label>
                                    <select class="form-select" name="user_id" required data-validation="required,select" data-error="#ea_u_<?= (int)$a['id'] ?>">
                                      <option value="">Select</option>
                                      <?php foreach ($allUsers as $u2): ?>
                                        <option value="<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>" <?= ($a['user_id'] ?? '') === $u2['email'] ? 'selected' : '' ?>><?= htmlspecialchars($u2['fullname'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>)</option>
                                      <?php endforeach; ?>
                                    </select><small id="ea_u_<?= (int)$a['id'] ?>"></small>
                                  </div>
                                  <div class="mb-2"><label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($a['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#ea_n_<?= (int)$a['id'] ?>"><small id="ea_n_<?= (int)$a['id'] ?>"></small>
                                  </div>
                                  <div class="mb-2"><label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($a['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,email" data-error="#ea_e_<?= (int)$a['id'] ?>"><small id="ea_e_<?= (int)$a['id'] ?>"></small>
                                  </div>
                                  <div class="mb-2"><label class="form-label">Mobile <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($a['mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,max" data-max="15" data-error="#ea_m_<?= (int)$a['id'] ?>"><small id="ea_m_<?= (int)$a['id'] ?>"></small>
                                  </div>
                                  <div class="mb-1"><label class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="address" rows="3" required data-validation="required,min" data-min="5" data-error="#ea_a_<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea><small id="ea_a_<?= (int)$a['id'] ?>"></small>
                                  </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                              </form>
                            </div>
                          </div>
                        </div>
                        <div class="modal fade" id="daM_<?= (int)$a['id'] ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="post" novalidate>
                                <div class="modal-header">
                                  <h5 class="modal-title">Delete Address</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <p>Delete address for <strong><?= htmlspecialchars($a['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>?</p>
                                  <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                  <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int)$page ?>">
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endforeach;
                    endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="addAM_<?= $aid ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="post" novalidate>
                  <div class="modal-header">
                    <h5 class="modal-title">Add Address — <?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int)$page ?>">
                    <div class="mb-2"><label class="form-label">User <span class="text-danger">*</span></label>
                      <select class="form-select" name="user_id" required data-validation="required,select" data-error="#aa_u_<?= $aid ?>">
                        <option value="">Select</option>
                        <?php foreach ($allUsers as $u2): ?>
                          <option value="<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>" <?= $u2['email'] === $em ? 'selected' : '' ?>><?= htmlspecialchars($u2['fullname'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>)</option>
                        <?php endforeach; ?>
                      </select><small id="aa_u_<?= $aid ?>"></small>
                    </div>
                    <div class="mb-2"><label class="form-label">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="name" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#aa_n_<?= $aid ?>"><small id="aa_n_<?= $aid ?>"></small>
                    </div>
                    <div class="mb-2"><label class="form-label">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,email" data-error="#aa_e_<?= $aid ?>"><small id="aa_e_<?= $aid ?>"></small>
                    </div>
                    <div class="mb-2"><label class="form-label">Mobile <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="mobile" required data-validation="required,max" data-max="15" data-error="#aa_m_<?= $aid ?>"><small id="aa_m_<?= $aid ?>"></small>
                    </div>
                    <div class="mb-1"><label class="form-label">Address <span class="text-danger">*</span></label>
                      <textarea class="form-control" name="address" rows="3" required data-validation="required,min" data-min="5" data-error="#aa_a_<?= $aid ?>"></textarea><small id="aa_a_<?= $aid ?>"></small>
                    </div>
                  </div>
                  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Add Address</button></div>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <nav class="products-pagination mt-4">
        <div class="products-pagination-meta">Page <?= (int)$page ?> of <?= (int)$tp ?> · <?= (int)$total ?> users</div>
        <ul class="products-pagination-list">
          <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li>
          <?php for ($p = 1; $p <= $tp; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?>
          <li class="products-pagination-item <?= $page >= $tp ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
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
        window.location.href = 'admin_addresses.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
      }, 400);
    });
  });
</script>
<?php
mysqli_stmt_close($us);
$admin_content = ob_get_clean();
include 'admin_layout.php';
