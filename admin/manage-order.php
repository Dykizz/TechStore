<?php 
require "auth.php";
require "db_connect.php";

$limit = 5; // Số đơn hàng mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$sort = $_GET['sort'] ?? 'default';

// Xác định ORDER BY tương ứng
switch ($sort) {
    case 'price-increase':
        $orderBy = "o.totalAmount ASC";
        break;
    case 'price-decrease':
        $orderBy = "o.totalAmount DESC";
        break;
    case 'time-increase':
        $orderBy = "o.orderDate ASC";
        break;
    case 'time-decrease':
        $orderBy = "o.orderDate DESC";
        break;
    default:
        $orderBy = "o.orderDate DESC"; // Mặc định
}

$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';

// Lấy dữ liệu lọc từ form
$dateStart = isset($_GET['date-start']) ? $_GET['date-start'] : '';
$dateEnd = isset($_GET['date-end']) ? $_GET['date-end'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Xây dựng câu truy vấn lọc
$conditions = [];
if (!empty($search)) {
  $conditions[] = "(o.orderCode LIKE '%$search%' OR u.name LIKE '%$search%')";
}
if (!empty($dateStart)) {
    $conditions[] = "o.orderDate >= '$dateStart'";
}
if (!empty($dateEnd)) {
    $conditions[] = "o.orderDate <= '$dateEnd'";
}
if (!empty($status)) {
    $conditions[] = "o.status = '$status'";
}

// Gộp điều kiện vào SQL
$whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Lấy tổng số đơn hàng (phục vụ phân trang)
$sqlCount = "SELECT COUNT(*) as total 
             FROM Orders o 
             JOIN User u ON o.userId = u.userId
             $whereClause";

$resultCount = $conn->query($sqlCount);
$totalOrders = $resultCount->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);
$offset = ($page - 1) * $limit;

// Lấy danh sách đơn hàng có phân trang
$sql = "SELECT o.orderId, o.orderCode, u.name, o.orderDate, o.status, o.totalAmount 
        FROM Orders o
        JOIN User u ON o.userId = u.userId
        $whereClause
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
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
      <li class="active">
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
      <h2>Quản lý đơn hàng</h2>
      <div class="card mb-3">
        <div class="card-header bg-success font-weight-bold">Bộ lọc</div>
        <div class="card-body">
          <form action="manage-order.php">
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
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="order-status">Tình trạng đơn hàng</label>
                  <select
                    class="form-control"
                    name="status"
                    id="order-status"
                  >
                    <option value="" disabled>
                      --Chọn tình trạng đơn hàng--
                    </option>
                    <option value="" selected>Tất cả tình trạng</option>
                    <option value="Pending">Chưa xử lý</option>
                    <option value="Confirmed">Đã xác nhận</option>
                    <option value="Delivered">Giao thành công</option>
                    <option value="Cancelled">Đã hủy</option>
                  </select>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Áp dụng</button>
            <a href="manage-order.php" class="btn btn-secondary">
              <i class="fa-solid fa-rotate-left"></i> Reset
            </a>
          </form>
        </div>
      </div>
      <h4>Danh sách đơn hàng</h4>
      <div class="row">
        <div class="col-6">
          <div class="card" >
            <div class="card-header mb-0 bg-success">Bộ sắp xếp</div>
            <div class="card-body mb-0">
              <div class="form-group">
                <label for="sort">Sắp xếp theo</label>
                <div class="input-group">
                  <select class="form-control" name="" id="sortSelect">
                    <option value="" selected disabled>
                      --Chọn tiêu chí sắp xếp--
                    </option>
                    <option value="default">Mặc định</option>
                    <option value="price-increase">Giá tiền tăng dần</option>
                    <option value="price-decrease">Giá tiền giảm dần</option>
                    <option value="time-increase">Thời gian tăng dần</option>
                    <option value="time-decrease">Thời gian giảm dần</option>
                  </select>
                  <button class="btn btn-success" onclick="sortData()">
                    Sắp xếp
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class='col-6'>
          <div class="card">
            <div class="card-header mb-0 bg-success">Tìm kiếm đơn hàng</div>
            <div class="card-body mb-0">
              <div class="form-group">
              <label for="search">Tìm kiếm theo</label>
                <div class="input-group">
                  <input
                    id = "search"
                    class="form-control"
                    name="search"
                    type="text"
                    placeholder="Nhập tên khách hàng hoặc mã đơn hàng"
                  />
                  <button
                    type="submit"
                    class="input-group-append btn btn-success "
                    onclick="searchData()"
                  >
                    Tìm kiếm
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="alert alert-info d-flex align-items-center">
        <i class="fa-solid fa-circle-info mr-2"></i>
        Tìm thấy &nbsp;<span class="badge badge-primary p-2"><?= $totalOrders  ?></span>&nbsp; đơn hàng phù hợp với điều kiện tìm kiếm
      </div>
      <table class="table table-hover table-bordered text-center">
        <thead class="bg-success">
          <th>STT</th>
          <th>Mã đơn hàng</th>
          <th>Tên khách hàng</th>
          <th>Thời gian</th>
          <th>Giá trị đơn hàng</th>
          <th>Tình trạng</th>
          <th>Hành động</th>
        </thead>
        <tbody>
            <?php
            if (!empty($orders)) {
                $stt = 1;

                // Mảng ánh xạ trạng thái với class badge Bootstrap
                $statusOptions = [
                    'Pending' => ['label' => 'Chưa xử lý', 'class' => 'badge-secondary'],
                    'Confirmed' => ['label' => 'Đã xác nhận', 'class' => 'badge-info'],
                    'Delivered' => ['label' => 'Giao thành công', 'class' => 'badge-success'],
                    'Cancelled' => ['label' => 'Đã hủy', 'class' => 'badge-danger']
                ];

                // Mảng trạng thái hợp lệ cho từng trạng thái hiện tại
                $validTransitions = [
                    'Pending' => ['Confirmed'],
                    'Confirmed' => ['Delivered', 'Cancelled'],
                    'Delivered' => [],
                    'Cancelled' => []
                ];

                foreach ($orders as $order) {
                    echo "<tr>
                            <td>{$stt}</td>
                            <td>{$order['orderCode']}</td>
                            <td>{$order['name']}</td>
                            <td>" . date("d/m/Y", strtotime($order['orderDate'])) . "</td>
                            <td>" . number_format($order['totalAmount'], 0, ',', '.') . " VND</td>
                            <td>";

                    $currentStatus = $order['status'];
                    $currentClass = $statusOptions[$currentStatus]['class'];

                   
                    echo "<select class='badge {$currentClass}' 
                            style='border: none; padding: 5px; border-radius: 5px; color: white;'
                            onchange=\"changeBadgeColor(this,{$order['orderId']})\">";

                    // Luôn hiển thị trạng thái hiện tại
                    echo "<option value='{$currentStatus}' selected>{$statusOptions[$currentStatus]['label']}</option>";

                    // Hiển thị các trạng thái có thể chuyển đổi
                    foreach ($validTransitions[$currentStatus] as $nextStatus) {
                        echo "<option value='{$nextStatus}'>{$statusOptions[$nextStatus]['label']}</option>";
                    }

                    echo "</select>";

                    echo "</td>
                            <td>
                                <button class='btn btn-sm btn-primary' onclick=\"location.href='./order-detail.php?orderId={$order['orderId']}'\">
                                    Chi tiết
                                </button>
                            </td>
                          </tr>";

                    $stt++;
                }
            }
            ?>
        </tbody>
      </table>

      <div class="inner-pagination">
        <ul class="pagination">
            <!-- Nút trang đầu -->
            <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                <a href="./manage-order.php?page=1" class="page-link">&lt;&lt;</a>
            </li>

            <!-- Vòng lặp tạo số trang -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a href="./manage-order.php?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Nút trang cuối -->
            <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                <a href="./manage-order.php?page=<?= $totalPages ?>" class="page-link">&gt;&gt;</a>
            </li>
        </ul>
      </div>

    </div>
    <script src="../js/announcement.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
      let urlParams = new URLSearchParams(window.location.search);
      let sortValue = urlParams.get("sort");
      let searchValue = urlParams.get("search");

      if (sortValue) {
        document.getElementById("sortSelect").value = sortValue;
      }
      if (searchValue){
        document.getElementById("search").value = searchValue;
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
      updateURLParameter("sort", sortValue);
    }

    function searchData(){
      let searchValue = document.getElementById("search").value;
      updateURLParameter("search", searchValue);
    }

    async function changeBadgeColor(selectElement, orderId) {
        let oldStatus = selectElement.dataset.oldStatus || selectElement.value; // Lưu trạng thái cũ
        let newStatus = selectElement.value;

        let statusClasses = {
            'Pending': 'badge-secondary',
            'Confirmed': 'badge-info',
            'Delivered': 'badge-success',
            'Cancelled': 'badge-danger'
        };

        // Xóa tất cả class badge cũ
        selectElement.classList.remove('badge-secondary', 'badge-info', 'badge-success', 'badge-danger');

        // Thêm class badge mới
        selectElement.classList.add(statusClasses[newStatus]);
        console.log(orderId,newStatus);
        try {
            let response = await fetch('update-statusOrder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `orderId=${orderId}&status=${newStatus}`
            });

            let data = await response.json();

            if (data.status === "success") {
                showAnnouncement("success", data.message);
                selectElement.dataset.oldStatus = newStatus; // Cập nhật trạng thái mới
            } else {
                throw new Error(data.message); // Nếu thất bại, ném lỗi
            }
        } catch (error) {
            // Quay lại trạng thái cũ nếu lỗi
            selectElement.value = oldStatus;
            selectElement.classList.remove('badge-secondary', 'badge-info', 'badge-success', 'badge-danger');
            selectElement.classList.add(statusClasses[oldStatus]);
            showAnnouncement("danger", error.message);
        }
    }


    </script>
  </body>
</html>
