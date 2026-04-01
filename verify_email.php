<?php
include_once 'db_config.php';

if (isset($_GET['token']) && isset($_GET['em'])) {
    $token = $_GET['token'];
    $email = $_GET['em'];

    $q = "select * from registration where email='$email' and token='$token'";
    $res = mysqli_query($con, $q);
    $data = mysqli_fetch_Assoc($res);
    if ($data['status'] == 'Active') {
        setcookie("success", "Email alreday verified");
        // $url1="login.php";
    } else {
        $update = "update registration set status='Active' where email='$email' and token='$token'";
        echo $update;
        if (mysqli_query($con, $update)) {
            setcookie("success", "Email verified successfully", time() + 5);
        } else {
            setcookie("error", "Error in verifying email", time() + 5);
        }
    }
} else {
    setcookie("error", "Invalid verification link.", time() + 5, "/");
}

?>
<script>
    window.location.href = "login.php";
</script>