<?php
header('Content-Type: application/json');
require_once 'db_connect.php'; // Kết nối database

$response = ["status" => "error", "message" => "Yêu cầu không hợp lệ"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["productId"])) {
    require_once 'db_connect.php';

    $productId = intval($_POST["productId"]);

    if (!$conn) {
        echo json_encode(["status" => "error", "message" => "Lỗi kết nối database!"]);
        exit();
    }

    // Kiểm tra xem sản phẩm đã từng được bán chưa
    $sqlCheckOrder = "SELECT COUNT(*) AS total FROM OrderDetail WHERE productId = ?";
    $stmtCheckOrder = $conn->prepare($sqlCheckOrder);
    $stmtCheckOrder->bind_param("i", $productId);
    $stmtCheckOrder->execute();
    $resultCheck = $stmtCheckOrder->get_result();
    $rowCheck = $resultCheck->fetch_assoc();
    $stmtCheckOrder->close();

    if ($rowCheck["total"] > 0) {
        // Nếu sản phẩm đã từng được bán, cập nhật isActive = 0
        $sqlUpdate = "UPDATE Product SET isActive = 0 WHERE productId = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("i", $productId);
        
        if ($stmtUpdate->execute()) {
            $response = ["status" => "success", "message" => "Sản phẩm đã từng được bán, chuyển sang không hoạt động."];
        } else {
            $response = ["status" => "error", "message" => "Lỗi khi cập nhật trạng thái sản phẩm!"];
        }

        $stmtUpdate->close();
    } else {
        // Nếu chưa từng được bán, tiến hành xóa như cũ
        $sqlGetImage = "SELECT image FROM Product WHERE productId = ?";
        $stmtGetImage = $conn->prepare($sqlGetImage);
        $stmtGetImage->bind_param("i", $productId);
        $stmtGetImage->execute();
        $result = $stmtGetImage->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $imagePath = "../" . $row["image"];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $stmtGetImage->close();

        $sqlDelete = "DELETE FROM Product WHERE productId = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $productId);

        if ($stmtDelete->execute()) {
            $response = ["status" => "success", "message" => "Sản phẩm đã được xóa thành công!"];
        } else {
            $response = ["status" => "error", "message" => "Lỗi khi xóa sản phẩm!"];
        }

        $stmtDelete->close();
    }

    $conn->close();
}

echo json_encode($response);
exit();
?>
