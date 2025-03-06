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
    <link rel="stylesheet" href="css/font-awesome.min.css" />
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
    <div class="alert alert-show announce" role="alert"></div>
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
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
    </ul>
    <div class="content">
      <h2 class="mb-3">Chỉnh sửa thông tin người dùng</h2>
      <?php if ($user): ?>
        <form action="update-user.php"  id="form-edit" method="post" enctype="multipart/form-data">
          <input hidden type="number"name="userId" value = <?= $userId ?>>
          <div class="form-group">
            <label for="name">Họ và tên</label>
            <input
              class="form-control"
              type="text"
              name="name"
              id="name"
              value= "<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
              required
            />
          </div>

          <div class="row">
            <div class="col-6 col-lg-4">
              <div>Trạng thái</div>
              <label>
                <input type="radio" name="status" value="ACTIVE" <?= $user['status'] === 'ACTIVE' ? 'checked' : '' ?> />
                Hoạt động
              </label>
              <label class="ml-3">
                <input type="radio" name="status" value="INACTIVE" <?= $user['status'] === 'INACTIVE' ? 'checked' : '' ?> />
                Dừng hoạt động
              </label>
            </div>
            <div class="col-6 col-lg-4">
              <div>Giới tính</div>
              <label>
                <input type="radio" name="gender" value="MALE" <?= $user['gender'] === 'MALE' ? 'checked' : '' ?> />
                Nam
              </label>
              <label class="ml-3">
                <input type="radio" name="gender" value="FEMALE" <?= $user['gender'] === 'FEMALE' ? 'checked' : '' ?> />
                Nữ
              </label>
            </div>
          </div>

          <div class="row">
            <div class="col-4">
              <label for="email">Email </label>
              <input
                type="email"
                name="email"
                id="email"
                value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                class="form-control"
                required
              />
            </div>
            <div class="col-4">
              <label for="dateOfBirth">Ngày sinh </label>
              <input
                type="date"
                name="dateOfBirth"
                id="dateOfBirth"
                class="form-control"
                value="<?= isset($user['dateOfBirth']) ? htmlspecialchars($user['dateOfBirth']) : '' ?>"
              />
            </div>
            <div class="col-4">
              <label for="phone"> Số điện thoại </label>
              <input
                type="text"
                id="phone"
                name="phone"
                value="<?= htmlspecialchars($user['phoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                class="form-control"
                required
              />
            </div>
          </div>

          <div class="row">
            <div class="col-6">
              <label for="img-input">Ảnh đại diện</label>
              <div class="custom-file">
                <input
                  type="file"
                  name="img-client"
                  class="custom-file-input"
                  id="img-input"
                  aria-describedby="inputGroupFileAddon01"
                />
                <label class="custom-file-label" for="img-input">
                  <?= str_replace('img/', '', $user["avatar"]) ?>
                </label>
              </div>
              <div class="inner-img">
                <img src="<?= !empty($user['avatar']) 
                      ? "../" . htmlspecialchars($user['avatar']) 
                      : ($user['gender'] === 'MALE' 
                          ? '../img/avarta-man.png' 
                          : '../img/avarta-woman.svg') ?>" 
                      alt="Avatar" />
                <span class="cancel">x</span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <label for="address-default">Địa chỉ giao hàng mặc định</label>
              <textarea
                name="address-default"
                class="description"
                id="address-default"
                rows="3"
              ><?= $user['defaultAddress'] ?></textarea>
            </div>
          </div>

          <button
            class="btn btn-warning btn-announce"
            type="submit"
          >
            Xác nhận thay đổi
          </button>
        </form>
      <?php else: ?>
        <p class="text-danger">Không tìm thấy người dùng.</p>
      <?php endif; ?>
    </div>
  </body>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../js/handleFileImage.js"></script>
  <!-- <script src="../js/helper.js"></script> -->
  <script src="../js/announcement.js"></script>
  <script>
    document.getElementById("form-edit").addEventListener("submit", async function (event) {
    event.preventDefault(); // Ngăn form load lại trang

    let formData = new FormData(this); // Thêm dòng này để lấy dữ liệu form

    try {
        let response = await fetch("update-user.php", {
            method: "POST",
            body: formData,
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        let contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Response is not JSON");
        }
        let data = await response.json(); // Kiểm tra dữ liệu thực sự nhận được
        showAnnouncement(data.status, data.message);   
    } catch (error) {
        console.log("Fetch Error:", error);
        showAnnouncement("danger", "Lỗi khi kết nối server hoặc phản hồi không hợp lệ!");
    }
});

  </script>
</html>
