<?php
session_start();
session_destroy(); // Xóa toàn bộ session
header("Location: login.php"); // Chuyển hướng về trang đăng nhập
exit();
?>
