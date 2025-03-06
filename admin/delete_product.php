<?php
header('Content-Type: application/json'); // Đảm bảo phản hồi là JSON
require_once 'db_connect.php'; // Đảm bảo kết nối database

$response = ["status" => "error", "message" => "Yêu cầu không hợp lệ"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["productId"])) {
    require_once 'db_connect.php'; // Kết nối database

    $productId = intval($_POST["productId"]);

    // Kiểm tra kết nối database
    if (!$conn) {
        echo json_encode(["status" => "error", "message" => "Lỗi kết nối database!"]);
        exit();
    }

    // Truy vấn ảnh của sản phẩm
    $sqlGetImage = "SELECT image FROM Product WHERE productId = ?";
    $stmtGetImage = $conn->prepare($sqlGetImage);
    $stmtGetImage->bind_param("i", $productId);
    $stmtGetImage->execute();
    $result = $stmtGetImage->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $imagePath = "../" . $row["image"];
        if (file_exists($imagePath)) {
            unlink($imagePath); // Xóa file ảnh
        }
    }

    $stmtGetImage->close();

    // Xóa sản phẩm khỏi database
    $sqlDelete = "DELETE FROM Product WHERE productId = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $productId);

    if ($stmtDelete->execute()) {
        $response = ["status" => "success", "message" => "Sản phẩm đã được xóa thành công!"];
    } else {
        $response = ["status" => "error", "message" => "Lỗi khi xóa sản phẩm!"];
    }

    $stmtDelete->close();
    $conn->close();
}

echo json_encode($response);
exit();
?>
