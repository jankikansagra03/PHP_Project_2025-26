<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_contact_info.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    $companyName = trim($_POST['company_name'] ?? '');
    $tagline = trim($_POST['tagline'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $alternatePhone = trim($_POST['alternate_phone'] ?? '');
    $whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $facebookUrl = trim($_POST['facebook_url'] ?? '');
    $twitterUrl = trim($_POST['twitter_url'] ?? '');
    $instagramUrl = trim($_POST['instagram_url'] ?? '');
    $linkedinUrl = trim($_POST['linkedin_url'] ?? '');
    $youtubeUrl = trim($_POST['youtube_url'] ?? '');
    $mapEmbedUrl = trim($_POST['map_embed_url'] ?? '');

    if ($action === 'create') {
        if ($companyName === '' || $email === '' || $phone === '' || $address === '') {
            setcookie('error', 'Please fill required fields.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'INSERT INTO contact_info (company_name, tagline, email, phone, alternate_phone, whatsapp_number, address, city, state, country, postal_code, facebook_url, twitter_url, instagram_url, linkedin_url, youtube_url, map_embed_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssssssssssssssss', $companyName, $tagline, $email, $phone, $alternatePhone, $whatsappNumber, $address, $city, $state, $country, $postalCode, $facebookUrl, $twitterUrl, $instagramUrl, $linkedinUrl, $youtubeUrl, $mapEmbedUrl);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Contact info created.', time() + 5, '/');
        else setcookie('error', 'Failed to create contact info.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $companyName === '' || $email === '' || $phone === '' || $address === '') {
            setcookie('error', 'Invalid update request.', time() + 5, '/');
            header('Location: ' . $redirectUrl);
            exit();
        }
        $stmt = mysqli_prepare($con, 'UPDATE contact_info SET company_name=?, tagline=?, email=?, phone=?, alternate_phone=?, whatsapp_number=?, address=?, city=?, state=?, country=?, postal_code=?, facebook_url=?, twitter_url=?, instagram_url=?, linkedin_url=?, youtube_url=?, map_embed_url=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssssssssssssssssi', $companyName, $tagline, $email, $phone, $alternatePhone, $whatsappNumber, $address, $city, $state, $country, $postalCode, $facebookUrl, $twitterUrl, $instagramUrl, $linkedinUrl, $youtubeUrl, $mapEmbedUrl, $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Contact info updated.', time() + 5, '/');
        else setcookie('error', 'Failed to update contact info.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($con, 'DELETE FROM contact_info WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) setcookie('success', 'Contact info deleted.', time() + 5, '/');
        else setcookie('error', 'Failed to delete contact info.', time() + 5, '/');
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = $search !== '' ? ' WHERE company_name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?' : '';
$like = '%' . $search . '%';

$countStmt = mysqli_prepare($con, 'SELECT COUNT(*) AS total FROM contact_info' . $where);
if ($search !== '') mysqli_stmt_bind_param($countStmt, 'ssss', $like, $like, $like, $like);
mysqli_stmt_execute($countStmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'] ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$listStmt = mysqli_prepare($con, 'SELECT * FROM contact_info' . $where . ' ORDER BY id DESC LIMIT ?, ?');
if ($search !== '') mysqli_stmt_bind_param($listStmt, 'ssssii', $like, $like, $like, $like, $offset, $perPage);
else mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
mysqli_stmt_execute($listStmt);
$result = mysqli_stmt_get_result($listStmt);

$title = 'Admin Contact Info - JK Store';
$admin_active = 'contact_info';
$admin_page_title = 'Contact Info';

ob_start();
?>
<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Contact Information</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editContactInfoModal"><i class="fas fa-pen me-1"></i><?= $info ? 'Edit Details' : 'Add Details' ?></button>
    </div>
    <div class="products-body">
        <?php if ($info): ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold">Company</h6>
                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars((string) ($info['company_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mb-1"><strong>Tagline:</strong> <?= htmlspecialchars((string) ($info['tagline'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars((string) ($info['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold">Phone & Address</h6>
                        <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars((string) ($info['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mb-1"><strong>WhatsApp:</strong> <?= htmlspecialchars((string) ($info['whatsapp_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mb-0"><strong>Address:</strong> <?= nl2br(htmlspecialchars((string) ($info['address'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                </div>
                <div class="col-12">
                    <div class="border rounded p-3">
                        <h6 class="fw-bold">Social Links</h6>
                        <div class="row g-2">
                            <div class="col-md-4 small"><strong>Facebook:</strong> <?= htmlspecialchars((string) ($info['facebook_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="col-md-4 small"><strong>Twitter:</strong> <?= htmlspecialchars((string) ($info['twitter_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="col-md-4 small"><strong>Instagram:</strong> <?= htmlspecialchars((string) ($info['instagram_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">No contact information has been configured yet.</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="editContactInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <h5 class="mb-0 fw-bold">Contact Information</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addContactInfoModal"><i class="fas fa-plus me-1"></i>Add Contact Info</button>
        <div class="modal-header">
            <h5 class="modal-title"><?= $info ? 'Edit Contact Information' : 'Add Contact Information' ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            <form method="get" class="mb-3" novalidate>
                <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by company, email, phone, city..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle products-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= (int) ($row['id'] ?? 0) ?></td>
                                    <td>
                                        <div class="fw-semibold small"><?= htmlspecialchars((string) ($row['company_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars((string) ($row['tagline'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <div class="small"><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars((string) ($row['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <div class="small"><?= htmlspecialchars((string) ($row['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($row['state']) ? ', ' . htmlspecialchars((string) $row['state'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars((string) ($row['country'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td><small><?= htmlspecialchars((string) ($row['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                                    <td>
                                        <div class="products-actions d-flex gap-1"><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editContactInfoModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteContactInfoModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button></div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editContactInfoModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <form method="post" novalidate>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Contact Info #<?= (int) $row['id'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                    <div class="row g-2">
                                                        <div class="col-md-6"><label class="form-label">Company Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars((string) ($row['company_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="200" data-error="#ci_cn_<?= (int) $row['id'] ?>"><small id="ci_cn_<?= (int) $row['id'] ?>"></small></div>
                                                        <div class="col-md-6"><label class="form-label">Tagline</label><input type="text" class="form-control" name="tagline" value="<?= htmlspecialchars((string) ($row['tagline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    </div>
                                                    <div class="row g-2 mt-1">
                                                        <div class="col-md-4"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,email" data-error="#ci_em_<?= (int) $row['id'] ?>"><small id="ci_em_<?= (int) $row['id'] ?>"></small></div>
                                                        <div class="col-md-4"><label class="form-label">Phone <span class="text-danger">*</span></label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars((string) ($row['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,max" data-max="20" data-error="#ci_ph_<?= (int) $row['id'] ?>"><small id="ci_ph_<?= (int) $row['id'] ?>"></small></div>
                                                        <div class="col-md-4"><label class="form-label">Alternate Phone</label><input type="text" class="form-control" name="alternate_phone" value="<?= htmlspecialchars((string) ($row['alternate_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    </div>
                                                    <div class="row g-2 mt-1">
                                                        <div class="col-md-4"><label class="form-label">WhatsApp</label><input type="text" class="form-control" name="whatsapp_number" value="<?= htmlspecialchars((string) ($row['whatsapp_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                        <div class="col-md-4"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars((string) ($row['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                        <div class="col-md-4"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="<?= htmlspecialchars((string) ($row['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    </div>
                                                    <div class="row g-2 mt-1">
                                                        <div class="col-md-6"><label class="form-label">Country</label><input type="text" class="form-control" name="country" value="<?= htmlspecialchars((string) ($row['country'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                        <div class="col-md-6"><label class="form-label">Postal Code</label><input type="text" class="form-control" name="postal_code" value="<?= htmlspecialchars((string) ($row['postal_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    </div>
                                                    <div class="mt-2"><label class="form-label">Address <span class="text-danger">*</span></label><textarea class="form-control" name="address" rows="3" required data-validation="required,min" data-min="5" data-error="#ci_ad_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small id="ci_ad_<?= (int) $row['id'] ?>"></small></div>
                                                    <hr>
                                                    <div class="row g-2">
                                                        <div class="col-md-4"><label class="form-label">Facebook URL</label><input type="text" class="form-control" name="facebook_url" value="<?= htmlspecialchars((string) ($row['facebook_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                        <div class="col-md-4"><label class="form-label">Twitter URL</label><input type="text" class="form-control" name="twitter_url" value="<?= htmlspecialchars((string) ($row['twitter_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                        <div class="col-md-4"><label class="form-label">Instagram URL</label><input type="text" class="form-control" name="instagram_url" value="<?= htmlspecialchars((string) ($row['instagram_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    </div>
                                                    <div class="row g-2 mt-1">
                                                        <div class="col-md-6"><label class="form-label">LinkedIn URL</label><input type="text" class="form-control" name="linkedin_url" value="<?= htmlspecialchars((string) ($row['linkedin_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                        <div class="col-md-6"><label class="form-label">YouTube URL</label><input type="text" class="form-control" name="youtube_url" value="<?= htmlspecialchars((string) ($row['youtube_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                                    </div>
                                                    <div class="mt-2"><label class="form-label">Map Embed URL</label><textarea class="form-control" name="map_embed_url" rows="2"><?= htmlspecialchars((string) ($row['map_embed_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                                                </div>
                                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="deleteContactInfoModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" novalidate>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Contact Info</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Delete contact info for <strong><?= htmlspecialchars((string) ($row['company_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?</p><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                                </div>
                                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No contact info records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav class="products-pagination mt-3" aria-label="Contact info pagination">
                <div class="products-pagination-meta">Page <?= (int) $page ?> of <?= (int) $totalPages ?> · <?= (int) $total ?> total</div>
                <ul class="products-pagination-list">
                    <li class="products-pagination-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-chevron-left me-1 small"></i>Prev</a></li><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="products-pagination-item <?= $p === $page ? 'active' : '' ?>"><a class="products-pagination-link" href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $p ?></a></li><?php endfor; ?><li class="products-pagination-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="products-pagination-link is-nav" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next<i class="fas fa-chevron-right ms-1 small"></i></a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="modal fade" id="addContactInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">Add Contact Info</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="create"><input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_page" value="<?= (int) $page ?>">
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label">Company Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="company_name" required data-validation="required,min,max" data-min="2" data-max="200" data-error="#aci_cn"><small id="aci_cn"></small></div>
                            <div class="col-md-6"><label class="form-label">Tagline</label><input type="text" class="form-control" name="tagline"></div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-4"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" required data-validation="required,email" data-error="#aci_em"><small id="aci_em"></small></div>
                            <div class="col-md-4"><label class="form-label">Phone <span class="text-danger">*</span></label><input type="text" class="form-control" name="phone" required data-validation="required,max" data-max="20" data-error="#aci_ph"><small id="aci_ph"></small></div>
                            <div class="col-md-4"><label class="form-label">Alternate Phone</label><input type="text" class="form-control" name="alternate_phone"></div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-4"><label class="form-label">WhatsApp</label><input type="text" class="form-control" name="whatsapp_number"></div>
                            <div class="col-md-4"><label class="form-label">City</label><input type="text" class="form-control" name="city"></div>
                            <div class="col-md-4"><label class="form-label">State</label><input type="text" class="form-control" name="state"></div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6"><label class="form-label">Country</label><input type="text" class="form-control" name="country"></div>
                            <div class="col-md-6"><label class="form-label">Postal Code</label><input type="text" class="form-control" name="postal_code"></div>
                        </div>
                        <div class="mt-2"><label class="form-label">Address <span class="text-danger">*</span></label><textarea class="form-control" name="address" rows="3" required data-validation="required,min" data-min="5" data-error="#aci_ad"></textarea><small id="aci_ad"></small></div>
                        <hr>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Facebook URL</label><input type="text" class="form-control" name="facebook_url"></div>
                            <div class="col-md-4"><label class="form-label">Twitter URL</label><input type="text" class="form-control" name="twitter_url"></div>
                            <div class="col-md-4"><label class="form-label">Instagram URL</label><input type="text" class="form-control" name="instagram_url"></div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6"><label class="form-label">LinkedIn URL</label><input type="text" class="form-control" name="linkedin_url"></div>
                            <div class="col-md-6"><label class="form-label">YouTube URL</label><input type="text" class="form-control" name="youtube_url"></div>
                        </div>
                        <div class="mt-2"><label class="form-label">Map Embed URL</label><textarea class="form-control" name="map_embed_url" rows="2"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Create</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4"><label class="form-label">Instagram URL</label><input type="text" class="form-control" name="instagram_url" value="<?= htmlspecialchars((string) ($info['instagram_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
</div>
<div class="row g-2 mt-1">
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
                    window.location.href = 'admin_contact_info.php?page=1' + (val ? '&search=' + encodeURIComponent(val) : '');
                }, 400);
            });
        });
    </script>
    <div class="col-md-6"><label class="form-label">LinkedIn URL</label><input type="text" class="form-control" name="linkedin_url" value="<?= htmlspecialchars((string) ($info['linkedin_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
    mysqli_stmt_close($listStmt);
    <div class="col-md-6"><label class="form-label">YouTube URL</label><input type="text" class="form-control" name="youtube_url" value="<?= htmlspecialchars((string) ($info['youtube_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
</div>
<div class="mt-2"><label class="form-label">Map Embed URL</label><textarea class="form-control" name="map_embed_url" rows="2"><?= htmlspecialchars((string) ($info['map_embed_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
</form>
</div>
</div>
</div>

<script src="js/jquery.js"></script>
<script src="js/validate.js"></script>
<?php
$admin_content = ob_get_clean();
include 'admin_layout.php';
