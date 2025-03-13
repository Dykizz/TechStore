<?php
require "auth.php";  // Nếu có xác thực người dùng
require "db_connect.php"; // Kết nối database

$dateStart = isset($_GET['date-start']) ? $_GET['date-start'] : null;
$dateEnd = isset($_GET['date-end']) ? $_GET['date-end'] : null;

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
if ($userId <= 0) {
    die("User ID không hợp lệ!");
}
$limit = 3;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$sql = "
    SELECT 
        o.orderId, o.orderCode, o.orderDate, o.status, 
        p.productId, p.name AS productName, p.image, 
        od.quantity, od.price, od.subtotal, u.name, o.totalAmount
    FROM Orders o
    JOIN OrderDetail od ON o.orderId = od.orderId
    JOIN Product p ON od.productId = p.productId
    JOIN User u ON o.userId = u.userId
    WHERE o.userId = ? AND o.status = 'Delivered'
";

$params = ["i", $userId]; // Kiểu dữ liệu: 'i' là số nguyên

if (!empty($dateStart) && !empty($dateEnd)) {
    $sql .= " AND o.orderDate BETWEEN ? AND ?";
    $params[0] .= "ss"; // Thêm kiểu dữ liệu chuỗi 'ss'
    $params[] = $dateStart;
    $params[] = $dateEnd;
}

$sql .= " ORDER BY o.orderDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['orderId'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'orderId' => $row['orderId'],
            'name' => $row['name'],
            'totalAmount' => $row['totalAmount'],
            'orderCode' => $row['orderCode'],
            'orderDate' => $row['orderDate'],
            'status' => $row['status'],
            'products' => []
        ];
    }
    $orders[$orderId]['products'][] = [
        'productId' => $row['productId'],
        'productName' => $row['productName'],
        'image' => $row['image'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'subtotal' => $row['subtotal']
    ];
}

$stmt->close();

$sqlCount = "SELECT COUNT(DISTINCT o.orderId) AS total FROM Orders o WHERE o.userId = ?";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param("i", $userId);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$totalOrders = $resultCount->fetch_assoc()['total'];

$totalPages = ceil($totalOrders / $limit);
$stmtCount->close();

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
      <h2>Các đơn hàng liên quan</h2>
      <p>
          Khách hàng: 
          <a class="text-primary" href="./client-detail.php?userId=<?= $userId ?>">
              <?= $orders[array_key_first($orders)]['name'] ?? 'Không xác định' ?>
          </a>
      </p>
      <p>
          <i class="text-success">
              Có <strong><?= count($orders) ?></strong> đơn hàng liên quan đến khách hàng này!
          </i>
      </p>
      <h4>Danh sách đơn hàng</h4>
      <div>
          <?php foreach ($orders as $order) : ?>
              <div class="card mb-3">
                  <div class="card-body">
                      <p>Mã đơn: <strong><?= $order['orderCode'] ?></strong></p>
                      <p>Ngày tạo: <strong><?= date('d/m/Y H:i:s', strtotime($order['orderDate'])) ?></strong></p>
                      <p>Trạng thái: <strong><?= $order['status'] ?></strong></p>
                      <p>Tên người nhận: <strong><?= $order['name'] ?></strong></p>
                      <p>Các sản phẩm:</p>
                      <ul>
                          <?php foreach ($order['products'] as $product) : ?>
                              <li><?= $product['productName'] ?> <b>x<?= $product['quantity'] ?></b></li>
                          <?php endforeach; ?>
                      </ul>
                      <p>Tổng tiền: <strong><?= number_format($order['totalAmount'], 0, ',', '.') ?> VND</strong></p>
                      <a class="text-primary" href="./order-detail.php?orderId=<?= $order['orderId'] ?>">Xem chi tiết</a>
                  </div>
              </div>
          <?php endforeach; ?>
      </div>
       <!-- Phân trang -->
       <div class="inner-pagination">
        <ul class="pagination">
            <!-- Nút trang đầu -->
            <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                <a href="./orderClient-relate.php?userId=<?= $userId ?>&page=1<?= !empty($dateStart) && !empty($dateEnd) ? '&date-start=' . urlencode($dateStart) . '&date-end=' . urlencode($dateEnd) : '' ?>" 
                  class="page-link">&lt;&lt;</a>
            </li>

            <!-- Vòng lặp tạo số trang -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a href="./orderClient-relate.php?userId=<?= $userId ?>&page=<?= $i ?><?= !empty($dateStart) && !empty($dateEnd) ? '&date-start=' . urlencode($dateStart) . '&date-end=' . urlencode($dateEnd) : '' ?>" 
                      class="page-link"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Nút trang cuối -->
            <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                <a href="./orderClient-relate.php?userId=<?= $userId ?>&page=<?= $totalPages ?><?= !empty($dateStart) && !empty($dateEnd) ? '&date-start=' . urlencode($dateStart) . '&date-end=' . urlencode($dateEnd) : '' ?>" 
                  class="page-link">&gt;&gt;</a>
            </li>
        </ul>

      </div>
    </div>
  </body>
</html>
