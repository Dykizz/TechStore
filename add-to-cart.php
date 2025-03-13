<?php
session_start();
header('Content-Type: application/json');

$productId = isset($_POST['productId']) ? (int)$_POST['productId'] : 0;
$name = isset($_POST['name']) ? $_POST['name'] : '';
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$image = isset($_POST['image']) ? $_POST['image'] : '';

if ($productId > 0 && $name && $price > 0) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => 1
        ];
    }

    $cartQuantity = array_sum(array_column($_SESSION['cart'], 'quantity'));

    echo json_encode([
        'status' => 'success',
        'message' => 'Thêm sản phẩm vào giỏ hàng thành công!',
        'cartQuantity' => $cartQuantity
    ]);
} else {
    echo json_encode([
        'status' => 'danger',
        'message' => 'Dữ liệu không hợp lệ!'
    ]);
}
exit;
?>