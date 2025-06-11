<?php
$host = 'localhost';
$db   = 'payroll_system';
$user = 'root';
$pass = '';

$con = new mysqli($host, $user, $pass, $db);
if(!$con){
    die(mysqli_error($con));
}
?>