document.addEventListener("click", async (e) => { // Escucho todos los clicks de la página (delegación)
  const btn = e.target.closest(".reac-opt"); // Busco si el click fue en un botón de reacción
  if (!btn) return; // Si no es una reacción, no hago nada

  const form = btn.closest("form"); // Cada reacción está dentro de un form
  if (!form) return; // Si por alguna razón no hay form, salgo

  e.preventDefault();   // Evito que el form se envíe de forma normal
  e.stopPropagation();  // Evito que el click burbujee (por ejemplo, que cierre menús)

  // Evita que el usuario haga doble click y envíe varias veces
  if (btn.dataset.loading === "1") return;
  btn.dataset.loading = "1"; // Marco este botón como “enviando”

  // Creo el FormData a partir del form (ya incluye id_publicacion y tipo)
  const fd = new FormData(form);
  fd.set("ajax", "1"); // Flag para que el backend sepa que es una petición AJAX

  // Comprobación defensiva: el campo "tipo" debería existir siempre
  if (!fd.get("tipo")) {
    alert("Falta el campo 'tipo' en el form de reacción.");
    btn.dataset.loading = "0";
    return;
  }

  try {
    // Envío la reacción al backend usando fetch
    const res = await fetch(form.action, {
      method: "POST",
      body: fd,
      headers: { "X-Requested-With": "XMLHttpRequest" }, // Marco que es AJAX
      cache: "no-store", // Evito cache para que la respuesta sea siempre actual
    });

    const text = await res.text(); // Leo la respuesta como texto primero

    let data;
    try {
      data = JSON.parse(text); // Intento convertir la respuesta a JSON
    } catch {
      // Si falla aquí, normalmente es porque el PHP no se está ejecutando
      // (Live Server, redirecciones, errores HTML, etc.)
      console.error("Respuesta NO JSON (esto pasa si PHP no corre o hay redirect/HTML):", text);
      alert("La reacción falló: el servidor no devolvió JSON. Estás abriendo con Live Server (3000) o el PHP redirige.");
      return;
    }

    // Si el backend responde con ok = false
    if (!data.ok) {
      alert(data.error || "No se pudo reaccionar");
      return;
    }

    const postId = data.postId; // Id del post que viene de vuelta desde el backend

    // Actualizo el botón principal de reacción (el grande)
    // No tiene id fijo, así que lo busco por data-post
    const mainBtn = document.querySelector(`.reac-btn[data-post="${postId}"]`);
    if (mainBtn) mainBtn.textContent = data.iconoPrincipal; // Cambio el emoji principal

    // Actualizo el resumen de reacciones (LOVE · LIKE · etc.)
    const meta = document.getElementById(`reac-meta-${postId}`);
    if (meta && data.resumenTexto != null) meta.textContent = data.resumenTexto;

    // Actualizo el total de reacciones
    const totalEl = document.getElementById(`reac-total-${postId}`);
    if (totalEl && data.total != null) totalEl.textContent = data.total;

    // Cierro el menú de reacciones del post
    const menu = document.getElementById(`reac-menu-${postId}`);
    if (menu) menu.classList.remove("open");

  } catch (err) {
    // Error de red o excepción inesperada
    console.error(err);
    alert("Error de red al reaccionar");
  } finally {
    // Pase lo que pase, desbloqueo el botón
    btn.dataset.loading = "0";
  }
});
