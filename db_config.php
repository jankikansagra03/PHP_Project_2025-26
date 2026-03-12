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
