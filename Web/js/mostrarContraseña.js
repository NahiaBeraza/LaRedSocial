const botonOjo = document.getElementById("togglePass");
const inputContrasena = document.getElementById("contrasena");
const iconoOjo = document.getElementById("iconPass");

if (botonOjo && inputContrasena && iconoOjo) {
  botonOjo.addEventListener("click", () => {
    const mostrarContrasena = inputContrasena.type === "password";

    inputContrasena.type = mostrarContrasena ? "text" : "password";
    iconoOjo.src = mostrarContrasena
      ? "estilos/imagenes/ojoCerrado.png"
      : "estilos/imagenes/ojoAbierto.png";
    iconoOjo.alt = mostrarContrasena
      ? "Ocultar contraseña"
      : "Mostrar contraseña";
  });
}
