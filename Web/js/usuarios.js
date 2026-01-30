// js/usuarios.js
// Buscador de usuarios (filtra la lista mientras escribes)

(function () { // IIFE para no dejar variables en el scope global
  const input = document.getElementById("search-input"); // Input del buscador
  const cards = Array.from(document.querySelectorAll(".usuario-card")); // Todas las tarjetas de usuarios
  const sin = document.getElementById("sin-resultados"); // Mensaje de "sin resultados"
  const contador = document.getElementById("contador-usuarios"); // Contador de usuarios visibles

  function actualizarContador(visibles) { // Actualiza el texto del contador
    if (!contador) return; // Si no existe el contador, no hago nada
    contador.textContent = visibles + " usuario(s)"; // Muestro cuántos usuarios se ven
  }

  function filtrar() { // Función que filtra usuarios según lo que se escribe
    const q = (input.value || "").trim().toLowerCase(); // Texto buscado en minúsculas
    let visibles = 0; // Contador de usuarios visibles

    cards.forEach((card) => { // Recorro todas las tarjetas
      const name = card.getAttribute("data-name") || ""; // Nombre del usuario (ya viene en minúsculas)
      const ok = q === "" ? true : name.includes(q); // Si no hay búsqueda, muestro todo; si no, filtro

      card.style.display = ok ? "" : "none"; // Muestro u oculto la tarjeta
      if (ok) visibles++; // Sumo visibles
    });

    if (sin) sin.style.display = visibles === 0 ? "" : "none"; // Muestro mensaje si no hay resultados
    actualizarContador(visibles); // Actualizo el contador
  }

  // Si no estamos en la página de usuarios (no existe el input), no hago nada
  if (!input) return;

  // Contador inicial (todos visibles)
  actualizarContador(cards.length);

  // Filtro en tiempo real mientras escribo
  input.addEventListener("input", filtrar);
})();
