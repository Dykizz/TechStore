<?php
include 'connect.php';
include 'information.php';

// Kiểm tra kết nối
if ($conn === false || $conn->connect_error) {
    die("Lỗi kết nối database: " . $conn->connect_error);
}

// Lấy thông tin người dùng từ database
$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
$userInfo = [];
if ($userId) {
    $userStmt = $conn->prepare("SELECT u.name, u.phoneNumber, ua.address 
    FROM User u
    LEFT JOIN UserAddress ua ON u.userId = ua.userId
    WHERE u.userId = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userInfo = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();
}

$cartItems = [];
$cartCount = 0;
$totalPrice = 0;

// Xử lý dữ liệu giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout']) && isset($_POST['selected_items'])) {
    // Thanh toán các sản phẩm được chọn từ shopping-cart.php
    $selectedItems = $_POST['selected_items'];
    if (!empty($selectedItems)) {
        $productIds = array_column($selectedItems, 'productId');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $cartStmt = $conn->prepare("
            SELECT p.productId, p.name, p.price, p.image
            FROM Product p
            WHERE p.productId IN ($placeholders)
        ");
        $types = str_repeat('i', count($productIds));
        $cartStmt->bind_param($types, ...$productIds);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        while ($item = $cartResult->fetch_assoc()) {
            $productId = $item['productId'];
            $quantity = (int)$selectedItems[$productId]['quantity'];
            $cartItems[$productId] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'image' => $item['image'],
                'quantity' => $quantity
            ];
            $cartCount += $quantity;
            $totalPrice += $item['price'] * $quantity;
        }
        $cartStmt->close();
    }
} else {
    // Thanh toán toàn bộ giỏ hàng khi truy cập từ header
    if ($userId) {
        $cartStmt = $conn->prepare("
            SELECT p.productId, p.name, p.price, p.image, ci.quantity
            FROM Product p
            INNER JOIN CartItem ci ON p.productId = ci.productId
            WHERE ci.userId = ?
        ");
        $cartStmt->bind_param("i", $userId);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        while ($item = $cartResult->fetch_assoc()) {
            $productId = $item['productId'];
            $quantity = (int)$item['quantity'];
            $cartItems[$productId] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'image' => $item['image'],
                'quantity' => $quantity
            ];
            $cartCount += $quantity;
            $totalPrice += $item['price'] * $quantity;
        }
        $cartStmt->close();
    }
}

// Xử lý đặt hàng
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $recipientName = filter_input(INPUT_POST, 'recipient-name', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_input(INPUT_POST, 'phone-number', FILTER_SANITIZE_STRING);
    $deliveryNotes = filter_input(INPUT_POST, 'delivery-notes', FILTER_SANITIZE_STRING) ?? '';
    $address = !empty($_POST['new-address']) ? filter_input(INPUT_POST, 'new-address', FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'saved-address', FILTER_SANITIZE_STRING);
    $paymentMethod = filter_input(INPUT_POST, 'payment', FILTER_SANITIZE_STRING) ?? '';
    $totalAmount = $totalPrice;
    $orderCode = 'ORD' . time() . rand(1000, 9999);
    $status = 'Pending';

    $mappedPaymentMethod = '';
    switch ($paymentMethod) {
        case 'cash': $mappedPaymentMethod = 'CASH'; break;
        case 'bank-transfer': $mappedPaymentMethod = 'BANK_TRANSFER'; break;
        case 'card': $mappedPaymentMethod = 'CREDIT_CARD'; break;
        default: $mappedPaymentMethod = 'CASH';
    }

    $cardHolderName = $mappedPaymentMethod === 'CREDIT_CARD' ? filter_input(INPUT_POST, 'card-name', FILTER_SANITIZE_STRING) : NULL;
    $cardNumber = $mappedPaymentMethod === 'CREDIT_CARD' ? filter_input(INPUT_POST, 'card-number', FILTER_SANITIZE_STRING) : NULL;
    $cardExpiryDate = $mappedPaymentMethod === 'CREDIT_CARD' ? filter_input(INPUT_POST, 'card-expiry', FILTER_SANITIZE_STRING) : NULL;

    if (empty($recipientName) || empty($phoneNumber) || empty($address) || empty($paymentMethod)) {
        $error = "Vui lòng điền đầy đủ thông tin bắt buộc!";
    } elseif (empty($cartItems)) {
        $error = "Không có sản phẩm nào được chọn để thanh toán!";
    } elseif ($mappedPaymentMethod === 'CREDIT_CARD' && (empty($cardHolderName) || empty($cardNumber) || empty($cardExpiryDate))) {
        $error = "Vui lòng điền đầy đủ thông tin thẻ tín dụng!";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO Orders (userId, orderCode, orderDate, status, totalAmount, customShippingAddress, paymentMethod, cardHolderName, cardNumber, cardExpiryDate)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt === false) {
            $error = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
        } else {
            $stmt->bind_param("ississsss", $userId, $orderCode, $status, $totalAmount, $address, $mappedPaymentMethod, $cardHolderName, $cardNumber, $cardExpiryDate);
            if ($stmt->execute()) {
                $orderId = $conn->insert_id;

                // Lưu chi tiết đơn hàng
                $detailStmt = $conn->prepare("
                    INSERT INTO OrderDetail (orderId, productId, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($cartItems as $productId => $item) {
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $detailStmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
                    $detailStmt->execute();
                }
                $detailStmt->close();

                // Xóa chỉ các sản phẩm đã được thanh toán khỏi giỏ hàng
                $productIdsToDelete = isset($_POST['selected_items']) ? array_column($_POST['selected_items'], 'productId') : array_keys($cartItems);
                if (!empty($productIdsToDelete)) {
                    $placeholders = implode(',', array_fill(0, count($productIdsToDelete), '?'));
                    $deleteStmt = $conn->prepare("DELETE FROM CartItem WHERE userId = ? AND productId IN ($placeholders)");
                    $params = array_merge([$userId], $productIdsToDelete);
                    $types = str_repeat('i', count($params));
                    $deleteStmt->bind_param($types, ...$params);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                }

                $stmt->close();
                header("Location: order-success.php?orderId=" . $orderId);
                exit;
            } else {
                $error = "Lỗi khi lưu đơn hàng: " . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Thanh toán</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
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
                            <a href="./index.php" class="logo">
                                <img src="./img/logo.png" alt="" />
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="header-search">
                            <form action="./products.php">
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
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>Không có sản phẩm nào trong giỏ hàng!</p>
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

    <!-- Checkout Section -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <div class="billing-details">
                        <div class="section-title">
                            <h3 class="title">Thông Tin Người Nhận</h3>
                        </div>
                        <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
                        <form method="POST" action="">
                            <!-- Hidden fields để giữ dữ liệu sản phẩm được chọn -->
                            <?php if (isset($_POST['checkout'])): ?>
                                <?php foreach ($cartItems as $productId => $item): ?>
                                    <input type="hidden" name="selected_items[<?php echo $productId; ?>][productId]" value="<?php echo $productId; ?>">
                                    <input type="hidden" name="selected_items[<?php echo $productId; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="recipient-name">Tên người nhận:</label>
                                <input class="input" type="text" id="recipient-name" name="recipient-name" value="<?php echo htmlspecialchars($userInfo['name'] ?? ''); ?>" required />
                            </div>
                            <div class="form-group">
                                <label for="phone-number">Số điện thoại:</label>
                                <input class="input" type="tel" id="phone-number" name="phone-number" value="<?php echo htmlspecialchars($userInfo['phoneNumber'] ?? ''); ?>" required />
                            </div>
                            <div class="form-group">
                                <label for="delivery-notes">Ghi chú giao hàng (nếu có):</label>
                                <textarea class="input" id="delivery-notes" name="delivery-notes" rows="3" placeholder="Nhập ghi chú giao hàng"></textarea>
                            </div>
                    </div>

                    <div class="shiping-details">
                        <div class="section-title">
                            <h3 class="title">Chọn Địa Chỉ Giao Hàng</h3>
                        </div>
                        <div class="form-group">
                            <label for="saved-address">Địa chỉ giao hàng mặc định:</label>
                            <input class="input" type="text" id="saved-address" name="saved-address" value="<?php echo htmlspecialchars($userInfo['address'] ?? ''); ?>" readonly />
                        </div>
                        <div class="input-checkbox">
                            <input type="checkbox" id="new-address-checkbox" name="new-address-checkbox" />
                            <label for="new-address-checkbox"><span></span> Nhập địa chỉ giao hàng mới</label>
                        </div>
                        <div class="form-group">
                            <label for="new-address">Địa chỉ giao hàng mới:</label>
                            <input class="input" type="text" id="new-address" name="new-address" placeholder="Nhập địa chỉ giao hàng" disabled />
                        </div>
                    </div>

                    <div class="payment-method">
                        <div class="section-title">
                            <h3 class="title">Chọn Phương Thức Thanh Toán</h3>
                        </div>
                        <div class="input-radio">
                            <input type="radio" name="payment" id="payment-cash" value="cash" />
                            <label for="payment-cash"><span></span> Tiền mặt</label>
                        </div>
                        <div class="input-radio">
                            <input type="radio" name="payment" id="payment-bank-transfer" value="bank-transfer" />
                            <label for="payment-bank-transfer"><span></span> Chuyển khoản</label>
                        </div>
                        <div class="input-radio">
                            <input type="radio" name="payment" id="payment-card" value="card" />
                            <label for="payment-card"><span></span> Thẻ tín dụng</label>
                        </div>
                        <div id="card-details" style="display: none;">
                            <h4>Thông Tin Thẻ</h4>
                            <div class="form-group">
                                <label for="card-name">Tên chủ thẻ:</label>
                                <input class="input" type="text" id="card-name" name="card-name" placeholder="Nhập tên chủ thẻ" />
                            </div>
                            <div class="form-group">
                                <label for="card-number">Số thẻ:</label>
                                <input class="input" type="text" id="card-number" name="card-number" placeholder="Nhập số thẻ" />
                            </div>
                            <div class="form-group">
                                <label for="card-expiry">Ngày hết hạn:</label>
                                <input 
                                    class="input" 
                                    type="text" 
                                    id="card-expiry" 
                                    name="card-expiry" 
                                    placeholder="MM/YYYY" 
                                    maxlength="7" 
                                    oninput="formatExpiry(this)" 
                                />
                            </div>
                            <script>
                                function formatExpiry(input) {
                                    input.value = input.value.replace(/[^0-9\/]/g, '');
                                    if (input.value.length === 2 && !input.value.includes('/')) {
                                        input.value += '/';
                                    }
                                    if (input.value.length > 7) {
                                        input.value = input.value.slice(0, 7);
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 order-details">
                    <div class="section-title text-center">
                        <h3 class="title">Tóm Tắt Hóa Đơn</h3>
                    </div>
                    <div class="product-list">
                        <h4>Danh sách sản phẩm</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($cartItems)) {
                                    foreach ($cartItems as $productId => $item) {
                                        echo "<tr>
                                            <td><span id='product-name-{$productId}'>" . htmlspecialchars($item['name']) . "</span></td>
                                            <td class='text-center'><span id='product-quantity-{$productId}'>x" . htmlspecialchars($item['quantity']) . "</span></td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>Không có sản phẩm nào được chọn!</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="order-summary">
                        <div class="order-col">
                            <div><strong>Tổng tiền:</strong></div>
                            <div><strong><span id="grand-total-summary"><?php echo number_format($totalPrice, 0, ',', '.'); ?> VND</span></strong></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Tên người nhận:</strong></div>
                            <div><span id="recipient-name-summary"><?php echo htmlspecialchars($userInfo['name'] ?? ''); ?></span></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Số điện thoại:</strong></div>
                            <div><span id="phone-number-summary"><?php echo htmlspecialchars($userInfo['phoneNumber'] ?? ''); ?></span></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Ghi chú giao hàng:</strong></div>
                            <div><span id="delivery-notes-summary"></span></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Địa chỉ giao hàng:</strong></div>
                            <div><span id="delivery-address-summary"><?php echo htmlspecialchars($userInfo['address'] ?? ''); ?></span></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Phương thức thanh toán:</strong></div>
                            <div><span id="payment-method-summary"></span></div>
                        </div>
                    </div>
                    <div class="input-checkbox">
                        <input type="checkbox" id="terms" name="terms" required />
                        <label for="terms"><span></span> Tôi đã đọc và chấp nhận điều khoản và điều kiện</label>
                    </div>
                    <button type="submit" name="place_order" class="primary-btn order-submit">Đặt hàng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        const newAddressCheckbox = document.getElementById('new-address-checkbox');
        const newAddressInput = document.getElementById('new-address');
        const savedAddressInput = document.getElementById('saved-address');

        newAddressCheckbox.addEventListener('change', function() {
            if (this.checked) {
                newAddressInput.disabled = false;
                newAddressInput.required = true;
                savedAddressInput.required = false;
            } else {
                newAddressInput.disabled = true;
                newAddressInput.required = false;
                newAddressInput.value = "";
                savedAddressInput.required = true;
            }
            updateSummary();
        });

        if (!newAddressCheckbox.checked) {
            newAddressInput.disabled = true;
            savedAddressInput.required = true;
        }

        document.querySelectorAll('input[name="payment"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('card-details').style.display = this.value === 'card' ? 'block' : 'none';
                updateSummary();
            });
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment"]:checked');
            if (!document.getElementById('terms').checked) {
                e.preventDefault();
                alert('Vui lòng chấp nhận điều khoản và điều kiện!');
            } else if (!paymentMethod) {
                e.preventDefault();
                alert('Vui lòng chọn phương thức thanh toán!');
            }
        });

        function updateSummary() {
            const recipientName = document.getElementById('recipient-name').value;
            const phoneNumber = document.getElementById('phone-number').value;
            const deliveryNotes = document.getElementById('delivery-notes').value;
            const address = newAddressCheckbox.checked && newAddressInput.value ? newAddressInput.value : savedAddressInput.value;
            const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value || '';

            let paymentMethodText = '';
            switch (paymentMethod) {
                case 'cash': paymentMethodText = 'Tiền mặt'; break;
                case 'bank-transfer': paymentMethodText = 'Chuyển khoản ngân hàng'; break;
                case 'card':
                    const cardName = document.getElementById('card-name').value || 'Chưa nhập';
                    const cardNumber = document.getElementById('card-number').value || 'Chưa nhập';
                    const cardExpiry = document.getElementById('card-expiry').value || 'Chưa nhập';
                    paymentMethodText = `Thẻ tín dụng: ${cardName} - ${cardNumber} - Hết hạn: ${cardExpiry}`;
                    break;
                default: paymentMethodText = 'Chưa chọn phương thức thanh toán';
            }

            document.getElementById('recipient-name-summary').textContent = recipientName || '';
            document.getElementById('phone-number-summary').textContent = phoneNumber || '';
            document.getElementById('delivery-notes-summary').textContent = deliveryNotes || '';
            document.getElementById('delivery-address-summary').textContent = address || '';
            document.getElementById('payment-method-summary').textContent = paymentMethodText;
        }

        document.querySelectorAll('#recipient-name, #phone-number, #delivery-notes, #new-address, #saved-address, input[name="payment"], #card-name, #card-number, #card-expiry').forEach(function(element) {
            element.addEventListener('input', updateSummary);
        });

        updateSummary();
    </script>
</body>
</html>

<?php
$conn->close();
?>