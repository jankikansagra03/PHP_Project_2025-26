<?php
include_once 'db_config.php';

ob_start();

$select = "select * from products";
$data = mysqli_query($con, $select);
?>
<div class="row">

    <?php
    while ($products = mysqli_fetch_assoc($data)) {
    ?>
        <div class="col-md-6 col-lg-3 fade-in-up">
            <div class="card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 text-center">
                        <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 250px; height: 250px; overflow: hidden;">
                            <img src="<?= $products['image'] ?>" alt="" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">

                        </div>
                    </div>
                    <h4 class="fw-bold mb-2"><?php echo $products['name']; ?></h4>
                    <p class="text-muted mb-3"><?php echo $products['description']; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h4 mb-0 fw-bold price-primary"><?php echo $products['price']; ?></span>
                        <button class="btn btn-gradient">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    ?>


</div>
<?php
$content = ob_get_clean();
include_once 'layout.php';
