<?php 
require "auth.php";
require "db_connect.php";

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

if ($userId > 0) {
    $sql = "SELECT 
                u.userId, u.name, u.email, u.phoneNumber, u.gender, 
                u.avatar, u.isAdmin, u.status, u.createdAt, u.updatedAt, u.dateOfBirth,
                ua.address AS defaultAddress
            FROM User u
            LEFT JOIN UserAddress ua 
                ON u.userId = ua.userId 
                AND ua.isDefault = TRUE
            WHERE u.userId = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Kiểm tra nếu có kết quả
$user = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
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
      <li class="active">
        <div class="inner-icon">
          <i class="fa-solid fa-people-group"></i>
        </div>
        <a href="./manage-client.php">Quản lý người dùng</a>
      </li>
      <li>
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
    </ul>
    <div class="content">
      <h2 class="mb-4">Quản lý người dùng</h2>

      <h4 class="mb-3">Thông tin người dùng</h4>
      <?php if ($user): ?>
        <div class="frame-user">
          <div class="row">
            <div class="col-lg-3 col-4">
              <div class="avarta-user">
              <img src="<?= !empty($user['avatar']) 
                    ? "../" . htmlspecialchars($user['avatar']) 
                    : ($user['gender'] === 'MALE' 
                        ? '../img/avarta-man.png' 
                        : '../img/avarta-woman.svg') ?>" 
                    alt="Avatar" />
              </div>
            </div>
            <div class="col-lg-9 col-8">
              <div class="sub-infor-user">
                <p>
                  Họ và tên :
                  <strong><?= $user['name']?></strong>
                </p>
                <p>
                  Ngày sinh :
                  <strong><?= isset($user['dateOfBirth']) ? date("d/m/Y", strtotime($user['dateOfBirth'])) : 'Chưa cập nhật' ?></strong>
                </p>
                <p>
                  Số điện thoại :
                  <strong><?=$user['phoneNumber']?></strong>
                </p>
                <p>
                  Giới tính :
                  <strong><?= ($user['gender'] == 'MALE') ? 'Nam' : 'Nữ' ?></strong>
                </p>
                <p>
                  Email :
                  <strong><?=$user['email']?></strong>
                </p>
                <p>
                  Trạng thái :
                  <span class="badge <?= ($user['status'] == 'ACTIVE') ? 'badge-success' : 'badge-danger' ?>">
                    <?= ($user['status'] == 'ACTIVE') ? 'Hoạt động' : 'Dừng hoạt động' ?>
                  </span>
                </p>
              </div>
            </div>
            <div class="col-12 mb-0">
              <div class="form-group">
                <label for="address-default">Địa chỉ giao hàng mặc định : </label>
                <textarea
                  name="address-default"
                  id="address-default"
                  class="form-control"
                  rows="3"
                  readonly
                ><?= $user['defaultAddress'] ?? 'Chưa có địa chỉ' ?></textarea>
              </div>
              <div class="btn btn-warning mb-0">
                <a href="client-edit.php?userId=<?= $userId ?>">Sửa thông tin</a>
              </div>
            </div>
          </div>
        </div>
        <?php else: ?>
            <p class="text-danger">Không tìm thấy người dùng.</p>
        <?php endif; ?>
    </div>
  </body>
  <script
    src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
    integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
    crossorigin="anonymous"
  ></script>
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
    crossorigin="anonymous"
  ></script>
</html>
