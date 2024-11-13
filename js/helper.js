// Thêm sự kiện cho các nút bấm thông báo
const elements = document.querySelectorAll(".btn-announce");

const element_anounce = document.querySelector(".announce");
if (elements) {
  elements.forEach((element) => {
    element.addEventListener("click", () => {
      if (element_anounce) {
        element_anounce.classList.add("active");
        const type = element.getAttribute("type-announce");
        const mess = element.getAttribute("message");
        element_anounce.setAttribute("type-announce", type);
        element_anounce.textContent = mess;
        setTimeout(() => {
          element_anounce.classList.remove("active");
        }, 5000);
      }
    });
  });
}
/* 
-Thêm <div class="alert alert-success alert-show announce" role="alert"></div>; vào trang muốn thông báo
-Thêm class btn-announce, type-announce = 1 trong 3 loại [success , danger , warning]
-Thêm message = "đoạn mess muốn thông báo"

*/
