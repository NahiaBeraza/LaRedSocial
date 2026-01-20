<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

$idUsuario = (int)($_POST['id_usuario'] ?? 0); // el que me sigue
$idPerfil = (int)($_POST['id_perfil'] ?? $idYo);
$modal = $_POST['modal'] ?? 'followers';

if ($idUsuario <= 0 || $idUsuario === $idYo) {
  header("Location: ../perfil.php?id=" . $idYo);
  exit();
}

/*
  Eliminar seguidor = borrar:
  seguidor.id_usuario = idUsuario (el que sigue)
  seguidor.id_seguido = idYo (a quien sigue)
*/
$sql = "DELETE FROM seguidor WHERE id_usuario = ? AND id_seguido = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $idUsuario, $idYo);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// volver al modal del perfil
header("Location: ../perfil.php?id=" . (int)$idPerfil . "&modal=" . urlencode($modal));
exit();
