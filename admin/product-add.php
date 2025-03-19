<?php
require "auth.php";
require "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Lấy dữ liệu từ form
  $name = $_POST["name"];
  $categoryId = $_POST["category"];
  $price = $_POST["price"];
  $stock = $_POST["stock"];
  $discount = $_POST["discount"];
  $description = $_POST["description"];

  // Xử lý upload ảnh
  $imagePath = "";
  if (!empty($_FILES["img-product"]["name"])) {
      $targetDir = "../img/"; // Truy cập thư mục img từ admin
      $imageLocation = $targetDir . basename($_FILES["img-product"]["name"]);
      $imagePath = "img/" . basename($_FILES["img-product"]["name"]);

      if (!move_uploaded_file($_FILES["img-product"]["tmp_name"], $imageLocation)) {
          die("Lỗi khi upload ảnh.");
      }
  }

  // **Bắt đầu transaction**
  $conn->begin_transaction();

  try {
      // Thêm sản phẩm
      $sql = "INSERT INTO Product (name, categoryId, image, price, stock, discountPercent, description)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sisdids", $name, $categoryId, $imagePath, $price, $stock, $discount, $description);
      $stmt->execute();
      $productId = $stmt->insert_id;
      $stmt->close();

      // Lưu thuộc tính sản phẩm (nếu có)
      if (!empty($_POST["attributeId"]) && !empty($_POST["values"])) {
          $sql_attr = "INSERT INTO AttributeValue (productId, attributeId, value) VALUES (?, ?, ?)";
          $stmt_attr = $conn->prepare($sql_attr);

          foreach ($_POST["attributeId"] as $key => $attributeId) {
              $value = $_POST["values"][$key];
              $stmt_attr->bind_param("iis", $productId, $attributeId, $value);
              $stmt_attr->execute();
          }

          $stmt_attr->close();
      }

      // **Commit nếu không có lỗi**
      $conn->commit();
      echo "Thêm sản phẩm thành công!";
  } catch (Exception $e) {
      // **Rollback nếu có lỗi**
      $conn->rollback();
      echo "Lỗi: " . $e->getMessage();
  }
  header("Location: product-detail.php?productId=" . $productId);
  // echo "<script>alert('Thêm sản phẩm thành công!');</script>";
}


// Lấy danh mục sản phẩm
$sql_categories = "SELECT categoryId, name FROM Category";
$result_categories = $conn->query($sql_categories);
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
      <h2 class="mb-3">Thêm sản phẩm mới</h2>

      <form action="./product-add.php" id="form-add" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="name">Tên sản phẩm</label>
          <input
            class="form-control"
            type="text"
            name="name"
            id="name"
            required
          />
        </div>

        <div class="row">
          <div class="col-6">
            <label for="category">Danh mục sản phẩm </label>
            <select class="custom-select" name="category" id="category">
              <option selected disabled>--- Chọn danh mục ---</option>
              <?php 
                if ($result_categories->num_rows > 0) {
                    while ($row = $result_categories->fetch_assoc()) {
                      echo "<option value='{$row['categoryId']}'>{$row['name']}</option>";
                    }
                }
              ?>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-6">
          <label for="img-input">Hình ảnh sản phẩm</label>
            <div class="custom-file">
              <input
                type="file"
                name="img-product"
                class="custom-file-input"
                id="img-input"
                aria-describedby="inputGroupFileAddon01"
              />
              <label class="custom-file-label" for="img-product">
                Chọn hình ảnh từ máy
              </label>
            </div>
            <div class="inner-img">
              <img src="../<?= $product["image"] ?>" alt="sản phẩm" srcset="" />
              <span class="cancel">x</span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <label for="price">Giá</label>
            <div class="input-group">
              <input
                class="form-control"
                type="number"
                name="price"
                id="price"
                min="0"
              />
              <div class="input-group-append">
                <span class="input-group-text">VND</span>
              </div>
            </div>
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
                require
              />
            </div>
          </div>
          <div class="col-4">
            <div class="form-group">
              <label for="discount"> Giảm giá</label>
              <div class="input-group">
                <input
                  class="form-control"
                  type="number"
                  name="discount"
                  step="0.01"
                  id="discount"
                  min="0"
                  require
                />
                <div class="input-group-append">
                  <span class="input-group-text">%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <h4>Thông số kĩ thuật</h4>
        <i class="text-info"
          >Lưu ý : Chọn danh mục mà sản phẩm đó thuộc về trước!</i
        >
        <div id="specifications"></div>
        <div class="row">
          <div class="col-12">
            <label for="description">Mô tả sản phẩm</label>
            <textarea
              name="description"
              class="description"
              id="description"
              rows="10"
            ></textarea>
          </div>
        </div>
        <button class="btn btn-success" type="submit">Xác nhận thêm</button>
      </form>
    </div>
  </body>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../js/handleFileImage.js"></script>
  <script src="../js/handleCategories.js"></script>
</html>
