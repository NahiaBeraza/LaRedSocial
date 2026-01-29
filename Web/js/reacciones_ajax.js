document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".reac-opt");
  if (!btn) return;

  const form = btn.closest("form");
  if (!form) return;

  e.preventDefault();
  e.stopPropagation();

  if (btn.dataset.loading === "1") return;
  btn.dataset.loading = "1";

  // FormData del form (incluye id_publicacion + tipo)
  const fd = new FormData(form);
  fd.set("ajax", "1");

  // Importante: asegúrate de que el hidden "tipo" está
  if (!fd.get("tipo")) {
    alert("Falta el campo 'tipo' en el form de reacción.");
    btn.dataset.loading = "0";
    return;
  }

  try {
    const res = await fetch(form.action, {
      method: "POST",
      body: fd,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      cache: "no-store",
    });

    const text = await res.text();

    let data;
    try {
      data = JSON.parse(text);
    } catch {
      console.error("Respuesta NO JSON (esto pasa si PHP no corre o hay redirect/HTML):", text);
      alert("La reacción falló: el servidor no devolvió JSON. Estás abriendo con Live Server (3000) o el PHP redirige.");
      return;
    }

    if (!data.ok) {
      alert(data.error || "No se pudo reaccionar");
      return;
    }

    const postId = data.postId;

    // Tu botón principal NO tiene id reac-main-..., tiene clase reac-btn.
    // Lo buscamos por data-post:
    const mainBtn = document.querySelector(`.reac-btn[data-post="${postId}"]`);
    if (mainBtn) mainBtn.textContent = data.iconoPrincipal;

    // Actualiza el resumen (tu HTML NO tiene ids reac-count/reac-sum, está dentro .post-meta)
    // Si quieres actualizar el resumen sin recargar, necesitas un id. Te lo dejo opcional:
    // <span id="reac-meta-<?= $postId ?>"></span>
    const meta = document.getElementById(`reac-meta-${postId}`);
    if (meta && data.resumenTexto != null) meta.textContent = data.resumenTexto;

    const totalEl = document.getElementById(`reac-total-${postId}`);
    if (totalEl && data.total != null) totalEl.textContent = data.total;


    // Cierra el menú
    const menu = document.getElementById(`reac-menu-${postId}`);
    if (menu) menu.classList.remove("open");

  } catch (err) {
    console.error(err);
    alert("Error de red al reaccionar");
  } finally {
    btn.dataset.loading = "0";
  }
});
