<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Super Admin', 'Manager'])) {
    header("Location: ./logout.php");
    exit();
}
?>
