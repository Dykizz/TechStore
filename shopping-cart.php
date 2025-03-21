<?php
include 'connect.php';
include 'information.php';

$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
if (!$userId) {
    header("Location: login.php");
    exit();
}

// Xử lý cập nhật số lượng hoặc xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $productId = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        if ($quantity < 1) {
            $quantity = 1; // Đặt lại thành 1 nếu nhỏ hơn 1
        }
        $updateStmt = $conn->prepare("UPDATE CartItem SET quantity = ? WHERE userId = ? AND productId = ?");
        $updateStmt->bind_param("iii", $quantity, $userId, $productId);
        $updateStmt->execute();
        $updateStmt->close();
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => true, 'message' => 'Cập nhật số lượng thành công']);
            exit();
        }
    } elseif (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        $deleteStmt = $conn->prepare("DELETE FROM CartItem WHERE userId = ? AND productId = ?");
        $deleteStmt->bind_param("ii", $userId, $productId);
        $deleteStmt->execute();
        $deleteStmt->close();
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
            exit();
        }
    }
}

// Lấy dữ liệu giỏ hàng để hiển thị
$cartItems = [];
$cartCount = 0;
$totalPrice = 0;

if ($userId) {
    $cartStmt = $conn->prepare("
        SELECT ci.productId, ci.quantity, p.name, p.price, p.image, p.discountPercent
        FROM CartItem ci
        JOIN Product p ON ci.productId = p.productId
        WHERE ci.userId = ?
    ");
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();

    while ($item = $cartResult->fetch_assoc()) {
        $discountPercent = isset($item['discountPercent']) ? $item['discountPercent'] : 0;
        $newPrice = $item['price'] * (1 - $discountPercent / 100);
        $quantity = max(1, $item['quantity']);
    
        $cartItems[$item['productId']] = [
            'name' => $item['name'],
            'price' => $item['price'],
            'image' => $item['image'],
            'quantity' => $quantity,
            'newPrice' => $newPrice
        ];
        $cartCount += $quantity;
        $totalPrice += $newPrice * $quantity;
    }
    
    $cartStmt->close();
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - Giỏ Hàng</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css?v=1.1" />
</head>
<body>
    <div class="alert alert-success alert-show announce" role="alert"><?php echo htmlspecialchars($message); ?></div>

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
                                                                <?php echo number_format($item['newPrice'], 0, ',', '.'); ?> VND
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
                                            <small><?php echo $cartCount; ?> sản phẩm trong giỏ</small>
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

    <!-- CART -->
    <div class="container mt-5">
        <h2 class="text-center" style="margin: 20px">Giỏ Hàng</h2>
        <div class="table-container">
            <form method="POST" action="checkout.php" id="checkout-form">
                <table class="table-giohang">
                    <thead class="thead-light">
                        <tr>
                            <th><input type="checkbox" id="select_all" onclick="toggle(this)"></th>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>Hình ảnh</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cartItems)): ?>
                            <?php $index = 1; foreach ($cartItems as $id => $item): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_items[<?php echo $id; ?>][productId]" value="<?php echo $id; ?>" 
                                               data-price="<?php echo $item['newPrice']; ?>" 
                                               onchange="updateTotal()">
                                        <input type="hidden" name="selected_items[<?php echo $id; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
                                    </td>
                                    <td><?php echo $index++; ?></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <div class="product-table">
                                            <div class="product-img">
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="product-price-container">
                                            <span class="price"><?php echo number_format($item['newPrice'], 0, ',', '.'); ?></span>
                                            <span class="currency">VND</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="quantity-control" style="display: flex; justify-content: center;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm decrease" data-product-id="<?php echo $id; ?>">-</button>
                                            <input style="text-align: center;" type="number" class="quantity-input form-control form-control-sm" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                            <button type="button" class="btn btn-outline-secondary btn-sm increase" data-product-id="<?php echo $id; ?>">+</button>
                                        </div>
                                    </td>
                                    <td class="item-total" data-price="<?php echo $item['newPrice']; ?>">
                                        <div class="product-price-container">
                                            <span class="price"><?php echo number_format($item['newPrice'] * $item['quantity'], 0, ',', '.'); ?></span>
                                            <span class="currency">VND</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="remove-btn btn delete-product" data-product-id="<?php echo $id; ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8">Giỏ hàng trống!</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td colspan="5">Tổng tiền (đã chọn):</td>
                            <td colspan="3"><strong id="selected-total">0 VND</strong></td>
                        </tr>
                    </tfoot>
                </table>
                <div style="text-align: center">
                    <button type="submit" name="checkout" class="primary-btn" style="margin-bottom: 20px">Thanh Toán</button>
                </div>
            </form>
        </div>
    </div>
    <!-- /CART -->

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
                                <li><a href="#"><i class="fa fa-phone"></i><strong>+84 975 419 019</strong> </a></li>
                                <li><a href="#"><i class="fa fa-envelope-o"></i>nhom6@email.com </a></li>
                                <li><a href="#"><i class="fa fa-map-marker"></i>273 An Dương Vương, Phường 3, Quận 5 </a></li>
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
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select_all');
        const checkboxes = document.querySelectorAll('input[name$="[productId]"]');
        const totalElement = document.getElementById('selected-total');
        const cartQtyElement = document.querySelector('.header-ctn .qty');
        const form = document.getElementById('checkout-form');

        function updateTotal() {
            const total = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .reduce((sum, checkbox) => {
                    const pricePerUnit = parseFloat(checkbox.getAttribute('data-price'));
                    const quantity = parseInt(checkbox.closest('tr').querySelector('.quantity-input').value);
                    return sum + (pricePerUnit * quantity);
                }, 0);
            totalElement.textContent = total.toLocaleString('vi-VN') + ' VND';
        }

        function updateSelectAll() {
            selectAll.checked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        }

        function toggle(source) {
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
            updateTotal();
        }

        selectAll.addEventListener('click', function () {
            toggle(this);
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                updateTotal();
                updateSelectAll();
            });
        });

        document.querySelectorAll('.quantity-control .btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const quantityControl = this.closest('.quantity-control');
                const quantityInput = quantityControl.querySelector('.quantity-input');
                const itemTotalElement = this.closest('tr').querySelector('.item-total');
                const checkbox = this.closest('tr').querySelector('input[type="checkbox"]');
                const hiddenQuantity = this.closest('tr').querySelector('input[type="hidden"]');
                const pricePerUnit = parseFloat(itemTotalElement.getAttribute('data-price'));
                let quantity = parseInt(quantityInput.value);
                let originalQuantity = quantity; // Lưu giá trị ban đầu để so sánh

                if (this.classList.contains('decrease') && quantity > 1) quantity--;
                if (this.classList.contains('increase')) quantity++;

                if (quantity < 1) quantity = 1;

                quantityInput.value = quantity;
                hiddenQuantity.value = quantity;

                const newTotal = pricePerUnit * quantity;
                itemTotalElement.querySelector('.price').textContent = newTotal.toLocaleString('vi-VN');
                checkbox.setAttribute('data-price', pricePerUnit);

                let currentCartCount = parseInt(cartQtyElement.textContent);
                if (this.classList.contains('increase')) currentCartCount++;
                if (this.classList.contains('decrease') && originalQuantity > 1) currentCartCount--; // Kiểm tra quantity ban đầu
                cartQtyElement.textContent = Math.max(0, currentCartCount);

                const formData = new FormData();
                formData.append('update_quantity', true);
                formData.append('product_id', productId);
                formData.append('quantity', quantity);

                fetch('shopping-cart.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTotal();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        document.querySelectorAll('.delete-product').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) {
                    const row = this.closest('tr');
                    const quantity = parseInt(row.querySelector('.quantity-input').value);
                    let currentCartCount = parseInt(cartQtyElement.textContent);
                    currentCartCount -= quantity;
                    cartQtyElement.textContent = Math.max(0, currentCartCount);

                    const formData = new FormData();
                    formData.append('delete_product', true);
                    formData.append('product_id', productId);

                    fetch('shopping-cart.php', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            row.remove();
                            updateTotal();
                            updateRowNumbers();
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });

        function updateRowNumbers() {
            const rows = document.querySelectorAll('.table-giohang tbody tr');
            rows.forEach((row, index) => {
                row.querySelector('td:nth-child(2)').textContent = index + 1;
            });
        }

        form.addEventListener('submit', function(e) {
            const selectedItems = Array.from(checkboxes).filter(cb => cb.checked);
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán!');
            }
        });

        updateTotal();
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>