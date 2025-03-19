<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if (!$userId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vui lòng đăng nhập để thực hiện thao tác!'
    ]);
    exit;
}

function getCartQuantity($conn, $userId) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM CartItem WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'] ?? 0;
}

switch ($action) {
    case 'add':
        $productId = isset($_POST['productId']) ? (int)$_POST['productId'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
        $image = isset($_POST['image']) ? trim($_POST['image']) : '';
        $quantity = 1;

        if (!$productId || !$name || !$price || !$image) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Thông tin sản phẩm không hợp lệ!'
            ]);
            exit;
        }

        // Kiểm tra tồn kho
        $stockStmt = $conn->prepare("SELECT stock FROM Product WHERE productId = ?");
        $stockStmt->bind_param("i", $productId);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        $stock = $stockResult->fetch_assoc()['stock'] ?? 0;

        if ($stock < $quantity) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sản phẩm đã hết hàng hoặc không đủ số lượng!'
            ]);
            exit;
        }

        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $checkStmt = $conn->prepare("SELECT cartItemId, quantity FROM CartItem WHERE userId = ? AND productId = ?");
        $checkStmt->bind_param("ii", $userId, $productId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $row = $checkResult->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            if ($newQuantity > $stock) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Số lượng vượt quá tồn kho!'
                ]);
                exit;
            }
            $updateStmt = $conn->prepare("UPDATE CartItem SET quantity = ? WHERE cartItemId = ?");
            $updateStmt->bind_param("ii", $newQuantity, $row['cartItemId']);
            $updateStmt->execute();
            $message = 'Đã cập nhật số lượng sản phẩm trong giỏ hàng!';
        } else {
            $insertStmt = $conn->prepare("INSERT INTO CartItem (userId, productId, quantity) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $userId, $productId, $quantity);
            $insertStmt->execute();
            $message = 'Đã thêm sản phẩm vào giỏ hàng!';
        }

        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'cartQuantity' => getCartQuantity($conn, $userId)
        ]);
        break;

    case 'update_quantity':
        $productId = isset($_POST['productId']) ? (int)$_POST['productId'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

        if ($productId <= 0 || $quantity < 1) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Số lượng không hợp lệ!'
            ]);
            exit;
        }

        // Kiểm tra tồn kho
        $stockStmt = $conn->prepare("SELECT stock FROM Product WHERE productId = ?");
        $stockStmt->bind_param("i", $productId);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        $stock = $stockResult->fetch_assoc()['stock'] ?? 0;

        if ($quantity > $stock) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Số lượng vượt quá tồn kho!'
            ]);
            exit;
        }

        $updateStmt = $conn->prepare("UPDATE CartItem SET quantity = ? WHERE userId = ? AND productId = ?");
        $updateStmt->bind_param("iii", $quantity, $userId, $productId);
        if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Đã cập nhật số lượng!',
                'cartQuantity' => getCartQuantity($conn, $userId)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Không tìm thấy sản phẩm trong giỏ hàng!'
            ]);
        }
        break;

    case 'remove':
        $productId = isset($_POST['productId']) ? (int)$_POST['productId'] : 0;

        if ($productId <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sản phẩm không hợp lệ!'
            ]);
            exit;
        }

        $deleteStmt = $conn->prepare("DELETE FROM CartItem WHERE userId = ? AND productId = ?");
        $deleteStmt->bind_param("ii", $userId, $productId);
        if ($deleteStmt->execute() && $deleteStmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!',
                'cartQuantity' => getCartQuantity($conn, $userId)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Không tìm thấy sản phẩm trong giỏ hàng!'
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Hành động không hợp lệ!'
        ]);
}

$conn->close();
exit;
?>