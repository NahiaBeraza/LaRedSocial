document.addEventListener("click", (e) => {
  const btn = e.target.closest(".reac-btn");
  const anyMenu = e.target.closest(".reac-menu");

  // click fuera => cerrar todo
  if (!btn && !anyMenu) {
    document.querySelectorAll(".reac-menu.open").forEach(m => m.classList.remove("open"));
    return;
  }

  if (!btn) return;

  const postId = btn.dataset.post;
  const menu = document.getElementById(`reac-menu-${postId}`);
  if (!menu) return;

  // cerrar otros
  document.querySelectorAll(".reac-menu.open").forEach(m => {
    if (m !== menu) m.classList.remove("open");
  });

  menu.classList.toggle("open");
});
