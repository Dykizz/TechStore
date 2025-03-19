function showAnnouncement(type, message) {
  const elementAnnounce = document.querySelector(".announce");
  if (elementAnnounce) {
    elementAnnounce.classList.add("active", `alert-${type}`);
    elementAnnounce.innerHTML = message;

    setTimeout(() => {
      elementAnnounce.classList.remove("active", `alert-${type}`);
    }, 4000);
  }
}
