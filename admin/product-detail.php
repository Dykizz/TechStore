<?php
require "auth.php"; 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techstore";

// Kết nối MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy productId từ query string
$productId = isset($_GET['productId']) ? (int)$_GET['productId'] : 0;

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
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trang Admin</title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
      integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
      crossorigin="anonymous"
    />
    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" href="style.css" />
  </head>

  <body>
    <header>
      <div class="inner-logo">
        <a href="./index.php">
          <img src="../img/logo.png" alt="Logo" srcset="" />
        </a>
      </div>
      <div class="inner-user">
        <div class="notification">
          <i class="fa-regular fa-bell"></i>
          <span>Thông báo</span>
        </div>
        <div class="infor-user">
          <div class="avatar">
            <i class="fa-solid fa-user"></i>
          </div>
          <span><?= $_SESSION["admin"] ?></span>
        </div>
        <div class="btn-logout">
    <a href="logout.php">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Đăng xuất</span>
    </a>
</div>
      </div>
    </header>

    <ul class="sider">
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-gauge-high"></i>
        </div>
        <a href="./index.php">Tổng quan</a>
      </li>
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-people-group"></i>
        </div>
        <a href="./manage-client.php">Quản lý người dùng</a>
      </li>
      <li class="active">
        <div class="inner-icon">
          <i class="fa-brands fa-product-hunt"></i>
        </div>
        <a href="./manage-product.php">Quản lý sản phẩm</a>
      </li>
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <a href="./manage-order.php">Quản lý đơn hàng</a>
      </li>
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
      
    </ul>
    <div class="content">
      <h2 class="mb-3">Chi tiết sản phẩm</h2>
      <?php if (!empty($product)): ?>
        <div class="card">
          <div class="card-header bg-info text-white">
            <h4 class="d-inline-block"><?= $product["productName"] ?></h4>
            <span class="badge <?= $product['isActive'] == 1 ? 'badge-success' : 'badge-danger' ?> ml-2">
              <?= $product['isActive'] == 1 ? 'Hoạt động' : 'Dừng hoạt động' ?>
          </span>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-6">
                <div>Hình ảnh sản phẩm</div>
                <div class="inner-img">
                  <img src="../<?= $product["image"] ?>" alt="sản phẩm" srcset="" />
                </div>
              </div>
              <div class="col-6">
                <div>
                  <label for=""> Đánh giá người dùng : </label>
                  <div class="product-rating">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                  </div>
                  <div class="mb-0">
                    <div class="d-inline-block">Số lượng người đánh giá :</div>
                    <span>
                      <strong>324 người</strong>
                    </span>
                  </div>
                  <div>
                    <div class="d-inline-block">Thuộc danh mục :</div>
                    <span>
                      <strong><?= $product["categoryName"] ?></strong>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-4">
                <label for="price">Giá (VND)</label>

                <input
                  class="form-control"
                  type="number"
                  name="price"
                  id="price"
                  min="0"
                  value="<?= $product["price"] ?>"
                  disabled
                />
              </div>
              <div class="col-4">
                <div class="form-group">
                  <label for="stock">Số lượng</label>
                  <input
                    class="form-control"
                    type="number"
                    name="stock"
                    id="stock"
                    min="0"
                    value="<?= $product["stock"] ?>"
                    disabled
                  />
                </div>
              </div>
              <div class="col-4">
                <div class="form-group">
                  <label for="discount"> Giảm giá(%)</label>
                  <input
                    class="form-control"
                    type="number"
                    name="discount"
                    step="0.01"
                    id="discount"
                    min="0"
                    value="<?= $product["discountPercent"] ?>"
                    disabled
                  />
                </div>
              </div>
            </div>
            <h4>Thông số kĩ thuật</h4>
            <div id="specifications">
              <div class="card mb-3">
                <div class="card-header">Thông số kĩ thuật của điện thoại</div>
                <div class="card-body">
                <?php if (!empty($product["attributes"])): ?>
                    <?php foreach ($product["attributes"] as $attribute): ?>
                      <label for="color"><?= $attribute["name"] ?></label>
                      <input
                      class="form-control mb-2"
                      name="color"
                      id="color"
                      type="text"
                      value="<?= $attribute["value"] ?>"
                      disabled
                    />
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span>Không rõ thông tin</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <label for="description">Mô tả sản phẩm</label>
                <textarea
                  name="description"
                  class="description"
                  id="description"
                  rows="10"
                  disabled
                ><?= $product["description"] ?>
                </textarea>
              </div>
            </div>
            <a class="btn btn-warning" href="./product-edit.php?productId=<?= $product['productId'] ?>">
                Chỉnh sửa thông tin
            </a>
          </div>
        </div>
      <?php else: ?>
          <p class="text-danger">Sản phẩm không tồn tại.</p>
      <?php endif; ?>
    </div>
  </body>
</html>
