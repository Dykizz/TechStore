<?php
include 'connect.php';
include 'information.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : PHP_INT_MAX;

// Pagination parameters
$limit = 9; // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM Product WHERE isActive = 1";
if ($category_id !== null && $category_id !== 0) {
    $sql .= " AND categoryId = $category_id";
}
if (!empty($keyword)) {
    $sql .= " AND name LIKE '%$keyword%'";
}
if ($min_price > 0) {
    $sql .= " AND price >= $min_price";
}
if ($max_price < PHP_INT_MAX) {
    $sql .= " AND price <= $max_price";
}

$sql_car = "SELECT name FROM Category WHERE categoryId = $category_id";
$result_car = $conn->query($sql_car);
if ($result_car->num_rows > 0) {
    $category_name = $result_car->fetch_assoc()['name'];
}
// Get total number of products
$total_result = $conn->query($sql);
$total_products = $total_result->num_rows;
$total_pages = ceil($total_products / $limit);

// Add limit and offset to the query
$sql .= " LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Electro - <?php echo $category_name ?? 'Phụ kiện'; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css"/>
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="css/style.css"/>

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
            <!-- /ACCOUNT -->
          </div>
          <!-- row -->
        </div>
        <!-- container -->
      </div>

    <!-- NAVIGATION -->
    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li><a href="./index.php">Trang chủ</a></li>
                    <li class="<?php echo $category_id == 1 ? 'active' : ''; ?>"><a href="./products.php?category=1">Máy tính</a></li>
                    <li class="<?php echo $category_id == 2 ? 'active' : ''; ?>"><a href="./products.php?category=2">Điện thoại</a></li>
                    <li class="<?php echo $category_id == 3 ? 'active' : ''; ?>"><a href="./products.php?category=3">Máy ảnh</a></li>
                    <li class="<?php echo $category_id == 4 ? 'active' : ''; ?>"><a href="./products.php?category=4">Phụ kiện</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- SECTION -->
    <div class="section">
        <div class="container">
        <div class="alert alert-info d-flex align-items-center">
            <i class="fa-solid fa-circle-info mr-2"></i>
            Tìm thấy &nbsp;<span class="badge badge-primary p-2"><?= $total_products ?></span>&nbsp; sản phẩm phù hợp với điều kiện tìm kiếm
        </div>
            <div class="row">
                <!-- ASIDE -->

                <div id="aside" class="col-md-3">
                    <div class="aside border-red">
                        <h3 class="aside-title text-center">Tìm kiếm nâng cao</h3>
                        <form method="GET" action="./products.php">
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
                                $stars = str_repeat('<i class="fa fa-star"></i>', 5);
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
                                                <del class='product-old-price-index'>" . number_format($row['price'] * (1 + $row['discountPercent'] / 100), 0, ',', '.') . " VND</del>
                                                <span class='new-price-index'>" . number_format($row['price'], 0, ',', '.') . " VND</span>
                                            </h4>
                                            <div class='product-rating'>
                                                {$stars}
                                            </div>
                                        </div>
                                        <div class='add-to-cart'>
                                            <button class='add-to-cart-btn btn-announce' 
                                                    data-product-id='{$row['productId']}' 
                                                    data-name='{$row['name']}' 
                                                    data-price='{$row['price']}' 
                                                    data-image='{$row['image']}'>
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
                </div>
                <!-- /STORE -->
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

    <!-- jQuery Plugins -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
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
    </script>
</body>
</html>
<?php
$conn->close();
?>