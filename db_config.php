<?php
//Step-1 : Create connection
$con = mysqli_connect("localhost", "root", "");

if ($con) {
    // echo "Connection successful";
} else {
    echo "Error in connection";
}

//step-3: select database
try {
    mysqli_select_db($con, "25_26_A");
} catch (Exception) {
    echo "Error in connecting with DB";
}
// // Step-4: create table 
// $create_table = "create table register(
// id int auto_increment primary key, 
// name char(30), email varchar(20), 
// password varchar(20),
// mobile bigint(10),
// gender char(10), 
// profile_picture text,
// role char(20) default 'user',
// status char(10) default 'Inactive')";

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
