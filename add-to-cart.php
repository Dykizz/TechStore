<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productId'])) {
    $productId = $_POST['productId'];
    $name = $_POST['name'];
    $price = (int)$_POST['price'];
    $image = $_POST['image'];

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Kiểm tra nếu sản phẩm đã tồn tại, tăng số lượng; nếu không, thêm mới
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => 1
        ];
    }

    // Chuyển hướng về trang trước với thông báo
    header("Location: store-accessories.php?category=" . $_GET['category'] . "&message=Thêm vào giỏ hàng thành công!");
    exit();
}

$conn->close();
?>