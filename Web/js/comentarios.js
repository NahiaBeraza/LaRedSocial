(() => {
  const modal = document.getElementById("commentsModal");
  const closeBtn = document.getElementById("closeModal");
  const list = document.getElementById("modalComments");
  const body = document.getElementById("modalBody");
  const form = document.getElementById("modalCommentForm");
  const postIdInput = document.getElementById("modalPostId");
  const textInput = document.getElementById("modalTexto");

  if (!modal || !closeBtn || !list || !body || !form || !postIdInput || !textInput) return;

  // ✅ Guard para evitar que el archivo se “enganche” dos veces si lo incluyes duplicado
  if (window.__commentsModalBound) return;
  window.__commentsModalBound = true;

  let currentPostId = 0;
  let isSubmitting = false; // ✅ evita doble envío

  function openModal() {
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
  }

  function closeModal() {
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
    currentPostId = 0;
    postIdInput.value = "";
    textInput.value = "";
    list.innerHTML = `<div class="c-item">Cargando…</div>`;
    isSubmitting = false;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = false;
  }

  closeBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
  });

  async function loadComments(postId) {
    list.innerHTML = `<div class="c-item">Cargando…</div>`;
    try {
      const r = await fetch(`php/comentar.php?list=1&id_publicacion=${encodeURIComponent(postId)}`, {
        cache: "no-store",
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });

      // Si por cualquier cosa no es 200, mostramos error claro
      if (!r.ok) {
        list.innerHTML = `<div class="c-item">Error cargando comentarios (HTTP ${r.status}).</div>`;
        return;
      }

      const html = await r.text();
      list.innerHTML = (html && html.trim())
        ? html
        : `<div class="c-item">No hay comentarios todavía.</div>`;

      // bajar abajo
      body.scrollTop = body.scrollHeight;
    } catch {
      list.innerHTML = `<div class="c-item">Error cargando comentarios.</div>`;
    }
  }

  // Abrir modal
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-open-comments");
    if (!btn) return;

    const postId = parseInt(btn.dataset.post || "0", 10);
    if (!postId) return;

    currentPostId = postId;
    postIdInput.value = String(postId);
    textInput.value = "";

    openModal();
    loadComments(postId);
    setTimeout(() => textInput.focus(), 30);
  });

  // Enviar comentario sin recargar página
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (isSubmitting) return; // ✅ bloqueo total
    isSubmitting = true;

    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;

    const postId = currentPostId;
    const texto = (textInput.value || "").trim();

    if (!postId || !texto) {
      isSubmitting = false;
      if (btn) btn.disabled = false;
      return;
    }

    const fd = new FormData(form);
    fd.set("id_publicacion", String(postId));
    fd.set("texto", texto);
    fd.set("ajax", "1");

    fd.set(
      "client_token",
      (crypto?.randomUUID?.() || (Date.now() + "-" + Math.random()))
    );

    try {
      const r = await fetch("php/comentar.php", {
        method: "POST",
        body: fd,
        cache: "no-store",
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });

      const data = await r.json();

      if (!data.ok) {
        alert(data.error || "No se pudo comentar");
        isSubmitting = false;
        if (btn) btn.disabled = false;
        return;
      }

      textInput.value = "";
      await loadComments(currentPostId);
    } catch {
      alert("Error de red comentando");
    } finally {
      isSubmitting = false;
      if (btn) btn.disabled = false;
      textInput.focus();
    }
  });
})();
