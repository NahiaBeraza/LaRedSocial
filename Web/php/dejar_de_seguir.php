<?php
require_once __DIR__ . "/require_login.php"; // Obliga a que el usuario esté logueado
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

// Este archivo solo debe ejecutarse por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido"; // Si alguien entra por URL directamente, corto aquí
  exit();
}

$conexion = conexionBD();              // Abro conexión con la BD
$idYo = (int)$_SESSION['id_usuario'];  // Mi id de usuario desde la sesión

$idSeguido = (int)($_POST['id_seguido'] ?? 0); // Id del usuario al que dejo de seguir
$idPerfil  = (int)($_POST['id_perfil'] ?? $idYo); // Perfil al que volver después (si no viene, el mío)
$modal     = $_POST['modal'] ?? 'following'; // Modal que estaba abierto (followers / following)

// Validación básica:
// - idSeguido debe ser válido
// - no puedo dejar de seguirme a mí mismo
if ($idSeguido <= 0 || $idSeguido === $idYo) {
  header("Location: ../perfil.php?id=" . $idYo); // Si algo no cuadra, vuelvo a mi perfil
  exit();
}

/*
  Dejar de seguir = borrar la relación en la tabla seguidor
  Donde:
  - seguidor.id_usuario = yo (el que sigue)
  - seguidor.id_seguido = el otro usuario
*/
$sql = "DELETE FROM seguidor WHERE id_usuario = ? AND id_seguido = ?";
$stmt = mysqli_prepare($conexion, $sql);        // Preparo la query
mysqli_stmt_bind_param($stmt, "ii", $idYo, $idSeguido); // Paso mi id y el id del seguido
mysqli_stmt_execute($stmt);                     // Ejecuto el DELETE
mysqli_stmt_close($stmt);                       // Cierro el statement

// Vuelvo al perfil desde el que se lanzó la acción,
// manteniendo el modal abierto (followers / following)
header("Location: ../perfil.php?id=" . (int)$idPerfil . "&modal=" . urlencode($modal));
exit();
