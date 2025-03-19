<?php
session_start();
include 'connect.php';

$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
if ($userId == 0) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ database
$userSql = "SELECT name FROM user WHERE userId = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userName = $user['name'] ?? "Khách";
$userStmt->close();

// Lấy giỏ hàng
/*$sql = "SELECT ci.*, p.name, p.price FROM CartItem ci JOIN Product p ON ci.productId = p.productId WHERE ci.userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();*/
//unset($_SESSION['cart']);

$sql = "SELECT ci.productId, ci.quantity, p.name, p.price, p.discountPercent, p.image 
        FROM CartItem ci 
        JOIN Product p ON ci.productId = p.productId 
        WHERE ci.userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Tính tổng tiền (sau giảm giá)
$totalAmount = 0;
foreach ($cartItems as $item) {
    $priceAfterDiscount = $item['price'] * ((100 - ($item['discountPercent'] ?? 0)) / 100);
    $totalAmount += $priceAfterDiscount * $item['quantity'];
    $item['price'] = $priceAfterDiscount; // Cập nhật giá sau giảm giá để hiển thị
}

// Lấy địa chỉ của người dùng
$addressSql = "SELECT * FROM UserAddress WHERE userId = ?";
$addressStmt = $conn->prepare($addressSql);
$addressStmt->bind_param("i", $userId);
$addressStmt->execute();
$addresses = $addressStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$addressStmt->close();

// Xử lý form khi nhấn "Đặt hàng"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cartItems)) {
    $recipientName = $_POST['recipient-name'];
    $phoneNumber = $_POST['phone-number'];
    $deliveryNotes = $_POST['delivery-notes'] ?? '';
    $shippingAddressId = $_POST['saved-address'] ? (int)$_POST['saved-address'] : null;
    $customShippingAddress = $_POST['new-address'] ?: null;
    $paymentMethod = $_POST['payment'];
    $cardHolderName = $paymentMethod === 'card' ? $_POST['card-name'] : null;
    $cardNumber = $paymentMethod === 'card' ? $_POST['card-number'] : null;
    $cardExpiryDate = $paymentMethod === 'card' ? $_POST['card-expiry'] : null;

    $paymentMethodMap = [
        'cash' => 'CASH',
        'bank-transfer' => 'BANK_TRANSFER',
        'card' => 'CREDIT_CARD'
    ];
    $paymentMethod = $paymentMethodMap[$paymentMethod];

    $orderCode = 'ORD' . time();
    $orderDate = date('Y-m-d H:i:s');

    $conn->begin_transaction();
    try {
        $orderSql = "INSERT INTO Orders (userId, orderCode, orderDate, status, totalAmount, shippingAddressId, customShippingAddress, paymentMethod, cardHolderName, cardNumber, cardExpiryDate) 
                     VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, ?, ?, ?)";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bind_param("ississssss", $userId, $orderCode, $orderDate, $totalAmount, $shippingAddressId, $customShippingAddress, $paymentMethod, $cardHolderName, $cardNumber, $cardExpiryDate);
        $orderStmt->execute();
        $orderId = $conn->insert_id;
        $orderStmt->close();

        $detailSql = "INSERT INTO OrderDetail (orderId, productId, quantity, price) VALUES (?, ?, ?, ?)";
        $detailStmt = $conn->prepare($detailSql);
        foreach ($cartItems as $item) {
            $detailStmt->bind_param("iiii", $orderId, $item['productId'], $item['quantity'], $item['price']);
            $detailStmt->execute();
        }
        $detailStmt->close();

        $deleteSql = "DELETE FROM CartItem WHERE userId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $userId);
        $deleteStmt->execute();
        $deleteStmt->close();

        $conn->commit();
        header("Location: purchasing-history.php"); 
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Lỗi: " . $e->getMessage();
    }
}
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
    header("Location: checkout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Thanh toán</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />
</head>
<body>
    <header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> 0975419019</a></li>
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
                            <a href="index.php" class="logo"><img src="./img/logo.png" alt="" /></a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="header-search">
                            <form action="store-search.php">
                                <input name="keyword" class="input" placeholder="Nhập sản phẩm muốn tìm kiếm ..." />
                                <button class="search-btn">Tìm kiếm</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-3 clearfix">
                        <div class="header-ctn">
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-user-o"></i>
                                    <span><?php echo htmlspecialchars($userName); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="account-information.php">Thông tin cá nhân</a></li>
                                    <li><a href="purchasing-history.php">Lịch sử mua hàng</a></li>
                                    <li><a href="change-password.php">Đổi mật khẩu</a></li>
                                    <li><a href="logout.php">Đăng xuất</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
        <i class="fa fa-shopping-cart"></i>
        <span>Giỏ hàng</span>
        <div class="qty"><?php echo array_sum(array_column($cartItems ?? [], 'quantity')); ?></div>
    </a>
    <div class="cart-dropdown">
        <div class="cart-list">
            <?php
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    echo "
                    <div class='product-widget'>
                        <div class='product-img'>
                            <img src='{$item['image']}' alt='' />
                        </div>
                        <div class='product-body'>
                            <h3 class='product-name'>
                                <a href='detail-product.php?id={$item['productId']}'>{$item['name']}</a>
                            </h3>
                            <h4 class='product-price'>
                                <span class='qty'>{$item['quantity']}x</span>" . number_format($item['price'], 0, ',', '.') . " VND
                            </h4>
                        </div>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='product_id' value='{$item['productId']}'>
                            <button type='submit' name='remove_from_cart' class='delete'><i class='fa fa-close'></i></button>
                        </form>
                    </div>";
                }
            } else {
                echo "<p>Giỏ hàng trống!</p>";
            }
            ?>
        </div>
        <div class="cart-summary">
            <small><?php echo array_sum(array_column($cartItems ?? [], 'quantity')); ?> sản phẩm được chọn</small>
            <h5>TỔNG: <?php echo number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cartItems ?? [])), 0, ',', '.'); ?> VND</h5>
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
    </header>

    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="store-latop.php">Máy tính</a></li>
                    <li><a href="store-smartphone.php">Điện thoại</a></li>
                    <li><a href="store-camera.php">Máy ảnh</a></li>
                    <li><a href="store-accessories.php">Phụ kiện</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <form method="POST" id="checkout-form">
                        <div class="billing-details">
                            <div class="section-title">
                                <h3 class="title">Thông Tin Người Nhận</h3>
                            </div>
                            <div class="form-group">
                                <label for="recipient-name">Tên người nhận:</label>
                                <input class="input" type="text" name="recipient-name" id="recipient-name" placeholder="Nhập tên người nhận" value="<?php echo htmlspecialchars($userName); ?>" required />
                            </div>
                            <div class="form-group">
                                <label for="phone-number">Số điện thoại:</label>
                                <input class="input" type="tel" name="phone-number" id="phone-number" placeholder="Nhập số điện thoại" required />
                            </div>
                            <div class="form-group">
                                <label for="delivery-notes">Ghi chú giao hàng (nếu có):</label>
                                <textarea class="input" name="delivery-notes" id="delivery-notes" rows="3" placeholder="Nhập ghi chú giao hàng"></textarea>
                            </div>
                        </div>

                        <div class="shiping-details">
                            <div class="section-title">
                                <h3 class="title">Chọn Địa Chỉ Giao Hàng</h3>
                            </div>
                            <div class="form-group">
                                <label for="saved-address">Chọn địa chỉ giao hàng:</label>
                                <select class="form-control" name="saved-address" id="saved-address">
                                    <option value="">Chọn địa chỉ</option>
                                    <?php foreach ($addresses as $addr): ?>
                                        <option value="<?php echo $addr['shippingAddressId']; ?>" <?php echo $addr['isDefault'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($addr['address']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-checkbox">
                                <input type="checkbox" id="new-address-checkbox" onchange="toggleNewAddress()">
                                <label for="new-address-checkbox"><span></span>Nhập địa chỉ giao hàng mới</label>
                            </div>
                            <div class="form-group" id="new-address-group" style="display: none;">
                                <label for="new-address">Địa chỉ giao hàng:</label>
                                <input class="input" type="text" name="new-address" id="new-address" placeholder="Nhập địa chỉ giao hàng" />
                            </div>
                        </div>

                        <div class="payment-method">
                            <div class="section-title">
                                <h3 class="title">Chọn Phương Thức Thanh Toán</h3>
                            </div>
                            <div class="input-radio">
                                <input type="radio" name="payment" id="payment-cash" value="cash" required checked />
                                <label for="payment-cash"><span></span>Tiền mặt</label>
                            </div>
                            <div class="input-radio">
                                <input type="radio" name="payment" id="payment-bank-transfer" value="bank-transfer" />
                                <label for="payment-bank-transfer"><span></span>Chuyển khoản</label>
                            </div>
                            <div class="input-radio">
                                <input type="radio" name="payment" id="payment-card" value="card" onchange="toggleCardDetails()" />
                                <label for="payment-card"><span></span>Thẻ tín dụng</label>
                            </div>
                            <div id="card-details" style="display: none;">
                                <h4>Thông Tin Thẻ</h4>
                                <div class="form-group">
                                    <label for="card-name">Tên chủ thẻ:</label>
                                    <input class="input" type="text" name="card-name" id="card-name" placeholder="Nhập tên chủ thẻ" />
                                </div>
                                <div class="form-group">
                                    <label for="card-number">Số thẻ:</label>
                                    <input class="input" type="text" name="card-number" id="card-number" placeholder="Nhập số thẻ" />
                                </div>
                                <div class="form-group">
                                    <label for="card-expiry">Ngày hết hạn:</label>
                                    <input class="input" type="text" name="card-expiry" id="card-expiry" placeholder="MM/YY" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-5 order-details">
                    <div class="section-title text-center">
                        <h3 class="title">Tóm Tắt Hóa Đơn</h3>
                    </div>
                    <div class="product-list">
    <h4>Danh sách sản phẩm</h4>
    <table class="table">
        <thead>
            <tr><th>Tên sản phẩm</th><th>Số lượng</th><th>Giá</th></tr>
        </thead>
        <tbody>
            <?php if (empty($cartItems)): ?>
                <tr><td colspan="3">Giỏ hàng trống</td></tr>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name'] ?? 'Không có tên'); ?></td>
                        <td class="text-center">x<?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VND</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
                    <div class="order-summary">
                        <div class="order-col">
                            <div><strong>Tổng tiền:</strong></div>
                            <div><strong><?php echo number_format($totalAmount, 0); ?> VND</strong></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Tên người nhận:</strong></div>
                            <div><span id="recipient-name-summary"></span></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Địa chỉ giao hàng:</strong></div>
                            <div><span id="delivery-address-summary"></span></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Phương thức thanh toán:</strong></div>
                            <div><span id="payment-method-summary"></span></div>
                        </div>
                    </div>
                    <div class="input-checkbox">
                        <input type="checkbox" id="terms" required />
                        <label for="terms"><span></span>Tôi đã đọc và chấp nhận điều khoản</label>
                    </div>
                    <button type="submit" form="checkout-form" class="primary-btn order-submit" <?php echo empty($cartItems) ? 'disabled' : ''; ?>>Đặt hàng</button>
                </div>
            </div>
        </div>
    </div>

    <footer id="footer">
        <div class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Về chúng tôi</h3>
                            <p>Chất lượng làm nên thương hiệu.</p>
                            <ul class="footer-links">
                                <li><a href="#"><i class="fa fa-phone"></i>0975419019</a></li>
                                <li><a href="#"><i class="fa fa-envelope-o"></i>nhom6@email.com</a></li>
                                <li><a href="#"><i class="fa fa-map-marker"></i>273 An Dương Vương, Phường 3, Quận 5</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Sản phẩm</h3>
                            <ul class="footer-links">
                                <li><a href="store-latop.php">Máy tính</a></li>
                                <li><a href="store-smartphone.php">Điện thoại</a></li>
                                <li><a href="store-camera.php">Máy ảnh</a></li>
                                <li><a href="store-accessories.php">Phụ kiện</a></li>
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
                                <li><a href="account-information.php">Tài khoản</a></li>
                                <li><a href="lichsu_muahang.php">Giỏ hàng</a></li>
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

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        function toggleNewAddress() {
            var checkbox = document.getElementById('new-address-checkbox');
            var newAddressGroup = document.getElementById('new-address-group');
            newAddressGroup.style.display = checkbox.checked ? 'block' : 'none';
        }

        function toggleCardDetails() {
            var cardRadio = document.getElementById('payment-card');
            var cardDetails = document.getElementById('card-details');
            cardDetails.style.display = cardRadio.checked ? 'block' : 'none';
        }

        document.getElementById('checkout-form').addEventListener('input', function() {
            document.getElementById('recipient-name-summary').textContent = document.getElementById('recipient-name').value;
            var savedAddress = document.getElementById('saved-address').options[document.getElementById('saved-address').selectedIndex].text;
            var newAddress = document.getElementById('new-address').value;
            document.getElementById('delivery-address-summary').textContent = newAddress || savedAddress;
            var paymentMethod = document.querySelector('input[name="payment"]:checked').nextElementSibling.textContent.trim();
            document.getElementById('payment-method-summary').textContent = paymentMethod;
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>