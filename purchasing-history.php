<?php
session_start();
include 'connect.php';


// Lấy userId từ session
$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;

if ($userId == 0) {
    // Nếu chưa đăng nhập, chuyển hướng về login.php
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ database
$userSql = "SELECT name FROM User WHERE userId = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userName = $user['name'] ?? "Khách"; // Nếu không tìm thấy, mặc định là "Khách"
$userStmt->close();

// Lấy danh sách đơn hàng
$sql = "
    SELECT o.orderId, o.orderCode, o.orderDate, o.status, o.totalAmount,
           ua.address AS shippingAddress, o.customShippingAddress, o.paymentMethod
    FROM Orders o
    LEFT JOIN UserAddress ua ON o.shippingAddressId = ua.shippingAddressId
    WHERE o.userId = ?
    ORDER BY o.orderDate DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Xử lý hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = (int)$_POST['order_id'];

    // Kiểm tra trạng thái đơn hàng trước khi hủy
    $checkSql = "SELECT status FROM Orders WHERE orderId = ? AND userId = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $orderId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $orderStatus = $checkResult->fetch_assoc()['status'];
    $checkStmt->close();

    // Chỉ cho phép hủy nếu trạng thái là Pending hoặc Confirmed
    if (in_array($orderStatus, ['Pending', 'Confirmed'])) {
        $updateSql = "UPDATE Orders SET status = 'Cancelled' WHERE orderId = ? AND userId = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $orderId, $userId);
        $updateStmt->execute();
        $updateStmt->close();

        // Lưu thông báo vào session
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Đơn hàng đã được hủy thành công!'
        ];

        // Làm mới trang để cập nhật trạng thái
        header("Location: purchasing-history.php");
        exit;
    } else {
        // Lưu thông báo lỗi nếu không thể hủy
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Không thể hủy đơn hàng này!'
        ];

        header("Location: purchasing-history.php");
        exit;
    }
}
// Đếm tổng số đơn hàng
$totalOrders = count($orders);

// Lấy thông tin giỏ hàng
$cartSql = "
    SELECT ci.productId, ci.quantity, p.name AS productName, p.price, p.discountPercent, p.image
    FROM CartItem ci
    JOIN Product p ON ci.productId = p.productId
    WHERE ci.userId = ?
";
$cartStmt = $conn->prepare($cartSql);
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartItems = $cartResult->fetch_all(MYSQLI_ASSOC);
$cartStmt->close();


// Xử lý xóa sản phẩm khỏi giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $productId = (int)$_POST['product_id'];

    // Xóa sản phẩm khỏi CartItem
    $deleteSql = "DELETE FROM CartItem WHERE userId = ? AND productId = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("ii", $userId, $productId);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Làm mới trang để cập nhật giỏ hàng
    header("Location: purchasing-history.php");
    exit;
}

// Tính tổng số sản phẩm trong giỏ và tổng tiền (sau giảm giá)
$cartCount = count($cartItems);
$cartTotal = 0;
foreach ($cartItems as &$item) {
    $priceAfterDiscount = $item['price'] * ((100 - ($item['discountPercent'] ?? 0)) / 100);
    $cartTotal += $priceAfterDiscount * $item['quantity'];
    $item['price'] = $priceAfterDiscount; // Cập nhật giá sau giảm giá để hiển thị
}
unset($item);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lịch Sử Mua Hàng</title>
    <link rel="shortcut icon" href="./img/logo.png" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css?v=1.1" />
    <style>
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: #fff;
        font-weight: 500;
        z-index: 1000;
        display: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        transition: opacity 0.5s ease;
    }
    .notification.success {
        background-color: #28a745;
    }
    .notification.error {
        background-color: #dc3545;
    }
    .notification.show {
        display: block;
        opacity: 1;
    }
    .notification.hide {
        opacity: 0;
    }
    .bg-red{
        background-color:rgb(225, 35, 35);
    }

</style>
</head>
<body>

    <!-- Thông báo -->
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?php echo $_SESSION['notification']['type']; ?> show">
            <?php echo $_SESSION['notification']['message']; ?>
        </div>
        <?php unset($_SESSION['notification']); // Xóa thông báo sau khi hiển thị ?>
    <?php endif; ?>
    <!--/Thông báo-->
    
    <!-- HEADER -->
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
                            <a href="index.php" class="logo">
                                <img src="./img/logo.png" alt="" />
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="header-search">
                            <form action="./store-search.php">
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
                                    <span><?php echo htmlspecialchars($userName); ?></span>
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
            <?php if (empty($cartItems)): ?>
                <p>Giỏ hàng trống!</p>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="product-widget">
                        <div class="product-img">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" />
                        </div>
                        <div class="product-body">
                            <h3 class="product-name">
                                <a href="./detail-product.php?id=<?php echo $item['productId']; ?>">
                                    <?php echo htmlspecialchars($item['productName']); ?>
                                </a>
                            </h3>
                            <h4 class="product-price">
                                <span class="qty"><?php echo $item['quantity']; ?>x</span>
                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VND
                            </h4>
                        </div>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $item['productId']; ?>">
                            <button type="submit" name="remove_from_cart" class="delete"><i class="fa fa-close"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-summary">
            <small><?php echo $cartCount; ?> sản phẩm được chọn</small>
            <h5>TỔNG: <?php echo number_format($cartTotal, 0, ',', '.'); ?> VND</h5>
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
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- /HEADER -->

    <!-- NAVIGATION -->
    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li><a href="./index.php">Trang chủ</a></li>
                    <li><a href="./products.php?category=1">Máy tính</a></li>
                    <li><a href="./products.php?category=2">Điện thoại</a></li>
                    <li><a href="./products.php?category=3">Máy ảnh</a></li>
                    <li><a href="./products.php?category=4">Phụ kiện</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- /NAVIGATION -->

    <!-- SECTION -->
    <div class="container">
    <div class="section">
        <h2 class="text-center">Lịch sử mua hàng</h2>
        <p>Bạn đã mua tổng cộng <strong><?php echo $totalOrders; ?></strong> đơn hàng!</p>
        <?php if (empty($orders)): ?>
            <p>Chưa có đơn hàng nào.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        switch ($order['status']) {
                            case 'Pending':
                                $statusClass = 'badge';
                                $statusText = 'Chưa xử lý';
                                break;
                            case 'Confirmed':
                                $statusClass = 'badge badge-info';
                                $statusText = 'Đã xác nhận';
                                break;
                            case 'Delivered':
                                $statusClass = 'badge badge-success';
                                $statusText = 'Giao thành công';
                                break;
                            case 'Cancelled':
                                $statusClass = 'badge bg-red';
                                $statusText = 'Đã hủy';
                                break;
                        }
                        ?>
                        <span class="<?php echo $statusClass; ?> mb-3"><?php echo htmlspecialchars($statusText); ?></span>
                        <p>Thời gian: <strong><?php echo htmlspecialchars($order['orderDate']); ?></strong></p>
                        <p>Mã đơn: <strong><?php echo htmlspecialchars($order['orderCode']); ?></strong></p>
                        <p>Tên người nhận: <strong><?php echo htmlspecialchars($userName); ?></strong></p>
                        <p>Địa chỉ giao hàng: <strong><?php echo htmlspecialchars($order['customShippingAddress'] ?? $order['shippingAddress'] ?? 'Không có địa chỉ'); ?></strong></p>
                        <?php
                            $paymentMethods = [
                                'CASH' => 'Tiền mặt',
                                'BANK_TRANSFER' => 'Chuyển khoản ngân hàng',
                                'CREDIT_CARD' => 'Thẻ tín dụng'
                            ];

                            $paymentText = $paymentMethods[$order['paymentMethod']] ?? 'Không xác định';
                        ?>
                        <p>Phương thức thanh toán: <strong><?php echo htmlspecialchars($paymentText); ?></strong></p>
                        <p>Các sản phẩm:</p>
                        <ul>
                            <?php
                            $detailSql = "
                                SELECT od.quantity, p.name AS productName
                                FROM OrderDetail od
                                JOIN Product p ON od.productId = p.productId
                                WHERE od.orderId = ?
                            ";
                            $detailStmt = $conn->prepare($detailSql);
                            $detailStmt->bind_param("i", $order['orderId']);
                            $detailStmt->execute();
                            $detailResult = $detailStmt->get_result();
                            $details = $detailResult->fetch_all(MYSQLI_ASSOC);
                            $detailStmt->close();

                            foreach ($details as $detail): ?>
                                <li><?php echo htmlspecialchars($detail['productName']); ?> <b>x<?php echo $detail['quantity']; ?></b></li>
                            <?php endforeach; ?>
                        </ul>
                        <p>Tổng tiền: <strong><?php echo number_format($order['totalAmount'], 0, ',', '.'); ?> VND</strong></p>
                        <a class="text-primary" href="./order-detail.php?orderId=<?php echo $order['orderId']; ?>" style="color: #337ab7">Xem chi tiết</a>
                        <?php if ($order['status']=='Pending'): ?>
                            <form method="POST" style="display:inline; margin-left: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['orderId']; ?>">
                                <button type="submit" name="cancel_order" class="btn btn-danger btn-sm">Hủy đơn hàng</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
    <!-- /SECTION -->

    <!-- NEWSLETTER -->
    <div id="newsletter" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="newsletter">
                        <p>Đăng ký để nhận <strong>THÔNG BÁO MỚI NHẤT</strong></p>
                        <form>
                            <input class="input" type="email" placeholder="Nhập email" />
                            <button class="newsletter-btn"><i class="fa fa-envelope"></i> Đăng ký</button>
                        </form>
                        <ul class="newsletter-follow">
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                            <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /NEWSLETTER -->

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
                                <li><a href="./store-latop.php">Máy tính</a></li>
                                <li><a href="./store-smartphone.php">Điện thoại</a></li>
                                <li><a href="./store-camera.php">Máy ảnh</a></li>
                                <li><a href="./store-accessories.php">Phụ kiện</a></li>
                            </ul>
                        </div>
                    </div>
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
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/main.js"></script>
    <script>
    // Tự động ẩn thông báo sau 3 giây
    document.addEventListener('DOMContentLoaded', function() {
        const notification = document.querySelector('.notification');
        if (notification) {
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
            }, 3000); // Ẩn sau 3 giây
        }
    });
</script>
</body>
</html>