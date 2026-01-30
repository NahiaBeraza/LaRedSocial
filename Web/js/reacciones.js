document.addEventListener("click", (e) => { // Escucho todos los clicks de la página (delegación)
  const btn = e.target.closest(".reac-btn"); // Botón principal de reacción (el emoji grande)
  const anyMenu = e.target.closest(".reac-menu"); // Compruebo si el click fue dentro de algún menú de reacciones

  // Si hago click fuera de un botón y fuera de cualquier menú,
  // cierro todos los menús de reacciones abiertos
  if (!btn && !anyMenu) {
    document.querySelectorAll(".reac-menu.open").forEach(m => m.classList.remove("open"));
    return;
  }

  if (!btn) return; // Si no he clicado en un botón de reacción, no hago nada más

  const postId = btn.dataset.post; // Id del post asociado a este botón
  const menu = document.getElementById(`reac-menu-${postId}`); // Menú de reacciones de ese post
  if (!menu) return; // Si no existe el menú, salgo

  // Cierro cualquier otro menú que esté abierto (para que solo haya uno a la vez)
  document.querySelectorAll(".reac-menu.open").forEach(m => {
    if (m !== menu) m.classList.remove("open");
  });

  // Abro o cierro el menú de este post (toggle)
  menu.classList.toggle("open");
});
