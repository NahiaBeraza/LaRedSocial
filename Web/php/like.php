<?php
require_once __DIR__ . "/require_login.php";
include_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$idUsuario = (int)$_SESSION['id_usuario'];
$idPublicacion = (int)($_POST['id_publicacion'] ?? 0);

if ($idPublicacion <= 0) {
  $back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
  header("Location: " . $back);
  exit();
}

$conexion = conexionBD();

// ¿Ya dio like?
$sqlCheck = "SELECT 1
             FROM reaccion
             WHERE id_usuario = ? AND id_publicacion = ? AND tipo = 'LIKE'
             LIMIT 1";

$stmtCheck = mysqli_prepare($conexion, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, "ii", $idUsuario, $idPublicacion);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_store_result($stmtCheck);

$yaLike = (mysqli_stmt_num_rows($stmtCheck) === 1);
mysqli_stmt_close($stmtCheck);

if ($yaLike) {
  // unlike
  $sqlDel = "DELETE FROM reaccion
             WHERE id_usuario = ? AND id_publicacion = ? AND tipo = 'LIKE'";
  $stmtDel = mysqli_prepare($conexion, $sqlDel);
  mysqli_stmt_bind_param($stmtDel, "ii", $idUsuario, $idPublicacion);
  mysqli_stmt_execute($stmtDel);
  mysqli_stmt_close($stmtDel);
} else {
  // like
  $fecha = date("Y-m-d H:i:s");
  $sqlIns = "INSERT INTO reaccion (id_usuario, id_publicacion, tipo, fecha_reaccion)
             VALUES (?, ?, 'LIKE', ?)";
  $stmtIns = mysqli_prepare($conexion, $sqlIns);
  mysqli_stmt_bind_param($stmtIns, "iis", $idUsuario, $idPublicacion, $fecha);
  mysqli_stmt_execute($stmtIns);
  mysqli_stmt_close($stmtIns);
}

// Volver a la página anterior (perfil, index, etc.)
$back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: " . $back);
exit();
