<?php
session_start();
include 'connect.php';
include 'information.php';
include 'add-to-cart.php';

$product_id = $_GET['id'] ?? 0;
$product = null;
$reviews = [];
$attributes = [];

if ($product_id) {
    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM product WHERE productId = ? AND isActive = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("Sản phẩm không tồn tại!");
    }

    // Lấy đánh giá
    $stmt_reviews = $conn->prepare("SELECT * FROM reviews WHERE productId = ?");
    $stmt_reviews->bind_param("i", $product_id);
    $stmt_reviews->execute();
    $result_reviews = $stmt_reviews->get_result();
    while ($row = $result_reviews->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt_reviews->close();

    // Lấy thuộc tính
    $stmt_attributes = $conn->prepare("
        SELECT a.name, pa.value 
        FROM product_attributes pa 
        JOIN attributes a ON pa.attributeId = a.attributeId 
        WHERE pa.productId = ?
    ");
    $stmt_attributes->bind_param("i", $product_id);
    $stmt_attributes->execute();
    $result_attributes = $stmt_attributes->get_result();
    while ($row = $result_attributes->fetch_assoc()) {
        $attributes[] = $row;
    }
    $stmt_attributes->close();

    // Cập nhật average_rating và reviews_count (nếu cần)
    if (!empty($reviews)) {
        $total_rating = array_sum(array_column($reviews, 'rating'));
        $reviews_count = count($reviews);
        $average_rating = $total_rating / $reviews_count;

        $stmt_update = $conn->prepare("UPDATE product SET average_rating = ?, reviews_count = ? WHERE productId = ?");
        $stmt_update->bind_param("dii", $average_rating, $reviews_count, $product_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
}
?>
  <!DOCTYPE html>
  <html lang="vi">
    <head>
      <meta charset="utf-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

      <title>Electro - Phụ kiện</title>

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

      <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
    </head>

    <body>
      <div class="alert alert-show announce" role="alert"></div>
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
                      <li class="active"><a href="./index.php">Trang chủ</a></li>
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
            <!-- Product main img -->
            <div class="col-md-6">
              <div id="product-main-img">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="product" />
              </div>
            </div>
            <!-- /Product main img -->

            <!-- Product details -->
            <div class="col-md-6">
              <div class="product-details">
                <h2 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
                <div>
                  <div class="product-rating">
                    <?php
                    $rating = $product['average_rating'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $product['average_rating']) {
                            echo "<i class='fa fa-star'></i>";
                        } else {
                            echo "<i class='fa fa-star-o'></i>";
                        }
                    }
                    ?>
                  </div>
                  <a class="review-link"><?php echo htmlspecialchars($product['reviews_count']); ?> đánh giá </a>
                </div>
                <div>
                  <h3 class="product-price">
                    <?php if ($product['discountPercent'] > 0) : ?>
                      <del class="product-old-price"><?php echo number_format($product['old_price'], 0, ',', '.'); ?> VND</del>
                    <?php endif; ?>  
                  </h3>
                  <br/>
                  <h3 class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</h3>
                  <span class="product-available"><?php echo htmlspecialchars($product['availability']); ?></span>
                </div>
                <div class="justified-text">
                  <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                <br/>
                <div class="add-to-cart">
                  <div class="qty-label">
                    <span class="qty-text">Số lượng</span>
                    <div class="text-center qty-buttons">
                      <button class="btn btn-secondary" onClick="updateQuantity(-1)">-</button>
                      <span id="quantity">1</span>
                      <button class="btn btn-secondary"  onClick="updateQuantity(1)">+</button>
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
                <ul class="product-links">
                  <li>Danh mục:</li>
                  <li><a href="./store-accessories.php?category=<?php echo $product['categoryId']; ?>">Phụ kiện</a></li>
                </ul>
                <ul class="product-links">
                  <li>Share:</li>
                  <li>
                    <a href="#"><i class="fa fa-facebook"></i></a>
                  </li>
                  <li>
                    <a href="#"><i class="fa fa-twitter"></i></a>
                  </li>
                  <li>
                    <a href="#"><i class="fa fa-google-plus"></i></a>
                  </li>
                  <li>
                    <a href="#"><i class="fa fa-envelope"></i></a>
                  </li>
                </ul>
              </div>
            </div>
            <!-- /Product details -->

            <!-- Product tab -->
            <div class="col-md-12">
              <div id="product-tab">
                <ul class="tab-nav">
                  <li class="active"><a data-toggle="tab" href="#tab1">Thông số kĩ thuật</a></li>
                  <li><a data-toggle="tab" href="#tab2">Nhận xét</a></li>
                </ul>
                <!-- /product tab nav -->

                <!-- product tab content -->
                <div class="tab-content">

                  <div id="tab1" class="tab-pane fade in active">
                    <div class="row">
                      <div class="col-md-12">
                        <table class="table">
                          <?php foreach ($attributes as $attribute) : ?>
                            <tr>
                              <td><?php echo htmlspecialchars($attribute['name']); ?>:</td>
                              <td><?php echo htmlspecialchars($attribute['value']); ?>:</td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                      </div>
                    </div>
                  </div>
                  <!-- /tab1  -->

                  <!-- tab2  -->
                  <div id="tab2" class="tab-pane fade in">
                    <div class="row">
                      <!-- Rating -->
                      <div class="col-md-12">
                        <div id="reviews">
                          <?php foreach ($reviews as $review) : ?>
                            <li>
                              <div class="review-heading">
                                <h5 class="name">Người dùng</h5>
                                <p class="date"><?php echo htmlspecialchars($review['created_at']); ?></p>
                                <div class="review-rating">
                                  <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <i class="fa fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                  <? endfor; ?>    
                                </div>
                              </div>
                              <div class="review-body">
                                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                              </div>
                            </li>
                            <?php endforeach; ?>
                                  </ul>
                                  </div>
                                  </div>
                                  </div>
                                  </div>
                                  </div>
                                  </div>
                      <!-- /Rating -->

                      

      <!-- Section -->
      <div class="section">
        <!-- container -->
        <div class="container">
          <!-- row -->
          <div class="row">
            <div class="col-md-12">
              <div class="section-title text-center">
                <h3 class="title">Sản phẩm liên quan</h3>
              </div>
            </div>
            <div class="col-md-12">
              <div class="row">
                <div class="products-tabs">
                  <!-- tab -->
                  <div id="tab1" class="tab-pane active">
                    <div class="products-slick" data-nav="#slick-nav-1">
                      <!-- product -->
                      <div class="product" style="margin-bottom: 50px">
                        <div class="product-img">
                          <img src="./img/sanphammoi_samsung-z-lip5.png" alt="" />
                          <div class="product-label">
                            <span class="sale">-45%</span>
                            <span class="new">MỚI</span>
                          </div>
                        </div>
                        <div class="product-body">
                          <h3 class="product-name">
                            <a href="./detail-product-smartphone.html"
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
                            <a href="./detail-product-laptop.html"
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
                            <a href="./detail-product-accessories.html"
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
                      <div class="product">
                        <div class="product-img">
                          <img src="./img/sanphammoi_canon.png" alt="" />
                          <div class="product-label">
                            <span class="sale">-29%</span>
                            <span class="new">MỚI</span>
                          </div>
                        </div>
                        <div class="product-body">
                          <h3 class="product-name">
                            <a href="./detail-product-camera.html"
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
            <!-- product -->
          </div>
          <!-- /row -->
        </div>
        <!-- /container -->
      </div>
      <!-- /Section -->

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
                    <li><a href="./store-latop.html">Máy tính</a></li>
                    <li><a href="./store-smartphone.html">Điện thoại</a></li>
                    <li><a href="./store-camera.html">Máy ảnh</a></li>
                    <li><a href="./store-accessories.html">Phụ kiện</a></li>
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
                    <li><a href="./account-information.html">Tài khoản</a></li>
                    <li><a href="./shopping-cart.html">Giỏ hàng</a></li>
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
      <script src="js/jquery.zoom.min.js"></script>
      <script src="js/main.js"></script>
    </body>
  </html>
