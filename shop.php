<?php
$title = "Shop - JK Store";
ob_start();
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
    $products = [
        ['name' => 'Premium Product 1', 'price' => '$99.99', 'desc' => 'High-quality product with amazing features'],
        ['name' => 'Premium Product 2', 'price' => '$149.99', 'desc' => 'Professional-grade solution for your needs'],
        ['name' => 'Premium Product 3', 'price' => '$79.99', 'desc' => 'Affordable excellence at your fingertips'],
        ['name' => 'Premium Product 4', 'price' => '$199.99', 'desc' => 'Top-tier performance and reliability'],
        ['name' => 'Premium Product 5', 'price' => '$129.99', 'desc' => 'Innovation meets practicality'],
        ['name' => 'Premium Product 6', 'price' => '$89.99', 'desc' => 'Perfect blend of quality and value'],
        ['name' => 'Premium Product 7', 'price' => '$129.99', 'desc' => 'Innovation meets practicality'],
        ['name' => 'Premium Product 8', 'price' => '$89.99', 'desc' => 'Perfect blend of quality and value'],
    ];

    foreach ($products as $index => $product):
        $delay = ($index % 3) * 0.1;
    ?>
        <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: <?php echo $delay; ?>s;">
            <div class="card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 text-center">
                        <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-box fa-4x text-white"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-2"><?php echo $product['name']; ?></h4>
                    <p class="text-muted mb-3"><?php echo $product['desc']; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h4 mb-0 fw-bold" style="color: #667eea;"><?php echo $product['price']; ?></span>
                        <button class="btn btn-gradient">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<?php
$content = ob_get_clean();
include 'layout.php';
?>