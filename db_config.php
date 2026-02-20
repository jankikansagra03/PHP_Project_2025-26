<?php
//Step-1 : Create connection
$con = mysqli_connect("localhost", "root", "",);

if ($con) {
    // echo "Connection successful";
} else {
    echo "Error in connection";
}

//step-3: select database
try {
    mysqli_select_db($con, "PHP_Project_25_261");
} catch (Exception) {
    echo "Error in connecting with DB";
}

// *********************REGISTRATION TABLE*************** */
// $q = "create table registration (
//     id int auto_increment primary key,
//     fullname char(50),
//     email varchar(50) unique,
//     password varchar(25),
//     gender char(6),
//     mobile bigint(10),
//     profile_picture varchar(100),
//     address text,
//     status char(8) default 'Inactive',
//     role char(10) default 'User',
//     token text
//     )";


// if (mysqli_query($con, $q)) {
//     echo "Table Created";
// } else {
//     echo "Error in creating table";
// }
// Step-2: create database this is onetime process so we can comment it after creating database

// $create_db = "create database PHP_Project_25_261";

// if (mysqli_query($con, $create_db)) {
//     echo "Database created";
// } else {
//     echo "error in creating database";
// }