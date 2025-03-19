<?php 
require "auth.php";
require "db_connect.php";

$dateStart = isset($_GET['date-start']) ? $_GET['date-start'] : '';
$dateEnd = isset($_GET['date-end']) ? $_GET['date-end'] : '';

// Câu lệnh SQL cơ bản với điều kiện status = 'Delivered'
$sql = "SELECT 
    u.gender,
    u.userId,
    u.name AS customerName,
    u.avatar,
    COUNT(o.orderId) AS orderCount,
    SUM(o.totalAmount) AS totalRevenue
FROM User u
JOIN Orders o ON u.userId = o.userId
WHERE o.status = 'Delivered'"; // Thêm điều kiện lọc Delivered

// Nếu có dateStart và dateEnd thì thêm điều kiện lọc theo orderDate
if (!empty($dateStart) && !empty($dateEnd)) {
    $sql .= " AND o.orderDate BETWEEN ? AND ?";
}

$sql .= " GROUP BY u.userId, u.name, u.avatar
ORDER BY totalRevenue DESC
LIMIT 5;";

$stmt = $conn->prepare($sql);

// Nếu có dateStart và dateEnd thì bind tham số
if (!empty($dateStart) && !empty($dateEnd)) {
    $stmt->bind_param("ss", $dateStart, $dateEnd);
}

$stmt->execute();
$result = $stmt->get_result();

$topCustomers = []; // Mảng chứa danh sách khách hàng

while ($row = $result->fetch_assoc()) {
    $topCustomers[] = $row;
}

// Giải phóng bộ nhớ
$stmt->close();
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
      
      <li class="active">
        <div class="inner-icon">
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
    </ul>
    <div class="content">
      <h4 class="mb-3">Top 5 khách hàng tạo doanh thu nhiều nhất (Đã giao hàng)</h4>
      <div class="card mb-3">
        <div class="card-header bg-success font-weight-bold">Bộ lọc</div>
        <div class="card-body">
          <form action="./top5-client.php">
            <div class="row">
              <div class="col-4">
                <div class="form-group">
                  <label for="date-start">Thời gian bắt đầu</label>
                  <input
                    class="form-control"
                    type="date"
                    value="<?= isset($_GET['date-start']) ? $_GET['date-start'] : '' ?>"
                    name="date-start"
                    id="date-start"
                  />
                </div>
              </div>
              <div class="col-4">
                <div class="form-group">
                  <label for="date-end">Thời gian kết thúc</label>
                  <input
                    class="form-control"
                    type="date"
                    value="<?= isset($_GET['date-end']) ? $_GET['date-end'] : '' ?>"
                    name="date-end"
                    id="date-end"
                  />
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Áp dụng</button>
            <a href="./top5-client.php" class="btn btn-secondary">
              <i class="fa-solid fa-rotate-left"></i> Reset
            </a>
          </form>
        </div>
      </div>
      <table class="table table-hover table-bordered text-center">
        <thead class="bg-success">
          <th>STT</th>
          <th>Tên khách hàng</th>
          <th>Hình ảnh</th>
          <th>Số lượng đơn hàng</th>
          <th>Doanh thu</th>
          <th>Các đơn đã mua</th>
        </thead>
        <tbody>
          <?php $stt = 1; ?>
          <?php foreach ($topCustomers as $customer) : ?>
              <tr>
                  <td><?= $stt++ ?></td>
                  <td><?= htmlspecialchars($customer['customerName']) ?></td>
                  <td class="table_inner-img">
                    <img src="<?= !empty($customer['avatar']) 
                      ? "../" . htmlspecialchars($customer['avatar']) 
                      : ($customer['gender'] === 'MALE' 
                          ? '../img/avarta-man.png' 
                          : '../img/avarta-woman.svg') ?>" 
                      alt="Avatar" />
                  </td>
                  <td><?= $customer['orderCount'] ?></td>
                  <td><?= number_format($customer['totalRevenue'], 0, ',', '.') ?> VND</td>
                  <td>
                  <?php
                  $params = ['userId' => $customer['userId']];
                  if (!empty($dateStart) && !empty($dateEnd)) {
                      $params['date-start'] = $dateStart;
                      $params['date-end'] = $dateEnd;
                  }
                  $url = 'orderClient-relate.php?' . http_build_query($params);
                  ?>
                  <a href="<?= $url ?>" class="btn btn-primary">Xem</a>
                </td>
              </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
</body>
</html>