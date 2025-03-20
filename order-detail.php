<?php
session_start();
include 'connect.php';

// Lấy userId từ session
$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;

if ($userId == 0) {
    header("Location: login.php");
    exit;
}

// Lấy orderId từ query string
$orderId = isset($_GET['orderId']) ? (int)$_GET['orderId'] : 0;

if ($orderId == 0) {
    header("Location: purchasing-history.php");
    exit;
}

// Lấy thông tin người dùng
$userSql = "SELECT name FROM User WHERE userId = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userName = $user['name'] ?? "Khách";
$userStmt->close();

// Lấy thông tin đơn hàng
$orderSql = "
    SELECT o.orderId, o.orderCode, o.orderDate, o.status, o.totalAmount,
           ua.address AS shippingAddress, o.customShippingAddress, o.paymentMethod
    FROM Orders o
    LEFT JOIN UserAddress ua ON o.shippingAddressId = ua.shippingAddressId
    WHERE o.orderId = ? AND o.userId = ?
";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("ii", $orderId, $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();
$orderStmt->close();

if (!$order) {
    header("Location: purchasing-history.php");
    exit;
}

// Lấy chi tiết đơn hàng
$detailSql = "
    SELECT od.quantity, od.price, p.name AS productName, p.image
    FROM OrderDetail od
    JOIN Product p ON od.productId = p.productId
    WHERE od.orderId = ?
";
$detailStmt = $conn->prepare($detailSql);
$detailStmt->bind_param("i", $orderId);
$detailStmt->execute();
$detailResult = $detailStmt->get_result();
$details = $detailResult->fetch_all(MYSQLI_ASSOC);
$detailStmt->close();

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

// Tính tổng số sản phẩm trong giỏ và tổng tiền (sau giảm giá)
$cartCount = count($cartItems);
$cartTotal = 0;
foreach ($cartItems as &$item) {
    $priceAfterDiscount = $item['price'] * ((100 - ($item['discountPercent'] ?? 0)) / 100);
    $cartTotal += $priceAfterDiscount * $item['quantity'];
    $item['price'] = $priceAfterDiscount;
}
unset($item);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chi Tiết Đơn Hàng</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css?v=1.1" />
</head>
<body>
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
    <div class="section">
        <div class="container">
            <h2 class="text-center">Chi Tiết Đơn Hàng</h2>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <p>Mã đơn: <strong><?php echo htmlspecialchars($order['orderCode']); ?></strong></p>
                            <p>Thời gian: <strong><?php echo htmlspecialchars($order['orderDate']); ?></strong></p>
                            <?php
                            $statusText = [
                                'Pending' => 'Chưa xử lý',
                                'Confirmed' => 'Đã xác nhận',
                                'Delivered' => 'Giao thành công',
                                'Cancelled' => 'Đã hủy'
                            ];
                            ?>
                            <p>Trạng thái: <strong><?php echo $statusText[$order['status']] ?? 'Không xác định'; ?></strong></p>

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
                            <h4>Danh sách sản phẩm</h4>
                            <table class="table text-center">
                                <thead >
                                    <tr>
                                        <th class ="text-left">Sản phẩm</th>
                                        <th class = "text-center">Hình ảnh</th>
                                        <th class = "text-center">Số lượng</th>
                                        <th class = "text-center">Giá</th>
                                        <th class = "text-center">Tổng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $detail): ?>
                                        <tr>
                                            <td class="text-left"><?php echo htmlspecialchars($detail['productName']); ?></td>
                                            <td><img src="<?php echo htmlspecialchars($detail['image']); ?>" alt="" style="width: 50px; height: 50px;" /></td>
                                            <td><?php echo $detail['quantity']; ?></td>
                                            <td><?php echo number_format($detail['price'], 0, ',', '.'); ?> VND</td>
                                            <td><?php echo number_format($detail['price'] * $detail['quantity'], 0, ',', '.'); ?> VND</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <p>Tổng tiền: <strong><?php echo number_format($order['totalAmount'], 0, ',', '.'); ?> VND</strong></p>
                            <a href="purchasing-history.php" class="btn btn-primary">Quay lại</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /SECTION -->

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
    <script src="js/main.js"></script>
</body>
</html>
<?php
$conn->close();
?>