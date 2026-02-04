// =====================
// VALIDACIÓN FRONTEND
// =====================
const form = document.getElementById("login-form");
const passInput = document.getElementById("contrasena");
const userInput = document.getElementById("usuario");
const errorMsg = document.getElementById("error-msg");

form.addEventListener("submit", (e) => {
    const pass = passInput.value.trim();

    // Reset estado
    errorMsg.style.display = "none";
    errorMsg.textContent = "";
    passInput.classList.remove("input-error");
    userInput.classList.remove("input-error");

    // Reglas
    const tieneMayuscula = /[A-Z]/.test(pass);
    const tieneNumero = /[0-9]/.test(pass);

    if (
        pass.length < 8 ||
        !tieneMayuscula ||
        !tieneNumero
    ) {
        e.preventDefault(); // 🚫 NO ENVÍA

        errorMsg.style.display = "block";
        errorMsg.textContent =
            "La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número";

        passInput.classList.add("input-error");
    }
});

// =====================
// ERRORES DESDE LA URL
// =====================
const params = new URLSearchParams(window.location.search);

if (params.has("error")) {
    errorMsg.style.display = "block";

    if (params.get("error") === "usuario") {
        errorMsg.textContent = "El nombre de usuario ya existe";
        userInput.classList.add("input-error");
    }

    if (params.get("error") === "campos") {
        errorMsg.textContent = "Completa todos los campos";
    }

    if (params.get("error") === "general") {
        errorMsg.textContent = "Error al registrar el usuario";
    }

    if (params.get("error") === "pass") {
        errorMsg.textContent =
            "La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número";
        passInput.classList.add("input-error");
    }
}
