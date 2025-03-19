<?php
require "db_connect.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orderId = isset($_POST['orderId']) ? intval($_POST['orderId']) : 0;
    $status = $_POST['status'] ?? null;

    // Danh sách trạng thái hợp lệ
    $validStatuses = ['Pending', 'Confirmed', 'Delivered', 'Cancelled'];

    // Validate input data
    if ($orderId <= 0) {
        echo json_encode(["status" => "danger", "message" => "Dữ liệu không hợp lệ!"]);
        exit();
    }

    if (!in_array($status, $validStatuses)) {
        echo json_encode(["status" => "danger", "message" => "Trạng thái không hợp lệ!"]);
        exit();
    }

    // Chuẩn bị câu lệnh SQL
    $sql = "UPDATE Orders SET status = ?, statusUpdatedAt = NOW() WHERE orderId = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "danger", "message" => "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("si", $status, $orderId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Thay đổi trạng thái đơn hàng thành công!"]);
    } else {
        echo json_encode(["status" => "danger", "message" => "Lỗi không chuyển đổi trạng thái"]);
    }

    $stmt->close();
    exit();
} else {
    echo json_encode(["status" => "danger", "message" => "Method không hợp lệ!"]);
    exit();
}
?>
