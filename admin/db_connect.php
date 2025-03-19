<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techstore"; // Đổi tên database của bạn

// Kết nối MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>