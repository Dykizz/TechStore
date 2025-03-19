<?php
require "db_connect.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orderId = isset($_POST['orderId']) ? intval($_POST['orderId']) : 0;
    $status = $_POST['status'] ?? null;

    $validStatuses = ['Pending', 'Confirmed', 'Delivered', 'Cancelled'];

    if ($orderId <= 0) {
        echo json_encode(["status" => "danger", "message" => "Dữ liệu không hợp lệ!"]);
        exit();
    }

    if (!in_array($status, $validStatuses)) {
        echo json_encode(["status" => "danger", "message" => "Trạng thái không hợp lệ!"]);
        exit();
    }

    // Lấy trạng thái hiện tại của đơn hàng
    $sql = "SELECT status FROM Orders WHERE orderId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) {
        echo json_encode(["status" => "danger", "message" => "Đơn hàng không tồn tại!"]);
        exit();
    }

    $currentStatus = $order['status'];

    // Nếu từ "Pending" → "Confirmed" thì giảm số lượng tồn kho
    // Nếu từ "Confirmed" → "Cancelled" thì tăng số lượng tồn kho lại
    if ($currentStatus !== $status) {
        $conn->begin_transaction(); // Bắt đầu transaction để đảm bảo tính nhất quán

        if ($status === "Confirmed") {
            // Lấy danh sách sản phẩm trong đơn hàng
            $sql = "SELECT productId, quantity FROM OrderDetail WHERE orderId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()) {
                $sqlUpdateStock = "UPDATE Product SET stock = stock - ? WHERE productId = ? AND stock >= ?";
                $stmtUpdate = $conn->prepare($sqlUpdateStock);
                $stmtUpdate->bind_param("iii", $row['quantity'], $row['productId'], $row['quantity']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
        } elseif ($currentStatus === "Confirmed" && $status === "Cancelled") {
            // Hoàn lại số lượng sản phẩm khi huỷ đơn hàng đã Confirmed
            $sql = "SELECT productId, quantity FROM OrderDetail WHERE orderId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()) {
                $sqlUpdateStock = "UPDATE Product SET stock = stock + ? WHERE productId = ?";
                $stmtUpdate = $conn->prepare($sqlUpdateStock);
                $stmtUpdate->bind_param("ii", $row['quantity'], $row['productId']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
        }

        // Cập nhật trạng thái đơn hàng
        $sql = "UPDATE Orders SET status = ?, statusUpdatedAt = NOW() WHERE orderId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $orderId);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $conn->commit(); // Xác nhận transaction
            echo json_encode(["status" => "success", "message" => "Cập nhật trạng thái đơn hàng thành công!"]);
        } else {
            $conn->rollback(); // Hoàn tác nếu lỗi
            echo json_encode(["status" => "danger", "message" => "Lỗi khi cập nhật trạng thái đơn hàng!"]);
        }
    } else {
        echo json_encode(["status" => "warning", "message" => "Trạng thái không thay đổi!"]);
    }

    exit();
} else {
    echo json_encode(["status" => "danger", "message" => "Method không hợp lệ!"]);
    exit();
}
?>
