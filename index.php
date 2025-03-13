<?php
include 'information.php';
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trang chủ</title>
    <!--Favicon-->
    <link rel="shortcut icon" href="./img/logo.png" type="image/x-icon" />
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
    <!-- Custom stlylesheet -->
    <link type="text/css" rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <div class="alert alert-show announce" role="alert"></div>
    <!-- HEADER -->
    <header>
      <!-- TOP HEADER -->
      <div id="top-header">
        <div class="container">
          <ul class="header-links pull-left">
            <li>
              <a href="#"><i class="fa fa-phone"></i> Hotline: <strong>+84 975 419 019</strong>
            </li>
            <li>
              <a href="#"><i class="fa fa-envelope-o"></i> nhom6@email.com </a>
            </li>
            <li>
              <a href="#"
                ><i class="fa fa-map-marker"></i> 273 An Dương Vương, Phường 3,
                Quận 5
              </a>
            </li>
          </ul>
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
            <li class="active"><a href="./index.php">Trang chủ</a></li>
            <li><a href="./products.php?category=1">Máy tính</a></li>
            <li><a href="./products.php?category=2">Điện thoại</a></li>
            <li><a href="./products.php?category=3">Máy ảnh</a></li>
            <li><a href="./products.php?category=4">Phụ kiện</a></li>
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
          <!-- shop -->
          <div class="col-md-3 col-xs-6">
            <div class="shop">
              <div class="shop-img">
                <img src="./img/shop01.png" alt="" />
              </div>
              <div class="shop-body">
                <h3>Máy tính</h3>
                <a href="./products.php?category=1" class="cta-btn">
                  Mua ngay <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
          <!-- /shop -->
          <!-- shop -->
          <div class="col-md-3 col-xs-6">
            <div class="shop">
              <div class="shop-img">
                <img src="./img/shop02.png" alt="" />
              </div>
              <div class="shop-body">
                <h3>Điện thoại</h3>
                <a href="./products.php?category=2" class="cta-btn">
                  Mua ngay <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
          <!-- /shop -->
          <!-- shop -->
          <div class="col-md-3 col-xs-6">
            <div class="shop">
              <div class="shop-img">
                <img src="./img/shop03.png" alt="" />
              </div>
              <div class="shop-body">
                <h3>Máy ảnh</h3>
                <a href="./products.php?category=3" class="cta-btn">
                  Mua ngay <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
          <!-- /shop -->
          <!-- shop -->
          <div class="col-md-3 col-xs-6">
            <div class="shop">
              <div class="shop-img">
                <img src="./img/shop04.png" alt="" />
              </div>
              <div class="shop-body">
                <h3>Phụ kiện</h3>
                <a href="./products.php?category=4" class="cta-btn">
                  Mua ngay <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
          <!-- /shop -->
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>
    <!-- /SECTION -->
    <!-- SECTION -->
    <div class="section">
      <!-- container -->
      <div class="container">
        <!-- row -->
        <div class="row">
          <!-- section title -->
          <div class="col-md-12">
            <div class="section-title">
              <h3 class="title">Sản phẩm mới</h3>
            </div>
          </div>
          <!-- /section title -->
          <!-- Products tab & slick -->
          <div class="col-md-12">
            <div class="row">
              <div class="products-tabs">
                <!-- tab -->
                <div id="tab1" class="tab-pane active">
                  <div class="products-slick" data-nav="#slick-nav-1">
                    <!-- product -->
                    <div class="product">
                      <div class="product-img">
                        <img src="./img/sanphammoi_samsung-z-lip5.png" alt="" />
                        <div class="product-label">
                          <span class="sale">-45%</span>
                          <span class="new">MỚI</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-smartphone.php"
                            >Samsung Galaxy Z Flip5 512GB</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >29.990.000 VND</del
                          >
                          <span class="new-price-index">16.490.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                    <!-- product -->
                    <div class="product">
                      <div class="product-img">
                        <img src="./img/Sanphamoi_laptopASUS.png" alt="" />
                        <div class="product-label">
                          <span class="sale">-45%</span>
                          <span class="new">MỚI</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-laptop.php"
                            >Laptop ASUS Gaming VivoBook K3605ZC-RP564W</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >25.290.000 VND</del
                          >
                          <span class="new-price-index">19.290.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                    <!-- product -->
                    <div class="product">
                      <div class="product-img">
                        <img src="./img/sanphammoi_banphim.png" alt="" />
                        <div class="product-label">
                          <span class="sale">-40%</span>
                          <span class="new">MỚI</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-accessories.php"
                            >Bàn phím cơ E-DRA EK375 Alpha Đen Đỏ</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index">729.000 VND</del>
                          <span class="new-price-index">440.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                    <!-- product -->
                    <div class="product" style="margin-bottom: 50px">
                      <div class="product-img">
                        <img src="./img/sanphammoi_canon.png" alt="" />
                        <div class="product-label">
                          <span class="sale">-29%</span>
                          <span class="new">MỚI</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-camera.php"
                            >Canon EOS R8, Mới 100% (Chính hãng Canon)</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >39.990.000 VND</del
                          >
                          <span class="new-price-index">28.490.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                  </div>
                  <div id="slick-nav-1" class="products-slick-nav"></div>
                </div>
                <!-- /tab -->
              </div>
            </div>
          </div>
          <!-- Products tab & slick -->
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>
    <!-- /SECTION -->
    <!-- HOT DEAL SECTION -->
    <div id="hot-deal" class="section">
      <!-- container -->
      <div class="container">
        <!-- row -->
        <div class="row">
          <div class="col-md-12">
            <div class="hot-deal">
              <ul class="hot-deal-countdown">
                <li>
                  <div>
                    <h3>02</h3>
                    <span>Ngày</span>
                  </div>
                </li>
                <li>
                  <div>
                    <h3>10</h3>
                    <span>Giờ</span>
                  </div>
                </li>
                <li>
                  <div>
                    <h3>34</h3>
                    <span>Phút</span>
                  </div>
                </li>
                <li>
                  <div>
                    <h3>60</h3>
                    <span>Giây</span>
                  </div>
                </li>
              </ul>
              <h2 class="text-uppercase">Ưu đãi tuần này!</h2>
              <p>Giảm tới 50%</p>
              <a class="primary-btn cta-btn" href="#">Mua ngay</a>
            </div>
          </div>
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>
    <!-- /HOT DEAL SECTION -->
    <!-- SECTION -->
    <div class="section">
      <!-- container -->
      <div class="container">
        <!-- row -->
        <div class="row">
          <!-- section title -->
          <div class="col-md-12">
            <div class="section-title">
              <h3 class="title">Sản phẩm bán chạy</h3>
            </div>
          </div>
          <!-- /section title -->
          <!-- Products tab & slick -->
          <div class="col-md-12">
            <div class="row">
              <div class="products-tabs">
                <!-- tab -->
                <div id="tab2" class="tab-pane fade in active">
                  <div class="products-slick" data-nav="#slick-nav-2">
                    <!-- product -->
                    <div class="product">
                      <div class="product-img">
                        <img src="./img/iphone-15-pro-max_3.png" alt="" />
                        <div class="product-label">
                          <span class="sale">-16%</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-smartphone.php"
                            >iPhone 15 Pro Max 256GB | Chính hãng VN/A</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >34.990.000 VND</del
                          >
                          <span class="new-price-index">29.490.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                    <!-- product -->
                    <div class="product" style="margin-bottom: 50px">
                      <div class="product-img">
                        <img src="./img/sanphambanchay_asus.png" alt="" />
                        <div class="product-label">
                          <span class="sale">4%</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-laptop.php"
                            >Laptop ASUS TUF Gaming A14 FA401WV-RG061WS</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >46.990.000 VND</del
                          >
                          <span class="new-price-index">44.990.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                    <!-- product -->
                    <div class="product">
                      <div class="product-img">
                        <img
                          src="./img/apple-airpods-pro-2-usb-c_1_.png"
                          alt=""
                        />
                        <div class="product-label">
                          <span class="sale">-6%</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-accessories.php"
                            >Tai nghe Bluetooth Apple AirPods Pro 2 2023 USB-C
                          </a>
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >6.190.000 VND</del
                          >
                          <span class="new-price-index">5.790.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                    <!-- product -->
                    <div class="product">
                      <div class="product-img">
                        <img
                          src="./img/Máy ảnh Canon EOS R10 kit RF-S18-45mm F4.5-6.3 IS STM.png"
                          alt=""
                        />
                        <div class="product-label">
                          <span class="sale">-23%</span>
                        </div>
                      </div>
                      <div class="product-body">
                        <h3 class="product-name">
                          <a href="./detail-product-camera.php"
                            >Máy ảnh Canon EOS R10 kit RF-S18-45mm F4.5-6.3 IS
                            STM</a
                          >
                        </h3>
                        <h4 class="product-price-index">
                          <del class="product-old-price-index"
                            >28.330.000 VND</del
                          >
                          <span class="new-price-index">21.900.000 VND</span>
                        </h4>
                        <div class="product-rating">
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>
                      <div class="add-to-cart">
                        <button
                          class="add-to-cart-btn btn-announce"
                          type-announce="success"
                          message="Thêm sản phẩm vào giỏ hàng thành công!"
                        >
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                      </div>
                    </div>
                    <!-- /product -->
                  </div>
                  <div id="slick-nav-2" class="products-slick-nav"></div>
                </div>
                <!-- /tab -->
              </div>
            </div>
          </div>
          <!-- /Products tab & slick -->
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>
    <!-- /SECTION -->
    <!-- SECTION -->

    <!-- /SECTION -->
    <!-- store bottom filter -->
    <div class="store-filter clearfix">
      <ul class="store-pagination">
        <li class="active">1</li>
        <li><a href="./index.php">2</a></li>
        <li><a href="./index.php">3</a></li>
        <li><a href="./index.php">4</a></li>
        <li>
          <a href="./index.php"><i class="fa fa-angle-right"></i></a>
        </li>
      </ul>
    </div>
    <!-- store bottom filter -->
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
                    <a href="#"> <i class="fa fa-phone"></i><strong>+84 975 419 019</strong>
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
                Copyright &copy;
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
    <script src="js/helper.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/nouislider.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>
