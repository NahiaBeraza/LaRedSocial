const params = new URLSearchParams(window.location.search);
const errorMsg = document.getElementById("error-msg");

if (params.has("error")) {
    errorMsg.style.display = "block";

    if (params.get("error") === "usuario") {
        errorMsg.textContent = "El nombre de usuario ya existe";
        document.getElementById("usuario").classList.add("input-error");
    }

    if (params.get("error") === "campos") {
        errorMsg.textContent = "Completa todos los campos";
    }

    if (params.get("error") === "general") {
        errorMsg.textContent = "Error al registrar el usuario";
    }
}

