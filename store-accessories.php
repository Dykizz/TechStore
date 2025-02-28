<?php
include 'connect.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 4;
$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : PHP_INT_MAX;

$sql = "SELECT * FROM Product WHERE categoryId = $category_id";
if (!empty($keyword)) {
    $sql .= " AND name LIKE '%$keyword%'";
}
if ($min_price > 0) {
    $sql .= " AND price >= $min_price";
}
if ($max_price < PHP_INT_MAX) {
    $sql .= " AND price <= $max_price";
}
$sql .= " GROUP BY name, categoryId"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Electro - <?php echo $category_name; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css"/>
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
</head>
<body>
    <div class="alert alert-success alert-show announce" role="alert"></div>
    
    <!-- HEADER -->
    <header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> 0975419019 </a></li>
                    <li><a href="#"><i class="fa fa-envelope-o"></i> nhom6@email.com </a></li>
                    <li><a href="#"><i class="fa fa-map-marker"></i> 273 An Dương Vương, Phường 3, Quận 5 </a></li>
                </ul>
            </div>
        </div>
        <div id="header">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="header-logo">
                            <a href="./index.php" class="logo">
                                <img src="./img/logo.png" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="header-search">
                        <form action="./store-accessories.php" method="GET">
                            <input name="keyword" class="input" placeholder="Nhập sản phẩm muốn tìm kiếm ..." value="<?php echo htmlspecialchars($keyword); ?>"/>
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>"/>
                            <input type="hidden" name="min_price" value="<?php echo $min_price > 0 ? $min_price : ''; ?>"/>
                            <input type="hidden" name="max_price" value="<?php echo $max_price < PHP_INT_MAX ? $max_price : ''; ?>"/>
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
                                    <div class="qty">4</div>
                                </a>
                                <div class="cart-dropdown">
                                    <div class="cart-list">
                                        <div class="product-widget">
                                            <div class="product-img">
                                                <img src="./img/sp1_giohang.png" alt="">
                                            </div>
                                            <div class="product-body">
                                                <h3 class="product-name">
                                                    <a href="./detail-product-smartphone.php">Iphone 16 Pro Max 512GB | Chính hãng VN/A</a>
                                                </h3>
                                                <h4 class="product-price"><span class="qty">1x</span>40.990.000 VND</h4>
                                            </div>
                                            <button class="delete"><i class="fa fa-close"></i></button>
                                        </div>
                                        <div class="product-widget">
                                            <div class="product-img">
                                                <img src="./img/sp2_giohang.png" alt="">
                                            </div>
                                            <div class="product-body">
                                                <h3 class="product-name">
                                                    <a href="./detail-product-accessories.php">Tai nghe Bluetooth Apple AirPods 3 MagSafe</a>
                                                </h3>
                                                <h4 class="product-price"><span class="qty">3x</span>3.990.000 VND</h4>
                                            </div>
                                            <button class="delete"><i class="fa fa-close"></i></button>
                                        </div>
                                    </div>
                                    <div class="cart-summary">
                                        <small>4 sản phẩm được chọn</small>
                                        <h5>TỔNG: 52.960.000 VND</h5>
                                    </div>
                                    <div class="cart-btns">
                                        <a href="./shopping-cart.php">Xem giỏ hàng</a>
                                        <a href="./checkout.php"> Thanh toán <i class="fa fa-arrow-circle-right"></i></a>
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

    <!-- NAVIGATION -->
    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li><a href="./index.php">Trang chủ</a></li>
                    <li class="<?php echo $category_id == 1 ? 'active' : ''; ?>"><a href="./store-laptop.php?category=1">Máy tính</a></li>
                    <li class="<?php echo $category_id == 2 ? 'active' : ''; ?>"><a href="./store-smartphone.php?category=2">Điện thoại</a></li>
                    <li class="<?php echo $category_id == 3 ? 'active' : ''; ?>"><a href="./store-camera.php?category=3">Máy ảnh</a></li>
                    <li class="<?php echo $category_id == 4 ? 'active' : ''; ?>"><a href="./store-accessories.php?category=4">Phụ kiện</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- SECTION -->
    <div class="section">
        <div class="container">
            <div class="row">
                <!-- ASIDE -->
                <div id="aside" class="col-md-3">
                    <div class="aside border-red">
                        <h3 class="aside-title text-center">Tìm kiếm nâng cao</h3>
                        <form method="GET" action="./store-accessories.php">
                            <label for="product-name">Tên sản phẩm</label>
                            <input class="form-control" style="margin-bottom: 10px;" name="keyword" id="product-name" type="text" placeholder="Nhập tên sản phẩm muốn tìm" value="<?php echo htmlspecialchars($keyword); ?>">
                            <label>Loại sản phẩm</label>
                            <select class="form-control" style="margin-bottom: 10px;" name="category">
                                <option value="">Tất cả</option>
                                <?php
                                $cat_sql = "SELECT * FROM Category";
                                $cat_result = $conn->query($cat_sql);
                                while ($cat = $cat_result->fetch_assoc()) {
                                    $selected = $cat['categoryId'] == $category_id ? 'selected' : '';
                                    echo "<option value='{$cat['categoryId']}' $selected>{$cat['name']}</option>";
                                }
                                ?>
                            </select>
                            <label>Giá (VND)</label>
                            <input class="form-control" style="margin-bottom: 10px;" type="number" min="0" name="min_price" placeholder="Giá nhỏ nhất" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                            <input class="form-control" style="margin-bottom: 10px;" type="number" min="0" name="max_price" placeholder="Giá lớn nhất" value="<?php echo $max_price < PHP_INT_MAX ? $max_price : ''; ?>">
                            <button type="submit" class="btn btn-filter">Tìm kiếm</button>
                        </form>
                    </div>
                    <div class="aside">
                        <h3 class="aside-title">Sản phẩm bán chạy</h3>
                        <!-- Giữ nguyên tĩnh tạm thời -->
                        <div class="product-widget">
                            <div class="product-img"><img src="./img/iphone-15-pro-max_3.png" alt=""></div>
                            <div class="product-body">
                                <h3 class="product-name"><a href="./detail-product-smartphone.php">iPhone 15 Pro Max 256GB | Chính hãng VN/A</a></h3>
                                <h4 class="product-price">29.490.000₫ <del class="product-old-price">34.990.000₫</del></h4>
                            </div>
                        </div>
                        <div class="product-widget">
                            <div class="product-img"><img src="./img/sanphambanchay_asus.png" alt=""></div>
                            <div class="product-body">
                                <h3 class="product-name"><a href="./detail-product-laptop.php">Laptop ASUS TUF Gaming A14 FA401WV-RG061WS</a></h3>
                                <h4 class="product-price">44.990.000₫ <del class="product-old-price">46.990.000₫</del></h4>
                            </div>
                        </div>
                        <div class="product-widget">
                            <div class="product-img"><img src="./img/apple-airpods-pro-2-usb-c_1_.png" alt=""></div>
                            <div class="product-body">
                                <h3 class="product-name"><a href="./detail-product-accessories.php">Tai nghe Bluetooth Apple AirPods Pro 2 2023 USB-C</a></h3>
                                <h4 class="product-price">5.790.000₫ <del class="product-old-price">6.190.000₫</del></h4>
                            </div>
                        </div>
                        <div class="product-widget">
                            <div class="product-img"><img src="./img/canon.png" alt=""></div>
                            <div class="product-body">
                                <h3 class="product-name"><a href="./detail-product-camera.php">Máy ảnh Canon EOS R10 kit RF-S18-45mm F4.5-6.3 IS STM</a></h3>
                                <h4 class="product-price">21.900.000₫ <del class="product-old-price">28.330.000₫</del></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /ASIDE -->

                <!-- STORE -->
                <div id="store" class="col-md-9">
                    <div class="row">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $discount = $row['discountPercent'] ? "-{$row['discountPercent']}%" : "";
                                // Dùng cột "rating" từ bảng Review nếu có, hiện tại giả định rating là 5
                                $stars = str_repeat('<i class="fa fa-star"></i>', 5); // Thay bằng logic từ Review nếu cần
                                echo "
                                <div class='col-md-4 col-xs-6' style='margin-bottom: 50px;'>
                                    <div class='product'>
                                        <div class='product-img'>
                                            <img src='{$row['image']}' alt=''>
                                            <div class='product-label'>
                                                <span class='sale'>{$discount}</span>
                                            </div>
                                        </div>
                                        <div class='product-body'>
                                            <h3 class='product-name'>
                                                <a href='detail-product.php?id={$row['productId']}'>{$row['name']}</a>
                                            </h3>
                                            <h4 class='product-price-index'>
                                                <del class='product-old-price-index'>" . number_format($row['price'] * (1 + $row['discountPercent'] / 100)) . " VND</del>
                                                <span class='new-price-index'>" . number_format($row['price']) . " VND</span>
                                            </h4>
                                            <div class='product-rating'>
                                                {$stars}
                                            </div>
                                        </div>
                                        <div class='add-to-cart'>
                                            <button class='add-to-cart-btn btn-announce' type-announce='success' 
                                            message='Thêm sản phẩm vào giỏ hàng thành công!'>
                                                <i class='fa fa-shopping-cart'></i> Thêm vào giỏ hàng
                                            </button>
                                        </div>
                                    </div>
                                </div>";
                            }
                        } else {
                            echo "<p>Không có sản phẩm nào trong danh mục này.</p>";
                        }
                        ?>
                    </div>
                    <div class="store-filter clearfix">
                        <ul class="store-pagination">
                            <li class="active">1</li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li><a href="#">4</a></li>
                            <li><a href="#"><i class="fa fa-angle-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                <!-- /STORE -->
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
                            <input class="input" type="email" placeholder="Nhập email">
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
                                <li><a href="#"><i class="fa fa-phone"></i>0975419019 </a></li>
                                <li><a href="#"><i class="fa fa-envelope-o"></i>nhom6@email.com </a></li>
                                <li><a href="#"><i class="fa fa-map-marker"></i>273 An Dương Vương, Phường 3, Quận 5 </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Sản phẩm</h3>
                            <ul class="footer-links">
                                <li><a href="./store.php?category=1">Máy tính</a></li>
                                <li><a href="./store.php?category=2">Điện thoại</a></li>
                                <li><a href="./store.php?category=3">Máy ảnh</a></li>
                                <li><a href="./store.php?category=4">Phụ kiện</a></li>
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

    <!-- jQuery Plugins -->
    <script src="js/helper.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        document.querySelectorAll('.btn-announce').forEach(button => {
            button.addEventListener('click', function() {
                const message = this.getAttribute('message');
                const type = this.getAttribute('type-announce');
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-show announce`;
                alert.setAttribute('role', 'alert');
                alert.textContent = message;
                document.body.insertBefore(alert, document.body.firstChild);
                setTimeout(() => alert.remove(), 3000);
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>