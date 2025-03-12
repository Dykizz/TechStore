<?php
include 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Lấy thông tin hiện tại từ database khi tải trang (GET)
$fullname = '';
$phone = '';
$sex = 'MALE';
$birthday = '';
$address = '';
$email = $_SESSION['email'] ?? '';
$avatarPath = './img/avarta-man.png'; // Giá trị mặc định

$sql = "SELECT name, phoneNumber, gender, dateOfBirth, email, avatar FROM user WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $fullname = $row['name'] ?? '';
    $phone = $row['phoneNumber'] ?? '';
    $sex = $row['gender'] ?? 'MALE';
    $birthday = $row['dateOfBirth'] ?? '';
    $email = $row['email'] ?? $_SESSION['email'];
    $avatarPath = $row['avatar'] ?? './img/avarta-man.png';
}
$stmt->close();

$sql_address = "SELECT address FROM useraddress WHERE userId = ? AND isDefault = 1";
$stmt = $conn->prepare($sql_address);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $address = $row['address'] ?? '';
}
$stmt->close();

// Xử lý yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');

    $name = trim($_POST["name"] ?? '');
    $email = trim($_SESSION['email'] ?? '');
    $phone = trim($_POST["phone"] ?? '') ?: null;
    $gender = $_POST["sex"] ?? null;
    $status = 'ACTIVE';
    $dateOfBirth = trim($_POST["birthday"] ?? '') ?: null;
    $address = trim($_POST["address"] ?? '') ?: null;

    if (empty($name)) {
        echo json_encode(["status" => "danger", "message" => "Họ và tên không được để trống!"]);
        exit();
    }
    // Chỉ kiểm tra nếu số điện thoại không rỗng, cho phép null
    if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        echo json_encode(["status" => "danger", "message" => "Số điện thoại không hợp lệ! Phải từ 10 đến 15 chữ số. Giá trị nhập: '$phone'"]);
        exit();
    }
    if (!in_array($gender, ['MALE', 'FEMALE', null])) {
        echo json_encode(["status" => "danger", "message" => "Giới tính không hợp lệ!"]);
        exit();
    }

    $avatarPathNew = null;
    if (!empty($_FILES["avatar-input"]["name"])) {
        $uploadDir = "../img/";
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            echo json_encode(["status" => "danger", "message" => "Thư mục upload không tồn tại hoặc không ghi được!"]);
            exit();
        }
        $avatarLocation = $uploadDir . basename($_FILES["avatar-input"]["name"]);
        $avatarPathNew = "img/" . basename($_FILES["avatar-input"]["name"]);
        
        if (!move_uploaded_file($_FILES["avatar-input"]["tmp_name"], $avatarLocation)) {
            echo json_encode(["status" => "danger", "message" => "Lỗi khi tải ảnh đại diện lên!"]);
            exit();
        }
    } else {
        $avatarPathNew = $avatarPath; // Giữ ảnh cũ nếu không upload mới
    }

    $sql = "UPDATE user SET name = ?, email = ?, phoneNumber = ?, gender = ?, status = ?, dateOfBirth = ?, avatar = ?, updatedAt = NOW() WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "danger", "message" => "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("sssssssi", $name, $email, $phone, $gender, $status, $dateOfBirth, $avatarPathNew, $userId);

    if ($stmt->execute()) {
        if (!empty($address)) {
            $sqlCheck = "SELECT shippingAddressId FROM useraddress WHERE userId = ? AND isDefault = 1";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $userId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $count = $result->num_rows;
            $stmtCheck->close();

            if ($count > 0) {
                $row = $result->fetch_assoc();
                $shippingAddressId = $row['shippingAddressId'];
                $sqlAddress = "UPDATE useraddress SET address = ? WHERE shippingAddressId = ?";
                $stmtAddress = $conn->prepare($sqlAddress);
                $stmtAddress->bind_param("si", $address, $shippingAddressId);
                $stmtAddress->execute();
                $stmtAddress->close();
            } else {
                $sqlInsert = "INSERT INTO useraddress (userId, address, isDefault) VALUES (?, ?, 1)";
                $stmtInsert = $conn->prepare($sqlInsert);
                $stmtInsert->bind_param("is", $userId, $address);
                $stmtInsert->execute();
                $stmtInsert->close();
            }
        }
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
    } else {
        echo json_encode(["status" => "danger", "message" => "Lỗi khi cập nhật thông tin: " . $stmt->error]);
    }
    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="alert alert-show announce" role="alert" id="message-box"></div>
    <header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> Hotline: <strong>+84 975 419 019</strong></a></li>
                    <li><a href="#"><i class="fa fa-envelope-o"></i> nhom6@email.com </a></li>
                    <li><a href="#"><i class="fa fa-map-marker"></i> 273 An Dương Vương, Phường 3, Quận 5</a></li>
                </ul>
            </div>
        </div>
        <div id="header">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="header-logo">
                            <a href="./index.php" class="logo">
                                <img src="./img/logo.png" alt="" />
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="header-search">
                            <form action="./store-search.html">
                                <input name="keyword" class="input" placeholder="Nhập sản phẩm muốn tìm kiếm ..." />
                                <button class="search-btn">Tìm kiếm</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-3 clearfix">
                        <div class="header-ctn">
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                    <i class="fa fa-user-o"></i>
                                    <span><?php echo htmlspecialchars($fullname); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="./account-information.php">Thông tin cá nhân</a></li>
                                    <li><a href="./purchasing-history.php">Lịch sử mua hàng</a></li>
                                    <li><a href="./change-password.php">Đổi mật khẩu</a></li>
                                    <li><a href="./index-notlogin.php">Đăng xuất</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="section">
            <h2 class="text-center">Thông tin tài khoản</h2>
            <div class="account-info-form">
                <form action="account-information.php" method="POST" enctype="multipart/form-data">
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Avatar" id="avatar-preview">
                        </div>
                        <div class="avatar-edit">
                            <label for="avatar-input" class="upload-button">
                                <i class="fa fa-camera"></i>
                                Thay đổi ảnh đại diện
                            </label>
                            <input type="file" name="avatar-input" id="avatar-input" accept="image/*" style="display: none;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Họ và tên</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($fullname); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Nhập số điện thoại">
                    </div>
                    <div class="form-group" id="sex-group">
                        <label>Giới tính</label>
                        <div class="radio-group">
                            <label for="sex-male">
                                <input type="radio" id="sex-male" name="sex" value="MALE" <?php echo ($sex == 'MALE') ? 'checked' : ''; ?>> Nam
                            </label>
                            <label for="sex-female">
                                <input type="radio" id="sex-female" name="sex" value="FEMALE" <?php echo ($sex == 'FEMALE') ? 'checked' : ''; ?>> Nữ
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="birthday">Ngày sinh</label>
                        <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($birthday); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <textarea name="address" id="address" rows="3" placeholder="Nhập địa chỉ"><?php echo htmlspecialchars($address); ?></textarea>
                    </div>
                    <div>
                        <button type="submit" class="custom-btn primary-btn btn-announce">Cập nhật thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/helper.js"></script>
    <script>
    $(document).ready(function() {
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    const data = JSON.parse(response);
                    const messageBox = $('#message-box');
                    messageBox.text(data.message).removeClass('alert-success alert-danger');
                    messageBox.addClass(data.status === 'success' ? 'alert-success' : 'alert-danger');
                    messageBox.show();
                    if (data.status === 'success') {
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function(xhr) {
                    const messageBox = $('#message-box');
                    messageBox.text('Lỗi khi gửi yêu cầu: ' + xhr.statusText).addClass('alert-danger').show();
                }
            });
        });

        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>