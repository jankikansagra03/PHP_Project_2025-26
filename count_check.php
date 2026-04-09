<?php
mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_connect('localhost', 'root', '', 'php_project_25_26');
if (!$con) {
    die('Connection failed');
}

$tables = [
    'registration',
    'register',
    'categories',
    'products',
    'addresses',
    'offers',
    'site_pages',
    'contact_info',
    'team_members',
    'faq',
    'contact_us',
    'orders',
    'order_items',
    'cart',
    'wishlist',
    'reviews',
    'offer_usage',
    'password_token'
];

foreach ($tables as $table) {
    $result = @mysqli_query($con, "SELECT COUNT(*) AS c FROM {$table}");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo $table . ':' . $row['c'] . PHP_EOL;
    } else {
        echo $table . ':missing' . PHP_EOL;
    }
}
