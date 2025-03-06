<?php
require "auth.php";      // Nếu có xác thực người dùng
require "db_connect.php"; // Kết nối database

// Kiểm tra nếu có productId được truyền vào từ URL
$productId = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
if ($productId <= 0) {
    die("Product ID không hợp lệ!");
}

$limit = 3;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Lấy tên sản phẩm từ bảng Product
$sqlProduct = "SELECT name FROM Product WHERE productId = ?";
$stmtProduct = $conn->prepare($sqlProduct);
$stmtProduct->bind_param("i", $productId);
$stmtProduct->execute();
$resultProduct = $stmtProduct->get_result();
$productName = "";
if ($rowProduct = $resultProduct->fetch_assoc()) {
    $productName = $rowProduct['name'];
}
$stmtProduct->close();

// Đếm tổng số đơn hàng liên quan đến sản phẩm có productId được truyền vào
$sqlCount = "SELECT COUNT(DISTINCT o.orderId) AS totalOrders
             FROM Orders o
             JOIN OrderDetail od ON o.orderId = od.orderId
             WHERE od.productId = ?";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param("i", $productId);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$totalOrders = 0;
if ($rowCount = $resultCount->fetch_assoc()) {
    $totalOrders = $rowCount['totalOrders'];
}
$stmtCount->close();

// Tính tổng số trang
$totalPages = ceil($totalOrders / $limit);

// Truy vấn lấy danh sách đơn hàng theo phân trang (chỉ lấy các dòng có sản phẩm có productId đó)
$sql = "SELECT 
            o.orderCode, 
            o.orderId,
            u.name AS userName,
            p.name AS productName,
            od.quantity,
            o.totalAmount
        FROM Orders o
        JOIN User u ON o.userId = u.userId
        JOIN OrderDetail od ON o.orderId = od.orderId
        JOIN Product p ON od.productId = p.productId
        WHERE p.productId = ?
        ORDER BY o.orderCode DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $productId, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Sử dụng orderCode làm key (giả sử mỗi orderCode là duy nhất)
    $orderCode = $row['orderCode'];
    
    if (!isset($orders[$orderCode])) {
        $orders[$orderCode] = [
            'orderCode'   => $row['orderCode'],
            'userName'    => $row['userName'],
            'products'    => [],
            'totalAmount' => $row['totalAmount'],
            'orderId'     => $row['orderId']
        ];
    }

    // Lưu thông tin sản phẩm có liên quan (lưu ý: chỉ lấy sản phẩm khớp productId)
    $orders[$orderCode]['products'][] = [
        'name'     => $row['productName'],
        'quantity' => $row['quantity']
    ];
}

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
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />
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
          <img src="../img/logo.png" alt="Logo" />
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
          <span><?= htmlspecialchars($_SESSION["admin"]) ?></span>
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
      <?php if ($productName) : ?>
          <h2>Các đơn hàng liên quan</h2>
          <p>
              Sản phẩm :
              <a class="text-primary" href="./product-detail.php?productId=<?= htmlspecialchars($productId) ?>">
                  <?= htmlspecialchars($productName) ?>
              </a>
          </p>
          <p>
              <i class="text-success">
                  Có <strong><?= $totalOrders ?></strong> đơn hàng liên quan đến sản phẩm này!
              </i>
          </p>
      <?php endif; ?>

      <h4>Danh sách đơn hàng</h4>
      <div>
        <?php if (!empty($orders)) : ?>
          <?php foreach ($orders as $order) : ?>
              <div class="card mb-3">
                  <div class="card-body">
                      <p>
                          Mã đơn :
                          <strong><?= htmlspecialchars($order['orderCode']) ?></strong>
                      </p>
                      <p>
                          Tên người nhận :
                          <strong><?= htmlspecialchars($order['userName']) ?></strong>
                      </p>
                      <p>Sản phẩm :</p>
                      <ul>
                          <?php foreach ($order['products'] as $product) : ?>
                              <li>
                                  <?= htmlspecialchars($product['name']) ?>
                                  <b>x<?= htmlspecialchars($product['quantity']) ?></b>
                              </li>
                          <?php endforeach; ?>
                          <span>Các sản phẩm khác ....</span>
                      </ul>
                      <p>
                          Tổng tiền :
                          <strong><?= number_format($order['totalAmount'], 0, ',', '.') ?> VND</strong>
                      </p>
                      <a class="text-primary" href="./order-detail.php?orderId=<?= htmlspecialchars($order['orderId']) ?>">Xem chi tiết</a>
                  </div>
              </div>
          <?php endforeach; ?>
        <?php else : ?>
            <p>Không tìm thấy đơn hàng nào chứa sản phẩm này.</p>
        <?php endif; ?>
      </div>

      <!-- Phân trang -->
      <div class="inner-pagination">
        <ul class="pagination">
            <!-- Nút trang đầu -->
            <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                <a href="./order-relate.php?productId=<?= $productId ?>&page=1" class="page-link">&lt;&lt;</a>
            </li>

            <!-- Vòng lặp tạo số trang -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a href="./order-relate.php?productId=<?= $productId ?>&page=<?= $i ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Nút trang cuối -->
            <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                <a href="./order-relate.php?productId=<?= $productId ?>&page=<?= $totalPages ?>" class="page-link">&gt;&gt;</a>
            </li>
        </ul>
      </div>
    </div>
  </body>
</html>
