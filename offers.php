<?php
include_once 'db_config.php';
$title = "Special Offers - JK Store";
$q = "select * from offers where status ='Active'";
$data = mysqli_query($con, $q);
ob_start();
?>

<div class="container">
    <div class="row mb-5 fade-in-up">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3 text-white">
                Special Offers
            </h1>
            <p class="lead text-white">Don't miss out on our amazing deals and exclusive discounts!</p>
        </div>
    </div>



    <!-- Coupon Codes Section -->
    <div class="row mb-4 fade-in-up">
        <div class="col-12">
            <h3 class="fw-bold mb-4 text-white">Available Coupon Codes</h3>
        </div>
    </div>

    <div class="row g-4 mb-5 fade-in-up">
        <?php
        while ($coupon = mysqli_fetch_assoc($data)) {
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h4 class="fw-bold mb-0"><?php echo $coupon['code']; ?></h4>
                            <button class="btn btn-light btn-sm rounded-pill px-3" onclick="copyCode('<?php echo $coupon['code']; ?>')">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>
                        <p class="mb-3 opacity-75"><?= $coupon['description']; ?></p>
                        <small class="opacity-75"><i class="fas fa-clock me-1"></i>Valid till: <?php echo $coupon['valid_to']; ?></small>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="row g-4 mb-5">
        <?php
        $q1 = "select * from products where discount>0";
        $data1 = mysqli_query($con, $q1);

        while ($offer = mysqli_fetch_assoc($data1)) {

        ?>
            <div class="col-md-6 col-lg-4 fade-in-up" style="animation-delay: <?php echo $delay; ?>s;">
                <div class="card h-100 border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-danger px-3 py-2"><?php echo $offer['discount']; ?></span>
                        </div>
                        <div class="mb-3 text-center">
                            <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-tag fa-4x text-white"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-2"><?php echo $offer['name']; ?></h4>
                        <p class="text-muted mb-3"><?php echo $offer['description']; ?></p>
                        <div class="d-flex align-items-center mb-3">
                            <span class="text-decoration-line-through text-muted me-2"><?php echo $offer['price']; ?></span>
                            <span class="h4 mb-0 fw-bold text-danger"><?php echo $offer['final_price']; ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-gradient flex-fill">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>



<script>
    function copyCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            alert('Coupon code "' + code + '" copied to clipboard!');
        });
    }
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>