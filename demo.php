<?php
session_start();
include 'connect.php';

// Kiểm tra kết nối
if ($conn === false) {
    die("Lỗi kết nối database!");
}

// Lấy thông tin người dùng từ session (giả sử đã đăng nhập)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$userInfo = null;
if ($userId) {
    $stmt = $conn->prepare("SELECT name, phoneNumber FROM User WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Xử lý form đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $recipientName = trim($_POST['recipient-name']);
    $phoneNumber = trim($_POST['phone-number']);
    $deliveryNotes = trim($_POST['delivery-notes']);
    $address = trim($_POST['new-address'] ?? $_POST['saved-address']);
    $paymentMethod = $_POST['payment'];

    // Validate data
    if (empty($recipientName) || empty($phoneNumber)) {
        $error = "Vui lòng điền đầy đủ thông tin người nhận và số điện thoại!";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phoneNumber)) {
        $error = "Số điện thoại không hợp lệ!";
    } elseif (empty($address)) {
        $error = "Vui lòng chọn hoặc nhập địa chỉ giao hàng!";
    } else {
        // Tạo mã đơn hàng ngẫu nhiên
        $orderCode = 'ORD_' . time() . rand(1000, 9999);
        $totalAmount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $_SESSION['cart'] ?? []));

        // Lưu vào bảng Orders
        $stmt = $conn->prepare("INSERT INTO Orders (userId, orderCode, orderDate, status, totalAmount, customShippingAddress, paymentMethod) VALUES (?, ?, NOW(), ?, ?, ?, ?)");
        $status = 'Pending';
        $stmt->bind_param("ississ", $userId, $orderCode, $status, $totalAmount, $address, $paymentMethod);
        if ($stmt->execute()) {
            $orderId = $conn->insert_id;
            $stmt->close();

            // Lưu chi tiết đơn hàng
            if (!empty($_SESSION['cart'])) {
                $stmt = $conn->prepare("INSERT INTO OrderDetail (orderId, productId, quantity, price) VALUES (?, ?, ?, ?)");
                foreach ($_SESSION['cart'] as $productId => $item) {
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
                    $stmt->execute();
                }
                $stmt->close();

                // Xóa giỏ hàng sau khi đặt hàng thành công
                unset($_SESSION['cart']);

                // Chuyển hướng đến success-order.php với orderId
                header("Location: order-success.php?orderId=" . $orderId);
                exit;
            } else {
                $error = "Giỏ hàng trống, không thể đặt hàng!";
            }
        } else {
            $error = "Lỗi khi lưu đơn hàng: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Checkout</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <!-- HEADER -->
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
                            <a href="./index.php" class="logo">
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
                                    <span><?php echo htmlspecialchars($userInfo['name'] ?? 'Tài khoản'); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="./account-information.php">Thông tin cá nhân</a></li>
                                    <li><a href="./purchasing-history.php">Lịch sử mua hàng</a></li>
                                    <li><a href="./change-password.php">Đổi mật khẩu</a></li>
                                    <li><a href="./index-notlogin.php">Đăng xuất</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span>Giỏ hàng</span>
                                    <div class="qty"><?php echo array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')); ?></div>
                                </a>
                                <div class="cart-dropdown">
                                    <div class="cart-list">
                                        <?php
                                        if (!empty($_SESSION['cart'])) {
                                            foreach ($_SESSION['cart'] as $id => $item) {
                                                echo "
                                                <div class='product-widget'>
                                                    <div class='product-img'>
                                                        <img src='{$item['image']}' alt='' />
                                                    </div>
                                                    <div class='product-body'>
                                                        <h3 class='product-name'><a href='detail-product.php?id={$id}'>{$item['name']}</a></h3>
                                                        <h4 class='product-price'><span class='qty'>{$item['quantity']}x</span>" . number_format($item['price'], 0, ',', '.') . " VND</h4>
                                                    </div>
                                                    <button class='delete'><i class='fa fa-close'></i></button>
                                                </div>";
                                            }
                                        } else {
                                            echo "<p>Giỏ hàng trống!</p>";
                                        }
                                        ?>
                                    </div>
                                    <div class="cart-summary">
                                        <small><?php echo array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')); ?> sản phẩm được chọn</small>
                                        <h5>TỔNG: <?php echo number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $_SESSION['cart'] ?? [])), 0, ',', '.'); ?> VND</h5>
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

    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li><a href="./index.php">Trang chủ</a></li>
                    <li><a href="./store-laptop.php">Máy tính</a></li>
                    <li><a href="./store-smartphone.php">Điện thoại</a></li>
                    <li><a href="./store-camera.php">Máy ảnh</a></li>
                    <li><a href="./store-accessories.php">Phụ kiện</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <div class="billing-details">
                        <div class="section-title">
                            <h3 class="title">Thông Tin Người Nhận</h3>
                        </div>
                        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
                        <form method="POST" action="">
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
                            <label for="saved-address">Chọn địa chỉ giao hàng:</label>
                            <div class="select-container">
                                <select class="form-control" id="saved-address" name="saved-address">
                                    <option value="" disabled selected>Chọn địa chỉ</option>
                                    <option value="273, An Dương Vương, Phường 3, Quận 5, TP.Hồ Chí Minh">273, An Dương Vương, Phường 3, Quận 5, TP.Hồ Chí Minh</option>
                                    <option value="59C, Nguyễn Đình Chiểu, Phường 6, Quận 3, TP.Hồ Chí Minh">59C, Nguyễn Đình Chiểu, Phường 6, Quận 3, TP.Hồ Chí Minh</option>
                                    <option value="19, Đ. Nguyễn Hữu Thọ, Tân Hưng, Quận 7, TP.Hồ Chí Minh">19, Đ. Nguyễn Hữu Thọ, Tân Hưng, Quận 7, TP.Hồ Chí Minh</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-checkbox">
                            <input type="checkbox" id="new-address-checkbox" name="new-address-checkbox" />
                            <label for="new-address-checkbox"><span></span> Nhập địa chỉ giao hàng mới</label>
                        </div>
                        <div class="form-group">
                            <label for="new-address">Địa chỉ giao hàng:</label>
                            <input class="input" type="text" id="new-address" name="new-address" placeholder="Nhập địa chỉ giao hàng" disabled />
                        </div>
                    </div>

                    <div class="payment-method">
                        <div class="section-title">
                            <h3 class="title">Chọn Phương Thức Thanh Toán</h3>
                        </div>
                        <div class="input-radio">
                            <input type="radio" name="payment" id="payment-cash" value="cash" required />
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
                                <input class="input" type="text" id="card-expiry" name="card-expiry" placeholder="MM/YY" />
                            </div>
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
                                if (!empty($_SESSION['cart'])) {
                                    foreach ($_SESSION['cart'] as $productId => $item) {
                                        echo "<tr>
                                            <td><span id='product-name-{$productId}'>" . htmlspecialchars($item['name']) . "</span></td>
                                            <td class='text-center'><span id='product-quantity-{$productId}'>x" . htmlspecialchars($item['quantity']) . "</span></td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>Giỏ hàng trống!</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="order-summary">
                        <div class="order-col">
                            <div><strong>Tổng tiền:</strong></div>
                            <div><strong><span id="grand-total-summary"><?php echo number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $_SESSION['cart'] ?? [])), 0, ',', '.'); ?> VND</span></strong></div>
                        </div>
                        <div class="order-col">
                            <div><strong>Tên người nhận:</strong></div>
                            <div><span id="recipient-name-summary"><?php echo htmlspecialchars($userInfo['name'] ?? ''); ?></span></div>
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
                    <!-- Di chuyển phần đồng ý điều khoản và nút đặt hàng vào đây -->
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

    <footer id="footer">
        <div class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Về chúng tôi</h3>
                            <p>Chất lượng làm nên thương hiệu.</p>
                            <ul class="footer-links">
                                <li><a href="#"><i class="fa fa-phone"></i>0975419019 </a></li>
                                <li><a href="#"><i class="fa fa-envelope-o"></i>nhom6@email.com</a></li>
                                <li><a href="#"><i class="fa fa-map-marker"></i>273 An Dương Vương, Phường 3, Quận 5</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Sản phẩm</h3>
                            <ul class="footer-links">
                                <li><a href="./store-laptop.php">Máy tính</a></li>
                                <li><a href="./store-smartphone.php">Điện thoại</a></li>
                                <li><a href="./store-camera.php">Máy ảnh</a></li>
                                <li><a href="./store-accessories.php">Phụ kiện</a></li>
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
                                <li><a href="./purchasing-history.php">Giỏ hàng</a></li>
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

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Xử lý logic địa chỉ giao hàng: chọn 1 trong 2 cách
        const newAddressCheckbox = document.getElementById('new-address-checkbox');
        const newAddressInput = document.getElementById('new-address');
        const savedAddressSelect = document.getElementById('saved-address');

        newAddressCheckbox.addEventListener('change', function() {
            if (this.checked) {
                newAddressInput.disabled = false;
                newAddressInput.required = true;
                savedAddressSelect.required = false;
                savedAddressSelect.value = ""; // Reset lựa chọn địa chỉ đã lưu
            } else {
                newAddressInput.disabled = true;
                newAddressInput.required = false;
                newAddressInput.value = ""; // Reset địa chỉ mới
                savedAddressSelect.required = true;
            }
        });

        // Khi trang tải, kiểm tra trạng thái ban đầu
        if (!newAddressCheckbox.checked) {
            newAddressInput.disabled = true;
            savedAddressSelect.required = true;
        }

        document.querySelectorAll('input[name="payment"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('card-details').style.display = this.value === 'card' ? 'block' : 'none';
            });
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            if (!document.getElementById('terms').checked) {
                e.preventDefault();
                alert('Vui lòng chấp nhận điều khoản và điều kiện!');
            }
        });

        // Cập nhật tóm tắt hóa đơn
        function updateSummary() {
            const recipientName = document.getElementById('recipient-name').value;
            const address = document.getElementById('new-address').value || document.getElementById('saved-address').value;
            const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value || '';

            // Ánh xạ phương thức thanh toán sang tiếng Việt
            const paymentMethodText = {
                'cash': 'Tiền mặt',
                'bank-transfer': 'Chuyển khoản',
                'card': 'Thẻ tín dụng'
            }[paymentMethod] || '';

            document.getElementById('recipient-name-summary').textContent = recipientName || '';
            document.getElementById('delivery-address-summary').textContent = address || '';
            document.getElementById('payment-method-summary').textContent = paymentMethodText;
        }

        document.querySelectorAll('#recipient-name, #new-address, #saved-address, input[name="payment"]').forEach(function(element) {
            element.addEventListener('change', updateSummary);
        });
        updateSummary();
    </script>
</body>
</html>

<?php
$conn->close();
?>