<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Kết nối database
include 'connect.php';

// Luôn truy vấn lại database để lấy thông tin mới nhất
$email = $_SESSION['email'];
$sql = "SELECT name FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $fullname = $row['name']; // Lấy từ database
    $_SESSION['fullname'] = $fullname; // Cập nhật session với giá trị mới
} else {
    $fullname = "Không tìm thấy thông tin";
    $_SESSION['fullname'] = $fullname; // Cập nhật session nếu không tìm thấy
}
?>