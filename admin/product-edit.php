
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $productId = isset($_POST["productId"]) ? (int)$_POST["productId"] : 0;
  $name = $_POST["name"] ?? "";
  $price = isset($_POST["price"]) ? $_POST["price"] : 0;
  $stock = isset($_POST["stock"]) ? (int)$_POST["stock"] : 0;
  $discount = isset($_POST["discountPercent"]) ? (float)$_POST["discountPercent"] : 0;
  $description = $_POST["description"] ?? "";
  $categoryId = isset($_POST["category"]) ? (int)$_POST["category"] : 0;
  $isActive = isset($_POST["isActive"]) ? (int)$_POST["isActive"] : 0;

  // Kiểm tra hình ảnh mới
  $imagePath = ""; // Giữ ảnh cũ mặc định
  if (!empty($_FILES["img-product"]["name"])) {
    $targetDir = "../img/";
    $imageLocation = $targetDir . basename($_FILES["img-product"]["name"]);
    $imagePath = "img/" . basename($_FILES["img-product"]["name"]);

    if (!move_uploaded_file($_FILES["img-product"]["tmp_name"], $imageLocation)) {
        die("Lỗi khi upload ảnh.");
    }

    // Cập nhật cả ảnh
    $sqlUpdate = "UPDATE Product 
                  SET name = ?, price = ?, stock = ?, discountPercent = ?, 
                      description = ?, categoryId = ?, image = ? , isActive = ?
                  WHERE productId = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("siidsisii", $name, $price, $stock, $discount, $description, $categoryId, $imagePath,$isActive, $productId);
  } else {
      // Không cập nhật ảnh
    $sqlUpdate = "UPDATE Product 
                  SET name = ?, price = ?, stock = ?, discountPercent = ?, 
                      description = ?, categoryId = ?, isActive = ?
                  WHERE productId = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("siidsiii", $name, $price, $stock, $discount, $description, $categoryId,$isActive, $productId);
  }
  
    $stmt->execute();
    $stmt->close();
  

  // Xử lý thông số kỹ thuật (attributes)
  // Xử lý cập nhật thuộc tính sản phẩm (AttributeValue)
  $attributeIds = isset($_POST["attributeId"]) ? $_POST["attributeId"] : [];
  $attributeValueIds = isset($_POST["attributeValueId"]) ? $_POST["attributeValueId"] : [];
  $attributeValues = isset($_POST["values"]) ? $_POST["values"] : [];


  foreach ($attributeIds as $index => $attrId) {
    $attrValue = $attributeValues[$index] ?? "";
    $attrValueId = $attributeValueIds[$index] ?? 0;

    if ($attrValueId > 0) {
        // Nếu thuộc tính đã có, cập nhật giá trị
        $sqlUpdateAttr = "UPDATE AttributeValue SET value = ? WHERE attributeValueId = ?";
        $stmtAttr = $conn->prepare($sqlUpdateAttr);
        $stmtAttr->bind_param("si", $attrValue, $attrValueId);
        $stmtAttr->execute();
        $stmtAttr->close();
    }
    else {
        // Nếu thuộc tính chưa có, thêm mới
        if (!empty($attrValue)) {
            $sqlInsertAttr = "INSERT INTO AttributeValue (productId, attributeId, value) VALUES (?, ?, ?)";
            $stmtAttr = $conn->prepare($sqlInsertAttr);
            $stmtAttr->bind_param("iis", $productId, $attrId, $attrValue);
            $stmtAttr->execute();
            $stmtAttr->close();
        }
    }
  }
  header("Location: product-detail.php?productId=" . $productId);
}


if ($_SERVER["REQUEST_METHOD"] === "GET"){

  // Lấy productId từ query string
  $productId = isset($_GET['productId']) ? (int)$_GET['productId'] : 0;

  if ($productId > 0) {
    $sql = "SELECT 
                p.productId, p.name AS productName, p.image, p.description, 
                p.stock, p.price, p.discountPercent, c.categoryId, p.isActive,
                a.attributeId, a.name AS attributeName, av.value AS attributeValue , av.attributeValueId 
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
            ];
        }
        if ($row["attributeId"]) {
            $product["attributes"][] = [
                "attributeId" => $row["attributeId"],  // Lấy thêm ID
                "attributeValueId" => $row["attributeValueId"],
                "name" => $row["attributeName"],
                "value" => $row["attributeValue"]
            ];
        }
    }
    $stmt->close();
  }

  // Lấy danh mục sản phẩm
  $sql_categories = "SELECT categoryId, name FROM Category";
  $result_categories = $conn->query($sql_categories);
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
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <a href="./statistic.php">Thống kê kinh doanh</a>
      </li>
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
    </ul>
    <div class="content">
      <h2 class="mb-3">Chỉnh sửa sản phẩm</h2>

      <form action="./product-edit.php" id="form-edit" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="productId" value="<?= $product["productId"] ?>" />
        <div class="form-group">
          <label for="name">Tên sản phẩm</label>
          <input
            class="form-control"
            type="text"
            name="name"
            id="name"
            value="<?= $product["productName"] ?>"
          />
        </div>

        <div class="row">

          <div class="col-6">
            <label for="category">Danh mục sản phẩm </label>
            <select class="custom-select" name="category" id="category">
              <option disabled>--- Chọn danh mục ---</option>
              <?php 
              if ($result_categories->num_rows > 0) {
                  while ($row = $result_categories->fetch_assoc()) {
                      $selected = ($row['categoryId'] == $product['categoryId']) ? "selected" : "";
                      echo "<option value='{$row['categoryId']}' $selected>{$row['name']}</option>";
                  }
              }
              ?>
            </select>
          </div>
          <div class="col-6">
            <label class="d-block">Trạng thái:</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="isActive" id="active" value="1"
                    <?= isset($product['isActive']) && $product['isActive'] == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="active">Hoạt động</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="isActive" id="inactive" value="0"
                    <?= isset($product['isActive']) && $product['isActive'] == 0 ? 'checked' : '' ?>>
                <label class="form-check-label" for="inactive">Dừng hoạt động</label>
            </div>
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
              <label class="custom-file-label" for="img-input">
                <?= str_replace('img/', '', $product["image"]) ?>
              </label>
            </div>
            <span class="text-info mb-2" >Lưu ý : Nếu không có hình ảnh sẽ vẫn giữ ảnh cũ</span>
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
                value="<?= $product["price"] ?>"
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
                value="<?= $product["stock"] ?>"
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
                  name="discountPercent"
                  step="0.01"
                  id="discount"
                  min="0"
                  value="<?= $product["discountPercent"] ?>"
                />
                <div class="input-group-append">
                  <span class="input-group-text">%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <h4>Thông số kĩ thuật</h4>
        <div id="specifications">
        <div class="card mb-3">
          <div class="card-body">
            <?php if (!empty($product["attributes"])): ?>
              <?php foreach ($product["attributes"] as $attribute): ?>
                <div class="attribute-row">
                  <label><?= $attribute["name"] ?></label>
                  <input type="hidden" name="attributeId[]" value="<?= $attribute["attributeId"] ?>">
                  <input type="hidden" name="attributeValueId[]" value="<?= $attribute["attributeValueId"] ?>">
                  <input class="form-control mb-2" name="values[]" type="text" value="<?= $attribute["value"] ?>">
                </div>
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
            ><?= $product["description"] ?></textarea
            >
          </div>
        </div>

        <button class="btn btn-warning" type="submit">Xác nhận thay đổi</button>
      </form>
    </div>
  </body>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../js/handleFileImage.js"></script>
  <script src="../js/helper.js"></script>
  <script src="../js/handleCategories.js"></script>
</html>
