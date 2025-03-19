<?php 
require "auth.php";
require "db_connect.php";

// Truy vấn tài khoản người dùng
$totalUsersQuery = "SELECT COUNT(*) as total FROM User";
$activeUsersQuery = "SELECT COUNT(*) as active FROM User WHERE status = 'ACTIVE'";
$inactiveUsersQuery = "SELECT COUNT(*) as inactive FROM User WHERE status = 'INACTIVE'";

$totalUsersResult = $conn->query($totalUsersQuery);
$activeUsersResult = $conn->query($activeUsersQuery);
$inactiveUsersResult = $conn->query($inactiveUsersQuery);

$totalUsers = $totalUsersResult->fetch_assoc()['total'];
$activeUsers = $activeUsersResult->fetch_assoc()['active'];
$inactiveUsers = $inactiveUsersResult->fetch_assoc()['inactive'];

// Truy vấn sản phẩm
$totalProductsQuery = "SELECT COUNT(*) as total FROM Product";
$activeProductsQuery = "SELECT COUNT(*) as active FROM Product WHERE stock > 0";
$outOfStockProductsQuery = "SELECT COUNT(*) as out_of_stock FROM Product WHERE stock = 0";

$totalProductsResult = $conn->query($totalProductsQuery);
$activeProductsResult = $conn->query($activeProductsQuery);
$outOfStockProductsResult = $conn->query($outOfStockProductsQuery);

$totalProducts = $totalProductsResult->fetch_assoc()['total'];
$activeProducts = $activeProductsResult->fetch_assoc()['active'];
$outOfStockProducts = $outOfStockProductsResult->fetch_assoc()['out_of_stock'];

// Truy vấn đơn hàng hôm nay
$totalOrdersQuery = "SELECT COUNT(*) as total FROM Orders WHERE DATE(orderDate) = CURDATE()";
$pendingOrdersQuery = "SELECT COUNT(*) as pending FROM Orders WHERE status = 'Pending'";
$processedOrdersQuery = "SELECT COUNT(*) as processed FROM Orders WHERE status IN ('Confirmed', 'Delivered')";
$cancelledOrdersQuery = "SELECT COUNT(*) as cancelled FROM Orders WHERE status = 'Cancelled'";

$totalOrdersResult = $conn->query($totalOrdersQuery);
$pendingOrdersResult = $conn->query($pendingOrdersQuery);
$processedOrdersResult = $conn->query($processedOrdersQuery);
$cancelledOrdersResult = $conn->query($cancelledOrdersQuery);

$totalOrders = $totalOrdersResult->fetch_assoc()['total'];
$pendingOrders = $pendingOrdersResult->fetch_assoc()['pending'];
$processedOrders = $processedOrdersResult->fetch_assoc()['processed'];
$cancelledOrders = $cancelledOrdersResult->fetch_assoc()['cancelled'];

// Đóng kết nối
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
        <div href="#" class="notification">
          <i class="fa-regular fa-bell"></i>
          <span>Thông báo</span>
        </div>
        <div href="#" class="infor-user">
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
      <li class="active">
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
      <li>
        <div class="inner-icon">
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
    </ul>
    <div class="content">
      <h2>Tổng quan</h2>
      <div class="row">
        <div class="col-6">
          <div class="card">
            <div class="card-header">Tài khoản người dùng</div>
            <div class="card-body">
              <p>
                Số lượng tài khoản:
                <strong><?= $totalUsers ?></strong>
                tài khoản
              </p>
              <p>
                Đang hoạt động:
                <strong><?= $activeUsers ?></strong>
                tài khoản
              </p>
              <p>
                Ngừng hoạt động:
                <strong><?= $inactiveUsers ?></strong>
                tài khoản
              </p>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card">
            <div class="card-header">Sản phẩm</div>
            <div class="card-body">
              <p>
                Số lượng sản phẩm:
                <strong><?= $totalProducts ?></strong>
                sản phẩm
              </p>
              <p>
                Đang hoạt động:
                <strong><?= $activeProducts ?></strong>
                sản phẩm
              </p>
              <p>
                Hết hàng:
                <strong><?= $outOfStockProducts ?></strong>
                sản phẩm
              </p>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card">
            <div class="card-header">Đơn hàng hôm nay</div>
            <div class="card-body">
              <p>
                Số lượng đơn hàng hôm nay:
                <strong><?= $totalOrders ?></strong>
                đơn hàng
              </p>
              <p>
                Chưa xác nhận:
                <strong><?= $pendingOrders ?></strong>
                đơn hàng
              </p>
              <p>
                Đã xử lý:
                <strong><?= $processedOrders ?></strong>
                đơn hàng
              </p>
              <p>
                Đã hủy:
                <strong><?= $cancelledOrders ?></strong>
                đơn hàng
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>
</html>