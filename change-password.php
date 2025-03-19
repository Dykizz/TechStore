<?php
include 'connect.php';
include 'information.php';

// Xử lý đổi mật khẩu
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = trim($_POST['currentPassword']);
    $newPassword = trim($_POST['newPassword']);
    $rePassword = trim($_POST['rePassword']);
    $userId = $_SESSION['userId'];

    $sql = "SELECT password FROM User WHERE userId = " . mysqli_real_escape_string($conn, $userId);
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($currentPassword === $user['password']) {
            if ($currentPassword === $newPassword) {
                $message = "Mật khẩu mới không được trùng với mật khẩu cũ!";
                $message_type = "error";
            } elseif ($newPassword !== $rePassword) {
                $message = "Xác nhận mật khẩu không khớp!";
                $message_type = "error";
            } else {
                $newPassword = mysqli_real_escape_string($conn, $newPassword);
                $update_sql = "UPDATE User SET password = '$newPassword', updatedAt = CURRENT_TIMESTAMP WHERE userId = " . mysqli_real_escape_string($conn, $userId);
                if (mysqli_query($conn, $update_sql)) {
                    $message = "Đã đổi mật khẩu thành công!";
                    $message_type = "success";
                } else {
                    $message = "Có lỗi xảy ra khi cập nhật mật khẩu!";
                    $message_type = "error";
                }
            }
        } else {
            $message = "Mật khẩu hiện tại không đúng!";
            $message_type = "error";
        }
    } else {
        $message = "Không tìm thấy người dùng!";
        $message_type = "error";
    }
}

// Lấy thông tin giỏ hàng từ database
$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
$cartItems = [];
$cartCount = 0;
$totalPrice = 0;

if ($userId) {
    $cartStmt = $conn->prepare("
        SELECT ci.productId, ci.quantity, p.name, p.price, p.image
        FROM CartItem ci
        JOIN Product p ON ci.productId = p.productId
        WHERE ci.userId = ?
    ");
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();

    while ($item = $cartResult->fetch_assoc()) {
        $cartItems[$item['productId']] = [
            'name' => $item['name'],
            'price' => $item['price'],
            'image' => $item['image'],
            'quantity' => $item['quantity']
        ];
        $cartCount += $item['quantity'];
        $totalPrice += $item['price'] * $item['quantity'];
    }
    $cartStmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Đổi mật khẩu</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="./img/logo.png" type="image/x-icon" />
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <!-- Slick -->
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="css/style.css" />
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .message.success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .message.error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="alert alert-show announce" role="alert"></div>
    <!-- HEADER -->
    <header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> Hotline: <strong>+84 975 419 019</strong></a></li>
                    <li><a href="#"><i class="fa fa-envelope-o"></i> nhom6@email.com</a></li>
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
                            <form action="./products.php" method="GET">
                                <input name="keyword" class="input" placeholder="Nhập sản phẩm muốn tìm kiếm ..." />
                                <button class="search-btn">Tìm kiếm</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-3 clearfix">
                        <?php if (isset($fullname) && $fullname): ?>
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
                                        <li><a href="./logout.php">Đăng xuất</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-shopping-cart"></i>
                                        <span>Giỏ hàng</span>
                                        <div class="qty"><?php echo $cartCount; ?></div>
                                    </a>
                                    <div class="cart-dropdown">
                                        <div class="cart-list">
                                            <?php if (!empty($cartItems)): ?>
                                                <?php foreach ($cartItems as $id => $item): ?>
                                                    <div class="product-widget">
                                                        <div class="product-img">
                                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" />
                                                        </div>
                                                        <div class="product-body">
                                                            <h3 class="product-name">
                                                                <a href="detail-product.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                                            </h3>
                                                            <h4 class="product-price">
                                                                <span class="qty"><?php echo $item['quantity']; ?>x</span>
                                                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VND
                                                            </h4>
                                                        </div>
                                                        <button class="delete" data-product-id="<?php echo $id; ?>"><i class="fa fa-close"></i></button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>Giỏ hàng trống!</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cart-summary">
                                            <small><?php echo $cartCount; ?> sản phẩm được chọn</small>
                                            <h5>TỔNG: <?php echo number_format($totalPrice, 0, ',', '.'); ?> VND</h5>
                                        </div>
                                        <div class="cart-btns">
                                            <a href="./shopping-cart.php">Xem giỏ hàng</a>
                                            <a href="./checkout.php">Thanh toán <i class="fa fa-arrow-circle-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="menu-toggle">
                                    <a href="#"><i class="fa fa-bars"></i><span>Danh mục</span></a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="header-ctn">
                                <div><a href="./login.php" class="btn btn-primary">Đăng nhập</a></div>
                                <div><a href="./register.php" class="btn btn-primary">Đăng kí</a></div>
                                <div class="menu-toggle">
                                    <a href="#"><i class="fa fa-bars"></i><span>Danh mục</span></a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- /HEADER -->

    <!-- BODY -->
    <div class="body">
        <div class="login-container">
            <h2 class="login-title">Đổi mật khẩu</h2>
            <div class="login-form">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div>
                        <label for="currentPassword">Mật khẩu hiện tại</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="currentPassword" id="currentPassword" placeholder="Nhập mật khẩu hiện tại" required />
                        </div>
                    </div>
                    <div>
                        <label for="newPassword">Mật khẩu mới</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="newPassword" id="newPassword" placeholder="Nhập mật khẩu mới" required />
                        </div>
                    </div>
                    <div>
                        <label for="rePassword">Xác nhận mật khẩu mới</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="rePassword" id="rePassword" placeholder="Xác nhận mật khẩu mới" required />
                        </div>
                    </div>
                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="login-button btn-announce">Xác nhận</button>
                </form>
            </div>
        </div>
    </div>
    <!-- /BODY -->

    <!-- FOOTER -->
    <footer id="footer">
        <div class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Về chúng tôi</h3>
                            <p>Chất lượng làm nên thương hiệu.</p>
                            <ul class="footer-links">
                                <li><a href="#"><i class="fa fa-phone"></i><strong>+84 975 419 019</strong></a></li>
                                <li><a href="#"><i class="fa fa-envelope-o"></i>nhom6@email.com</a></li>
                                <li><a href="#"><i class="fa fa-map-marker"></i>273 An Dương Vương, Phường 3, Quận 5</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Sản phẩm</h3>
                            <ul class="footer-links">
                                <li><a href="./products.php?category=1">Máy tính</a></li>
                                <li><a href="./products.php?category=2">Điện thoại</a></li>
                                <li><a href="./products.php?category=3">Máy ảnh</a></li>
                                <li><a href="./products.php?category=4">Phụ kiện</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix visible-xs"></div>
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Thông tin</h3>
                            <ul class="footer-links">
                                <li><a href="#">Về chúng tôi</a></li>
                                <li><a href="#">Liên hệ với chúng tôi</a></li>
                                <li><a href="#">Chính sách bảo mật</a></li>
                                <li><a href="#">Đơn hàng & Hoàn trả</a></li>
                                <li><a href="#">Điều khoản & Điều kiện</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Dịch vụ</h3>
                            <ul class="footer-links">
                                <li><a href="./account-information.php">Tài khoản</a></li>
                                <li><a href="./shopping-cart.php">Giỏ hàng</a></li>
                                <li><a href="#">Trợ giúp</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="bottom-footer" class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <ul class="footer-payments">
                            <li><a href="#"><i class="fa fa-cc-visa"></i></a></li>
                            <li><a href="#"><i class="fa fa-credit-card"></i></a></li>
                            <li><a href="#"><i class="fa fa-cc-paypal"></i></a></li>
                            <li><a href="#"><i class="fa fa-cc-mastercard"></i></a></li>
                            <li><a href="#"><i class="fa fa-cc-discover"></i></a></li>
                            <li><a href="#"><i class="fa fa-cc-amex"></i></a></li>
                        </ul>
                        <span class="copyright">
                            Copyright © <script>document.write(new Date().getFullYear());</script> Bản quyền thuộc về Electro.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="js/helper.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/announcement.js"></script>
    <script>
        document.querySelectorAll('.cart-dropdown .delete').forEach(button => {
            button.addEventListener('click', async function () {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('productId', this.getAttribute('data-product-id'));

                try {
                    let response = await fetch("add-to-cart.php", {
                        method: "POST",
                        body: formData
                    });

                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    let data = await response.json();

                    if (data.status === "success") {
                        document.querySelector('.header-ctn .qty').textContent = data.cartQuantity;
                        showAnnouncement("success", "Đã xóa sản phẩm khỏi giỏ hàng!");
                        setTimeout(() => window.location.reload(), 3000);
                    } else {
                        showAnnouncement("danger", data.message);
                    }
                } catch (error) {
                    console.log("Fetch Error:", error);
                    showAnnouncement("danger", "Lỗi khi xóa sản phẩm!");
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>