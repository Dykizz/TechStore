<?php 
// Include database connection
require "db_connect.php"; // Ensure this file defines $conn

// Set response header
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get and sanitize input data
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $gender = $_POST["gender"] ?? null;
    $status = $_POST["status"] ?? null;
    $dateOfBirth = !empty($_POST["dateOfBirth"]) ? $_POST["dateOfBirth"] : null;
    $address = trim($_POST["address-default"] ?? '');

    // Validate input data
    if ($userId <= 0 || empty($name) || empty($email) || empty($phone)) {
        echo json_encode(["status" => "danger", "message" => "Dữ liệu không hợp lệ!"]);
        exit();
    }

    // Handle avatar upload
    $avatarPath = null;
    if (!empty($_FILES["img-client"]["name"])) {
        $uploadDir = "../img/";
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            echo json_encode(["status" => "danger", "message" => "Thư mục upload không tồn tại hoặc không ghi được!"]);
            exit();
        }
        $avatarLocation = $uploadDir . basename($_FILES["img-client"]["name"]);
        $avatarPath = "img/" . basename($_FILES["img-client"]["name"]);
        
        if (!move_uploaded_file($_FILES["img-client"]["tmp_name"], $avatarLocation)) {
            echo json_encode(["status" => "danger", "message" => "Lỗi khi tải ảnh đại diện lên!"]);
            exit();
        }
    }

    // Truy vấn ảnh của user
    if ($avatarPath == null){
        $sqlGetImage = "SELECT avatar FROM User WHERE userId = ?";
        $stmtGetImage = $conn->prepare($sqlGetImage);
        $stmtGetImage->bind_param("i", $userId);
        $stmtGetImage->execute();
        $result = $stmtGetImage->get_result();
        $row = $result->fetch_assoc();

        if ($row["avatar"]) {
            $imagePath = "../" . $row["avatar"];
            if (file_exists($imagePath)) {
                unlink($imagePath); // Xóa file ảnh
            }
        }
    }

    // Update user information
    $sql = "UPDATE User SET 
                name = ?, email = ?, phoneNumber = ?, gender = ?, status = ?, dateOfBirth = ?, updatedAt = NOW() , avatar = ? WHERE userId = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "danger", "message" => "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("sssssssi", $name, $email, $phone, $gender, $status, $dateOfBirth, $avatarPath, $userId);
    

    if ($stmt->execute()) {
        // Update or insert default address if provided
        if (!empty($address)) {
            // Check if a default address exists
            $sqlCheck = "SELECT COUNT(*) FROM UserAddress WHERE userId = ? AND isDefault = TRUE";
            $stmtCheck = $conn->prepare($sqlCheck);
            if (!$stmtCheck) {
                echo json_encode(["status" => "danger", "message" => "Lỗi kiểm tra địa chỉ: " . $conn->error]);
                exit();
            }
            $stmtCheck->bind_param("i", $userId);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                // Update existing default address
                $sqlAddress = "UPDATE UserAddress SET address = ? WHERE userId = ? AND isDefault = TRUE";
                $stmtAddress = $conn->prepare($sqlAddress);
                if (!$stmtAddress) {
                    echo json_encode(["status" => "danger", "message" => "Lỗi chuẩn bị cập nhật địa chỉ: " . $conn->error]);
                    exit();
                }
                $stmtAddress->bind_param("si", $address, $userId);
                if (!$stmtAddress->execute()) {
                    echo json_encode(["status" => "danger", "message" => "Lỗi cập nhật địa chỉ: " . $stmtAddress->error]);
                    exit();
                }
            } else {
                // Insert new default address
                $sqlInsert = "INSERT INTO UserAddress (userId, address, isDefault) VALUES (?, ?, TRUE)";
                $stmtInsert = $conn->prepare($sqlInsert);
                if (!$stmtInsert) {
                    echo json_encode(["status" => "danger", "message" => "Lỗi chuẩn bị thêm địa chỉ: " . $conn->error]);
                    exit();
                }
                $stmtInsert->bind_param("is", $userId, $address);
                if (!$stmtInsert->execute()) {
                    echo json_encode(["status" => "danger", "message" => "Lỗi thêm địa chỉ: " . $stmtInsert->error]);
                    exit();
                }
            }
        }
        // Success response
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
    } else {
        echo json_encode(["status" => "danger", "message" => "Lỗi khi cập nhật thông tin người dùng: " . $stmt->error]);
    }
    $stmt->close();
    exit();
}

// If not a POST request, return an error
echo json_encode(["status" => "danger", "message" => "Yêu cầu không hợp lệ!"]);
exit();
?>