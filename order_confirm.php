<?php
session_start();
include 'connect.php';

$userId = $_SESSION['userId'] ?? 0;
$orderId = $_GET['orderId'] ?? 0;

if ($userId == 0 || $orderId == 0) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT o.*, u.name AS userName, ua.address AS shippingAddress 
        FROM Orders o 
        JOIN Users u ON o.userId = u.userId 
        LEFT JOIN UserAddress ua ON o.shippingAddressId = ua.shippingAddressId 
        WHERE o.orderId = ? AND o.userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

$detailSql = "SELECT od.*, p.name AS productName 
              FROM OrderDetail od 
              JOIN Product p ON od.productId = p.productId 
              WHERE od.orderId = ?";
$detailStmt = $conn->prepare($detailSql);
$detailStmt->bind_param("i", $orderId);
$detailStmt->execute();
$orderDetails = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$detailStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Order Confirmation</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />
</head>
<body>
<header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> 0975419019 </a></li>
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
                            <a href="./index.html" class="logo">
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
                                    <span>Nguyễn Thế Anh</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="./account-information.html">Thông tin cá nhân</a></li>
                                    <li><a href="./purchasing-history.php">Lịch sử mua hàng</a></li>
                                    <li><a href="./change-password.html">Đổi mật khẩu</a></li>
                                    <li><a href="./index-notlogin.html">Đăng xuất</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span>Giỏ hàng</span>
                                    <div class="qty"><?php echo count($cartItems); ?></div>
                                </a>
                                <div class="cart-dropdown">
                                    <div class="cart-list">
                                        <?php foreach ($cartItems as $item): ?>
                                            <div class="product-widget">
                                                <div class="product-body">
                                                    <h3 class="product-name"><?php echo $item['name']; ?></h3>
                                                    <h4 class="product-price"><span class="qty"><?php echo $item['quantity']; ?>x</span><?php echo number_format($item['price'], 0); ?> VND</h4>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="cart-summary">
                                        <small><?php echo count($cartItems); ?> sản phẩm được chọn</small>
                                        <h5>TỔNG: <?php echo number_format($totalAmount, 0); ?> VND</h5>
                                    </div>
                                    <div class="cart-btns">
                                        <a href="./purchasing-history.php">Xem giỏ hàng</a>
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
     
    <!-- SECTION -->
    <div class="section">
        <div class="container">
            <h2>Đơn hàng #<?php echo $order['orderCode']; ?> đã được đặt thành công!</h2>
            <div class="row">
                <div class="col-md-7">
                    <h3>Thông tin đơn hàng</h3>
                    <p><strong>Khách hàng:</strong> <?php echo $order['userName']; ?></p>
                    <p><strong>Ngày đặt:</strong> <?php echo $order['orderDate']; ?></p>
                    <p><strong>Trạng thái:</strong> <?php echo $order['status']; ?></p>
                    <p><strong>Địa chỉ giao:</strong> <?php echo $order['customShippingAddress'] ?? $order['shippingAddress'] ?? 'Chưa xác định'; ?></p>
                    <p><strong>Phương thức thanh toán:</strong> <?php echo $order['paymentMethod']; ?></p>
                    <?php if ($order['paymentMethod'] === 'CREDIT_CARD'): ?>
                        <p><strong>Tên chủ thẻ:</strong> <?php echo $order['cardHolderName']; ?></p>
                        <p><strong>Số thẻ:</strong> <?php echo $order['cardNumber']; ?></p>
                        <p><strong>Ngày hết hạn:</strong> <?php echo $order['cardExpiryDate']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-5">
                    <h3>Chi tiết đơn hàng</h3>
                    <table class="table">
                        <thead>
                            <tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Tổng phụ</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderDetails as $detail): ?>
                                <tr>
                                    <td><?php echo $detail['productName']; ?></td>
                                    <td><?php echo $detail['quantity']; ?></td>
                                    <td><?php echo number_format($detail['price'], 0); ?></td>
                                    <td><?php echo number_format($detail['subtotal'], 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3"><strong>Tổng tiền</strong></td>
                                <td><strong><?php echo number_format($order['totalAmount'], 0); ?> VND</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <a href="./index.html" class="primary-btn">Quay lại trang chủ</a>
                </div>
            </div>
        </div>
    </div>
    <!-- /SECTION -->

    <!-- FOOTER (Tương tự checkout.php) -->
    <footer id="footer">
        <!-- Giữ nguyên footer từ checkout.php -->
    </footer>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>