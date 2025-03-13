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
          <i class="fa-solid fa-medal"></i>
        </div>
        <a href="./top5-client.php">Top 5 khách hàng</a>
      </li>
    </ul>
    <div class="content">
      <h2 class="mb-3">Kết quả sau khi thêm sản phẩm</h2>
      <a class="btn btn-primary mb-3" href="./manage-product.php"
        >Quay về trang quản lý sản phẩm</a
      >

      <div class="card">
        <div class="card-header bg-info text-white">
          <h4 class="d-inline-block">Sản phẩm test</h4>
          <span class="badge badge-success ml-2">Hoạt động</span>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div>Hình ảnh sản phẩm</div>
              <div class="inner-img">
                <img src="../img/product01.png" alt="sản phẩm" srcset="" />
              </div>
            </div>
            <div class="col-6">
              <div>
                <div>
                  Đánh giá người dùng :
                  <strong>Chưa có đánh giá nào!</strong>
                </div>
                <div class="mb-0">
                  <div class="d-inline-block">Số lượng người đánh giá :</div>
                  <span>
                    <strong>0 người</strong>
                  </span>
                </div>
                <div>
                  <div class="d-inline-block">Thuộc danh mục :</div>
                  <span>
                    <strong>Máy tính</strong>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-4">
              <div class="form-group">
                <label for="price">Giá (VND)</label>
                <input
                  class="form-control"
                  type="number"
                  id="price"
                  value="1"
                  readonly
                />
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label for="stock">Số lượng </label>
                <input
                  class="form-control"
                  type="number"
                  id="stock"
                  value="3"
                  readonly
                />
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label for="discount">Giảm giá (%)</label>
                <input
                  class="form-control"
                  type="number"
                  id="discount"
                  value="10"
                  readonly
                />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label for="description">Mô tả sản phẩm</label>
              <textarea
                name="description"
                class="description"
                id="description"
                rows="10"
                class=""
                readonly
              >
Lorem ipsum dolor sit amet consectetur, adipisicing elit. Aperiam maiores reprehenderit officia, corporis excepturi dolor pariatur esse non quo eveniet velit minima quas distinctio reiciendis rem nihil. Obcaecati, voluptatem consequatur!
                        </textarea
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
