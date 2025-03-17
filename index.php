<?php
include 'connect.php';
include 'information.php';

//Phân trang động:
$limit = 4; // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_result = $conn->query($sql);
$total_products = $total_result->num_rows;
$total_pages = ceil($total_products / $limit);
$sql .= " LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';
$sql = "SELECT * FROM Product Where isActive=1";
$sql_count = "SELECT COUNT(*) AS total FROM Product Where isActive=1";
if (!empty($keyword)) {
    $sql .= " WHERE name LIKE '%$keyword%'";
    $sql_count .= " WHERE name LIKE '%$keyword%'";
}
$result = $conn->query($sql);
$count_result = $conn->query($sql_count);
$count = $count_result->fetch_assoc()['total'];

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
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - HTML Ecommerce Template</title>
    <!--Favicon-->
    <link rel="shortcut icon" href="./img/logo.png" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css?v=1.1" />
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
                            <form action="./products.php">
                                <input name="keyword" class="input" placeholder="Nhập sản phẩm muốn tìm kiếm ..." />
                                <button class="search-btn">Tìm kiếm</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-3 clearfix">
                        <?php if ($fullname): ?>
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
                                                            <img src="<?php echo $item['image']; ?>" alt="" />
                                                        </div>
                                                        <div class="product-body">
                                                            <h3 class="product-name">
                                                                <a href="detail-product.php?id=<?php echo $id; ?>"><?php echo $item['name']; ?></a>
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

    <!-- NAVIGATION -->
    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li class="active"><a href="./index.php">Trang chủ</a></li>
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
            <div class="row">
                <div class="col-md-3 col-xs-6">
                    <div class="shop">
                        <div class="shop-img"><img src="./img/shop01.png" alt="" /></div>
                        <div class="shop-body">
                            <h3>Máy tính</h3>
                            <a href="./products.php?category=1" class="cta-btn">Mua ngay <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="shop">
                        <div class="shop-img"><img src="./img/shop02.png" alt="" /></div>
                        <div class="shop-body">
                            <h3>Điện thoại</h3>
                            <a href="./products.php?category=2" class="cta-btn">Mua ngay <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="shop">
                        <div class="shop-img"><img src="./img/shop03.png" alt="" /></div>
                        <div class="shop-body">
                            <h3>Máy ảnh</h3>
                            <a href="./products.php?category=3" class="cta-btn">Mua ngay <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="shop">
                        <div class="shop-img"><img src="./img/shop04.png" alt="" /></div>
                        <div class="shop-body">
                            <h3>Phụ kiện</h3>
                            <a href="./products.php?category=4" class="cta-btn">Mua ngay <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /SECTION -->

    <!-- SECTION - Sản phẩm mới -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="section-title">
                        <h3 class="title">Sản phẩm mới</h3>
                    </div>
                </div>
                <div class="col-md-12">
    <div class="row">
        <div class="products-tabs">
            <div id="tab1" class="tab-pane active">
                <div class="products-slick" data-nav="#slick-nav-1">
                    <?php
                    // Truy vấn lấy 4 sản phẩm mới nhất từ bảng Product
                    $newProductsStmt = $conn->prepare("
                        SELECT productId, name, price, image, discountPercent 
                        FROM Product 
                        WHERE stock > 0 
                        ORDER BY createdAt DESC 
                        LIMIT 4
                    ");
                    $newProductsStmt->execute();
                    $newProducts = $newProductsStmt->get_result();

                    while ($row = $newProducts->fetch_assoc()) {
                        $discount = $row['discountPercent'] > 0 ? "-{$row['discountPercent']}%" : "";
                        $oldPrice = $row['discountPercent'] > 0 ? $row['price'] * (1 + $row['discountPercent'] / 100) : $row['price'];
                    ?>    
                    <div class="product">
                        <div class="product-img">
                            <img src="<?php echo $row['image']; ?>" alt="" />
                            <div class="product-label">
                                <?php if ($discount): ?>
                                    <span class="sale"><?php echo $discount; ?></span>
                                <?php endif; ?>
                                <span class="new">MỚI</span>
                            </div>
                        </div>
                        <div class="product-body">
                            <h3 class="product-name">
                                <a href="detail-product.php?id=<?php echo $row['productId']; ?>">
                                    <?php echo $row['name']; ?>
                                </a>
                            </h3>
                            <h4 class="product-price-index">
                                <?php if ($row['discountPercent'] > 0): ?>
                                    <del class="product-old-price-index">
                                        <?php echo number_format($oldPrice, 0, ',', '.'); ?> VND
                                    </del>
                                <?php endif; ?>
                                <span class="new-price-index">
                                    <?php echo number_format($row['price'], 0, ',', '.'); ?> VND
                                </span>
                            </h4>
                            <div class="product-rating">
                                <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                            </div>
                        </div>
                        <div class="add-to-cart">
                            <button class="add-to-cart-btn btn-announce"
                                    data-product-id="<?php echo $row['productId']; ?>"
                                    data-name="<?php echo $row['name']; ?>"
                                    data-price="<?php echo $row['price']; ?>"
                                    data-image="<?php echo $row['image']; ?>">
                                <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                    <?php } 
                    $newProductsStmt->close();
                    ?>
                </div>
                <div id="slick-nav-1" class="products-slick-nav"></div>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
    <!-- /SECTION -->

    <!-- HOT DEAL SECTION -->
    <div id="hot-deal" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="hot-deal">
                        <ul class="hot-deal-countdown">
                            <li><div><h3>02</h3><span>Ngày</span></div></li>
                            <li><div><h3>10</h3><span>Giờ</span></div></li>
                            <li><div><h3>34</h3><span>Phút</span></div></li>
                            <li><div><h3>60</h3><span>Giây</span></div></li>
                        </ul>
                        <h2 class="text-uppercase">Ưu đãi tuần này!</h2>
                        <p>Giảm tới 50%</p>
                        <a class="primary-btn cta-btn" href="#">Mua ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /HOT DEAL SECTION -->

    <!-- SECTION - Sản phẩm bán chạy -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="section-title">
                        <h3 class="title">Sản phẩm bán chạy</h3>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="products-tabs">
                            <div id="tab2" class="tab-pane fade in active">
                                <div class="products-slick" data-nav="#slick-nav-2">
                                    <?php
                                        $hotProductsStmt = $conn->prepare("
                                                SELECT p.productId, p.name, p.price, p.image, p.discountPercent, SUM(od.quantity) as total_sales
                                                FROM Product p
                                                LEFT JOIN OrderDetail od ON p.productId = od.productId
                                                WHERE p.stock > 0
                                                GROUP BY p.productId, p.name, p.price, p.image, p.discountPercent
                                                ORDER BY total_sales DESC
                                                LIMIT 4
                                            ");
                                            $hotProductsStmt->execute();
                                            $hotProducts = $hotProductsStmt->get_result();

                                            while ($row = $hotProducts->fetch_assoc()) {
                                                $discount = $row['discountPercent'] > 0 ? "-{$row['discountPercent']}%" : "";
                                                $oldPrice = $row['discountPercent'] > 0 ? $row['price'] * (1 + $row['discountPercent'] / 100) : $row['price'];
                                            ?>
                                                <div class="product">
                                                    <div class="product-img">
                                                        <img src="<?php echo $row['image']; ?>" alt="" />
                                                        <div class="product-label">
                                                            <?php if ($discount): ?>
                                                                <span class="sale"><?php echo $discount; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="product-body">
                                                        <h3 class="product-name"><a href="detail-product.php?id=<?php echo $row['productId']; ?>"><?php echo $row['name']; ?></a></h3>
                                                        <h4 class="product-price-index">
                                                            <?php if ($row['discountPercent'] > 0): ?>
                                                                <del class="product-old-price-index"><?php echo number_format($oldPrice, 0, ',', '.'); ?> VND</del>
                                                            <?php endif; ?>
                                                            <span class="new-price-index"><?php echo number_format($row['price'], 0, ',', '.'); ?> VND</span>
                                                        </h4>
                                                        <div class="product-rating">
                                                            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                        </div>
                                                    </div>
                                                    <div class="add-to-cart">
                                                        <button class="add-to-cart-btn btn-announce"
                                                                data-product-id="<?php echo $row['productId']; ?>"
                                                                data-name="<?php echo $row['name']; ?>"
                                                                data-price="<?php echo $row['price']; ?>"
                                                                data-image="<?php echo $row['image']; ?>">
                                                            <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php } 
                                            $hotProductsStmt->close();
                                            ?>
                                </div>
                                <div id="slick-nav-2" class="products-slick-nav"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /SECTION -->

    <!-- store bottom filter -->
                    <div class="store-filter clearfix">
                        <ul class="store-pagination">
                            <?php if ($page > 1): ?>
                                <li><a href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_id; ?>&keyword=<?php echo $keyword; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>"><i class="fa fa-angle-left"></i></a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="<?php echo $i == $page ? 'active' : ''; ?>"><a href="?page=<?php echo $i; ?>&category=<?php echo $category_id; ?>&keyword=<?php echo $keyword; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>"><?php echo $i; ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li><a href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_id; ?>&keyword=<?php echo $keyword; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>"><i class="fa fa-angle-right"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
    <!-- /store bottom filter -->

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
        document.querySelectorAll('.btn-announce').forEach(button => {
            button.addEventListener('click', async function (event) {
                event.preventDefault();

                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('productId', this.getAttribute('data-product-id'));
                formData.append('name', this.getAttribute('data-name'));
                formData.append('price', this.getAttribute('data-price'));
                formData.append('image', this.getAttribute('data-image'));
                
                try {
                    let response = await fetch("add-to-cart.php", {
                        method: "POST",
                        body: formData
                    });

                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    let data = await response.json();

                    if (data.status === "success") {
                        document.querySelector('.header-ctn .qty').textContent = data.cartQuantity;
                        showAnnouncement("success", "Thêm sản phẩm vào giỏ hàng thành công!");
                        setTimeout(() => window.location.reload(), 3000);
                    } else {
                        showAnnouncement("danger", data.message);
                    }
                } catch (error) {
                    console.log("Fetch Error:", error);
                    showAnnouncement("danger", "Lỗi khi thêm sản phẩm vào giỏ hàng!");
                }
            });
        });

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
<?php $conn->close(); ?>