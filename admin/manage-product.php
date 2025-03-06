<?php
require "auth.php";
require "db_connect.php";
// Số sản phẩm trên mỗi trang
$limit = 5; // Thay đổi số sản phẩm tùy ý

// Xác định trang hiện tại
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Tìm kiếm và lọc danh mục
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : '';

// Tạo câu lệnh SQL chung (không chứa LIMIT) để đếm tổng số sản phẩm phù hợp
$total_sql = "SELECT COUNT(*) as total FROM Product WHERE 1";

if (!empty($search)) {
    $total_sql .= " AND name LIKE '%$search%'";
}

if ($category > 0) {
    $total_sql .= " AND categoryId = $category";
}

$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total']; // Cập nhật tổng số sản phẩm phù hợp

// Tính tổng số trang theo số lượng sản phẩm đã lọc
$total_pages = ceil($total_products / $limit);

// Tính OFFSET
$offset = ($page - 1) * $limit;

// Lấy danh sách sản phẩm phù hợp với tìm kiếm + phân trang
$sql = "SELECT productId, name, image, price, stock FROM Product WHERE 1";

if (!empty($search)) {
    $sql .= " AND name LIKE '%$search%'";
}

if ($category > 0) {
    $sql .= " AND categoryId = $category";
}

// Thêm LIMIT & OFFSET sau khi đã có điều kiện lọc
$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Lấy danh mục sản phẩm
$sql_categories = "SELECT categoryId, name FROM Category";
$result_categories = $conn->query($sql_categories);
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
    <div class="alert alert-show announce" role="alert"></div>
    <div class="content">
      <h2 class="mb-4">Quản lý sản phẩm</h2>
      <div class="alert alert-info d-flex align-items-center">
        <i class="fa-solid fa-circle-info mr-2"></i>
        Tìm thấy &nbsp;<span class="badge badge-primary p-2"><?= $total_products ?></span>&nbsp; sản phẩm phù hợp với điều kiện tìm kiếm
      </div>
        <div class="filter-search card mb-3">
          <div class="card-header bg-success font-weight-bold">
              Lọc và tìm kiếm
          </div>
          <div class="card-body row">
              <div class="col-6">
                  <form method="GET" action="manage-product.php">
                      <div class="form-group">
                          <label for="filter">Lọc theo danh mục</label>
                          <select id="filter" name="category" class="form-control">
                              <option value="" selected>Tất cả</option>
                              <?php 
                              if ($result_categories->num_rows > 0) {
                                  while ($row = $result_categories->fetch_assoc()) {
                                      $selected = (isset($_GET['category']) && $_GET['category'] == $row['categoryId']) ? 'selected' : '';
                                      echo "<option value='{$row['categoryId']}' $selected>{$row['name']}</option>";
                                  }
                              }
                              ?>
                          </select>
                      </div>
              </div>
              <div class="col-6">
                  <div class="form-group">
                      <label for="search">Tìm kiếm</label>
                      <div class="input-group">
                          <input
                              id="search"
                              name="search"
                              class="form-control"
                              type="text"
                              placeholder="Nhập tên sản phẩm muốn tìm kiếm"
                              value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                          />
                      </div>
                  </div>
              </div>
              <div class="ml-3">
                  <button class="btn btn-primary" type="submit">
                      <i class="fa-solid fa-magnifying-glass"></i> Áp dụng
                  </button>
                  <a href="manage-product.php" class="btn btn-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
              </div>
              </form>
          </div>
      </div>


      <h4 class="mb-3">Danh sách sản phẩm</h4>

      <a href="./product-add.php" class="btn btn-outline-success mb-3">
        <i class="fa-solid fa-plus"></i>
        Thêm sản phẩm
      </a>

      <table class="table table-hover table-bordered text-center">
        <thead class="bg-success">
          <th>STT</th>
          <th>Tên sản phẩm</th>
          <th>Hình ảnh</th>
          <th>Giá</th>
          <th>Số lượng</th>
          <th>Hành động</th>
        </thead>
        <tbody>
          <?php
                if ($result->num_rows > 0) {
                    $stt = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$stt}</td>
                            <td>{$row['name']}</td>
                            <td class='table_inner-img'>
                              <img src='../{$row['image']}'  alt='{$row['image']}' >
                            </td>
                            <td>" . number_format($row['price'], 0, ',', '.') . " VND</td>
                            <td>{$row['stock']}</td>
                            <td class='table_inner-btn'>
                                <a href='./product-edit.php?productId={$row['productId']}' class='btn btn-warning btn-sm'>Sửa</a>
                                <a  class='btn btn-danger btn-sm delete-btn' data-toggle='modal' data-id='{$row['productId']}' data-target='#deleteModal' >Xóa</a>
                                <a href='./product-detail.php?productId={$row['productId']}' class='btn btn-primary btn-sm'>Chi tiết</a>
                            </td>
                        </tr>";
                        $stt++;
                    }
                } else {
                    echo "<tr><td colspan='6'>Không có sản phẩm nào</td></tr>";
                }
            ?>
        </tbody>
      </table>
      <?php
        // Tạo query string giữ lại các tham số tìm kiếm
        $query_string = http_build_query([
            'search' => $search,
            'category' => $category
        ]);
      ?>
      <div class="inner-pagination">
        <ul class="pagination">
            <!-- Nút "Trang đầu" -->
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a href="?page=1&<?= $query_string ?>" class="page-link">&laquo;&laquo;</a>
                </li>
            <?php } ?>

            <!-- Nút "Trang trước" -->
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a href="?page=<?= $page - 1 ?>&<?= $query_string ?>" class="page-link">&laquo;</a>
                </li>
            <?php } ?>

            <!-- Hiển thị các số trang -->
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a href="?page=<?= $i ?>&<?= $query_string ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php } ?>

            <!-- Nút "Trang sau" -->
            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a href="?page=<?= $page + 1 ?>&<?= $query_string ?>" class="page-link">&raquo;</a>
                </li>
            <?php } ?>

            <!-- Nút "Trang cuối" -->
            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a href="?page=<?= $total_pages ?>&<?= $query_string ?>" class="page-link">&raquo;&raquo;</a>
                </li>
            <?php } ?>
        </ul>
      </div>

      <div
        class="modal fade"
        id="deleteModal"
        tabindex="-1"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">
                Xác nhận xóa sản phẩm
              </h5>
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              Bạn có chắc chắn muốn xóa sản phẩm này ?
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal"
              >
                Hủy
              </button>
              <button type="button" class="btn btn-primary" id="confirmDeleteLink">
                Xác nhận xóa
              </button>
            </div>
          </div>
        </div>
      </div>
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
  <script src="../js/announcement.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".delete-btn");
    const confirmDeleteLink = document.getElementById("confirmDeleteLink");
    let selectedProductId = null;

    deleteButtons.forEach(button => {
        button.addEventListener("click", function () {
            selectedProductId = this.getAttribute("data-id");
        });
    });

    confirmDeleteLink.addEventListener("click", function () {
      $("#deleteModal").modal("hide");
    if (!selectedProductId) {
        showAnnouncement("danger", "Yêu cầu không hợp lệ: Thiếu productId!");
        return;
    }

    fetch("delete_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `productId=${selectedProductId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Lỗi HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        showAnnouncement(data.status === "success" ? "success" : "danger", data.message);
        if (data.status === "success") {
            setTimeout(() => location.reload(), 2000);
        }
    })
    .catch(error => {
        console.error(error);
        showAnnouncement("danger", "Lỗi khi kết nối server hoặc phản hồi không hợp lệ!");
    });
  });
});


  </script>
</html>
