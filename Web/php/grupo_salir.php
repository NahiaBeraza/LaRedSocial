<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: ../chat.php"); exit(); }

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];
$idGrupo = (int)($_POST["grupo"] ?? 0);

if ($idGrupo <= 0) { header("Location: ../chat.php"); exit(); }

// verificar miembro
$stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1");
mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo);
mysqli_stmt_execute($stmtM);
mysqli_stmt_store_result($stmtM);
$esMiembro = (mysqli_stmt_num_rows($stmtM) === 1);
mysqli_stmt_close($stmtM);

if ($esMiembro) {
  $stmt = mysqli_prepare($conexion, "DELETE FROM miembro WHERE id_grupo = ? AND id_usuario = ?");
  mysqli_stmt_bind_param($stmt, "ii", $idGrupo, $yo);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}

header("Location: ../chat.php");
exit();
