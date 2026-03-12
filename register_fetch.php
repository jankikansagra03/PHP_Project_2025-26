<?php
include_once 'db_config.php';
ob_start();

$q = "select * from registration";

$data = mysqli_query($con, $q);
?>

<table class="table table-striped table-responsive table-hover">
    <tr>
        <th>ID</th>
        <th>Fullname</th>
        <th>Email</th>
        <th>Profile Picture</th>
        <th>Actions</th>
    </tr>


    <?php

    while ($res = mysqli_fetch_assoc($data)) {
        if ($res['profile_picture'] == null) {
            $res['profile_picture'] = "default.png";
        }
    ?>
        <tr>
            <td><?= $res['id']; ?></td>
            <td><?= $res['fullname'] ?></td>
            <td><?= $res['email'] ?></td>
            <td>
                <img src="images/profile_pictures/<?= $res['profile_picture'] ?>" alt="" width="50px" height="50px" class="rounded-circle img-fluid">
            </td>
            <td>
                <a href="register_view_details.php?user_id=<?= $res['id'] ?>">
                    <button class="btn btn-secondary">View</button>
                </a>
                <a href="">
                    <button class="btn btn-primary">Edit</button>
                </a>
                <a href="">
                    <button class="btn btn-danger">Delete</button>
                </a>
            </td>
        </tr>
    <?php
    }
    ?>
</table>
<?php
$content = ob_get_clean();
include_once 'layout.php';
