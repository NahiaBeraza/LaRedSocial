// js/usuarios.js
// Buscador de usuarios (filtra la lista mientras escribes)

(function () {
  const input = document.getElementById("search-input");
  const cards = Array.from(document.querySelectorAll(".usuario-card"));
  const sin = document.getElementById("sin-resultados");
  const contador = document.getElementById("contador-usuarios");

  function actualizarContador(visibles) {
    if (!contador) return;
    contador.textContent = visibles + " usuario(s)";
  }

  function filtrar() {
    const q = (input.value || "").trim().toLowerCase();
    let visibles = 0;

    cards.forEach((card) => {
      const name = card.getAttribute("data-name") || "";
      const ok = q === "" ? true : name.includes(q);

      card.style.display = ok ? "" : "none";
      if (ok) visibles++;
    });

    if (sin) sin.style.display = visibles === 0 ? "" : "none";
    actualizarContador(visibles);
  }

  // Si no estamos en la página de usuarios, no hacemos nada
  if (!input) return;

  // contador inicial
  actualizarContador(cards.length);

  // filtra mientras escribes
  input.addEventListener("input", filtrar);
})();
