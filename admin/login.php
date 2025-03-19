<?php
session_start();
require "db_connect.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json"); // Đảm bảo trả về JSON

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM User WHERE isAdmin = true AND email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email,$password);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    // admin mật khẩu mặc định 123456

    if ($admin) {
        $_SESSION["admin"] = $admin["name"];
        echo json_encode(["status" => "success", "message" => "Đăng nhập thành công!"]);
    } else {
        echo json_encode(["status" => "danger", "message" => "Email hoặc mật khẩu không chính xác!"]);
    }
    exit();
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
    <header >
      <div class="inner-logo">
        <img src="../img/logo.png" alt="Logo" srcset="" />
      </div>
    </header>
    <div class="alert alert-show announce" role="alert"></div>
    <div class="body">
      <div class="login-container">
        <h2 class="login-title">Đăng nhập</h2>
        <form class="login-form" action="login.php" method="POST">
          <div>
            <label for="username">Tài khoản</label>
            <div class="input-wrapper">
              <i class="fas fa-user"></i>
              <input required type="email" id="username" name="email" placeholder="Nhập tài khoản" />
            </div>
          </div>
          <div>
            <label for="password">Mật khẩu</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input
                required
                type="password"
                id="password"
                name ="password"
                placeholder="Nhập mật khẩu"
              />
            </div>
          </div>
          <button type="submit" class="login-button">Xác nhận</button>
        </form>
      </div>
    </div>
  </body>
  <script src="../js/announcement.js"></script>
  <script>
  document.querySelector(".login-form").addEventListener("submit", function (event) {
      event.preventDefault(); // Ngăn reload trang

      const formData = new FormData(this);

      fetch("login.php", {
          method: "POST",
          body: formData,
      })
      .then(response => response.json()) 
      .then(data => {
          if (data.status === "danger") {
              showAnnouncement("danger", data.message); // Hiển thị thông báo
          } else {
              window.location.href = "/admin/index.php"; // Chuyển hướng nếu đăng nhập thành công
          }
      })
      .catch(error => {
          console.error("Lỗi:", error);
          showAnnouncement("danger", "Lỗi kết nối server!");
      });
  });
</script>

</html>
