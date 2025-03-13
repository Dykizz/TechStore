<?php
session_start(); 
include 'connect.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$fullname = 'Tài khoản'; 
if ($userId) {
    $stmt = $conn->prepare("SELECT name FROM User WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    if ($userInfo) {
        $fullname = $userInfo['name'];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Electro - HTML Ecommerce Template</title>

    <!-- Google font -->
    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700"
      rel="stylesheet"
    />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />

    <!-- Slick -->
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />

    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="css/font-awesome.min.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="css/style.css" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <!-- HEADER -->
    <header>
        <!-- TOP HEADER -->
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> Hotline: <strong>+84 975 419 019</strong></a></li>
                    <li><a href="#"><i class="fa fa-envelope-o"></i> nhom6@email.com</a></li>
                    <li><a href="#"><i class="fa fa-map-marker"></i> 273 An Dương Vương, Phường 3, Quận 5</a></li>
                </ul>
            </div>
        </div>
      </div>
      <!-- /TOP HEADER -->
      <!-- MAIN HEADER -->
      <div id="header">
        <!-- container -->
        <div class="container">
          <!-- row -->
          <div class="row">
            <!-- LOGO -->
            <div class="col-md-3">
              <div class="header-logo">
                <a href="index.html" class="logo">
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
            <div class="col-md-3 clearfix">
              <div class="header-ctn">
                <!-- Tài khoản -->
                <div class="dropdown">
                  <a
                    class="dropdown-toggle"
                    data-toggle="dropdown"
                    aria-expanded="true"
                  >
                    <i class="fa fa-user-o"></i>

                    <span>Nguyễn Thế Anh</span>
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a href="./account-information.html">Thông tin cá nhân</a>
                    </li>
                    <li>
                      <a href="./purchasing-history.html">Lịch sử mua hàng</a>
                    </li>
                    <li><a href="./change-password.html">Đổi mật khẩu</a></li>
                    <li><a href="./index-notlogin.html">Đăng xuất</a></li>
                  </ul>
                </div>
                <!-- /Tài khoản -->

                <!-- Giỏ hàng -->
                <div class="dropdown">
                  <a
                    class="dropdown-toggle"
                    data-toggle="dropdown"
                    aria-expanded="true"
                  >
                    <i class="fa fa-shopping-cart"></i>
                    <span>Giỏ hàng</span>
                    <div class="qty">4</div>
                  </a>
                  <div class="cart-dropdown">
                    <div class="cart-list">
                      <div class="product-widget">
                        <div class="product-img">
                          <img src="./img/sp1_giohang.png" alt="" />
                        </div>
                    </div>
                    <!-- /LOGO -->
                    <!-- SEARCH BAR -->
                    <div class="col-md-6">
                        <div class="header-search">
                            <form action="./store-search.php" method="GET">
                                <input name="keyword" class="input" placeholder="Nhập sản phẩm muốn tìm kiếm ..." value="<?php echo htmlspecialchars($keyword); ?>"/>
                                <input type="submit" class="search-btn" value="Tìm kiếm" />
                            </form>
                        </div>
                    </div>
                    <!-- /SEARCH BAR -->
                    <!-- ACCOUNT -->
                    <div class="col-md-3 clearfix">
                        <div class="header-ctn">
                            <!-- Tài khoản -->
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                    <i class="fa fa-user-o"></i>
                                    <span><?php echo htmlspecialchars($fullname); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="./account-information.php">Thông tin cá nhân</a></li>
                                    <li><a href="./purchasing-history.php">Lịch sử mua hàng</a></li>
                                    <li><a href="./change-password.php">Đổi mật khẩu</a></li>
                                    <li><a href="./index-notlogin.php">Đăng xuất</a></li>
                                </ul>
                            </div>
                            <!-- /Tài khoản -->
                            <!-- Giỏ hàng -->
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
                            <!-- /Giỏ hàng -->
                            <div class="menu-toggle">
                                <a href="#"><i class="fa fa-bars"></i><span>Danh mục</span></a>
                            </div>
                        </div>
                    </div>
                    <!-- /ACCOUNT -->
                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </div>
        <!-- /MAIN HEADER -->
    </header>
    <!-- /HEADER -->

    <!-- NAVIGATION -->
    <nav id="navigation">
        <!-- container -->
        <div class="container">
            <!-- responsive-nav -->
            <div id="responsive-nav">
                <!-- NAV -->
                <ul class="main-nav nav navbar-nav">
                    <li class=""><a href="./index.php">Trang chủ</a></li>
                    <li><a href="./store-laptop.php?category=1">Máy tính</a></li>
                    <li><a href="./store-smartphone.php?category=2">Điện thoại</a></li>
                    <li><a href="./store-camera.php?category=3">Máy ảnh</a></li>
                    <li><a href="./store-accessories.php?category=4">Phụ kiện</a></li>
                </ul>
                <!-- /NAV -->
            </div>
            <!-- /responsive-nav -->
        </div>
        <!-- /container -->
    </nav>
    <!-- /NAVIGATION -->

    <!-- SECTION -->
    <div class="section">
      <!-- container -->
      <div class="container">
        <!-- row -->
        <div class="row">
          <div class="success-notification">
            <h2>Đã đặt hàng thành công!</h2>
          </div>
          <div style="display: flex; height: 250px; justify-content: center">
            <img
              class="d-block"
              src="./img/order-success.jpg"
              alt="Đặt hàng thành công!"
              style="height: 100%; width: auto; margin: auto"
            />
          </div>
          <p
            class="text-center mb-3"
            style="font-weight: 500; font-size: medium"
          >
            Cảm ơn bạn đã đặt hàng tại cửa hàng chúng tôi! Đơn hàng của bạn đã
            được ghi nhận và đang chờ xác nhận. Nhân viên của chúng tôi sẽ liên
            hệ với bạn trong thời gian sớm nhất để xác nhận thông tin và tiến
            hành xử lý đơn hàng. Chúng tôi rất vui được phục vụ bạn và hy vọng
            sẽ mang lại trải nghiệm mua sắm tuyệt vời!
          </p>
          <div class="button-wrapper">
            <a href="./index.php" class="primary-btn continue-shopping"
              >Tiếp tục mua sắm</a
            >
          </div>
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>

    <!-- /SECTION -->

    <!-- NEWSLETTER -->
    <div id="newsletter" class="section">
      <!-- container -->
      <div class="container">
        <!-- row -->
        <div class="row">
          <div class="col-md-12">
            <div class="newsletter">
              <p>Đăng ký để nhận <strong>THÔNG BÁO MỚI NHẤT</strong></p>
              <form>
                <input class="input" type="email" placeholder="Nhập email" />
                <button class="newsletter-btn">
                  <i class="fa fa-envelope"></i> Đăng ký
                </button>
              </form>
              <ul class="newsletter-follow">
                <li>
                  <a href="#"><i class="fa fa-facebook"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-twitter"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-instagram"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-pinterest"></i></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>
    <!-- /NEWSLETTER -->

    <!-- FOOTER -->
    <footer id="footer">
      <!-- top footer -->
      <div class="section">
        <!-- container -->
        <div class="container">
          <!-- row -->
          <div class="row">
            <div class="col-md-3 col-xs-6">
              <div class="footer">
                <h3 class="footer-title">Về chúng tôi</h3>
                <p>Chất lượng làm nên thương hiệu.</p>
                <ul class="footer-links">
                  <li>
                    <a href="#"> <i class="fa fa-phone"></i>0975419019 </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="fa fa-envelope-o"></i>nhom6@email.com
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="fa fa-map-marker"></i>273 An Dương Vương, Phường
                      3, Quận 5
                    </a>
                  </li>
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
          <!-- /row -->
        </div>
        <!-- /container -->
      </div>
      <!-- /top footer -->
      <!-- bottom footer -->
      <div id="bottom-footer" class="section">
        <div class="container">
          <!-- row -->
          <div class="row">
            <div class="col-md-12 text-center">
              <ul class="footer-payments">
                <li>
                  <a href="#"><i class="fa fa-cc-visa"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-credit-card"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-cc-paypal"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-cc-mastercard"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-cc-discover"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-cc-amex"></i></a>
                </li>
              </ul>
              <span class="copyright">
                <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                Copyright ©
                <script>
                  document.write(new Date().getFullYear());
                </script>
                Bản quyền thuộc về Electro.
                <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
              </span>
            </div>
          </div>
          <!-- /row -->
        </div>
        <!-- /container -->
      </div>
      <!-- /bottom footer -->
    </footer>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/jquery.zoom.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>