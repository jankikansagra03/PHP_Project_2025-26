<?php
/**
 * address_handler.php
 * AJAX — add / edit / delete / set-default addresses
 */
session_start();
include_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login.']);
    exit;
}

$email  = mysqli_real_escape_string($con, $_SESSION['user']);
$action = $_POST['action'] ?? '';

switch ($action) {

    // ── Add ──────────────────────────────────────────────
    case 'add':
        $label   = mysqli_real_escape_string($con, $_POST['label']   ?? 'home');
        $name    = mysqli_real_escape_string($con, $_POST['name']    ?? '');
        $phone   = mysqli_real_escape_string($con, $_POST['phone']   ?? '');
        $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
        $city    = mysqli_real_escape_string($con, $_POST['city']    ?? '');
        $state   = mysqli_real_escape_string($con, $_POST['state']   ?? '');
        $zip     = mysqli_real_escape_string($con, $_POST['zip']     ?? '');
        $is_def  = isset($_POST['is_default']) && $_POST['is_default'] ? 1 : 0;

        if (!$name || !$phone || !$address || !$city || !$state) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
            exit;
        }
        if ($is_def) {
            mysqli_query($con, "UPDATE addresses SET is_default=0 WHERE email='$email'");
        }
        // user_id = email (used as identifier), email also stored separately
        $q = "INSERT INTO addresses (user_id, email, label, name, phone, address, city, state, zip, is_default)
              VALUES ('$email','$email','$label','$name','$phone','$address','$city','$state','$zip',$is_def)";
        if (mysqli_query($con, $q)) {
            $new_id = mysqli_insert_id($con);
            echo json_encode(['success' => true, 'message' => 'Address saved!', 'id' => $new_id,
                'label' => $label, 'name' => $name, 'phone' => $phone,
                'address' => $address, 'city' => $city, 'state' => $state, 'zip' => $zip, 'is_default' => $is_def]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save address. ' . mysqli_error($con)]);
        }
        break;

    // ── Edit ─────────────────────────────────────────────
    case 'edit':
        $id      = (int)($_POST['id'] ?? 0);
        $label   = mysqli_real_escape_string($con, $_POST['label']   ?? 'home');
        $name    = mysqli_real_escape_string($con, $_POST['name']    ?? '');
        $phone   = mysqli_real_escape_string($con, $_POST['phone']   ?? '');
        $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
        $city    = mysqli_real_escape_string($con, $_POST['city']    ?? '');
        $state   = mysqli_real_escape_string($con, $_POST['state']   ?? '');
        $zip     = mysqli_real_escape_string($con, $_POST['zip']     ?? '');
        $is_def  = isset($_POST['is_default']) && $_POST['is_default'] ? 1 : 0;

        if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid address.']); exit; }
        if ($is_def) {
            mysqli_query($con, "UPDATE addresses SET is_default=0 WHERE email='$email'");
        }
        $q = "UPDATE addresses SET label='$label',name='$name',phone='$phone',address='$address',
              city='$city',state='$state',zip='$zip',is_default=$is_def
              WHERE id=$id AND email='$email'";
        if (mysqli_query($con, $q) && mysqli_affected_rows($con) >= 0) {
            echo json_encode(['success' => true, 'message' => 'Address updated!', 'is_default' => $is_def]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update address.']);
        }
        break;

    // ── Delete ───────────────────────────────────────────
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid address.']); exit; }
        if (mysqli_query($con, "DELETE FROM addresses WHERE id=$id AND email='$email'")) {
            echo json_encode(['success' => true, 'message' => 'Address deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete.']);
        }
        break;

    // ── Set Default ──────────────────────────────────────
    case 'set_default':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid address.']); exit; }
        mysqli_query($con, "UPDATE addresses SET is_default=0 WHERE email='$email'");
        mysqli_query($con, "UPDATE addresses SET is_default=1 WHERE id=$id AND email='$email'");
        echo json_encode(['success' => true, 'message' => 'Default address updated.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
