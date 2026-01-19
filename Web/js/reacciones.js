// js/reacciones.js
document.addEventListener("DOMContentLoaded", () => {
  function cerrarTodo() {
    document.querySelectorAll(".reac-menu").forEach((m) => m.classList.remove("open"));
  }

  document.addEventListener("click", () => cerrarTodo());

  document.querySelectorAll(".reac-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      const postId = btn.getAttribute("data-post");
      const menu = document.getElementById("reac-menu-" + postId);
      if (!menu) return;

      const estaba = menu.classList.contains("open");
      cerrarTodo();
      if (!estaba) menu.classList.add("open");
    });
  });

  document.querySelectorAll(".reac-menu").forEach((menu) => {
    menu.addEventListener("click", (e) => e.stopPropagation());
  });
});
