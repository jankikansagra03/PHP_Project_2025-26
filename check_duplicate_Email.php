<?php
// Lightweight AJAX endpoint used by forgot-password form to validate registration email.
include_once 'db_config.php';

if (isset($_GET['em'])) {
    // Escape incoming email and check existence in registration table.
    $email = mysqli_real_escape_string($con, trim($_GET['em']));
    $sql = "SELECT id FROM registration WHERE email = '$email' LIMIT 1";
    $users = mysqli_query($con, $sql);
    if ($users === false) {
        die('Query failed: ' . mysqli_error($con));
    }

    if ($users->num_rows > 0) {
        // Return plain text expected by frontend JS.
        echo 'true';
    } else {
        // Email is not registered.
        echo 'false';
    }

    $users->free();
}
