<?php
session_start();
if (!isset($_SESSION['user'])) {
    setcookie('error', 'Please login to continue.', time() + 5, '/');
    header('Location: login.php');
    exit;
}
include_once 'db_config.php';
$email = mysqli_real_escape_string($con, $_SESSION['user']);
mysqli_query($con, "DELETE FROM wishlist WHERE user_email='$email'");
setcookie('success', 'Wishlist cleared successfully.', time() + 5, '/');
header('Location: wishlist.php');
exit;
