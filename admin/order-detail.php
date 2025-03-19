<?php 
require "auth.php";
require "db_connect.php";

$orderId = isset($_GET['orderId']) ? (int)$_GET['orderId'] : 0;
$orderCode = isset($_GET['orderCode']) ? $_GET['orderCode'] : '';

if (!$orderId && !$orderCode) {
    die("Thiếu orderId hoặc orderCode");
}

// Truy vấn lấy thông tin đơn hàng
$sql = "SELECT 
            o.orderId, o.orderCode, o.orderDate, o.status, o.totalAmount, o.statusUpdatedAt,
            o.paymentMethod, o.cardHolderName, o.cardNumber, o.cardExpiryDate,
            o.shippingAddressId, o.customShippingAddress, u.userId,
            u.name AS customerName, u.email AS customerEmail , u.phoneNumber
        FROM Orders o
        JOIN User u ON o.userId = u.userId
        WHERE o.orderId = ? OR o.orderCode = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $orderId, $orderCode);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();

if (!$order) {
    die("Không tìm thấy đơn hàng!");
}

// Truy vấn lấy danh sách sản phẩm trong đơn hàng
$sqlDetails = "SELECT 
                od.orderDetailId, od.productId, p.name AS productName, 
                od.quantity, od.price, od.subtotal , p.image
              FROM OrderDetail od
              JOIN Product p ON od.productId = p.productId
              WHERE od.orderId = ?";

$stmt = $conn->prepare($sqlDetails);
$stmt->bind_param("i", $order['orderId']);
$stmt->execute();
$detailsResult = $stmt->get_result();
$orderDetails = $detailsResult->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();


function formatPhoneNumber($phoneNumber) {
    // Xóa tất cả ký tự không phải số
    $digits = preg_replace('/\D/', '', $phoneNumber);

    // Kiểm tra độ dài số điện thoại (10 số)
    if (strlen($digits) === 10) {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3', $digits);
    }

    return $phoneNumber; // Trả về nguyên bản nếu không phải 10 số
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <div class="inner-logo">
            <a href="./index.php">
                <img src="../img/logo.png" alt="Logo" srcset="">
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
            <div href="./login.html" class="btn-logout">
                <a href="./login.html">
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
              <i class="fa-solid fa-medal"></i>
            </div>
            <a href="./top5-client.php">Top 5 khách hàng</a>
          </li>
    </ul>
    <div class="content">
        <h2>Chi tiết đơn hàng</h2>

        <div class="card">
            <div class="card-header">Thông tin chi tiết đơn hàng</div>
            <div class="card-body">
                <p>Mã đơn hàng : 
                    <strong><?= $order['orderCode']?></strong>
                </p>
                <?php
                $statusMap = [
                    'Pending' => ['text' => 'Chưa xử lý', 'class' => 'info'],
                    'Confirmed' => ['text' => 'Đã xác nhận', 'class' => 'secondary'],
                    'Delivered' => ['text' => 'Giao thành công', 'class' => 'success'],
                    'Cancelled' => ['text' => 'Đã hủy', 'class' => 'danger'],
                ];

                $status = $order['status'] ?? 'Pending'; // Mặc định nếu không có giá trị
                $statusText = $statusMap[$status]['text'] ?? 'Không xác định';
                $statusClass = $statusMap[$status]['class'] ?? 'secondary';
                ?>

                <p>Tình trạng đơn hàng:
                    <span class="badge badge-<?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span>
                    lúc <strong><?= $order['statusUpdatedAt'] ?></strong>
                </p>

                <p>Thời gian đặt :
                    <strong><?= $order['orderDate'] ?></strong>
                </p>

                <p>Tên tài khoản đặt hàng :
                    <strong >
                        <a class="text-primary" href="./client-detail.php?userId=<?=$order['userId']?>"><?=$order['customerName']?></a>
                    </strong>
                </p>

                <div class="section-title">
                    <h4 class="title">Thông tin người nhận</h4>
                </div>

                <p>Tên người nhận :
                    <strong><?=$order['customerName']?></strong>
                </p>



                <p>Số điện thoại :
                    <strong><?= formatPhoneNumber($order['phoneNumber']) ?></strong>
                </p>
                <div class="form-group">
                    <label for="address">Địa chỉ giao hàng : </label>
                    <textarea class="form-control" name="address" id="address" rows="3" disabled><?= $order['customShippingAddress']?>
                    </textarea>
                </div>
                <div class="form-group">
                    <label for="note">Ghi chú : </label>
                    <textarea class="form-control" name="note" id="note" rows="3" disabled>Gọi trước khi giao
                    </textarea>
                </div>

                <div class="section-title">
                    <h4 class="title">phương thức thanh toán</h4>
                </div>

                <?php
                $paymentMethods = [
                    'CASH' => 'Tiền mặt',
                    'BANK_TRANSFER' => 'Chuyển khoản ngân hàng',
                    'CREDIT_CARD' => 'Thẻ tín dụng'
                ];

                $paymentText = $paymentMethods[$order['paymentMethod']] ?? 'Không xác định';
                ?>

                <p>Thanh toán bằng: <strong><?= htmlspecialchars($paymentText) ?></strong></p>


                <?php if ($order['paymentMethod'] !== 'CASH') : ?>
                    <p>Chủ thẻ : <strong><?= htmlspecialchars($order['cardHolderName'] ?? 'N/A') ?></strong></p>
                    <p>Số thẻ : **** **** ****  <strong><?= substr($order['cardNumber'], -4) ?></strong></p>
                    <p>Hạn thẻ : <strong><?= htmlspecialchars($order['cardExpiryDate'] ?? 'N/A') ?></strong></p>
                <?php endif; ?>

                <div class="section-title">
                    <h4 class="title ">Chi tiết hóa đơn</h4>
                </div>
                <table class="table table-hover text-center">
                    <thead>
                        <th class="text-left">Tên sản phẩm</th>
                        <th>Hình ảnh</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                    </thead>
                    <tbody>
                        <?php foreach ($orderDetails as $index => $item) : ?>
                            <tr>
                                <td class="text-left"><?= htmlspecialchars($item['productName']) ?></td>
                                <td> <img src="../<?= $item['image'] ?>" alt="Product image" style="width: auto; height: 60px;">
                                </td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['price'], 0, ',', '.') ?> VND</td>
                                <td><?= number_format($item['subtotal'], 0, ',', '.') ?> VND</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p>Tổng tiền : 
                    <strong><?= number_format($order['totalAmount'], 0, ',', '.') ?> VND</strong>
                </p>

                </select>

            </div>
        </div>




    </div>
</body>

</html>