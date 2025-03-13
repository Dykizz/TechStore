<?php
include 'connect.php';
include 'information.php';

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!'
    ]);
    exit;
}

$userId = $_SESSION['userId'];
$productId = isset($_POST['productId']) ? intval($_POST['productId']) : 0;
$name = isset($_POST['name']) ? $_POST['name'] : '';
$price = isset($_POST['price']) ? intval($_POST['price']) : 0;
$image = isset($_POST['image']) ? $_POST['image'] : '';

// Validate input
if (empty($productId) || empty($name) || empty($price) || empty($image)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Thông tin sản phẩm không hợp lệ!'
    ]);
    exit;
}

// Check if product exists in cart already
$checkSql = "SELECT * FROM CartItem WHERE userId = ? AND productId = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $userId, $productId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Product already in cart, update quantity
    $cartItem = $result->fetch_assoc();
    $newQuantity = $cartItem['quantity'] + 1;
    
    $updateSql = "UPDATE CartItem SET quantity = ? WHERE userId = ? AND productId = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("iii", $newQuantity, $userId, $productId);
    
    if ($updateStmt->execute()) {
        // Success
        $countSql = "SELECT SUM(quantity) as total FROM CartItem WHERE userId = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $userId);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalQuantity = $countResult->fetch_assoc()['total'];
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã cập nhật số lượng sản phẩm trong giỏ hàng!',
            'cartQuantity' => $totalQuantity
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Không thể cập nhật giỏ hàng: ' . $conn->error
        ]);
    }
} else {
    // Product not in cart, insert new item
    $insertSql = "INSERT INTO CartItem (userId, productId, quantity) VALUES (?, ?, 1)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ii", $userId, $productId);
    
    if ($insertStmt->execute()) {
        // Success
        $countSql = "SELECT SUM(quantity) as total FROM CartItem WHERE userId = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $userId);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalQuantity = $countResult->fetch_assoc()['total'];
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
            'cartQuantity' => $totalQuantity
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Không thể thêm sản phẩm vào giỏ hàng: ' . $conn->error
        ]);
    }
}

$conn->close();
?>