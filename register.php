<?php
include 'connect.php';

// Bật báo lỗi MySQL chi tiết
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

$error_message = $success_message = $error_email = $error_name = '';
$error_password1 = $error_password2 = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password1 = trim($_POST['password1']);
    $password2 = trim($_POST['password2']);

    if (empty($name)) {
        $error_name = 'Họ tên không được để trống!';
    }
    if (empty($email)) {
        $error_email = 'Email không được để trống!';
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_email = 'Email không hợp lệ!';
        }
    }
    if (empty($password1)) {
        $error_password1 = 'Mật khẩu không được để trống!';
    }
    if (empty($password2)) {
        $error_password2 = 'Vui lòng nhập lại mật khẩu!';
    }

    if (empty($error_name) && empty($error_email) && empty($error_password1) && empty($error_password2)) {
        if ($password1 !== $password2) {
            $error_message = "Mật khẩu không khớp!";
        } else {
            $sql = "SELECT userId FROM user WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "Email đã tồn tại!";
            } else {
                $sql = "INSERT INTO user (name, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $name, $email, $password1);

                if ($stmt->execute()) {
                    $userId = $stmt->insert_id;
                    $_SESSION['userId'] = $userId;
                    $_SESSION['email'] = $email;
                    $_SESSION['fullname'] = $name;
                    $success_message = "Đăng ký thành công! Bạn sẽ được chuyển đến trang đăng nhập sau 3 giây.";
                    header("refresh:3;url=login.php");
                    exit();
                } else {
                    $error_message = "Lỗi khi đăng ký: " . $conn->error;
                }
            }
            $stmt->close();
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Đăng kí</title>
    <link rel="shortcut icon" href="./img/logo.png" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="alert alert-show announce" role="alert"><?php echo $success_message ?: $error_message; ?></div>
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
        <div id="header" style="border-bottom: 3px solid red">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="header-logo">
                            <a href="./index-notlogin.php" class="logo">
                                <img src="./img/logo.png" alt="" />
                            </a>
                        </div>
                    </div>
                    <div class="col-md-9 clearfix">
                        <div class="header-ctn">
                            <div><a href="./login.php" class="btn btn-primary"><span>Đăng nhập</span></a></div>
                            <div><a href="./register.php" class="btn btn-primary"><span>Đăng kí</span></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="body">
        <div class="login-container">
            <h2 class="login-title">Đăng kí</h2>
            <div class="login-form">
                <form action="register.php" method="POST">
                    <div>
                        <label for="name">Họ tên</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" id="name" placeholder="Nguyễn Văn A" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" />
                        </div>
                        <?php if (!empty($error_name)): ?>
                            <p class="error-name" style="color: red; font-style: italic;"><?php echo $error_name; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="email" name="email" id="email" placeholder="example@gmail.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" />
                        </div>
                        <?php if (!empty($error_email)): ?>
                            <p class="error-message" style="color: red; font-style: italic;"><?php echo $error_email; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="password1">Mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password1" id="password1" placeholder="Nhập mật khẩu" />
                        </div>
                        <?php if (!empty($error_password1)): ?>
                            <p class="error-message" style="color: red; font-style: italic;"><?php echo $error_password1; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="password2">Nhập lại mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password2" id="password2" placeholder="Nhập mật khẩu" />
                        </div>
                        <?php if (!empty($error_password2)): ?>
                            <p class="error-message" style="color: red; font-style: italic;"><?php echo $error_password2; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message" style="color: red; font-style: italic;"><?php echo $error_message; ?></div>
                    <?php elseif (!empty($success_message)): ?>
                        <div class="success-message" style="color: green; font-style: italic;"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <button style="margin-bottom: 10px" type="submit" class="login-button btn-announce">Xác nhận</button>
                </form>
                <p class="text-center">
                    Bạn đã có tài khoản?
                    <a style="font-weight: bold; color: blue" href="./login.php">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
    <script src="js/helper.js"></script>
</body>
</html>