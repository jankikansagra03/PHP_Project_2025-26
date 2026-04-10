<?php
session_start();
include_once 'db_config.php';

if (!isset($_SESSION['user'])) {
    echo 'error: Please login.';
    exit;
}

$email  = mysqli_real_escape_string($con, $_SESSION['user']);
$action = $_POST['action'] ?? '';

if ($action == 'add') {

    $label   = mysqli_real_escape_string($con, $_POST['label']   ?? 'home');
    $name    = mysqli_real_escape_string($con, $_POST['name']    ?? '');
    $phone   = mysqli_real_escape_string($con, $_POST['phone']   ?? '');
    $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
    $city    = mysqli_real_escape_string($con, $_POST['city']    ?? '');
    $state   = mysqli_real_escape_string($con, $_POST['state']   ?? '');
    $zip     = mysqli_real_escape_string($con, $_POST['zip']     ?? '');
    $is_def  = !empty($_POST['is_default']) ? 1 : 0;

    if (!$name || !$phone || !$address || !$city || !$state) {
        echo 'error: All required fields must be filled.';
        exit;
    }
    if ($is_def) {
        mysqli_query($con, "UPDATE addresses SET is_default=0 WHERE email='$email'");
    }
    $q = "INSERT INTO addresses (user_id, email, label, name, phone, address, city, state, zip, is_default)
          VALUES ('$email','$email','$label','$name','$phone','$address','$city','$state','$zip',$is_def)";
    if (mysqli_query($con, $q)) {
        echo 'success';
    } else {
        echo 'error: Failed to save address.';
    }
    exit;

} elseif ($action == 'edit') {

    $id      = (int)($_POST['id'] ?? 0);
    $label   = mysqli_real_escape_string($con, $_POST['label']   ?? 'home');
    $name    = mysqli_real_escape_string($con, $_POST['name']    ?? '');
    $phone   = mysqli_real_escape_string($con, $_POST['phone']   ?? '');
    $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
    $city    = mysqli_real_escape_string($con, $_POST['city']    ?? '');
    $state   = mysqli_real_escape_string($con, $_POST['state']   ?? '');
    $zip     = mysqli_real_escape_string($con, $_POST['zip']     ?? '');
    $is_def  = !empty($_POST['is_default']) ? 1 : 0;

    if ($is_def) {
        mysqli_query($con, "UPDATE addresses SET is_default=0 WHERE email='$email'");
    }
    $q = "UPDATE addresses SET label='$label', name='$name', phone='$phone', address='$address',
          city='$city', state='$state', zip='$zip', is_default=$is_def
          WHERE id=$id AND email='$email'";
    if (mysqli_query($con, $q)) {
        echo 'success';
    } else {
        echo 'error: Failed to update address.';
    }
    exit;

} elseif ($action == 'delete') {

    $id = (int)($_POST['id'] ?? 0);
    if (mysqli_query($con, "DELETE FROM addresses WHERE id=$id AND email='$email'")) {
        echo 'success';
    } else {
        echo 'error: Failed to delete address.';
    }
    exit;

} elseif ($action == 'set_default') {

    $id = (int)($_POST['id'] ?? 0);
    mysqli_query($con, "UPDATE addresses SET is_default=0 WHERE email='$email'");
    mysqli_query($con, "UPDATE addresses SET is_default=1 WHERE id=$id AND email='$email'");
    echo 'success';
    exit;

} else {
    echo 'error: Unknown action.';
    exit;
}
