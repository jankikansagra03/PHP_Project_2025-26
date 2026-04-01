<?php
include_once 'db_config.php';

if (isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = (int) ($_POST['id'] ?? 0);
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

    if ($id > 0) {
        $stmt = mysqli_prepare($con, 'UPDATE contact_info SET company_name=?, tagline=?, email=?, phone=?, alternate_phone=?, whatsapp_number=?, address=?, city=?, state=?, country=?, postal_code=?, facebook_url=?, twitter_url=?, instagram_url=?, linkedin_url=?, youtube_url=?, map_embed_url=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssssssssssssssssi', $companyName, $tagline, $email, $phone, $alternatePhone, $whatsappNumber, $address, $city, $state, $country, $postalCode, $facebookUrl, $twitterUrl, $instagramUrl, $linkedinUrl, $youtubeUrl, $mapEmbedUrl, $id);
    } else {
        $stmt = mysqli_prepare($con, 'INSERT INTO contact_info (company_name, tagline, email, phone, alternate_phone, whatsapp_number, address, city, state, country, postal_code, facebook_url, twitter_url, instagram_url, linkedin_url, youtube_url, map_embed_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssssssssssssssss', $companyName, $tagline, $email, $phone, $alternatePhone, $whatsappNumber, $address, $city, $state, $country, $postalCode, $facebookUrl, $twitterUrl, $instagramUrl, $linkedinUrl, $youtubeUrl, $mapEmbedUrl);
    }

    if (mysqli_stmt_execute($stmt)) setcookie('success', 'Contact information saved.', time() + 5, '/');
    else setcookie('error', 'Failed to save contact information.', time() + 5, '/');
    mysqli_stmt_close($stmt);
    header('Location: admin_contact_info.php');
    exit();
}

$infoRes = mysqli_query($con, 'SELECT * FROM contact_info ORDER BY id DESC LIMIT 1');
$info = $infoRes ? mysqli_fetch_assoc($infoRes) : null;

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
        <div class="modal-content">
            <form method="post" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title"><?= $info ? 'Edit Contact Information' : 'Add Contact Information' ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-scroll"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($info['id'] ?? 0) ?>">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Company Name</label><input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars((string) ($info['company_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Tagline</label><input type="text" class="form-control" name="tagline" value="<?= htmlspecialchars((string) ($info['tagline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($info['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars((string) ($info['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Alternate Phone</label><input type="text" class="form-control" name="alternate_phone" value="<?= htmlspecialchars((string) ($info['alternate_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4"><label class="form-label">WhatsApp</label><input type="text" class="form-control" name="whatsapp_number" value="<?= htmlspecialchars((string) ($info['whatsapp_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-4"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars((string) ($info['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-4"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="<?= htmlspecialchars((string) ($info['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6"><label class="form-label">Country</label><input type="text" class="form-control" name="country" value="<?= htmlspecialchars((string) ($info['country'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Postal Code</label><input type="text" class="form-control" name="postal_code" value="<?= htmlspecialchars((string) ($info['postal_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </div>
                    <div class="mt-2"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3" required><?= htmlspecialchars((string) ($info['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                    <hr>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Facebook URL</label><input type="text" class="form-control" name="facebook_url" value="<?= htmlspecialchars((string) ($info['facebook_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Twitter URL</label><input type="text" class="form-control" name="twitter_url" value="<?= htmlspecialchars((string) ($info['twitter_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Instagram URL</label><input type="text" class="form-control" name="instagram_url" value="<?= htmlspecialchars((string) ($info['instagram_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6"><label class="form-label">LinkedIn URL</label><input type="text" class="form-control" name="linkedin_url" value="<?= htmlspecialchars((string) ($info['linkedin_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
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
