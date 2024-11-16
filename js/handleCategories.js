const selectElement = document.getElementById("category");
const specifications = document.getElementById("specifications");
const phone = `<div class="card mb-3">
  <div class="card-header">Thông số kĩ thuật của điện thoại</div>
  <div class="card-body">
    <label for="color">Màu sắc</label>
    <input
      class="form-control mb-2"
      name="color"  
      id="color"
      type="text"
    />
    <label for="system">Hệ điều hành</label>
    <input
      class="form-control mb-2"
      name="system"
      id="system"
      type="text"
    />
    <label for="cpu">Chip xử lý (CPU)</label>
    <input
      class="form-control mb-2"
      name="cpu"
      id="cpu"
      type="text"
    />
    <label for="speed-cpu">Tốc độ CPU</label>
    <input
      class="form-control mb-2"
      name="speed-cpu"
      id="speed-cpu"
      type="text"
    />
    <label for="gpu">Chip đồ họa (GPU)</label>
    <input
      class="form-control mb-2"
      name="gpu"
      id="gpu"
      type="text"
    />
    <label for="ram">RAM</label>
    <input
      class="form-control mb-2"
      name="ram"
      id="ram"
      type="text"
    />
  </div>
</div>`;
const laptop = `<div class="card mb-3">
            <div class="card-header">Thông số kĩ thuật của máy tính</div>
            <div class="card-body">
              <label for="CPU">Chip xử lý (CPU) </label>
              <input
                class="form-control mb-2"
                name="cpu"
                id="CPU"
                type="text"
              />

              <label for="GPU">Chip đồ họa (GPU)</label>
              <input
                class="form-control mb-2"
                name="gpu"
                id="GPU"
                type="text"
              />

              <label for="RAM">RAM </label>
              <input
                class="form-control mb-2"
                name="ram"
                id="RAM"
                type="text"
              />

              <label for="storage">Ổ cứng (Bộ nhớ lưu trữ)</label>
              <input
                class="form-control mb-2"
                name="storage"
                id="storage"
                type="text"
              />

              <label for="screen">Kích thước màn hình</label>
              <input
                class="form-control mb-2"
                name="screen"
                id="screen"
                type="text"
              />

              <label for="os">Hệ điều hành</label>
              <input class="form-control mb-2" name="os" id="os" type="text" />
            </div>
          </div>`;
const camera = `<div class="card mb-3">
            <div class="card-header">Thông số kĩ thuật của máy ảnh</div>
            <div class="card-body">
              <label for="type-sensor">Loại cảm biến</label>
              <input
                class="form-control mb-2"
                name="type-sensor"
                id="type-sensor"
                type="text"
              />
              <label for="focus-type">Loại lấy nét</label>
              <input
                class="form-control mb-2"
                name="focus-type"
                id="focus-type"
                type="text"
              />
              <label for="memory-card">Khe cắm thẻ nhớ</label>
              <input
                class="form-control mb-2"
                name="memory-card"
                id="memory-card"
                type="text"
              />
              <label for="flash-mode">Chế độ Flash</label>
              <input
                class="form-control mb-2"
                name="flash-mode"
                id="flash-mode"
                type="text"
              />
              <label for="screen-size">Kích thước màn hình</label>
              <input
                class="form-control mb-2"
                name="screen-size"
                id="screen-size"
                type="text"
              />
              <label for="battery-type">Loại pin</label>
              <input
                class="form-control mb-2"
                name="battery-type"
                id="battery-type"
                type="text"
              />
            </div>
          </div>`;
const accessory = `<div class="card mb-3">
            <div class="card-header">Thông số kĩ thuật của linh kiện</div>
            <div class="card-body">
              <label for="power">Công suất</label>
              <input
                class="form-control mb-2"
                name="power"
                id="power"
                type="text"
              />

              <label for="power-source">Nguồn điện sử dụng</label>
              <input
                class="form-control mb-2"
                name="power-source"
                id="power-source"
                type="text"
              />

              <label for="connector-type">Cổng kết nối</label>
              <input
                class="form-control mb-2"
                name="connector-type"
                id="connector-type"
                type="text"
              />

              <label for="compatibility">Tương thích thiết bị</label>
              <input
                class="form-control mb-2"
                name="compatibility"
                id="compatibility"
                type="text"
              />

              <label for="durability">Độ bền</label>
              <input
                class="form-control mb-2"
                name="durability"
                id="durability"
                type="text"
              />

              <label for="warranty">Bảo hành</label>
              <input
                class="form-control mb-2"
                name="warranty"
                id="warranty"
                type="text"
              />
            </div>
          </div>`;

selectElement.addEventListener("change", () => {
  switch (selectElement.value) {
    case "phone":
      specifications.innerHTML = phone;
      break;
    case "laptop":
      specifications.innerHTML = laptop;
      break;
    case "camera":
      specifications.innerHTML = camera;
      break;
    case "accessory":
      specifications.innerHTML = accessory;
      break;
  }
});
