<?php
require "db_connect.php";
header('Content-Type: application/json');
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $status = isset($_POST['status']) ?? 'ACTIVE';
    // Validate input data
    if ($userId <= 0 ) {
        echo json_encode(["status" => "danger", "message" => "Dữ liệu không hợp lệ!"]);
        exit();
    }

    $sql = "UPDATE User SET status = ?, updatedAt = NOW() WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "danger", "message" => "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("si",$status, $userId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Thay đổi trạng thái thành công!"]);
        exit();
    }else{
        echo json_encode(["status" => "danger", "message" => "Lỗi không chuyển đổi trạng thái"]);
        exit();
    }
    echo json_encode(["status" => "danger", "message" => "Method không hợp lệ!"]);
    $stmt->close();
    exit();
}

?>