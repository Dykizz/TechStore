// Lấy tất cả các thẻ <li> trong danh sách
const navItems = document.querySelectorAll("#responsive-nav .main-nav li");

// Lặp qua từng <li> và thêm sự kiện click
navItems.forEach((item) => {
  item.addEventListener("click", () => {
    // Xóa class "active" khỏi tất cả các <li>
    navItems.forEach((el) => el.classList.remove("active"));

    // Thêm class "active" vào thẻ <li> được click
    item.classList.add("active");
  });
});
