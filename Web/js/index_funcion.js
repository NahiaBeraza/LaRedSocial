(() => { // IIFE: se ejecuta al cargar y no deja variables colgando en window
  const modal = document.getElementById("commentsModal"); // Modal completo de comentarios
  const closeBtn = document.getElementById("closeModal"); // Botón de cerrar (la X)
  const list = document.getElementById("modalComments"); // Contenedor donde se pintan los comentarios
  const body = document.getElementById("modalBody"); // Zona con scroll dentro del modal
  const form = document.getElementById("modalCommentForm"); // Formulario para enviar comentario
  const postIdInput = document.getElementById("modalPostId"); // Input hidden con el id del post
  const textInput = document.getElementById("modalTexto"); // Input/textarea del comentario

  if (!modal || !closeBtn || !list || !body || !form || !postIdInput || !textInput) return; // Si falta algo en el HTML, no inicializo nada

  let currentPostId = 0; // Aquí guardo el post que tengo abierto ahora en el modal

  function openModal() { // Abre el modal
    modal.classList.add("open"); // Clase que lo muestra
    modal.setAttribute("aria-hidden", "false"); // Accesibilidad: ahora está visible
    document.body.style.overflow = "hidden"; // Bloqueo el scroll del body cuando el modal está abierto
  }

  function closeModal() { // Cierra el modal y limpia estado
    modal.classList.remove("open"); // Quita clase para ocultar
    modal.setAttribute("aria-hidden", "true"); // Accesibilidad: vuelve a estar oculto
    document.body.style.overflow = ""; // Devuelvo el scroll normal al body
    currentPostId = 0; // Reseteo el post actual
    postIdInput.value = ""; // Limpio el hidden del post id
    textInput.value = ""; // Limpio el texto del comentario
    list.innerHTML = `<div class="c-item">Cargando…</div>`; // Dejo el placeholder por defecto
  }

  closeBtn.addEventListener("click", closeModal); // Click en la X -> cierro modal

  modal.addEventListener("click", (e) => { // Click fuera del contenido (en el fondo) -> cierro
    if (e.target === modal) closeModal(); // Solo si he clicado en el fondo, no dentro de la tarjeta
  });

  document.addEventListener("keydown", (e) => { // Escape -> cierro modal si está abierto
    if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
  });

  function loadComments(postId) { // Carga los comentarios del post y los pinta en el modal
    list.innerHTML = `<div class="c-item">Cargando…</div>`; // Mensaje mientras carga

    // Uso ruta relativa para que funcione igual aunque la carpeta cambie mayúsculas/minúsculas
    fetch(`php/comentar.php?list=1&id_publicacion=${encodeURIComponent(postId)}`, { // Pido al backend el listado en HTML
      cache: "no-store", // Sin cache para que salga lo último
      headers: { "X-Requested-With": "XMLHttpRequest" } // Marco que esto es AJAX
    })
      .then(r => {
        if (!r.ok) throw new Error("HTTP " + r.status); // Si falla, lo mando al catch
        return r.text(); // Este endpoint devuelve HTML
      })
      .then(html => {
        list.innerHTML = (html && html.trim())
          ? html // Si viene HTML, lo pongo tal cual
          : `<div class="c-item">No hay comentarios todavía.</div>`; // Si viene vacío, aviso
        body.scrollTop = body.scrollHeight; // Bajo al final para ver lo último
      })
      .catch(err => {
        console.error("Error cargar comentarios:", err); // Log para depurar
        list.innerHTML = `<div class="c-item">Error cargando comentarios.</div>`; // Mensaje al usuario
      });
  }

  // Abrir modal
  document.addEventListener("click", (e) => { // Delegación: escucho clicks globalmente
    const btn = e.target.closest(".js-open-comments"); // Solo me interesan los botones de abrir comentarios
    if (!btn) return; // Si no he clicado en uno, salgo

    const postId = parseInt(btn.dataset.post || "0", 10); // Saco el id del post desde data-post
    if (!postId) return; // Si no hay id válido, no hago nada

    currentPostId = postId; // Guardo el post actual para usarlo al enviar
    postIdInput.value = String(postId); // Lo meto en el hidden
    textInput.value = ""; // Limpio el texto por si había algo

    openModal(); // Abro el modal
    loadComments(postId); // Cargo comentarios de ese post
    setTimeout(() => textInput.focus(), 30); // Pongo el cursor en el input cuando ya está abierto
  });

  // Enviar comentario AJAX
  form.addEventListener("submit", (e) => {
    e.preventDefault(); // Evito el submit normal (no quiero recargar página)

    const postId = currentPostId; // Post al que voy a comentar
    const texto = (textInput.value || "").trim(); // Texto del comentario sin espacios extra
    if (!postId || !texto) return; // Si no hay post o texto, no envío nada

    const fd = new FormData(form); // Creo FormData con los campos del form
    fd.set("id_publicacion", String(postId)); // Aseguro el id del post
    fd.set("texto", texto); // Aseguro el texto limpio
    fd.set("ajax", "1"); // Flag para que el backend sepa que esto viene por AJAX

    fetch(`php/comentar.php`, { // Envío el comentario al backend (ruta relativa)
      method: "POST",
      body: fd,
      cache: "no-store",
      headers: { "X-Requested-With": "XMLHttpRequest" } // Marco AJAX
    })
      .then(r => r.json()) // Espero un JSON de respuesta
      .then(data => {
        if (!data.ok) { // Si el backend dice que no ha ido bien
          alert(data.error || "No se pudo comentar"); // Muestro el error
          return;
        }

        list.insertAdjacentHTML("beforeend", data.html); // Inserto el HTML del comentario nuevo al final sin recargar todo

        const countEl = document.getElementById(`ccount-${postId}`); // Cojo el contador de comentarios del post en la página
        if (countEl) countEl.textContent = String(data.count); // Actualizo el número (viene del backend)

        textInput.value = ""; // Limpio el input
        textInput.focus(); // Devuelvo foco al input
        body.scrollTop = body.scrollHeight; // Bajo al final para ver el comentario recién añadido
      })
      .catch(err => {
        console.error("Error comentar:", err); // Log para depurar
        alert("Error de red comentando"); // Mensaje simple al usuario
      });
  });
})();
