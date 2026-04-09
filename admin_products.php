<?php
include_once 'db_config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $returnSearch = trim($_POST['return_search'] ?? '');
    $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
    $redirectUrl = 'admin_products.php?page=' . $returnPage . ($returnSearch !== '' ? '&search=' . urlencode($returnSearch) : '');

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $categoryId = ($_POST['category_id'] ?? '') === '' ? null : (int) $_POST['category_id'];
        $brand = trim($_POST['brand'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $discount = (float) ($_POST['discount'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $longDescription = trim($_POST['long_description'] ?? '');
        $status = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';

        $mainDir = 'uploads/products/main/';
        $galleryDir = 'uploads/products/gallery/';
        if (!is_dir($mainDir)) mkdir($mainDir, 0755, true);
        if (!is_dir($galleryDir)) mkdir($galleryDir, 0755, true);

        $mainImagePath = $mainDir . uniqid() . '_' . basename($_FILES['main_image']['name']);
        $mainImageTmp = $_FILES['main_image']['tmp_name'];

        $galleryDestPaths = [];
        $galleryTmpPaths = [];
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            foreach ($_FILES['gallery_images']['name'] as $i => $fileName) {
                if ($fileName === '' || empty($_FILES['gallery_images']['tmp_name'][$i])) continue;
                $dest = $galleryDir . uniqid() . '_' . basename($fileName);
                $galleryTmpPaths[] = $_FILES['gallery_images']['tmp_name'][$i];
                $galleryDestPaths[] = $dest;
            }
        }

        $galleryCsv = !empty($galleryDestPaths) ? implode(',', $galleryDestPaths) : null;

        $stmt = mysqli_prepare($con, 'INSERT INTO products (name, category_id, brand, price, discount, stock, description, long_description, image, gallery_images, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sisddisssss', $name, $categoryId, $brand, $price, $discount, $stock, $description, $longDescription, $mainImagePath, $galleryCsv, $status);

        if (mysqli_stmt_execute($stmt)) {
            move_uploaded_file($mainImageTmp, $mainImagePath);
            foreach ($galleryDestPaths as $i => $destPath) move_uploaded_file($galleryTmpPaths[$i], $destPath);
            setcookie('success', 'Product created successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to create product.', time() + 5, '/');
        }
        mysqli_stmt_close($stmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'update') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $categoryId = ($_POST['category_id'] ?? '') === '' ? null : (int) $_POST['category_id'];
        $brand = trim($_POST['brand'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $discount = (float) ($_POST['discount'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $longDescription = trim($_POST['long_description'] ?? '');
        $status = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';

        $selectStmt = mysqli_prepare($con, 'SELECT image, gallery_images FROM products WHERE id = ?');
        mysqli_stmt_bind_param($selectStmt, 'i', $productId);
        mysqli_stmt_execute($selectStmt);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($selectStmt));
        mysqli_stmt_close($selectStmt);

        $oldMain = (string) ($existing['image'] ?? '');
        $newMainPath = $oldMain;
        $newMainTmp = null;
        $removeMain = isset($_POST['remove_main_image']) && $_POST['remove_main_image'] == '1';

        if ($removeMain) {
            $newMainPath = null;
        } elseif (!empty($_FILES['main_image']['name'])) {
            $mainDir = 'uploads/products/main/';
            if (!is_dir($mainDir)) mkdir($mainDir, 0755, true);
            $newMainPath = $mainDir . uniqid() . '_' . basename($_FILES['main_image']['name']);
            $newMainTmp = $_FILES['main_image']['tmp_name'];
        }

        $removeGallery = (array) ($_POST['remove_gallery_images'] ?? []);
        $existingGallery = !empty($existing['gallery_images']) ? array_map('trim', explode(',', $existing['gallery_images'])) : [];
        $finalGalleryList = array_values(array_filter($existingGallery, fn($img) => !in_array($img, $removeGallery, true)));

        $newGalleryTmp = [];
        $newGalleryDest = [];
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            $galleryDir = 'uploads/products/gallery/';
            if (!is_dir($galleryDir)) mkdir($galleryDir, 0755, true);
            foreach ($_FILES['gallery_images']['name'] as $i => $fileName) {
                if ($fileName === '' || empty($_FILES['gallery_images']['tmp_name'][$i])) continue;
                $dest = $galleryDir . uniqid() . '_' . basename($fileName);
                $newGalleryTmp[] = $_FILES['gallery_images']['tmp_name'][$i];
                $newGalleryDest[] = $dest;
                $finalGalleryList[] = $dest;
            }
        }

        $galleryCsv = !empty($finalGalleryList) ? implode(',', $finalGalleryList) : null;

        $updateStmt = mysqli_prepare($con, 'UPDATE products SET name=?, category_id=?, brand=?, price=?, discount=?, stock=?, description=?, long_description=?, image=?, gallery_images=?, status=? WHERE id=?');
        mysqli_stmt_bind_param($updateStmt, 'sisddisssssi', $name, $categoryId, $brand, $price, $discount, $stock, $description, $longDescription, $newMainPath, $galleryCsv, $status, $productId);

        if (mysqli_stmt_execute($updateStmt)) {
            if ($newMainTmp && $newMainPath) move_uploaded_file($newMainTmp, $newMainPath);
            foreach ($newGalleryDest as $i => $destPath) move_uploaded_file($newGalleryTmp[$i], $destPath);
            if ($removeMain && $oldMain && file_exists($oldMain)) @unlink($oldMain);
            if ($newMainTmp && $oldMain && file_exists($oldMain) && $oldMain !== $newMainPath) @unlink($oldMain);
            foreach ($removeGallery as $img) {
                if ($img && file_exists($img)) @unlink($img);
            }
            setcookie('success', 'Product updated successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to update product.', time() + 5, '/');
        }
        mysqli_stmt_close($updateStmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'delete') {
        $productId = (int) ($_POST['product_id'] ?? 0);

        $selectStmt = mysqli_prepare($con, 'SELECT image, gallery_images FROM products WHERE id = ?');
        mysqli_stmt_bind_param($selectStmt, 'i', $productId);
        mysqli_stmt_execute($selectStmt);
        $product = mysqli_fetch_assoc(mysqli_stmt_get_result($selectStmt));
        mysqli_stmt_close($selectStmt);

        $deleteStmt = mysqli_prepare($con, 'DELETE FROM products WHERE id = ?');
        mysqli_stmt_bind_param($deleteStmt, 'i', $productId);

        if (mysqli_stmt_execute($deleteStmt)) {
            if (!empty($product['image']) && file_exists($product['image'])) @unlink($product['image']);
            foreach (explode(',', $product['gallery_images'] ?? '') as $img) {
                $img = trim($img);
                if ($img && file_exists($img)) @unlink($img);
            }
            setcookie('success', 'Product deleted successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to delete product.', time() + 5, '/');
        }
        mysqli_stmt_close($deleteStmt);
        header('Location: ' . $redirectUrl);
        exit();
    }

    if ($action === 'change_status') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $newStatus = ($_POST['new_status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';

        $statusStmt = mysqli_prepare($con, 'UPDATE products SET status=? WHERE id=?');
        mysqli_stmt_bind_param($statusStmt, 'si', $newStatus, $productId);
        if (mysqli_stmt_execute($statusStmt)) {
            setcookie('success', 'Product status updated successfully.', time() + 5, '/');
        } else {
            setcookie('error', 'Failed to update status.', time() + 5, '/');
        }
        mysqli_stmt_close($statusStmt);
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;

$categories = [];
$catRes = mysqli_query($con, "SELECT id, category_name FROM categories ORDER BY category_name ASC");
if ($catRes) {
    while ($cat = mysqli_fetch_assoc($catRes)) {
        $categories[] = $cat;
    }
}

$hasSearch = ($search !== '');
$whereSql = '';
$params = [];
$types = '';

if ($hasSearch) {
    $whereSql = ' WHERE p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR c.category_name LIKE ? ';
    $searchLike = '%' . $search . '%';
    $params = [$searchLike, $searchLike, $searchLike, $searchLike];
    $types = 'ssss';
}

$countSql = 'SELECT COUNT(*) AS total FROM products p LEFT JOIN categories c ON c.id = p.category_id' . $whereSql;
$countStmt = mysqli_prepare($con, $countSql);
if ($hasSearch) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$countRes = mysqli_stmt_get_result($countStmt);
$totalProducts = (int) (($countRes ? mysqli_fetch_assoc($countRes)['total'] : 0) ?? 0);
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int) ceil($totalProducts / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id' . $whereSql . ' ORDER BY p.id DESC LIMIT ?, ?';
$listStmt = mysqli_prepare($con, $listSql);
if ($hasSearch) {
    $listTypes = $types . 'ii';
    $listParams = array_merge($params, [$offset, $perPage]);
    mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams);
} else {
    mysqli_stmt_bind_param($listStmt, 'ii', $offset, $perPage);
}
mysqli_stmt_execute($listStmt);
$productsResult = mysqli_stmt_get_result($listStmt);

$title = 'Admin Products - JK Store';
$admin_active = 'products';
$admin_page_title = 'Products';

ob_start();
?>

<div class="page-card">
    <div class="products-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-bold">Product Management</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-1"></i>Add Product
        </button>
    </div>

    <div class="products-body">

        <form method="get" class="row g-2 mb-3" novalidate>
            <div class="col-12">
                <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by name, brand, description, category..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" data-validation="max" data-max="120" data-error="#search_error">
                <small id="search_error"></small>
            </div>
        </form>

        <div id="productsListing">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle products-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Main Image</th>
                            <th>Status</th>
                            <th class="action-col-240">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($productsResult && mysqli_num_rows($productsResult) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($productsResult)): ?>
                                <?php
                                $statusIsActive = strtolower((string) ($row['status'] ?? '')) === 'active';
                                $galleryImages = !empty($row['gallery_images']) ? array_map('trim', explode(',', $row['gallery_images'])) : [];
                                ?>
                                <tr>
                                    <td><?= (int) $row['id'] ?></td>
                                    <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($row['category_name'] ?? ('#' . ($row['category_id'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>&#8377;<?= number_format((float) ($row['price'] ?? 0), 2) ?></td>
                                    <td><?= (int) ($row['stock'] ?? 0) ?></td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="<?= htmlspecialchars((string) $row['image'], ENT_QUOTES, 'UTF-8') ?>" alt="product" class="small-preview border">
                                        <?php else: ?>
                                            <span class="text-muted small">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusIsActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                            <?= $statusIsActive ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="products-actions d-flex flex-wrap gap-1">
                                            <button class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#viewProductModal<?= (int) $row['id'] ?>" title="View" aria-label="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal" data-bs-target="#editProductModal<?= (int) $row['id'] ?>" title="Edit" aria-label="Edit">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#deleteProductModal<?= (int) $row['id'] ?>" title="Delete" aria-label="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary mb-1" data-bs-toggle="modal" data-bs-target="#statusProductModal<?= (int) $row['id'] ?>" title="<?= $statusIsActive ? 'Deactivate' : 'Activate' ?>" aria-label="<?= $statusIsActive ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas <?= $statusIsActive ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="viewProductModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Product Details - <?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body modal-body-scroll">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <div class="border rounded p-3 h-100">
                                                            <h6 class="fw-bold">Basic</h6>
                                                            <p class="mb-1"><strong>ID:</strong> <?= (int) $row['id'] ?></p>
                                                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></p>
                                                            <p class="mb-1"><strong>Brand:</strong> <?= htmlspecialchars((string) ($row['brand'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                                            <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars((string) ($row['category_name'] ?? ($row['category_id'] ?? 'N/A')), ENT_QUOTES, 'UTF-8') ?></p>
                                                            <p class="mb-0"><strong>Status:</strong> <?= $statusIsActive ? 'Active' : 'Inactive' ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="border rounded p-3 h-100">
                                                            <h6 class="fw-bold">Price/Stock</h6>
                                                            <p class="mb-1"><strong>Price:</strong> &#8377;<?= number_format((float) ($row['price'] ?? 0), 2) ?></p>
                                                            <p class="mb-1"><strong>Discount:</strong> <?= number_format((float) ($row['discount'] ?? 0), 2) ?></p>
                                                            <p class="mb-1"><strong>Final Price:</strong> &#8377;<?= number_format(((float) ($row['price'] ?? 0) - (float) ($row['discount'] ?? 0)), 2) ?></p>
                                                            <p class="mb-0"><strong>Stock:</strong> <?= (int) ($row['stock'] ?? 0) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="border rounded p-3 h-100">
                                                            <h6 class="fw-bold">Main Image</h6>
                                                            <?php if (!empty($row['image'])): ?>
                                                                <img src="<?= htmlspecialchars((string) $row['image'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded border" alt="main image">
                                                            <?php else: ?>
                                                                <p class="text-muted mb-0">No main image</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="border rounded p-3">
                                                            <h6 class="fw-bold">Description</h6>
                                                            <p class="mb-2 text-muted"><?= nl2br(htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                                                            <h6 class="fw-bold mt-3">Long Description</h6>
                                                            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars((string) ($row['long_description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="border rounded p-3">
                                                            <h6 class="fw-bold">Gallery Images</h6>
                                                            <?php if (!empty($galleryImages)): ?>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php foreach ($galleryImages as $img): ?>
                                                                        <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="gallery" class="rounded border gallery-thumb-140">
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <p class="text-muted mb-0">No gallery images</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="editProductModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <form method="post" enctype="multipart/form-data" novalidate>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Product - <?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body modal-body-scroll">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="product_id" value="<?= (int) $row['id'] ?>">
                                                    <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="return_page" value="<?= (int) $page ?>">

                                                    <div class="mb-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,min,max" data-min="2" data-max="255" data-error="#edit_name_error_<?= (int) $row['id'] ?>">
                                                        <small id="edit_name_error_<?= (int) $row['id'] ?>"></small>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Category</label>
                                                            <select class="form-select" name="category_id" data-validation="select" data-error="#edit_category_id_error_<?= (int) $row['id'] ?>">
                                                                <option value="">Select category</option>
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($row['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars((string) $cat['category_name'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <small id="edit_category_id_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Brand</label>
                                                            <input type="text" class="form-control" name="brand" value="<?= htmlspecialchars((string) ($row['brand'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-validation="min,max" data-min="2" data-max="100" data-error="#edit_brand_error_<?= (int) $row['id'] ?>">
                                                            <small id="edit_brand_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Price</label>
                                                            <input type="number" step="0.01" min="0.01" class="form-control" name="price" value="<?= htmlspecialchars((string) ($row['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required" data-error="#edit_price_error_<?= (int) $row['id'] ?>">
                                                            <small id="edit_price_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Discount</label>
                                                            <input type="number" step="0.01" min="0" class="form-control" name="discount" value="<?= htmlspecialchars((string) ($row['discount'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" data-validation="required" data-error="#edit_discount_error_<?= (int) $row['id'] ?>">
                                                            <small id="edit_discount_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Stock</label>
                                                            <input type="number" step="1" min="0" class="form-control" name="stock" value="<?= htmlspecialchars((string) ($row['stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required data-validation="required,number" data-error="#edit_stock_error_<?= (int) $row['id'] ?>">
                                                            <small id="edit_stock_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" rows="3" name="description" data-validation="max" data-max="2000" data-error="#edit_description_error_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        <small id="edit_description_error_<?= (int) $row['id'] ?>"></small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Long Description</label>
                                                        <textarea class="form-control" rows="4" name="long_description" data-validation="max" data-max="10000" data-error="#edit_long_description_error_<?= (int) $row['id'] ?>"><?= htmlspecialchars((string) ($row['long_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        <small id="edit_long_description_error_<?= (int) $row['id'] ?>"></small>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Status</label>
                                                            <select class="form-select" name="status" data-validation="required,select" data-error="#edit_status_error_<?= (int) $row['id'] ?>">
                                                                <option value="Active" <?= $statusIsActive ? 'selected' : '' ?>>Active</option>
                                                                <option value="Inactive" <?= !$statusIsActive ? 'selected' : '' ?>>Inactive</option>
                                                            </select>
                                                            <small id="edit_status_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Upload New Main Image</label>
                                                            <input type="file" class="form-control" name="main_image" accept=".jpg,.jpeg,.png,.webp" data-validation="fileSize,fileType" data-filesize="2" data-filetype="jpg,jpeg,png,webp" data-error="#edit_main_image_error_<?= (int) $row['id'] ?>">
                                                            <small id="edit_main_image_error_<?= (int) $row['id'] ?>"></small>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($row['image'])): ?>
                                                        <div class="mb-3 p-3 border rounded bg-light">
                                                            <p class="mb-2 fw-semibold">Current Main Image</p>
                                                            <img src="<?= htmlspecialchars((string) $row['image'], ENT_QUOTES, 'UTF-8') ?>" alt="main" class="rounded border mb-2 main-thumb-120">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="remove_main_image" value="1" id="removeMain<?= (int) $row['id'] ?>">
                                                                <label class="form-check-label" for="removeMain<?= (int) $row['id'] ?>">Remove current main image</label>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="mb-3">
                                                        <label class="form-label">Add New Gallery Images</label>
                                                        <input type="file" class="form-control" name="gallery_images[]" accept=".jpg,.jpeg,.png,.webp" multiple data-validation="fileSize,fileType" data-filesize="2" data-filetype="jpg,jpeg,png,webp" data-error="#edit_gallery_images_error_<?= (int) $row['id'] ?>">
                                                        <small id="edit_gallery_images_error_<?= (int) $row['id'] ?>"></small>
                                                    </div>

                                                    <?php if (!empty($galleryImages)): ?>
                                                        <div class="mb-3 p-3 border rounded bg-light">
                                                            <p class="fw-semibold mb-2">Current Gallery Images</p>
                                                            <div class="row g-2">
                                                                <?php foreach ($galleryImages as $gIndex => $gImage): ?>
                                                                    <div class="col-6 col-md-3">
                                                                        <img src="<?= htmlspecialchars($gImage, ENT_QUOTES, 'UTF-8') ?>" alt="gallery" class="img-fluid rounded border gallery-thumb-100">
                                                                        <div class="form-check mt-1">
                                                                            <input class="form-check-input" type="checkbox" name="remove_gallery_images[]" value="<?= htmlspecialchars($gImage, ENT_QUOTES, 'UTF-8') ?>" id="rmGallery<?= (int) $row['id'] ?>_<?= (int) $gIndex ?>">
                                                                            <label class="form-check-label small" for="rmGallery<?= (int) $row['id'] ?>_<?= (int) $gIndex ?>">Remove</label>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Product</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="deleteProductModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" novalidate>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Product</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2">Delete <strong><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></strong> permanently?</p>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?= (int) $row['id'] ?>">
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

                                <div class="modal fade" id="statusProductModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" novalidate>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Change Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php $nextStatus = $statusIsActive ? 'Inactive' : 'Active'; ?>
                                                    <p class="mb-2">Set product <strong><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></strong> as <strong><?= htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8') ?></strong>?</p>
                                                    <input type="hidden" name="action" value="change_status">
                                                    <input type="hidden" name="product_id" value="<?= (int) $row['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?= htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8') ?>">
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
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav class="products-pagination" aria-label="Products pagination">
                <div class="products-pagination-meta">
                    Showing page <?= (int) $page ?> of <?= (int) $totalPages ?>
                    <?php if ($totalProducts > 0): ?>
                        · <?= (int) $totalProducts ?> total products
                    <?php endif; ?>
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
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-scroll">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="return_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_page" value="<?= (int) $page ?>">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required data-validation="required,min,max" data-min="2" data-max="255" data-error="#name_error">
                        <small id="name_error"></small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" data-validation="select" data-error="#category_id_error">
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars((string) $cat['category_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small id="category_id_error"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" data-validation="min,max" data-min="2" data-max="100" data-error="#brand_error">
                            <small id="brand_error"></small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="price" required data-validation="required" data-error="#price_error">
                            <small id="price_error"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="discount" value="0" data-validation="required" data-error="#discount_error">
                            <small id="discount_error"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" step="1" min="0" class="form-control" name="stock" required data-validation="required,number" data-error="#stock_error">
                            <small id="stock_error"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" data-validation="max" data-max="2000" data-error="#description_error"></textarea>
                        <small id="description_error"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Long Description</label>
                        <textarea class="form-control" name="long_description" rows="4" data-validation="max" data-max="10000" data-error="#long_description_error"></textarea>
                        <small id="long_description_error"></small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Main Image</label>
                            <input type="file" class="form-control" name="main_image" accept=".jpg,.jpeg,.png,.webp" required data-validation="required,fileSize,fileType" data-filesize="2" data-filetype="jpg,jpeg,png,webp" data-error="#main_image_error">
                            <small id="main_image_error"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gallery Images</label>
                            <input type="file" class="form-control" name="gallery_images[]" accept=".jpg,.jpeg,.png,.webp" multiple data-validation="fileSize,fileType" data-filesize="2" data-filetype="jpg,jpeg,png,webp" data-error="#gallery_images_error">
                            <small id="gallery_images_error"></small>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" data-validation="required,select" data-error="#status_error">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <small id="status_error"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Product</button>
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

        if (!searchInput.length) {
            return;
        }

        searchInput.focus();
        var value = searchInput.val() || '';
        if (searchInput[0] && typeof searchInput[0].setSelectionRange === 'function') {
            searchInput[0].setSelectionRange(value.length, value.length);
        }

        searchInput.on('input', function() {
            var searchValue = $(this).val().trim();
            var url = 'admin_products.php?page=1';
            if (searchValue) {
                url += '&search=' + encodeURIComponent(searchValue);
            }
            window.location.href = url;
        });
    });
</script>

<?php
mysqli_stmt_close($listStmt);
$admin_content = ob_get_clean();
include 'admin_layout.php';
