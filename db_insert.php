<?php
include_once "db_config.php";
$token = bin2hex(random_bytes(15));

$insert_query = "
INSERT INTO `register`(`name`, `email`, `password`, `mobile`, `gender`, `profile_picture`, `token`) VALUES ('janki','janki@gmail.com','Janki@1234',9876543210,'female','default.png','$token')";

if (
    mysqli_query($con, $insert_query)
) {
    echo "New record created successfully";
} else {
    echo "Error: " . $insert_query . "<br>";
}
