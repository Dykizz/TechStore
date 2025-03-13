<?php
include 'connect.php';

session_start(); // Bắt đầu session

if (!isset($_SESSION['email'])) {
    header("Location: login.php"); 
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT name, phoneNumber, gender, dateOfBirth FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $fullname = $row['name'] ?? '';
    $phone = $row['phone'] ?? '';
    $sex = $row['gender'] ?? 'Nam';
    $birthday = $row['birthday'] ?? '2005-03-15';
    $address = $row['address'] ?? '123 Đường Nguyễn Thị Minh Khai, Phường 6, Quận 3, TP. Hồ Chí Minh';
    $_SESSION['fullname'] = $fullname; // Cập nhật session
} else {
    $fullname = "Không tìm thấy thông tin"; 
    $phone = '';
    $sex = 'Nam';
    $birthday = '2005-03-15';
    $address = '';
}

$message = ''; // Khai báo biến thông báo

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_fullname = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    //$sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    if (empty($phone)) {
      $phone = NULL; // Hoặc đặt giá trị mặc định hợp lệ (ví dụ: '0000000000')
  } elseif (!preg_match('/^[0-9]{10}$/', $phone)) { // Kiểm tra định dạng số điện thoại 10 chữ số
      $message = "Số điện thoại không hợp lệ! Phải là 10 chữ số.";
  } else {
      // Cập nhật bảng user
      $update_user_sql = "UPDATE users SET name = '$new_fullname', phoneNumber = '$phone', gender = '$sex', dateOfBirth = '$birthday' WHERE email = '" . mysqli_real_escape_string($conn, $_SESSION['email']) . "'";
      if (mysqli_query($conn, $update_user_sql)) {
          $_SESSION['fullname'] = $new_fullname; // Cập nhật session
          $message = "Đã cập nhật thông tin cá nhân thành công!";
      } else {
          $message = "Lỗi khi cập nhật user: " . mysqli_error($conn);
      }
    // Cập nhật bảng user
    $update_user_sql = "UPDATE users SET name = '$new_fullname', phoneNumber = '$phone', gender = '$sex', dateOfBirth = '$birthday' WHERE email = '" . mysqli_real_escape_string($conn, $_SESSION['email']) . "'";
    if (mysqli_query($conn, $update_user_sql)) {
        $_SESSION['fullname'] = $new_fullname; // Cập nhật session
        $message = "Đã cập nhật thông tin cá nhân thành công!";
    } else {
        $message = "Lỗi khi cập nhật user: " . mysqli_error($conn);
    }

    // Cập nhật bảng useraddress (nếu tồn tại)
    $update_address_sql = "UPDATE useraddress SET address = '$address' WHERE userId = '" . mysqli_real_escape_string($conn, $_SESSION['email']) . "'";
    if (mysqli_query($conn, $update_address_sql)) {
        $message = "Đã cập nhật thông tin cá nhân thành công!";
    } else {
        $message .= " | Lỗi khi cập nhật address: " . mysqli_error($conn);
    }
}
}
mysqli_close($conn);
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
    <div class="alert alert-show announce" role="alert"><?php echo $message; ?></div>
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
                <!-- Giỏ hàng giữ nguyên -->
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
            <form method="POST" action="">
                <div class="avatar-upload">
                    <div class="avatar-preview">
                      <img src="./img/avarta-man.png" alt="Avatar" id="avatar-preview">
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
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($fullname); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>">
                </div>
                <div class="form-group" id="sex-group">
                    <label>Giới tính</label>
                    <div class="radio-group">
                        <label for="sex-male">
                            <input type="radio" id="sex-male" name="sex" value="Nam" <?php echo ($sex == 'Nam') ? 'checked' : ''; ?>> Nam
                        </label>
                        <label for="sex-female">
                            <input type="radio" id="sex-female" name="sex" value="Nữ" <?php echo ($sex == 'Nữ') ? 'checked' : ''; ?>> Nữ
                        </label>
                    </div>
                </div>                
                <div class="form-group">
                    <label for="birthday">Ngày sinh</label>
                    <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($birthday); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea name="address" id="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div>
                    <button type="submit" class="custom-btn primary-btn btn-announce" type-announce="success" message="Đã cập nhật thông cá nhân thành công!">Cập nhật thay đổi</button>
                </div>
            </form>
        </div>
      </div>
    </div>
    <script src="js/helper.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
    <script>
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
    </script>
  </body>
</html>