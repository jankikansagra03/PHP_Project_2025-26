<?php
include_once 'db_config.php';
ob_start();
$q = "select * from registration";

$data = mysqli_query($con, $q);
?>

<table class="table table-bordered table-striped table-hover table-responsive">
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Profile Picture</th>
        <th>Actions</th>
    </tr>

    <?php

    while ($r = mysqli_fetch_assoc($data)) {
        if ($r['profile_picture'] == NULL) {
            $r['profile_picture'] = 'default.png';
        }
    ?>
        <tr>
            <td><?= $r['id']; ?></td>
            <td><?= $r['fullname']; ?></td>
            <td><?= $r['email']; ?></td>
            <td>
                <img src="images/profile_pictures/<?= $r['profile_picture']; ?>" alt="" width="50" height="50" class="rounded-circle img-fluid">
            </td>
            <td>
                <a href="view_user.php?user_id=<?= $r['id']; ?>">
                    <button class="btn btn-primary btn-sm">View</button>
                </a>
                <a href="edit_user.php?user_id=<?= $r['id']; ?> ">
                    <button class="btn btn-warning btn-sm">Edit</button>
                </a>
                <a href="delete_user.php?user_id=<?= $r['id']; ?> ">
                    <button class="btn btn-danger btn-sm">Delete</button>
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
