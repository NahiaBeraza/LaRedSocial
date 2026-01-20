<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: ../chat.php"); exit(); }

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

$idGrupo = (int)($_POST["grupo"] ?? 0);
$idUsuario = (int)($_POST["id_usuario"] ?? 0);

if ($idGrupo <= 0 || $idUsuario <= 0 || $idUsuario === $yo) {
  header("Location: ../chat.php?grupo=" . $idGrupo . "&modal=miembros");
  exit();
}

// comprobar creador
$stmt = mysqli_prepare($conexion, "SELECT id_creador FROM grupo WHERE id_grupo = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $idGrupo);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row || (int)$row["id_creador"] !== $yo) {
  header("Location: ../chat.php?grupo=" . $idGrupo);
  exit();
}

// expulsar
$stmtD = mysqli_prepare($conexion, "DELETE FROM miembro WHERE id_grupo = ? AND id_usuario = ?");
mysqli_stmt_bind_param($stmtD, "ii", $idGrupo, $idUsuario);
mysqli_stmt_execute($stmtD);
mysqli_stmt_close($stmtD);

header("Location: ../chat.php?grupo=" . $idGrupo . "&modal=miembros");
exit();
