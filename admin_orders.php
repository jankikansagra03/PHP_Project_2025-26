<?php
include_once 'db_config.php';

function user_exists_by_email(mysqli $con, string $email): bool
{
  $stmt = mysqli_prepare($con, 'SELECT 1 FROM registration WHERE email = ? LIMIT 1');
  if (!$stmt) return false;
  mysqli_stmt_bind_param($stmt, 's', $email);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $ok = $res && mysqli_num_rows($res) > 0;
  mysqli_stmt_close($stmt);
  return $ok;
}

function generate_unique_order_number(mysqli $con): string
{
  for ($i = 0; $i < 5; $i++) {
    $candidate = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = mysqli_prepare($con, 'SELECT 1 FROM orders WHERE order_number = ? LIMIT 1');
    if (!$stmt) continue;
    mysqli_stmt_bind_param($stmt, 's', $candidate);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $exists = $res && mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    if (!$exists) return $candidate;
  }
  return 'ORD-' . strtoupper(bin2hex(random_bytes(6)));
}

function valid_order_status(string $status): bool
{
  return in_array($status, ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned'], true);
}

function valid_payment_status(string $status): bool
{
  return in_array($status, ['Pending', 'Paid', 'Failed', 'Refunded'], true);
}

function valid_payment_method(string $method): bool
{
  return in_array($method, ['razorpay', 'cod'], true);
}

if (isset($_POST['action'])) {
  $action = $_POST['action'];
  $rs = trim($_POST['return_search'] ?? '');
  $rp = max(1, (int)($_POST['return_page'] ?? 1));
  $to = 'admin_orders.php?page=' . $rp . ($rs !== '' ? '&search=' . urlencode($rs) : '');

  if ($action === 'create') {
    $num = generate_unique_order_number($con);
    $ue = trim($_POST['user_email'] ?? '');
    $dn = trim($_POST['delivery_name'] ?? '');
    $de = trim($_POST['delivery_email'] ?? '');
    $dm = trim($_POST['delivery_mobile'] ?? '');
    $da = trim($_POST['delivery_address'] ?? '');
    $sub = (float)($_POST['subtotal'] ?? 0);
    $disc = (float)($_POST['discount'] ?? 0);
    $ship = (float)($_POST['shipping_fee'] ?? 0);
    $tot = round(($sub - $disc + $ship), 2);
    $pm = trim($_POST['payment_method'] ?? 'cod');
    $ps = trim($_POST['payment_status'] ?? 'Pending');
    $os = trim($_POST['order_status'] ?? 'Pending');
    $an = trim($_POST['admin_notes'] ?? '');

    if ($ue === '' || !filter_var($ue, FILTER_VALIDATE_EMAIL) || !user_exists_by_email($con, $ue) || $dn === '' || mb_strlen($dn) > 100 || $de === '' || !filter_var($de, FILTER_VALIDATE_EMAIL) || $dm === '' || mb_strlen($dm) > 15 || $da === '' || $sub < 0 || $disc < 0 || $ship < 0 || $tot < 0 || !valid_payment_method($pm) || !valid_payment_status($ps) || !valid_order_status($os)) {
      setcookie('error', 'Please provide valid order details.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    if ($os === 'Delivered' && $ps !== 'Paid') {
      setcookie('error', 'Delivered orders must have payment status Paid.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }

    $s = mysqli_prepare($con, 'INSERT INTO orders (order_number,user_email,delivery_name,delivery_email,delivery_mobile,delivery_address,subtotal,discount,shipping_fee,total_amount,payment_method,payment_status,order_status,admin_notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    if (!$s) {
      setcookie('error', 'Failed to create order.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    mysqli_stmt_bind_param($s, 'ssssssddddssss', $num, $ue, $dn, $de, $dm, $da, $sub, $disc, $ship, $tot, $pm, $ps, $os, $an);
    if (mysqli_stmt_execute($s)) setcookie('success', 'Order created.', time() + 5, '/');
    else setcookie('error', 'Failed to create order.', time() + 5, '/');
    mysqli_stmt_close($s);
    header('Location:' . $to);
    exit();
  }
  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $ue = trim($_POST['user_email'] ?? '');
    $dn = trim($_POST['delivery_name'] ?? '');
    $de = trim($_POST['delivery_email'] ?? '');
    $dm = trim($_POST['delivery_mobile'] ?? '');
    $da = trim($_POST['delivery_address'] ?? '');
    $sub = (float)($_POST['subtotal'] ?? 0);
    $disc = (float)($_POST['discount'] ?? 0);
    $ship = (float)($_POST['shipping_fee'] ?? 0);
    $tot = round(($sub - $disc + $ship), 2);
    $pm = trim($_POST['payment_method'] ?? 'cod');
    $ps = trim($_POST['payment_status'] ?? 'Pending');
    $os = trim($_POST['order_status'] ?? 'Pending');
    $an = trim($_POST['admin_notes'] ?? '');
    $cr = trim($_POST['cancellation_reason'] ?? '');

    if ($id <= 0 || $ue === '' || !filter_var($ue, FILTER_VALIDATE_EMAIL) || !user_exists_by_email($con, $ue) || $dn === '' || mb_strlen($dn) > 100 || $de === '' || !filter_var($de, FILTER_VALIDATE_EMAIL) || $dm === '' || mb_strlen($dm) > 15 || $da === '' || $sub < 0 || $disc < 0 || $ship < 0 || $tot < 0 || !valid_payment_method($pm) || !valid_payment_status($ps) || !valid_order_status($os)) {
      setcookie('error', 'Invalid order update request.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    if ($os === 'Delivered' && $ps !== 'Paid') {
      setcookie('error', 'Delivered orders must have payment status Paid.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    if ($os !== 'Cancelled') {
      $cr = '';
    }

    $s = mysqli_prepare($con, 'UPDATE orders SET user_email=?,delivery_name=?,delivery_email=?,delivery_mobile=?,delivery_address=?,subtotal=?,discount=?,shipping_fee=?,total_amount=?,payment_method=?,payment_status=?,order_status=?,admin_notes=?,cancellation_reason=? WHERE id=?');
    if (!$s) {
      setcookie('error', 'Failed to update order.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    mysqli_stmt_bind_param($s, 'sssssddddsssss' . 'i', $ue, $dn, $de, $dm, $da, $sub, $disc, $ship, $tot, $pm, $ps, $os, $an, $cr, $id);
    if (mysqli_stmt_execute($s)) setcookie('success', 'Order updated.', time() + 5, '/');
    else setcookie('error', 'Failed.', time() + 5, '/');
    mysqli_stmt_close($s);
    header('Location:' . $to);
    exit();
  }
  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      setcookie('error', 'Invalid delete request.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    $s = mysqli_prepare($con, 'DELETE FROM orders WHERE id=?');
    if (!$s) {
      setcookie('error', 'Failed.', time() + 5, '/');
      header('Location:' . $to);
      exit();
    }
    mysqli_stmt_bind_param($s, 'i', $id);
    if (mysqli_stmt_execute($s)) setcookie('success', 'Order deleted.', time() + 5, '/');
    else setcookie('error', 'Failed.', time() + 5, '/');
    mysqli_stmt_close($s);
    header('Location:' . $to);
    exit();
  }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$like = '%' . $search . '%';
$hs = $search !== '' ? " HAVING o.user_email LIKE ? OR r.fullname LIKE ?" : "";
$cs = mysqli_prepare($con, "SELECT COUNT(*) AS t FROM (SELECT o.user_email FROM orders o LEFT JOIN registration r ON r.email=o.user_email GROUP BY o.user_email $hs) AS u");
if ($search !== '') mysqli_stmt_bind_param($cs, 'ss', $like, $like);
mysqli_stmt_execute($cs);
$total = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['t'] ?? 0);
mysqli_stmt_close($cs);
$tp = max(1, (int)ceil($total / $perPage));
if ($page > $tp) $page = $tp;
$offset = ($page - 1) * $perPage;

$us = mysqli_prepare($con, "SELECT o.user_email, r.fullname, r.profile_picture, COUNT(o.id) AS order_count, SUM(o.total_amount) AS lifetime_value, MAX(o.order_date) AS last_order FROM orders o LEFT JOIN registration r ON r.email=o.user_email GROUP BY o.user_email,r.fullname,r.profile_picture $hs ORDER BY last_order DESC LIMIT ?,?");
if ($search !== '') mysqli_stmt_bind_param($us, 'ssii', $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($us, 'ii', $offset, $perPage);
mysqli_stmt_execute($us);
$users = mysqli_stmt_get_result($us)->fetch_all(MYSQLI_ASSOC);

$uOrders = [];
foreach ($users as $u) {
  $em = $u['user_email'];
  $os = mysqli_prepare($con, 'SELECT * FROM orders WHERE user_email=? ORDER BY order_date DESC');
  mysqli_stmt_bind_param($os, 's', $em);
  mysqli_stmt_execute($os);
  $uOrders[$em] = mysqli_stmt_get_result($os)->fetch_all(MYSQLI_ASSOC);
  mysqli_stmt_close($os);
}

$allUsers = [];
$ar = mysqli_query($con, 'SELECT email,fullname FROM registration ORDER BY fullname ASC');
if ($ar) while ($u = mysqli_fetch_assoc($ar)) $allUsers[] = $u;

$statusColors = ['Pending' => 'warning', 'Confirmed' => 'primary', 'Processing' => 'info', 'Shipped' => 'primary', 'Delivered' => 'success', 'Cancelled' => 'danger', 'Returned' => 'secondary'];
$payColors = ['Pending' => 'warning', 'Paid' => 'success', 'Failed' => 'danger', 'Refunded' => 'info'];

$title = 'Admin Orders - JK Store';
$admin_active = 'orders';
$admin_page_title = 'Order Management';
ob_start();
?>
<div class="page-card">
  <div class="products-header d-flex align-items-center justify-content-between gap-2">
    <div>
      <h5 class="mb-0 fw-bold">Order Management</h5>
      <small class="text-muted"><?= (int)$total ?> customers with orders</small>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrderModal"><i class="fas fa-plus me-1"></i>New Order</button>
  </div>
  <div class="products-body">
    <form method="get" class="mb-3" novalidate>
      <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by user email or name..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </form>
    <?php if (empty($users)): ?><div class="text-center text-muted py-5">No orders found.</div>
    <?php else: ?>
      <div class="accordion" id="ordAccordion">
        <?php foreach ($users as $i => $user): $em = $user['user_email'];
          $aid = 'ord_' . md5($em);
          $av = !empty($user['profile_picture']) ? 'images/profile_pictures/' . $user['profile_picture'] : 'images/profile_pictures/default.png';
          $orders = $uOrders[$em] ?? []; ?>
          <div class="accordion-item border mb-2 rounded overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ob_<?= $aid ?>">
                <div class="d-flex align-items-center gap-3 w-100 pe-2">
                  <img src="<?= htmlspecialchars($av, ENT_QUOTES, 'UTF-8') ?>" class="avatar-36" alt="">
                  <div class="flex-fill-min">
                    <div class="fw-bold"><?= htmlspecialchars($user['fullname'] ?? $em, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></div>
                  </div>
                  <div class="d-flex gap-2 ms-auto shrink-0">
                    <span class="badge text-bg-primary"><?= (int)$user['order_count'] ?> orders</span>
                    <span class="badge text-bg-success">₹<?= number_format((float)$user['lifetime_value'], 2) ?></span>
                  </div>
                </div>
              </button>
            </h2>
            <div id="ob_<?= $aid ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#ordAccordion">
              <div class="accordion-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered align-middle mb-0 small">
                    <thead class="table-light">
                      <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($orders)): ?>
                        <tr>
                          <td colspan="6" class="text-center text-muted py-3">No orders.</td>
                        </tr>
                        <?php else: foreach ($orders as $o): $sc = $statusColors[$o['order_status'] ?? 'Pending'] ?? 'secondary';
                          $pc = $payColors[$o['payment_status'] ?? 'Pending'] ?? 'secondary'; ?>
                          <tr>
                            <td><code><?= htmlspecialchars($o['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><small><?= htmlspecialchars(substr($o['order_date'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td class="fw-semibold">₹<?= number_format((float)($o['total_amount'] ?? 0), 2) ?></td>
                            <td><span class="badge text-bg-<?= $pc ?>"><?= htmlspecialchars($o['payment_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><br><small class="text-muted"><?= strtoupper(htmlspecialchars($o['payment_method'] ?? '', ENT_QUOTES, 'UTF-8')) ?></small></td>
                            <td><span class="badge text-bg-<?= $sc ?>"><?= htmlspecialchars($o['order_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                              <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewOM_<?= (int)$o['id'] ?>" title="View" aria-label="View order"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editOM_<?= (int)$o['id'] ?>" title="Edit" aria-label="Edit order"><i class="fas fa-pen"></i></button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delOM_<?= (int)$o['id'] ?>" title="Delete" aria-label="Delete order"><i class="fas fa-trash"></i></button>
                              </div>
                            </td>
                          </tr>
                          <!-- View Modal -->
                          <div class="modal fade" id="viewOM_<?= (int)$o['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title">Order <?= htmlspecialchars($o['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <div class="row g-3">
                                    <div class="col-md-6">
                                      <h6 class="fw-bold text-muted small text-uppercase">Delivery Info</h6>
                                      <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($o['delivery_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                      <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($o['delivery_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                      <p class="mb-1"><strong>Mobile:</strong> <?= htmlspecialchars($o['delivery_mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                      <p class="mb-0"><strong>Address:</strong><br><?= nl2br(htmlspecialchars($o['delivery_address'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                      <h6 class="fw-bold text-muted small text-uppercase">Payment & Status</h6>
                                      <p class="mb-1"><strong>Method:</strong> <?= strtoupper(htmlspecialchars($o['payment_method'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                      <p class="mb-1"><strong>Payment:</strong> <span class="badge text-bg-<?= $pc ?>"><?= htmlspecialchars($o['payment_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></p>
                                      <p class="mb-1"><strong>Order Status:</strong> <span class="badge text-bg-<?= $sc ?>"><?= htmlspecialchars($o['order_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></p>
                                      <p class="mb-1"><strong>Date:</strong> <?= htmlspecialchars($o['order_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                    <div class="col-12">
                                      <h6 class="fw-bold text-muted small text-uppercase">Amounts</h6>
                                      <table class="table table-sm table-bordered mb-0">
                                        <tr>
                                          <td>Subtotal</td>
                                          <td class="text-end">₹<?= number_format((float)($o['subtotal'] ?? 0), 2) ?></td>
                                        </tr>
                                        <tr>
                                          <td>Discount</td>
                                          <td class="text-end text-danger">- ₹<?= number_format((float)($o['discount'] ?? 0), 2) ?></td>
                                        </tr>
                                        <tr>
                                          <td>Shipping</td>
                                          <td class="text-end">₹<?= number_format((float)($o['shipping_fee'] ?? 0), 2) ?></td>
                                        </tr>
                                        <tr class="fw-bold">
                                          <td>Total</td>
                                          <td class="text-end">₹<?= number_format((float)($o['total_amount'] ?? 0), 2) ?></td>
                                        </tr>
                                      </table>
                                    </div>
                                    <?php if (!empty($o['admin_notes'])): ?><div class="col-12"><strong>Admin Notes:</strong>
                                        <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($o['admin_notes'], ENT_QUOTES, 'UTF-8')) ?></p>
                                      </div><?php endif; ?>
                                    <?php if (!empty($o['cancellation_reason'])): ?><div class="col-12"><strong>Cancellation Reason:</strong>
                                        <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($o['cancellation_reason'], ENT_QUOTES, 'UTF-8')) ?></p>
                                      </div><?php endif; ?>
                                  </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                              </div>
                            </div>
                          </div>
                          <!-- Edit Modal -->
                          <div class="modal fade" id="editOM_<?= (int)$o['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                              <div class="modal-content">
                                <form method="post" novalidate>
                                  <div class="modal-header">
                                    <h5 class="modal-title">Edit Order <?= htmlspecialchars($o['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                  </div>
                                  <div class="modal-body modal-body-scroll">
                                    <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                    <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int)$page ?>">
                                    <div class="row g-3">
                                      <div class="col-md-6"><label class="form-label">User Email <span class="text-danger">*</span></label>
                                        <select class="form-select" name="user_email" required data-validation="required,select" data-error="#eo_ue_<?= (int)$o['id'] ?>">
                                          <option value="">Select</option>
                                          <?php foreach ($allUsers as $u2): ?><option value="<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>" <?= ($o['user_email'] ?? '') === $u2['email'] ? 'selected' : '' ?>><?= htmlspecialchars($u2['fullname'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
                                        </select><small id="eo_ue_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-6"><label class="form-label">Delivery Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="delivery_name" value="<?= htmlspecialchars($o['delivery_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#eo_dn_<?= (int)$o['id'] ?>"><small id="eo_dn_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-6"><label class="form-label">Delivery Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="delivery_email" value="<?= htmlspecialchars($o['delivery_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,email" data-error="#eo_de_<?= (int)$o['id'] ?>"><small id="eo_de_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-6"><label class="form-label">Delivery Mobile <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="delivery_mobile" value="<?= htmlspecialchars($o['delivery_mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,max" data-max="15" data-error="#eo_dm_<?= (int)$o['id'] ?>"><small id="eo_dm_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-12"><label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="delivery_address" rows="2" required data-validation="required,min" data-min="5" data-error="#eo_da_<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['delivery_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea><small id="eo_da_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-4"><label class="form-label">Subtotal (₹)<span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" name="subtotal" value="<?= number_format((float)($o['subtotal'] ?? 0), 2, '.', '') ?>" required data-validation="required,number" data-error="#eo_sb_<?= (int)$o['id'] ?>"><small id="eo_sb_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-4"><label class="form-label">Discount (₹)</label>
                                        <input type="number" step="0.01" class="form-control" name="discount" value="<?= number_format((float)($o['discount'] ?? 0), 2, '.', '') ?>">
                                      </div>
                                      <div class="col-md-4"><label class="form-label">Shipping (₹)</label>
                                        <input type="number" step="0.01" class="form-control" name="shipping_fee" value="<?= number_format((float)($o['shipping_fee'] ?? 0), 2, '.', '') ?>">
                                      </div>
                                      <div class="col-md-4"><label class="form-label">Total (₹) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" name="total_amount" value="<?= number_format((float)($o['total_amount'] ?? 0), 2, '.', '') ?>" required data-validation="required,number" data-error="#eo_ta_<?= (int)$o['id'] ?>"><small id="eo_ta_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-4"><label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select" name="payment_method" required data-validation="required,select" data-error="#eo_pm_<?= (int)$o['id'] ?>">
                                          <option value="cod" <?= ($o['payment_method'] ?? '') === 'cod' ? 'selected' : '' ?>>COD</option>
                                          <option value="razorpay" <?= ($o['payment_method'] ?? '') === 'razorpay' ? 'selected' : '' ?>>Razorpay</option>
                                        </select><small id="eo_pm_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-4"><label class="form-label">Payment Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="payment_status" required data-validation="required,select" data-error="#eo_ps_<?= (int)$o['id'] ?>">
                                          <?php foreach (['Pending', 'Paid', 'Failed', 'Refunded'] as $pv): ?><option value="<?= $pv ?>" <?= ($o['payment_status'] ?? '') === $pv ? 'selected' : '' ?>><?= $pv ?></option><?php endforeach; ?>
                                        </select><small id="eo_ps_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-md-6"><label class="form-label">Order Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="order_status" required data-validation="required,select" data-error="#eo_os_<?= (int)$o['id'] ?>">
                                          <?php foreach (['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned'] as $ov): ?><option value="<?= $ov ?>" <?= ($o['order_status'] ?? '') === $ov ? 'selected' : '' ?>><?= $ov ?></option><?php endforeach; ?>
                                        </select><small id="eo_os_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-12"><label class="form-label">Admin Notes</label>
                                        <textarea class="form-control" name="admin_notes" rows="2" data-validation="max" data-max="500" data-error="#eo_an_<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['admin_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea><small id="eo_an_<?= (int)$o['id'] ?>"></small>
                                      </div>
                                      <div class="col-12"><label class="form-label">Cancellation Reason</label>
                                        <textarea class="form-control" name="cancellation_reason" rows="2"><?= htmlspecialchars($o['cancellation_reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update Order</button></div>
                                </form>
                              </div>
                            </div>
                          </div>
                          <!-- Delete Modal -->
                          <div class="modal fade" id="delOM_<?= (int)$o['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <form method="post" novalidate>
                                  <div class="modal-header">
                                    <h5 class="modal-title">Delete Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                  </div>
                                  <div class="modal-body">
                                    <p>Permanently delete order <strong><?= htmlspecialchars($o['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>? All order items will also be deleted.</p>
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                    <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int)$page ?>">
                                  </div>
                                  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
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
        <?php endforeach; ?>
      </div>
      <nav class="products-pagination mt-4">
        <div class="products-pagination-meta">Page <?= (int)$page ?> of <?= (int)$tp ?> · <?= (int)$total ?> customers</div>
        <ul class="products-pagination-list">
          <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li>
          <?php for ($p = 1; $p <= $tp; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?>
          <li class="products-pagination-item <?= $page >= $tp ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="post" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Create New Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body modal-body-scroll">
          <input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int)$page ?>">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Customer <span class="text-danger">*</span></label>
              <select class="form-select" name="user_email" required data-validation="required,select" data-error="#ao_ue">
                <option value="">Select customer</option>
                <?php foreach ($allUsers as $u2): ?><option value="<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($u2['fullname'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u2['email'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?>
              </select><small id="ao_ue"></small>
            </div>
            <div class="col-md-6"><label class="form-label">Delivery Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="delivery_name" required data-validation="required,min,max" data-min="2" data-max="100" data-error="#ao_dn"><small id="ao_dn"></small>
            </div>
            <div class="col-md-6"><label class="form-label">Delivery Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="delivery_email" required data-validation="required,email" data-error="#ao_de"><small id="ao_de"></small>
            </div>
            <div class="col-md-6"><label class="form-label">Delivery Mobile <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="delivery_mobile" required data-validation="required,max" data-max="15" data-error="#ao_dm"><small id="ao_dm"></small>
            </div>
            <div class="col-12"><label class="form-label">Delivery Address <span class="text-danger">*</span></label>
              <textarea class="form-control" name="delivery_address" rows="2" required data-validation="required,min" data-min="5" data-error="#ao_da"></textarea><small id="ao_da"></small>
            </div>
            <div class="col-md-4"><label class="form-label">Subtotal (₹) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" name="subtotal" value="0.00" required data-validation="required,number" data-error="#ao_sb"><small id="ao_sb"></small>
            </div>
            <div class="col-md-4"><label class="form-label">Discount (₹)</label>
              <input type="number" step="0.01" class="form-control" name="discount" value="0.00">
            </div>
            <div class="col-md-4"><label class="form-label">Shipping (₹)</label>
              <input type="number" step="0.01" class="form-control" name="shipping_fee" value="0.00">
            </div>
            <div class="col-md-4"><label class="form-label">Total (₹) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" name="total_amount" value="0.00" required data-validation="required,number" data-error="#ao_ta"><small id="ao_ta"></small>
            </div>
            <div class="col-md-4"><label class="form-label">Payment Method <span class="text-danger">*</span></label>
              <select class="form-select" name="payment_method" required data-validation="required,select" data-error="#ao_pm">
                <option value="cod">COD</option>
                <option value="razorpay">Razorpay</option>
              </select><small id="ao_pm"></small>
            </div>
            <div class="col-md-4"><label class="form-label">Payment Status</label>
              <select class="form-select" name="payment_status">
                <?php foreach (['Pending', 'Paid', 'Failed', 'Refunded'] as $pv): ?><option value="<?= $pv ?>"><?= $pv ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Order Status</label>
              <select class="form-select" name="order_status">
                <?php foreach (['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned'] as $ov): ?><option value="<?= $ov ?>"><?= $ov ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-12"><label class="form-label">Admin Notes</label>
              <textarea class="form-control" name="admin_notes" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Create Order</button></div>
      </form>
    </div>
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
        window.location.href = 'admin_orders.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
      }, 400);
    });
  });
</script>
<?php
mysqli_stmt_close($us);
$admin_content = ob_get_clean();
include 'admin_layout.php';
