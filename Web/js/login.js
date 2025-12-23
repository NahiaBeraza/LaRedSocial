// Comprobar si hay error en la URL
const params = new URLSearchParams(window.location.search);

if (params.has("error")) {
    document.getElementById("error-msg").style.display = "block";

    document.getElementById("usuario").classList.add("input-error");
    document.getElementById("contrasena").classList.add("input-error");
}

