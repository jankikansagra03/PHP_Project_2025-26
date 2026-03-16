<?php
include_once 'user_authentication.php';
$email = $_SESSION['user'];
include_once 'db_config.php';
$title = "Shopping Cart - JK Store";
$active_sidebar = 'cart';
$q = "select * from cart where user_email='$email'";
$result = mysqli_query($con, $q);
$count = mysqli_num_rows($result);
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

    .quantity-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0 text-white">Shopping Cart <span class="text-white fs-4">(<?= $count . " items" ?>)</span></h2>
    <div class="d-flex gap-2">
        <button class="btn btn-sm rounded-pill text-white" data-bs-toggle="modal" data-bs-target="#clearCartModal">
            <i class="fas fa-trash me-2"></i>Clear Cart
        </button>
        <a href="shop.php" class="btn btn-sm rounded-pill text-white">
            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
        </a>
    </div>
</div>
<?php
if ($count == 0) {
?>
    <div class="alert alert-info" role="alert">
        Your cart is empty.
    </div>

<?php
} else {


?>
    <div class="row g-4">

        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="row g-4">
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    $pid = $row['product_id'];
                    $pq = $row['quantity'];
                    $q2 = "select * from products where id=$pid";
                    $result2 = mysqli_query($con, $q2);
                    $row2 = mysqli_fetch_assoc($result2);
                ?>
                    <!-- Item 1 -->
                    <div class="col-sm-6">
                        <div class="card border-0 shadow-sm h-100 product-card">
                            <div class="position-relative">
                                <img src="<?= $row2['image'] ?>" class="card-img-top p-4 bg-light" alt="<?= $row2['name'] ?>"
                                    style="height: 220px; object-fit: contain;">
                                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-3 rounded-circle shadow-sm"
                                    data-bs-toggle="modal" data-bs-target="#removeItemModal" title="Remove from cart">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span class="badge bg-secondary position-absolute top-0 start-0 m-3">Black</span>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-1"><?= $row2['name'] ?></h5>
                                <p class="text-primary fw-bold fs-5 mb-3">$<?= number_format($row2['price'], 2) ?></p>
                                <div class="input-group input-group-sm" style="width: 130px;">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="form-control text-center bg-white" value="<?= $pq ?>" readonly>
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                <?php
                }
                ?>

            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Order Summary</h4>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal (3 items)</span>
                        <span class="fw-semibold">$848.49</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success fw-semibold">Free</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax (Estimated)</span>
                        <span class="fw-semibold">$0.00</span>
                    </div>
                    <hr class="my-4">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-4 text-primary">$848.49</span>
                    </div>

                    <div class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Promo code">
                            <button class="btn btn-secondary px-4" type="button">Apply</button>
                        </div>
                    </div>

                    <a href="checkout.php" class="btn btn-gradient w-100 py-3 fw-bold shadow-sm mb-2">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>
                    <p class="text-center text-muted small mb-0">
                        <i class="fas fa-shield-alt me-1"></i>Secure checkout
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Remove Item Modal -->
    <div class="modal fade" id="removeItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-circle fa-4x text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Remove Item?</h4>
                    <p class="text-muted mb-4">Are you sure you want to remove this item from your cart?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger py-3">Yes, Remove Item</button>
                        <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Cart Modal -->
    <div class="modal fade" id="clearCartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-trash-alt fa-4x text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Clear Entire Cart?</h4>
                    <p class="text-muted mb-4">This will remove all items from your cart. This action cannot be undone.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger py-3">Yes, Clear Cart</button>
                        <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>