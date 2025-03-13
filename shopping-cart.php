<?php
include 'connect.php';
include 'information.php';

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý thay đổi số lượng
if (isset($_POST['update_quantity']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
    header("Location: shopping-cart.php");
    exit();
}

// Xử lý xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header("Location: shopping-cart.php");
    exit();
}

// Tính tổng số lượng và tổng tiền
$cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
$total_price = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $_SESSION['cart']));

// Hiển thị thông báo nếu có
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Xóa thông báo sau khi hiển thị
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
    <link type="text/css" rel="stylesheet" href="css/style.css" />
    <style>
        .table-giohang td { vertical-align: middle; text-align: center; }
        .table-giohang .product-img img { width: 50px; height: 50px; object-fit: cover; }
        .btn { padding: 5px 10px; }
        .alert-show { display: none; position: fixed; top: 20px; right: 20px; z-index: 1000; }
    </style>
</head>
<body>
    <div class="alert alert-success alert-show announce" role="alert"><?php echo $message; ?></div>
    <!-- HEADER -->
    <div id="header">
        <!-- container -->
        <div class="container">
          <!-- row -->
          <div class="row">
            <!-- LOGO -->
            <div class="col-md-3">
              <div class="header-logo">
                <a href="./index.php" class="logo">
                  <img src="./img/logo.png" alt="" />
                </a>
              </div>
            </div>
            <!-- /LOGO -->
            <!-- SEARCH BAR -->
            <div class="col-md-6">
              <div class="header-search">
                <form action="./products.php">
                  <input
                    name="keyword"
                    class="input"
                    placeholder="Nhập sản phẩm muốn tìm kiếm ..."
                  />
                  <button class="search-btn">Tìm kiếm</button>
                </form>
              </div>
            </div>
            <!-- /SEARCH BAR -->
            <!-- ACCOUNT -->
            <div class="col-md-3 clearfix"> <?php if ($fullname) : ?>
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
                                                        <h3 class='product-name'>
                                                            <a href='detail-product.php?id={$id}'>{$item['name']}</a>
                                                        </h3>
                                                        <h4 class='product-price'>
                                                            <span class='qty'>{$item['quantity']}x</span>" . number_format($item['price'], 0, ',', '.') . " VND
                                                        </h4>
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
                        <?php else : ?>
                            <div class="header-ctn">
                                <div>
                                    <a href="./login.php" class="btn btn-primary" aria-expanded="true">
                                        <span>Đăng nhập</span>
                                    </a>
                                </div>
                                <div>
                                    <a href="./register.php" class="btn btn-primary" aria-expanded="true">
                                        <span>Đăng kí</span>
                                    </a>
                                </div>
                                <div class="menu-toggle">
                                    <a href="#"><i class="fa fa-bars"></i><span>Danh mục</span></a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
            <!-- /ACCOUNT -->
          </div>
          <!-- row -->
        </div>
        <!-- container -->
      </div>
    <!-- /HEADER -->
    <!-- NAVIGATION -->
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
    <!-- /NAVIGATION -->
    <!-- CART -->
    <div class="container mt-5">
        <h2 class="text-center" style="margin: 20px">Giỏ Hàng</h2>
        <div class="table-container">
            <table class="table-giohang">
                <thead class="thead-light">
                    <tr>
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
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <?php $index = 1; foreach ($_SESSION['cart'] as $id => $item): ?>
                            <tr>
                                <td><?php echo $index++; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td>
                                    <div class="product-table">
                                        <div class="product-img">
                                            <img src="<?php echo $item['image']; ?>" alt="" />
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                        <button type="submit" name="update_quantity" value="decrease" class="btn">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 50px; text-align: center;" onchange="this.form.submit()">
                                        <button type="submit" name="update_quantity" value="increase" class="btn">+</button>
                                    </form>
                                </td>
                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VND</td>
                                <td>
                                    <a href="shopping-cart.php?action=remove&product_id=<?php echo $id; ?>" class="remove-btn btn">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Giỏ hàng trống!</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="total-row">
                    <tr>
                        <td colspan="4">Tổng tiền:</td>
                        <td colspan="3"><strong><?php echo number_format($total_price, 0, ',', '.'); ?> VND</strong></td>

                    </tr>
                </tfoot>
            </table>
        </div>
        <div style="text-align: center">
            <a href="./checkout.php" class="primary-btn" style="margin-bottom: 20px">Thanh Toán</a>
        </div>
    </div>
    <!-- /CART -->
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
                                <li><a href="#"> <i class="fa fa-phone"></i><strong>+84 975 419 019</strong></a></li>
                                <li><a href="#"> <i class="fa fa-envelope-o"></i>nhom6@email.com</a></li>
                                <li><a href="#"> <i class="fa fa-map-marker"></i>273 An Dương Vương, Phường 3, Quận 5</a></li>
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
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Xử lý nút tăng/giảm số lượng
        document.querySelectorAll('button[name="update_quantity"]').forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.form;
                const quantityInput = form.querySelector('input[name="quantity"]');
                let quantity = parseInt(quantityInput.value);
                if (this.value === 'decrease' && quantity > 1) quantity--;
                else if (this.value === 'increase') quantity++;
                quantityInput.value = quantity;
                form.submit();
            });
        });

        // Xử lý thêm vào giỏ hàng bằng AJAX
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Ngăn chặn submit form mặc định

                const form = this.closest('form');
                const formData = new FormData(form);

                fetch('add-to-cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hiển thị thông báo
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-show announce';
                        alert.setAttribute('role', 'alert');
                        alert.textContent = data.message;
                        document.body.insertBefore(alert, document.body.firstChild);
                        setTimeout(() => alert.remove(), 3000);

                        // Cập nhật số lượng giỏ hàng ở header
                        document.getElementById('cart-qty').textContent = data.cart_count;

                        // Cập nhật giỏ hàng dropdown (tuỳ chọn)
                        const cartDropdown = document.querySelector('.cart-dropdown .cart-list');
                        if (cartDropdown) {
                            // Lấy dữ liệu mới từ session (có thể cần API riêng)
                            // Ở đây chỉ làm mới toàn bộ dropdown thủ công (tuỳ chọn tối ưu hơn)
                            location.reload(); // Tạm thời reload để cập nhật dropdown
                        }
                    } else {
                        alert('Lỗi khi thêm vào giỏ hàng!');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>