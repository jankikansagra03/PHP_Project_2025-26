<?php
session_start();
include_once 'db_config.php';

// Google OAuth configuration
$google_client_id = '315515659521-jhjkoroi9n75d08l4vescphgnu6qdbb8.apps.googleusercontent.com';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setcookie('error', 'Invalid request method for Google login.', time() + 5, '/');
?>
    <script>
        window.location.href = "login.php";
    </script>
<?php
    exit();
}

$posted_csrf = isset($_POST['g_csrf_token']) ? trim($_POST['g_csrf_token']) : '';
$cookie_csrf = isset($_COOKIE['g_csrf_token']) ? trim($_COOKIE['g_csrf_token']) : '';

if ($posted_csrf === '' || $cookie_csrf === '' || $posted_csrf !== $cookie_csrf) {
    setcookie('error', 'Invalid Google login request. Please try again.', time() + 5, '/');
?>
    <script>
        window.location.href = "login.php";
    </script>
<?php
    exit();
}

$id_token = isset($_POST['credential']) ? trim($_POST['credential']) : '';
if ($id_token === '') {
    setcookie('error', 'Google credential missing. Please try again.', time() + 5, '/');
?>
    <script>
        window.location.href = "login.php";
    </script>
<?php
    exit();
}

$token_info_url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token);
$token_info_raw = @file_get_contents($token_info_url);

if ($token_info_raw === false) {
    setcookie('error', 'Unable to verify Google token right now. Please try again.', time() + 5, '/');
?>
    <script>
        window.location.href = "login.php";
    </script>
<?php
    exit();
}

$token_payload = json_decode($token_info_raw, true);
if (!is_array($token_payload)) {
    setcookie('error', 'Invalid response from Google verification.', time() + 5, '/');
?>
    <script>
        window.location.href = "login.php";
    </script>
<?php
    exit();
}

$aud = isset($token_payload['aud']) ? $token_payload['aud'] : '';
$iss = isset($token_payload['iss']) ? $token_payload['iss'] : '';
$email = isset($token_payload['email']) ? trim($token_payload['email']) : '';
$email_verified = isset($token_payload['email_verified']) ? $token_payload['email_verified'] : '';
$name = isset($token_payload['name']) ? trim($token_payload['name']) : '';
$exp = isset($token_payload['exp']) ? (int) $token_payload['exp'] : 0;

$valid_issuer = ($iss === 'accounts.google.com' || $iss === 'https://accounts.google.com');
$valid_expiry = ($exp > 0 && $exp >= time());
$verified_email = ($email_verified === 'true' || $email_verified === true || $email_verified === '1' || $email_verified === 1);

if ($aud !== $google_client_id || !$valid_issuer || !$valid_expiry || !$verified_email || $email === '') {
    setcookie('error', 'Google login validation failed. Please login again.', time() + 5, '/');
?>
    <script>
        window.location.href = "login.php";
    </script>
    <?php
    exit();
}

$safe_email = mysqli_real_escape_string($con, $email);
$lookup_query = "SELECT * FROM registration WHERE email='$safe_email' LIMIT 1";
$lookup_result = mysqli_query($con, $lookup_query);

if ($lookup_result && mysqli_num_rows($lookup_result) === 1) {
    $user = mysqli_fetch_assoc($lookup_result);

    if (isset($user['status']) && strtolower((string) $user['status']) !== 'active') {
        $activate_query = "UPDATE registration SET status='Active' WHERE email='$safe_email'";
        mysqli_query($con, $activate_query);
    }

    if (isset($user['role']) && strtolower((string) $user['role']) === 'admin') {
        $_SESSION['admin'] = $user['email'];
        setcookie('success', 'Google login successful.', time() + 5, '/');
    ?>
        <script>
            window.location.href = "admin_dashboard.php";
        </script>
    <?php
        exit();
    }

    $_SESSION['user'] = $user['email'];
    setcookie('success', 'Google login successful.', time() + 5, '/');
    ?>
    <script>
        window.location.href = "dashboard.php";
    </script>
<?php
    exit();
}

$display_name = $name !== '' ? $name : strstr($email, '@', true);
$safe_name = mysqli_real_escape_string($con, $display_name);
$random_password = mysqli_real_escape_string($con, bin2hex(random_bytes(12)));

$insert_query = "INSERT INTO registration (fullname, email, password, mobile, gender, profile_picture, token, status, role)
VALUES ('$safe_name', '$safe_email', '$random_password', 0, 'Other', 'default.png', NULL, 'Active', 'user')";

if (mysqli_query($con, $insert_query)) {
    $_SESSION['user'] = $email;
    setcookie('success', 'Account created with Google and login successful.', time() + 5, '/');
?>
    <script>
        window.location.href = "dashboard.php";
    </script>
<?php
    exit();
}

setcookie('error', 'Google login failed while creating account.', time() + 5, '/');
?>
<script>
    window.location.href = "login.php";
</script>