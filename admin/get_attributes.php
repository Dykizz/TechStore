<?php
$conn = new mysqli("localhost", "root", "", "techstore");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["categoryId"])) {
    $categoryId = $_POST["categoryId"];

    $sql = "SELECT attributeId, name FROM Attribute WHERE categoryId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $attributes = [];
    while ($row = $result->fetch_assoc()) {
        $attributes[] = $row;
    }

    echo json_encode($attributes); // Trả về JSON
    exit;
}
?>
