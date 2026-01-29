(() => {
  const modal = document.getElementById("commentsModal");
  const closeBtn = document.getElementById("closeModal");
  const list = document.getElementById("modalComments");
  const body = document.getElementById("modalBody");
  const form = document.getElementById("modalCommentForm");
  const postIdInput = document.getElementById("modalPostId");
  const textInput = document.getElementById("modalTexto");

  if (!modal || !closeBtn || !list || !body || !form || !postIdInput || !textInput) return;

  let currentPostId = 0;

  function openModal() {
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    currentPostId = 0;
    postIdInput.value = "";
    textInput.value = "";
    list.innerHTML = `<div class="c-item">Cargando…</div>`;
  }

  closeBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
  });

  function loadComments(postId) {
    list.innerHTML = `<div class="c-item">Cargando…</div>`;

    // ✅ Importante: usar ruta RELATIVA para que funcione tanto en /Web como en /web
    fetch(`php/comentar.php?list=1&id_publicacion=${encodeURIComponent(postId)}`, {
      cache: "no-store",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(r => {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.text();
      })
      .then(html => {
        list.innerHTML = (html && html.trim())
          ? html
          : `<div class="c-item">No hay comentarios todavía.</div>`;
        body.scrollTop = body.scrollHeight;
      })
      .catch(err => {
        console.error("Error cargar comentarios:", err);
        list.innerHTML = `<div class="c-item">Error cargando comentarios.</div>`;
      });
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

  // Enviar comentario AJAX
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const postId = currentPostId;
    const texto = (textInput.value || "").trim();
    if (!postId || !texto) return;

    const fd = new FormData(form);
    fd.set("id_publicacion", String(postId));
    fd.set("texto", texto);
    fd.set("ajax", "1");

    // ✅ Ruta relativa también en el POST
    fetch(`php/comentar.php`, {
      method: "POST",
      body: fd,
      cache: "no-store",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(r => r.json())
      .then(data => {
        if (!data.ok) {
          alert(data.error || "No se pudo comentar");
          return;
        }
        list.insertAdjacentHTML("beforeend", data.html);

        const countEl = document.getElementById(`ccount-${postId}`);
        if (countEl) countEl.textContent = String(data.count);

        textInput.value = "";
        textInput.focus();
        body.scrollTop = body.scrollHeight;
      })
      .catch(err => {
        console.error("Error comentar:", err);
        alert("Error de red comentando");
      });
  });
})();
