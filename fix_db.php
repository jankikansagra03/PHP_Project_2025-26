<?php
require 'db_config.php';
mysqli_query($con, "ALTER TABLE orders ADD COLUMN payment_txn_id VARCHAR(255) NULL AFTER payment_status");
if(mysqli_error($con)) echo mysqli_error($con); else echo 'Success';
?>
