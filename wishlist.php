<?php
// include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$q = "select * from wishlist where user_email='$email'";
$result = mysqli_query($con, $q);
$count = mysqli_num_rows($result);
$title = "My Wishlist - JK Store";
$active_sidebar = 'wishlist';
ob_start();
?>
<style>
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0 text-white">My Wishlist <span class="text-white fs-4">(<?= $count . " items" ?>)</span></h2>
    <div class="d-flex gap-2">
        <button class="btn btn-sm rounded-pill text-white" data-bs-toggle="modal" data-bs-target="#clearWishlistModal">
            <i class="fas fa-trash me-2"></i>Clear All
        </button>
        <a href="shop.php" class="btn btn-sm rounded-pill text-white">
            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
        </a>
    </div>
</div>

<div class="row g-4">
    <?php
    if ($count == 0) {

    ?>
        <div class="alert alert-info" role="alert">
            Your wishlist is empty.
        </div>
        <?php } else {

        while ($row = mysqli_fetch_assoc($result)) {
            $pid = $row['product_id'];
            $q2 = "select * from products where id=$pid";
            $result2 = mysqli_query($con, $q2);
            $row2 = mysqli_fetch_assoc($result2);
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 product-card">
                    <div class="position-relative">
                        <img src="<?= $row2['image'] ?>" class="card-img-top p-4 bg-light" alt="<?= $row2['name'] ?>" style="height: 250px; object-fit: contain;">
                        <button class="btn btn-gradient rounded-pill btn btn-danger btn-sm position-absolute top-0 end-0 m-3 rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#removeWishlistModal" title="Remove from wishlist">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-2 text-truncate"><?= $row2['name'] ?></h5>

                        <button class="btn btn-gradient rounded-pill btn btn-light btn-sm position-absolute top-0 start-0 m-3 rounded-circle shadow-sm"
                            title="Move to cart">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                    <div class="card-body p-4 text-center">

                        <div class="mb-2 text-warning small">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span class="text-muted ms-1">(4.5)</span>
                        </div>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                            <?php
                            if ($row2['discount'] > 0) {
                            ?>
                                <span class="text-muted text-decoration-line-through small">$<?= number_format($row2['price'], 2) ?></span>
                                <span class="fw-bold text-primary fs-5">$<?= $row2['final_price'] ?></span>
                            <?php

                            } else {
                            ?>
                                <span class="fw-bold text-primary fs-5">$<?= number_format($row2['final_price'], 2) ?></span>
                            <?php

                            }
                            ?>
                        </div>
                        <button class="btn btn-gradient w-100 rounded-pill">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>

</div>
<?php
    }
?>


<!-- Remove from Wishlist Modal -->
<div class="modal fade" id="removeWishlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-heart-broken fa-4x text-danger"></i>
                </div>
                <h4 class="fw-bold mb-3">Remove from Wishlist?</h4>
                <p class="text-muted mb-4">Are you sure you want to remove this item from your wishlist?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Remove</button>
                    <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Wishlist Modal -->
<div class="modal fade" id="clearWishlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                </div>
                <h4 class="fw-bold mb-3">Clear Entire Wishlist?</h4>
                <p class="text-muted mb-4">This will remove all items from your wishlist. This action cannot be undone.
                </p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Clear All</button>
                    <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>