<?php
require "auth.php";
require "db_connect.php";
// Lấy dữ liệu lọc từ form
$dateStart = isset($_GET['date-start']) ? $_GET['date-start'] : '';
$dateEnd = isset($_GET['date-end']) ? $_GET['date-end'] : '';
// Nhận tham số từ URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5; // Số sản phẩm trên mỗi trang
$offset = ($page - 1) * $limit;

// Tạo câu truy vấn SQL theo tiêu chí sắp xếp
$orderBy = "ORDER BY p.productId"; // Mặc định
switch ($sort) {
    case 'totalQuantitySold-increase':
        $orderBy = "ORDER BY totalQuantitySold ASC";
        break;
    case 'totalQuantitySold-decrease':
        $orderBy = "ORDER BY totalQuantitySold DESC";
        break;
    case 'totalRevenue-increase':
        $orderBy = "ORDER BY totalRevenue ASC";
        break;
    case 'totalRevenue-decrease':
        $orderBy = "ORDER BY totalRevenue DESC";
        break;
}


// Điều kiện lọc theo ngày
$dateCondition = "";
if (!empty($dateStart) && !empty($dateEnd)) {
    $dateCondition = "AND od.statusUpdatedAt BETWEEN '$dateStart' AND '$dateEnd'";
} elseif (!empty($dateStart)) {
    $dateCondition = "AND od.statusUpdatedAt >= '$dateStart'";
} elseif (!empty($dateEnd)) {
    $dateCondition = "AND od.statusUpdatedAt <= '$dateEnd'";
}

// Truy vấn lấy danh sách sản phẩm với doanh thu và số lượng đã bán
$sql = "
    SELECT 
        p.productId, p.name AS productName, p.image, p.price,
        COALESCE(SUM(od.quantity), 0) AS totalQuantitySold,
        COALESCE(SUM(od.subtotal), 0) AS totalRevenue
    FROM Product p
    INNER JOIN OrderDetail od ON p.productId = od.productId
    INNER JOIN Orders o ON od.orderId = o.orderId
    WHERE o.status = 'Delivered' $dateCondition
    GROUP BY p.productId
    HAVING totalQuantitySold > 0
    $orderBy
    LIMIT $limit OFFSET $offset
";


$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Truy vấn lấy tổng số sản phẩm
$totalQuery = "
    SELECT COUNT(DISTINCT p.productId) AS total
    FROM Product p
    INNER JOIN OrderDetail od ON p.productId = od.productId
    INNER JOIN Orders o ON od.orderId = o.orderId
    WHERE o.status = 'Delivered' $dateCondition
";


$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $limit);

// Truy vấn tổng doanh thu
$totalRevenueQuery = "
    SELECT COALESCE(SUM(od.subtotal), 0) AS totalRevenue
    FROM OrderDetail od
    INNER JOIN Orders o ON od.orderId = o.orderId
    WHERE o.status = 'Delivered' $dateCondition
";
$totalRevenueResult = $conn->query($totalRevenueQuery);
$totalRevenueRow = $totalRevenueResult->fetch_assoc();
$totalRevenue = $totalRevenueRow['totalRevenue'];


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
      <h2>Thống kê kinh doanh</h2>
      <div class="card mb-3">
        <div class="card-header bg-success font-weight-bold">Bộ lọc</div>
        <div class="card-body">
          <form action="./statistic.php">
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
            <a href="./statistic.php" class="btn btn-secondary">
              <i class="fa-solid fa-rotate-left"></i> Reset
            </a>
          </form>
        </div>
      </div>
      <h4>Danh sách mặt hàng được bán ra</h4>
      <div class="row">
        <div class="col-6">
          <div class="card mb-3 ">
            <div class="card-header mb-0 bg-success">Bộ sắp xếp</div>
            <div class="card-body mb-0 ">
              <div class="form-group">
                <label for="sort">Sắp xếp theo</label>
                <div class="input-group">
                  <select class="form-control" name="" id="sortSelect">
                    <option value="" selected disabled>
                      --Chọn tiêu chí sắp xếp--
                    </option>
                    <option value="default">Mặc định</option>
                    <option value="totalQuantitySold-increase">Số lượng mua tăng dần</option>
                    <option value="totalQuantitySold-decrease">Số lượng mua giảm dần</option>
                    <option value="totalRevenue-increase">Doanh thu tăng dần</option>
                    <option value="dtotalRevenue-decrease">Doanh thu giảm dần</option>
                  </select>
                  <button class="btn btn-success" onclick="sortData()">
                    Sắp xếp
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="alert alert-info d-flex align-items-center">
        <i class="fa-solid fa-circle-info mr-2"></i>
        Tìm thấy &nbsp;<span class="badge badge-primary p-2"><?= $totalProducts  ?></span>&nbsp; sản phẩm được bán ra phù hợp với điều kiện tìm kiếm (chỉ tính những sản phẩm đã được giao)
      </div>
      <table class="table table-hover table-bordered text-center">
        <thead class="bg-success">
          <th>STT</th>
          <th>Tên sản phẩm</th>
          <th>Hình ảnh</th>
          <th>Giá</th>
          <th>Số lượng</th>
          <th>Doanh thu</th>
          <th>Các đơn liên quan</th>
        </thead>
        <tbody>
            <?php
            $stt = 1;
            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>{$stt}</td>";
                echo "<td>{$product['productName']}</td>";
                echo "<td class='table_inner-img'><img src='../{$product['image']}' alt='Ảnh' width='80'></td>";
                echo "<td>" . number_format($product['price'], 0, '.', '.') . " VND</td>";
                echo "<td>{$product['totalQuantitySold']}</td>";
                echo "<td>" . number_format($product['totalRevenue'], 0, '.', '.') . " VND</td>";
                echo "<td><a class='btn btn-primary btn-sm' href='order-relate.php?productId={$product['productId']}'>Xem</a></td>";
                echo "</tr>";
                $stt++;
            }
            ?>
        </tbody>
      </table>
      <div class="inner-pagination">
        <ul class="pagination">
            <!-- Nút trang đầu -->
            <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                <a href="./statistic.php?page=1" class="page-link">&lt;&lt;</a>
            </li>

            <!-- Vòng lặp tạo số trang -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a href="./statistic.php?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Nút trang cuối -->
            <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                <a href="./statistic.php?page=<?= $totalPages ?>" class="page-link">&gt;&gt;</a>
            </li>
        </ul>
      </div>
      <p>
        Tổng tiền thu được :
        <strong><?= number_format($totalRevenue, 0, '.', '.') ?> VND</strong>
      </p>

    </div>
  </body>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      let urlParams = new URLSearchParams(window.location.search);
      let sortValue = urlParams.get("sort");

      if (sortValue) {
        document.getElementById("sortSelect").value = sortValue;
      }
    });
    function updateURLParameter(key, value) {
      let url = new URL(window.location.href);

      if (value) {
        url.searchParams.set(key, value); // Cập nhật giá trị tham số
      } else {
        url.searchParams.delete(key); // Xóa nếu giá trị rỗng
      }

      window.location.href = url.toString();
    }
    function sortData() {
      let sortValue = document.getElementById("sortSelect").value;
      console.log(sortValue)
      updateURLParameter("sort", sortValue);
    }
  </script>
</html>
