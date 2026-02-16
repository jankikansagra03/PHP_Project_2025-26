<?php
$title = "Special Offers - JK Store";
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
        $coupons = [
            ['code' => 'SAVE20', 'desc' => 'Get 20% off on orders above $100', 'valid' => 'Feb 28, 2026'],
            ['code' => 'FIRST50', 'desc' => 'Flat $50 off on your first order', 'valid' => 'Mar 15, 2026'],
            ['code' => 'FREESHIP', 'desc' => 'Free shipping on all orders', 'valid' => 'Feb 20, 2026'],
        ];

        foreach ($coupons as $coupon):
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
                        <p class="mb-3 opacity-75"><?php echo $coupon['desc']; ?></p>
                        <small class="opacity-75"><i class="fas fa-clock me-1"></i>Valid till: <?php echo $coupon['valid']; ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="row g-4 mb-5">
        <?php
        $offers = [
            ['name' => 'Wireless Headphones', 'original' => '$299.99', 'price' => '$149.99', 'discount' => '50% OFF', 'desc' => 'Premium noise-canceling technology'],
            ['name' => 'Smart Fitness Watch', 'original' => '$399.00', 'price' => '$199.00', 'discount' => 'Buy 1 Get 1', 'desc' => 'Track your health and fitness goals'],
            ['name' => 'Premium Backpack', 'original' => '$149.50', 'price' => '$104.65', 'discount' => '30% OFF', 'desc' => 'Durable and stylish everyday carry'],
            ['name' => 'Bluetooth Speaker', 'original' => '$89.99', 'price' => '$59.99', 'discount' => '33% OFF', 'desc' => '360° surround sound experience'],
            ['name' => 'Wireless Mouse', 'original' => '$49.99', 'price' => '$29.99', 'discount' => '40% OFF', 'desc' => 'Ergonomic design for comfort'],
            ['name' => 'USB-C Hub', 'original' => '$79.99', 'price' => '$54.99', 'discount' => 'Free Ship', 'desc' => '7-in-1 multiport adapter'],
        ];

        foreach ($offers as $index => $offer):
            $delay = ($index % 3) * 0.1;
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
                        <p class="text-muted mb-3"><?php echo $offer['desc']; ?></p>
                        <div class="d-flex align-items-center mb-3">
                            <span class="text-decoration-line-through text-muted me-2"><?php echo $offer['original']; ?></span>
                            <span class="h4 mb-0 fw-bold text-danger"><?php echo $offer['price']; ?></span>
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
        <?php endforeach; ?>
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