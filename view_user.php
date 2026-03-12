<?php
include_once 'db_config.php';
ob_start();
if (isset($_GET['user_id'])) {
    $uid = $_GET['user_id'];
    echo "User ID: " . $uid;
}
