$(document).ready(function () {
  $("#category").change(function () {
    var categoryId = $(this).val(); // Lấy ID danh mục được chọn
    console.log("click");
    if (categoryId) {
      $.ajax({
        url: "get_attributes.php", // API lấy thuộc tính theo categoryId
        type: "POST",
        data: { categoryId: categoryId },
        dataType: "json",
        success: function (data) {
          var html =
            '<div class="card mb-3"><div class="card-header">Thông số kỹ thuật</div><div class="card-body">';

          data.forEach(function (attribute) {
            html += '<div class="attribute-row">';
            html += "<label>" + attribute.name + "</label>";
            html +=
              '<input type="hidden" name="attributeId[]" value="' +
              attribute.attributeId +
              '">';
            html +=
              '<input class="form-control mb-2" name="values[]" type="text">';
            html += "</div>";
          });

          html += "</div></div>";
          $("#specifications").html(html); // Cập nhật vào form
        },
      });
    } else {
      $("#specifications").html(""); // Xóa nếu không chọn danh mục
    }
  });
});
