<?php
include 'connect.php';

// Bật báo lỗi MySQL chi tiết
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

$error_message = '';
$email_error = '';
$password_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email)) {
        $email_error = "Mời nhập email!";
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Email không hợp lệ!";
        }
    }
    if (empty($password)) {
        $password_error = "Mời nhập mật khẩu!";
    }

    if (empty($email_error) && empty($password_error)) {
        $sql = "SELECT userId, email, password, name FROM user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error_message = "Tài khoản không tồn tại!";
        } else {
            $user = $result->fetch_assoc();
            if ($password !== $user['password']) {
                $error_message = "Mật khẩu không chính xác!";
            } else {
                $_SESSION['userId'] = $user['userId'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['fullname'] = $user['name'];
                header("Location: index.php");
                exit();
            }
        }
        $stmt->close();
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
    <title>Đăng nhập</title>
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
    <div class="alert alert-show announce" role="alert"><?php echo $error_message; ?></div>
    <header>
    <div id="top-header">
        <div class="container">
          <ul class="header-links pull-left">
            <li>
              <a href="#"><i class="fa fa-phone"></i> Hotline: <strong>0975419019</strong>
            </li>
            <li>
              <a href="#"><i class="fa fa-envelope-o"></i> nhom6@email.com </a>
            </li>
            <li>
              <a href="#"
                ><i class="fa fa-map-marker"></i> 273 An Dương Vương, Phường 3,
                Quận 5
              </a>
            </li>
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
            <h2 class="login-title">Đăng nhập</h2>
            <form class="login-form" action="login.php" method="POST">
                <div>
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="email" id="email" placeholder="example@gmail.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" />
                    </div>
                    <?php if (!empty($email_error)): ?>
                        <div class="field-error"><?php echo $email_error; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="password">Mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" />
                    </div>
                    <?php if (!empty($password_error)): ?>
                        <div class="field-error"><?php echo $password_error; ?></div>
                    <?php endif; ?>
                </div>
                <a class="text-right" style="font-weight: bold; color: blue" href="./forgot-account.php">Quên mật khẩu?</a>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <button style="margin-bottom: 10px" type="submit" class="login-button">Xác nhận</button>
            </form>
            <p class="text-center">
                Bạn chưa có tài khoản?
                <a style="font-weight: bold; color: blue" href="./register.php">Đăng kí</a>
            </p>
        </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>