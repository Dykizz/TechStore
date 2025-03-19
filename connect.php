<?php

// Thông tin kết nối cơ sở dữ liệu
$host = "localhost";        // Máy chủ MySQL (thường là localhost)
$username = "root";         // Tên người dùng MySQL (mặc định là root trong XAMPP)
$password = "";             // Mật khẩu MySQL (mặc định trống trong XAMPP)
$database = "techstore";    // Tên cơ sở dữ liệu

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8");

?>