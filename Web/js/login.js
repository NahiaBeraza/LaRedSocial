// Leo los parámetros de la URL (por ejemplo ?error=1)
const params = new URLSearchParams(window.location.search);

// Si existe el parámetro "error" en la URL
if (params.has("error")) {
    // Muestro el mensaje de error que estaba oculto
    document.getElementById("error-msg").style.display = "block";

    // Marco los inputs como erróneos para resaltarlos visualmente
    document.getElementById("usuario").classList.add("input-error");
    document.getElementById("contrasena").classList.add("input-error");
}
