<?php
include_once 'db_config.php';
ob_start();

if (isset($_GET['user_id'])) {
    $uid = $_GET['user_id'];
    // echo $uid;
    $q = "select * from registration where id='$uid'";
    $data = mysqli_query($con, $q);
    $res = mysqli_fetch_assoc($data);
    if($res['profile_picture'] == null){
        $res['profile_picture'] = "default.png";
    }

?>

    <div class="card">
        
        <img src="images/profile_pictures/<?= $res['profile_picture'] ?>" class="card-img-top" alt="...">
        <div class="card-body">
            <h5 class="card-title"><?= $res['fullname'] ?></h5>
            <p class="card-text"><?= $res['email'] ?></p>
            <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
    </div>

<?php
} else {
    echo "User ID is not set";
}

$content = ob_get_clean();
include_once 'layout.php';
