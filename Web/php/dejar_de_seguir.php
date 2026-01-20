<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

$idSeguido = (int)($_POST['id_seguido'] ?? 0); // a quién dejo de seguir
$idPerfil = (int)($_POST['id_perfil'] ?? $idYo);
$modal = $_POST['modal'] ?? 'following';

if ($idSeguido <= 0 || $idSeguido === $idYo) {
  header("Location: ../perfil.php?id=" . $idYo);
  exit();
}

/*
  Dejar de seguir = borrar:
  seguidor.id_usuario = idYo
  seguidor.id_seguido = idSeguido
*/
$sql = "DELETE FROM seguidor WHERE id_usuario = ? AND id_seguido = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $idYo, $idSeguido);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// volver al modal del perfil
header("Location: ../perfil.php?id=" . (int)$idPerfil . "&modal=" . urlencode($modal));
exit();
