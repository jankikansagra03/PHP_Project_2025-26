<?php
include_once 'db_config.php';
$title = "Shop - JK Store";
ob_start();
$q = "select * from products";
$data = mysqli_query($con, $q);
?>

<div class="row mb-5 fade-in-up">
    <div class="col-12 text-center">
        <h1 class="display-4 fw-bold mb-3 text-white">
            Our Products
        </h1>
        <p class="lead text-white">Discover our amazing collection of products</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <?php


    while ($products = mysqli_fetch_assoc($data)) {

    ?>
        <div class="col-md-6 col-lg-3 fade-in-up">
            <div class="card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 text-center">
                        <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-box fa-4x text-white"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-2"><?php echo $products['name']; ?></h4>
                    <p class="text-muted mb-3"><?php echo $products['description']; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h4 mb-0 fw-bold" style="color: #667eea;"><?php echo $products['price']; ?></span>
                        <button class="btn btn-gradient">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>


<?php
$content = ob_get_clean();
include 'layout.php';
?>