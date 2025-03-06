<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: /admin/login.php"); // Chuyển hướng nếu chưa đăng nhập
    exit();
}
?>
