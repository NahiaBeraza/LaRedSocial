(() => { // IIFE: esto se ejecuta solo al cargar el archivo y no ensucia el scope global
  const modal = document.getElementById("commentsModal"); // Contenedor del modal de comentarios
  const closeBtn = document.getElementById("closeModal"); // Botón para cerrar el modal
  const list = document.getElementById("modalComments"); // Donde se pintan los comentarios (HTML que viene del PHP)
  const body = document.getElementById("modalBody"); // Parte scrollable del modal (para bajar al final)
  const form = document.getElementById("modalCommentForm"); // Formulario para enviar comentario
  const postIdInput = document.getElementById("modalPostId"); // Input hidden con el id del post
  const textInput = document.getElementById("modalTexto"); // Textarea/input donde escribo el comentario

  if (!modal || !closeBtn || !list || !body || !form || !postIdInput || !textInput) return; // Si falta algún elemento, no hago nada (evito errores)

  // Guard para no registrar eventos dos veces si este JS se incluye dos veces
  if (window.__commentsModalBound) return; // Si ya estaba marcado, salgo
  window.__commentsModalBound = true; // Marco que ya lo he inicializado

  let currentPostId = 0; // Aquí guardo el post que tengo abierto ahora en el modal
  let isSubmitting = false; // Para evitar doble envío si el usuario hace click varias veces

  function openModal() { // Abre el modal
    modal.classList.add("open"); // Clase que normalmente lo hace visible
    modal.setAttribute("aria-hidden", "false"); // Accesibilidad: ya no está oculto
    document.body.classList.add("modal-open"); // Normalmente para bloquear scroll del body
  }

  function closeModal() { // Cierra el modal y resetea todo
    modal.classList.remove("open"); // Lo oculta
    modal.setAttribute("aria-hidden", "true"); // Accesibilidad: vuelve a estar oculto
    document.body.classList.remove("modal-open"); // Devuelvo el scroll normal a la página

    currentPostId = 0; // Ya no hay post seleccionado
    postIdInput.value = ""; // Limpio el hidden
    textInput.value = ""; // Limpio el texto del comentario

    list.innerHTML = `<div class="c-item">Cargando…</div>`; // Dejo un placeholder por si se vuelve a abrir rápido
    isSubmitting = false; // Reseteo el bloqueo de envío

    const btn = form.querySelector('button[type="submit"]'); // Cojo el botón de enviar del form
    if (btn) btn.disabled = false; // Por si se quedó desactivado, lo vuelvo a activar
  }

  closeBtn.addEventListener("click", closeModal); // Click en la X -> cierro

  modal.addEventListener("click", (e) => { // Click fuera de la tarjeta (en el fondo) -> cierro
    if (e.target === modal) closeModal(); // Solo si el click es en el contenedor, no dentro del contenido
  });

  document.addEventListener("keydown", (e) => { // Si pulso Escape y está abierto -> cierro
    if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
  });

  async function loadComments(postId) { // Carga comentarios del post y los pinta dentro del modal
    list.innerHTML = `<div class="c-item">Cargando…</div>`; // Mensaje mientras carga

    try {
      const r = await fetch(`php/comentar.php?list=1&id_publicacion=${encodeURIComponent(postId)}`, { // Llamo al PHP que devuelve la lista en HTML
        cache: "no-store", // Para que no use caché y siempre traiga lo último
        headers: { "X-Requested-With": "XMLHttpRequest" } // Marca que esto viene de AJAX (por si el PHP cambia comportamiento)
      });

      if (!r.ok) { // Si no es 200-299, muestro error
        list.innerHTML = `<div class="c-item">Error cargando comentarios (HTTP ${r.status}).</div>`;
        return;
      }

      const html = await r.text(); // Lo que devuelve el PHP es HTML
      list.innerHTML = (html && html.trim())
        ? html // Si viene algo, lo pinto tal cual
        : `<div class="c-item">No hay comentarios todavía.</div>`; // Si viene vacío, pongo texto

      body.scrollTop = body.scrollHeight; // Bajo al final para ver el comentario más reciente
    } catch {
      list.innerHTML = `<div class="c-item">Error cargando comentarios.</div>`; // Error de red o excepción
    }
  }

  // Abrir modal
  document.addEventListener("click", (e) => { // Delegación: escucho clicks en toda la página
    const btn = e.target.closest(".js-open-comments"); // Busco si el click fue en un botón de abrir comentarios
    if (!btn) return; // Si no es uno de esos botones, ignoro

    const postId = parseInt(btn.dataset.post || "0", 10); // Saco el id del post desde data-post
    if (!postId) return; // Si es 0 o inválido, no hago nada

    currentPostId = postId; // Guardo el post actual
    postIdInput.value = String(postId); // Lo meto en el hidden para que el form lo tenga
    textInput.value = ""; // Limpio el texto por si había algo escrito de antes

    openModal(); // Abro el modal
    loadComments(postId); // Cargo los comentarios del post

    setTimeout(() => textInput.focus(), 30); // Le doy foco al input cuando ya está abierto (pequeño delay para que no falle)
  });

  // Enviar comentario sin recargar página
  form.addEventListener("submit", async (e) => {
    e.preventDefault(); // Evito el submit normal del navegador (no quiero recargar la página)

    if (isSubmitting) return; // Si ya estoy enviando, corto aquí para evitar duplicados
    isSubmitting = true; // Bloqueo envío

    const btn = form.querySelector('button[type="submit"]'); // Cojo el botón submit
    if (btn) btn.disabled = true; // Lo desactivo para que no se pueda spamear

    const postId = currentPostId; // Post al que voy a comentar
    const texto = (textInput.value || "").trim(); // Texto del comentario sin espacios extra

    if (!postId || !texto) { // Si no hay post o el comentario está vacío
      isSubmitting = false; // Quito bloqueo
      if (btn) btn.disabled = false; // Reactivo botón
      return;
    }

    const fd = new FormData(form); // Creo el FormData con los campos del form
    fd.set("id_publicacion", String(postId)); // Aseguro el postId correcto (por si el hidden estaba vacío)
    fd.set("texto", texto); // Aseguro el texto limpio
    fd.set("ajax", "1"); // Flag para que el PHP sepa que esto es una petición AJAX

    fd.set( // Token para identificar este envío (sirve para evitar duplicados si el backend lo usa)
      "client_token",
      (crypto?.randomUUID?.() || (Date.now() + "-" + Math.random()))
    );

    try {
      const r = await fetch("php/comentar.php", { // Envío el comentario al backend
        method: "POST",
        body: fd,
        cache: "no-store",
        headers: { "X-Requested-With": "XMLHttpRequest" } // Otra vez, marco que esto es AJAX
      });

      const data = await r.json(); // Espero que el PHP devuelva JSON con { ok: true/false, ... }

      if (!data.ok) { // Si el backend dice que no ha ido bien
        alert(data.error || "No se pudo comentar"); // Muestro error (el del backend si viene)
        isSubmitting = false; // Quito bloqueo
        if (btn) btn.disabled = false; // Reactivo botón
        return;
      }

      textInput.value = ""; // Limpio el textarea
      await loadComments(currentPostId); // Recargo la lista para que aparezca el comentario nuevo
    } catch {
      alert("Error de red comentando"); // Error de red o excepción
    } finally {
      isSubmitting = false; // Pase lo que pase, quito bloqueo
      if (btn) btn.disabled = false; // Reactivo el botón
      textInput.focus(); // Devuelvo el foco al input para seguir escribiendo
    }
  });
})();