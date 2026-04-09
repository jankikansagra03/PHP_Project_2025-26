<?php
include_once 'db_config.php';
$results = [];

// Fix addresses table: check both 'mobile' and 'phone' columns
$hasMobile = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM addresses LIKE 'mobile'")) > 0;
$hasPhone  = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM addresses LIKE 'phone'"))  > 0;

if (!$hasPhone && $hasMobile) {
    // Rename mobile -> phone for consistency
    $r = mysqli_query($con, "ALTER TABLE addresses CHANGE COLUMN mobile phone VARCHAR(20) DEFAULT NULL");
    $results[] = ['step' => 'Rename mobile→phone', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} elseif (!$hasPhone) {
    $r = mysqli_query($con, "ALTER TABLE addresses ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER name");
    $results[] = ['step' => 'Add phone column', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} else {
    $results[] = ['step' => 'phone column', 'ok' => true, 'msg' => 'Already exists'];
}

// Fix order_items: ensure product_image column exists
$hasPImg = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM order_items LIKE 'product_image'")) > 0;
if (!$hasPImg) {
    $r = mysqli_query($con, "ALTER TABLE order_items ADD COLUMN product_image VARCHAR(255) DEFAULT NULL AFTER product_name");
    $results[] = ['step' => 'order_items.product_image', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} else {
    $results[] = ['step' => 'order_items.product_image', 'ok' => true, 'msg' => 'Already exists'];
}

// Fix orders: ensure discount column exists  
$hasDiscount = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM orders LIKE 'discount'")) > 0;
if (!$hasDiscount) {
    $r = mysqli_query($con, "ALTER TABLE orders ADD COLUMN discount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER subtotal");
    $results[] = ['step' => 'orders.discount', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} else {
    $results[] = ['step' => 'orders.discount', 'ok' => true, 'msg' => 'Already exists'];
}

// Fix orders: ensure shipping_fee column exists
$hasShip = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM orders LIKE 'shipping_fee'")) > 0;
if (!$hasShip) {
    $r = mysqli_query($con, "ALTER TABLE orders ADD COLUMN shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER discount");
    $results[] = ['step' => 'orders.shipping_fee', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} else {
    $results[] = ['step' => 'orders.shipping_fee', 'ok' => true, 'msg' => 'Already exists'];
}

// Fix orders: ensure order_number column exists
$hasON = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM orders LIKE 'order_number'")) > 0;
if (!$hasON) {
    $r = mysqli_query($con, "ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) NOT NULL DEFAULT '' AFTER id");
    $results[] = ['step' => 'orders.order_number', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} else {
    $results[] = ['step' => 'orders.order_number', 'ok' => true, 'msg' => 'Already exists'];
}

// Fix orders: ensure delivery_name, delivery_email, delivery_mobile, delivery_address exist
$deliveryCols = ['delivery_name' => 'VARCHAR(100) NOT NULL DEFAULT ""', 'delivery_email' => 'VARCHAR(100) NOT NULL DEFAULT ""', 'delivery_mobile' => 'VARCHAR(15) NOT NULL DEFAULT ""', 'delivery_address' => 'TEXT NOT NULL'];
foreach ($deliveryCols as $col => $def) {
    $exists = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM orders LIKE '$col'")) > 0;
    if (!$exists) {
        $r = mysqli_query($con, "ALTER TABLE orders ADD COLUMN $col $def");
        $results[] = ['step' => "orders.$col", 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
    } else {
        $results[] = ['step' => "orders.$col", 'ok' => true, 'msg' => 'Already exists'];
    }
}

// Fix orders: ensure payment_method column exists
$hasPM = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM orders LIKE 'payment_method'")) > 0;
if (!$hasPM) {
    $r = mysqli_query($con, "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'cod'");
    $results[] = ['step' => 'orders.payment_method', 'ok' => (bool)$r, 'msg' => $r ? 'Done' : mysqli_error($con)];
} else {
    $results[] = ['step' => 'orders.payment_method', 'ok' => true, 'msg' => 'Already exists'];
}

// Show current columns of addresses table for verification
$cols_q = mysqli_query($con, "SHOW COLUMNS FROM addresses");
$addr_cols = [];
while ($c = mysqli_fetch_assoc($cols_q)) $addr_cols[] = $c['Field'];

echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green;} .err{color:red;}</style>";
echo "<h2>Schema Fix Results</h2><table border='1' cellpadding='6'><tr><th>Step</th><th>Status</th><th>Message</th></tr>";
foreach ($results as $r) {
    $cls = $r['ok'] ? 'ok' : 'err';
    echo "<tr><td>{$r['step']}</td><td class='$cls'>".($r['ok']?'✓':'✗')."</td><td>{$r['msg']}</td></tr>";
}
echo "</table>";
echo "<h3>Addresses table columns: " . implode(', ', $addr_cols) . "</h3>";
echo "<a href='checkout.php'>→ Go to Checkout</a>";
?>
