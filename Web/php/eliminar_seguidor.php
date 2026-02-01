<?php
require_once __DIR__ . "/require_login.php"; // Comprueba que el usuario esté logueado
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

// Este archivo solo debe ejecutarse por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido"; // Si alguien entra directamente por URL, corto aquí
  exit();
}

$conexion = conexionBD();              // Abro conexión con la BD
$idYo = (int)$_SESSION['id_usuario'];  // Mi id de usuario desde la sesión

$idUsuario = (int)($_POST['id_usuario'] ?? 0); // Id del usuario que me sigue (el seguidor)
$idPerfil  = (int)($_POST['id_perfil'] ?? $idYo); // Perfil al que volver después
$modal     = $_POST['modal'] ?? 'followers'; // Modal que estaba abierto (followers por defecto)

// Validación básica:
// - idUsuario debe ser válido
// - no tiene sentido eliminarme a mí mismo como seguidor
if ($idUsuario <= 0 || $idUsuario === $idYo) {
  header("Location: ../perfil.php?id=" . $idYo); // Si algo falla, vuelvo a mi perfil
  exit();
}

/*
  Eliminar seguidor = borrar la relación en la tabla seguidor
  Donde:
  - seguidor.id_usuario = idUsuario (el que seguía)
  - seguidor.id_seguido = idYo       (yo, a quien seguía)
*/
$sql = "DELETE FROM seguidor WHERE id_usuario = ? AND id_seguido = ?";
$stmt = mysqli_prepare($conexion, $sql);        // Preparo la query
mysqli_stmt_bind_param($stmt, "ii", $idUsuario, $idYo); // Paso el id del seguidor y mi id
mysqli_stmt_execute($stmt);                     // Ejecuto el DELETE
mysqli_stmt_close($stmt);                       // Cierro el statement

// Vuelvo al perfil desde el que se lanzó la acción,
// manteniendo el modal de seguidores abierto
header("Location: ../perfil.php?id=" . (int)$idPerfil . "&modal=" . urlencode($modal));
exit();
