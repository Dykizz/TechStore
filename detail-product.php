<?php
include 'connect.php';
include 'information.php';

// Lấy productId từ query string
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId > 0) {
  $sql = "SELECT 
              p.productId, p.name AS productName, p.image, p.description, p.isActive,
              p.stock, p.price, p.discountPercent, c.categoryId, c.name AS categoryName,
              a.attributeId, a.name AS attributeName, av.value AS attributeValue
          FROM Product p
          LEFT JOIN Category c ON p.categoryId = c.categoryId
          LEFT JOIN AttributeValue av ON p.productId = av.productId
          LEFT JOIN Attribute a ON av.attributeId = a.attributeId
          WHERE p.productId = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  $result = $stmt->get_result();

  $product = [];
  while ($row = $result->fetch_assoc()) {
      if (empty($product)) {
          $product = [
              "categoryId" => $row["categoryId"],
              "productId" => $row["productId"],
              "isActive" => $row["isActive"],
              "productName" => $row["productName"],
              "image" => $row["image"],
              "description" => $row["description"],
              "stock" => $row["stock"],
              "price" => $row["price"],
              "discountPercent" => $row["discountPercent"],
              "attributes" => [],
              "categoryName" => $row["categoryName"]
          ];
      }
      if ($row["attributeId"]) {
          $product["attributes"][] = [
              "attributeId" => $row["attributeId"],  // Lấy thêm ID
              "name" => $row["attributeName"],
              "value" => $row["attributeValue"]
          ];
      }
  }
  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electro - HTML Ecommerce Template</title>
    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700"
      rel="stylesheet"
    />
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="css/slick.css" />
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css" />
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <div class="alert alert-show announce" role="alert"></div>
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
                    <li class="<?php echo $product["categoryId"] == 1 ? 'active' : ''; ?>"><a href="./products.php?category=1">Máy tính</a></li>
                    <li class="<?php echo $product["categoryId"] == 2 ? 'active' : ''; ?>"><a href="./products.php?category=2">Điện thoại</a></li>
                    <li class="<?php echo $product["categoryId"] == 3 ? 'active' : ''; ?>"><a href="./products.php?category=3">Máy ảnh</a></li>
                    <li class="<?php echo $product["categoryId"] == 4 ? 'active' : ''; ?>"><a href="./products.php?category=4">Phụ kiện</a></li>
                </ul>
            </div>
        </div>
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
              <img src="./<?= $product['image']?>" alt="product" />
            </div>
          </div>
          <!-- /Product main img -->

          <!-- Product details -->
          <div class="col-md-6">
            <div class="product-details">
              <h2 class="product-name">
                <?= $product["productName"] ?>
              </h2>
              <div>
                <div class="product-rating">
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star-o"></i>
                </div>
                <a class="review-link">200 đánh giá </a>
              </div>

              <div>
                <h3 class="product-price">
                  <del class="product-old-price"><?=number_format($product['price'],0,',','.') ?> VND</del>
                </h3>
                <br />
                <h3 class="product-price"><?=number_format($product['price']*((100 - $product['discountPercent'])/ 100),0,',','.') ?> VND</h3>
                <span class="product-available">Còn <?= $product['stock'] ?> sản phẩm</span>
              </div>
              <div class="justified-text">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
              </div>
              <br />
              <div class="add-to-cart">
                <div class="qty-label">
                  <span class="qty-text">Số lượng</span>
                  <div class="text-center qty-buttons">
                    <button class="btn btn-secondary">-</button>
                    <span>1</span>
                    <button class="btn btn-secondary">+</button>
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
                <li><a href='./products.php?category=<?= $product["categoryId"]?>'><?= $product["categoryName"] ?></a></li>
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
              <!-- product tab nav -->
              <ul class="tab-nav">
                <li class="active">
                  <a data-toggle="tab" href="#tab1">Thông số kĩ thuật</a>
                </li>
                <li><a data-toggle="tab" href="#tab2">Nhận xét</a></li>
              </ul>
              <!-- /product tab nav -->

              <!-- product tab content -->
              <div class="tab-content">
                <!-- tab1  -->
                <div id="tab1" class="tab-pane fade in active">
                  <div class="row">
                    <div class="col-md-12">
                      <table class="table">
                        <tbody>
                          <?php if (!empty($product["attributes"])): ?>
                            <?php foreach ($product['attributes'] as $attribute): ?>
                              <tr>
                                <td><?= htmlspecialchars($attribute['name']) ?></td>
                                <td><?= htmlspecialchars($attribute['value']) ?></td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <span>Không rõ thông tin</span>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- /tab1  -->
                <!-- tab2  -->
                <div id="tab2" class="tab-pane fade in">
                  <div class="row">
                    <!-- Rating -->
                    <div class="col-md-3">
                      <div id="rating">
                        <div class="rating-avg">
                          <span>4.5</span>
                          <div class="rating-stars">
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-o"></i>
                          </div>
                        </div>
                        <ul class="rating">
                          <li>
                            <div class="rating-stars">
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                            </div>
                            <div class="rating-progress">
                              <div style="width: 80%"></div>
                            </div>
                            <span class="sum">3</span>
                          </li>
                          <li>
                            <div class="rating-stars">
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star-o"></i>
                            </div>
                            <div class="rating-progress">
                              <div style="width: 60%"></div>
                            </div>
                            <span class="sum">2</span>
                          </li>
                          <li>
                            <div class="rating-stars">
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star-o"></i>
                              <i class="fa fa-star-o"></i>
                            </div>
                            <div class="rating-progress">
                              <div></div>
                            </div>
                            <span class="sum">0</span>
                          </li>
                          <li>
                            <div class="rating-stars">
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star-o"></i>
                              <i class="fa fa-star-o"></i>
                              <i class="fa fa-star-o"></i>
                            </div>
                            <div class="rating-progress">
                              <div></div>
                            </div>
                            <span class="sum">0</span>
                          </li>
                          <li>
                            <div class="rating-stars">
                              <i class="fa fa-star"></i>
                              <i class="fa fa-star-o"></i>
                              <i class="fa fa-star-o"></i>
                              <i class="fa fa-star-o"></i>
                              <i class="fa fa-star-o"></i>
                            </div>
                            <div class="rating-progress">
                              <div></div>
                            </div>
                            <span class="sum">0</span>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <!-- /Rating -->

                    <!-- Reviews -->
                    <div class="col-md-6">
                      <div id="reviews">
                        <ul class="reviews">
                          <li>
                            <div class="review-heading">
                              <h5 class="name">Đoàn Ngọc Nhi</h5>
                              <p class="date">27 DEC 2018, 8:0 PM</p>
                              <div class="review-rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                              </div>
                            </div>
                            <div class="review-body">
                              <p>
                                Mình thật sự ấn tượng với thiết kế gập của
                                Galaxy Z Flip5. Khi gập lại thì nhỏ gọn, dễ bỏ
                                túi, còn khi mở ra thì màn hình lớn, tiện dụng.
                                Rất hợp với người thích điện thoại nhỏ gọn nhưng
                                vẫn muốn màn hình rộng khi dùng.
                              </p>
                            </div>
                          </li>
                          <li>
                            <div class="review-heading">
                              <h5 class="name">Nguyễn Văn Ngọc</h5>
                              <p class="date">27 DEC 2018, 8:0 PM</p>
                              <div class="review-rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                              </div>
                            </div>
                            <div class="review-body">
                              <p>
                                Cảm giác mở và gập máy rất thú vị, giống như
                                trải nghiệm của các điện thoại nắp gập ngày xưa
                                nhưng với công nghệ hiện đại. Mình cảm thấy
                                thích thú mỗi lần gập máy lại.
                              </p>
                            </div>
                          </li>
                          <li>
                            <div class="review-heading">
                              <h5 class="name">Trần Văn Nhân</h5>
                              <p class="date">27 DEC 2018, 8:0 PM</p>
                              <div class="review-rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star-o empty"></i>
                              </div>
                            </div>
                            <div class="review-body">
                              <p>
                                Với thiết kế gập, mình vẫn lo lắng về độ bền lâu
                                dài của màn hình và bản lề. Hy vọng Samsung đã
                                cải thiện hơn so với các phiên bản trước.
                              </p>
                            </div>
                          </li>
                        </ul>
                        <ul class="reviews-pagination">
                          <li class="active">1</li>
                          <li><a href="#">2</a></li>
                          <li><a href="#">3</a></li>
                          <li><a href="#">4</a></li>
                          <li>
                            <a href="#"><i class="fa fa-angle-right"></i></a>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <!-- /Reviews -->

                    <!-- Review Form -->
                    <div class="col-md-3">
                      <div id="review-form">
                        <form class="review-form">
                          <input
                            class="input"
                            type="text"
                            placeholder="Họ và tên"
                          />
                          <input
                            class="input"
                            type="email"
                            placeholder="Email"
                          />
                          <textarea
                            class="input"
                            placeholder="Nhận xét"
                          ></textarea>
                          <div class="input-rating">
                            <span>Đánh giá của bạn: </span>
                            <div class="stars">
                              <input
                                id="star5"
                                name="rating"
                                value="5"
                                type="radio"
                              /><label for="star5"></label>
                              <input
                                id="star4"
                                name="rating"
                                value="4"
                                type="radio"
                              /><label for="star4"></label>
                              <input
                                id="star3"
                                name="rating"
                                value="3"
                                type="radio"
                              /><label for="star3"></label>
                              <input
                                id="star2"
                                name="rating"
                                value="2"
                                type="radio"
                              /><label for="star2"></label>
                              <input
                                id="star1"
                                name="rating"
                                value="1"
                                type="radio"
                              /><label for="star1"></label>
                            </div>
                          </div>
                          <button class="primary-btn">Gửi</button>
                        </form>
                      </div>
                    </div>
                    <!-- /Review Form -->
                  </div>
                </div>
                <!-- /tab3  -->
              </div>
              <!-- /product tab content  -->
            </div>
          </div>
          <!-- /product tab -->
        </div>
        <!-- /row -->
      </div>
      <!-- /container -->
    </div>
    <!-- /SECTION -->

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
