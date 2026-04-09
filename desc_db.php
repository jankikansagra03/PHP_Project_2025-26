<?php
require 'db_config.php';
$res = mysqli_query($con, "SHOW COLUMNS FROM orders");
while($row=mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>
