<?php
//Step-1 : Create connection
try {
    $con = mysqli_connect("localhost", "root", "");
    if (!$con) {
        throw new Exception();
    }
} catch (Exception) {
    echo "Error in connection";
}

//step-3: select database
try {
    mysqli_select_db($con, "php_project_25_26");
} catch (Exception) {
    echo "Error in connecting with DB";
}

date_default_timezone_set('Asia/Kolkata');
$current_time = date("Y-m-d H:i:s");
// Reset OTP resend attempts after 24 hours from last_resend.
$reset_otp_attempts_query = "UPDATE password_token 
SET otp_attempts = 0 
WHERE last_resend IS NOT NULL 
AND last_resend <= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
mysqli_query($con, $reset_otp_attempts_query);


// set otp to null after 2 minutes of generation

$expire_otp_query = "UPDATE password_token SET otp = NULL WHERE expires_at < NOW()";
mysqli_query($con, $expire_otp_query);

// // Step-4: create table   is a one time process so after creating table we can comment it
// $create_table = "create table register(
// id int auto_increment primary key, 
// name char(30), email varchar(20), 
// password varchar(20),
// mobile bigint(10),
// gender char(10), 
// profile_picture text,
// role char(20) default 'user',
// status char(10) default 'Inactive',
// token varchar(255) default null)";

// if (mysqli_query($con, $create_table)) {
//     echo "Table created";
// } else {
//     echo "error in creating table";
// }

// Step-2: create database this is onetime process so we can comment it after creating database

// $create_db = "create database 25_26_A";

// if (mysqli_query($con, $create_db)) {
//     echo "Database created";
// } else {
//     echo "error in creating database";
// }
