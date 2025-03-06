// Xử lý hiển thị khi upload hình ảnh
document.addEventListener("DOMContentLoaded", function () {
  const inputFile = document.getElementById("img-input");
  const previewImg = document.querySelector(".inner-img img");
  const cancelBtn = document.querySelector(".inner-img .cancel");
  inputFile.addEventListener("change", function (event) {
    const file = event.target.files[0]; // Lấy file đầu tiên được chọn

    if (file) {
      const reader = new FileReader();

      reader.onload = function (e) {
        previewImg.classList.remove("d-none");
        previewImg.src = e.target.result; // Cập nhật src của ảnh
      };

      reader.readAsDataURL(file); // Chuyển file thành Data URL
    }
  });

  cancelBtn.addEventListener("click", function () {
    previewImg.classList.add("d-none");
    inputFile.value = ""; // Xóa giá trị của input file
    document.querySelector(".custom-file-label").textContent =
      "Chọn hình ảnh từ máy";
  });

  $("#img-input").on("change", function () {
    let fileName = $(this).val().split("\\").pop(); // Lấy tên file
    $(this).next(".custom-file-label").addClass("selected").html(fileName);
  });
});
