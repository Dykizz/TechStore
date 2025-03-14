<?php 
require "auth.php";
require "db_connect.php";
// Xác định trang hiện tại (nếu không có thì mặc định là trang 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Số lượng user mỗi trang
$offset = ($page - 1) * $limit; // Tính OFFSET

// Truy vấn lấy user không phải admin
$sql = "SELECT userId, name, status, avatar, gender FROM User WHERE isAdmin = FALSE ORDER BY createdAt DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Lấy dữ liệu
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Đếm tổng số user không phải admin để tính tổng số trang
$sqlCount = "SELECT COUNT(*) as total FROM User WHERE isAdmin = FALSE";
$countResult = $conn->query($sqlCount);
$totalUsers = $countResult->fetch_assoc()['total'];
$total_pages = ceil($totalUsers / $limit);
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
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
    </ul>
    <div class="alert alert-show announce" role="alert"></div>
    <div class="content">
      <h2 class="mb-4">Quản lý người dùng</h2>
      <div class="alert alert-info d-flex align-items-center">
        <i class="fa-solid fa-circle-info mr-2"></i>
        Có &nbsp; <strong><?= $totalUsers ?></strong> &nbsp; người dùng trên hệ thống
      </div>
      <h4 class="mb-3">Danh sách người dùng</h4>
      <table class="table table-hover table-bordered text-center">
        <thead class="bg-success">
          <th>STT</th>
          <th>Tên người dùng</th>
          <th>Hình ảnh</th>
          <th>Tình trạng</th>
          <th>Hành động</th>
        </thead>
        <tbody>
          <?php foreach ($users as $index => $user): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($user['name']) ?></td>
              <td class="table_inner-img">
                <img src="<?= !empty($user['avatar']) 
                    ? "../" . htmlspecialchars($user['avatar']) 
                    : ($user['gender'] === 'MALE' 
                        ? '../img/avarta-man.png' 
                        : '../img/avarta-woman.svg') ?>" 
                    alt="Avatar" />
              </td>
              <td>
                <span class="badge <?= $user['status'] == 'ACTIVE' ? 'badge-success' : 'badge-danger' ?>"><?= $user['status'] == 'ACTIVE' ? 'Hoạt động' : 'Dừng hoạt động' ?></span>
              </td>
              <td class="table_inner-btn">
                <button class="btn btn-sm btn-warning">
                  <a href="client-edit.php?userId=<?= $user['userId'] ?>" style="text-decoration: none;">Sửa</a>
                </button>
                <button class="btn btn-sm btn-<?= $user['status'] === 'ACTIVE' ? 'danger' : 'success' ?> change-status-btn"
                        data-user-id="<?= $user['userId'] ?>"
                        data-new-status="<?= $user['status'] === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' ?>"
                        data-toggle="modal"
                        data-target="#confirmModal">
                  <?= $user['status'] === 'ACTIVE' ? 'Khóa' : 'Mở khóa' ?>
                </button>
                <button class="btn btn-sm btn-primary">
                  <a href="client-detail.php?userId=<?= $user['userId'] ?>" style="color: white; text-decoration: none;">Chi tiết</a>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="inner-pagination">
        <ul class="pagination">
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a href="?page=1" class="page-link">««</a>
                </li>
            <?php } ?>
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a href="?page=<?= $page - 1 ?>" class="page-link">«</a>
                </li>
            <?php } ?>
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php } ?>
            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a href="?page=<?= $page + 1 ?>" class="page-link">»</a>
                </li>
            <?php } ?>
            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a href="?page=<?= $total_pages ?>" class="page-link">»»</a>
                </li>
            <?php } ?>
        </ul>
      </div>

      <!-- Modal xác nhận -->
      <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmModalLabel">Xác nhận thay đổi trạng thái</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              Bạn có chắc chắn muốn <span id="actionText"></span> người dùng này không?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
              <button type="button" class="btn btn-primary" id="confirmChange">Xác nhận</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="../js/announcement.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        let selectedUserId = null;
        let selectedNewStatus = null;

        // Xử lý khi nhấp vào nút "Khóa" hoặc "Mở khóa"
        document.querySelectorAll(".change-status-btn").forEach(button => {
            button.addEventListener("click", function () {
                selectedUserId = this.getAttribute("data-user-id");
                selectedNewStatus = this.getAttribute("data-new-status");

                // Cập nhật nội dung modal
                document.getElementById("actionText").textContent = selectedNewStatus === "INACTIVE" ? "khóa" : "mở khóa";
            });
        });

        // Xử lý khi nhấp nút "Xác nhận" trong modal
        document.getElementById("confirmChange").addEventListener("click", async function () {
            if (!selectedUserId || !selectedNewStatus) {
                showAnnouncement("danger", "Không có thông tin người dùng để thay đổi!");
                return;
            }

            try {
                let response = await fetch("change-status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `userId=${encodeURIComponent(selectedUserId)}&status=${encodeURIComponent(selectedNewStatus)}`
                });

                let data = await response.json();
                showAnnouncement(data.status === "success" ? "success" : "danger", data.message);

                // Đóng modal và reload trang nếu thành công
                if (data.status === "success") {
                    $("#confirmModal").modal("hide");
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } catch (error) {
                console.error(error);
                showAnnouncement("danger", "Lỗi khi kết nối server hoặc phản hồi không hợp lệ!");
            }
        });
    });
    </script>
</body>
</html>