<?php
include_once 'db_config.php';

if (isset($_GET['email1'])) {
    $email = trim($_GET['email1']);
    $stmt = $con->prepare("SELECT id FROM registration WHERE email = ? LIMIT 1");
    if ($stmt == false) {
        die('Prepare failed: ' . $con->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $users = $stmt->get_result();

    if ($users->num_rows > 0) {
        echo 'true';
    } else {
        echo 'false';
    }

    $users->free();
    $stmt->close();
}
