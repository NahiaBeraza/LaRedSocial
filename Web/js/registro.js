// Leo los parámetros de la URL (?error=...)
const params = new URLSearchParams(window.location.search);

// Contenedor donde muestro el mensaje de error
const errorMsg = document.getElementById("error-msg");

// Si existe el parámetro "error" en la URL
if (params.has("error")) {
    errorMsg.style.display = "block"; // Muestro el bloque de error

    // Error: el nombre de usuario ya existe
    if (params.get("error") === "usuario") {
        errorMsg.textContent = "El nombre de usuario ya existe";
        document.getElementById("usuario").classList.add("input-error"); // Marco el input de usuario como erróneo
    }

    // Error: faltan campos por rellenar
    if (params.get("error") === "campos") {
        errorMsg.textContent = "Completa todos los campos";
    }

    // Error genérico durante el registro
    if (params.get("error") === "general") {
        errorMsg.textContent = "Error al registrar el usuario";
    }
}