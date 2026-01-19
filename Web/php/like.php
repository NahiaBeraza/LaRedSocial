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

// Mira si existe cualquier reacción (no solo LIKE) para evitar choque con PK
$sqlCheck = "SELECT tipo FROM reaccion WHERE id_usuario = ? AND id_publicacion = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conexion, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, "ii", $idUsuario, $idPublicacion);
mysqli_stmt_execute($stmtCheck);
$res = mysqli_stmt_get_result($stmtCheck);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmtCheck);

$fecha = date("Y-m-d H:i:s");

if ($row) {
  // Si ya era LIKE, entonces toggle => borrar
  if ($row["tipo"] === "LIKE") {
    $sqlDel = "DELETE FROM reaccion WHERE id_usuario = ? AND id_publicacion = ?";
    $stmtDel = mysqli_prepare($conexion, $sqlDel);
    mysqli_stmt_bind_param($stmtDel, "ii", $idUsuario, $idPublicacion);
    mysqli_stmt_execute($stmtDel);
    mysqli_stmt_close($stmtDel);
  } else {
    // Si era otra, cambiar a LIKE
    $sqlUp = "UPDATE reaccion SET tipo = 'LIKE', fecha_reaccion = ? WHERE id_usuario = ? AND id_publicacion = ?";
    $stmtUp = mysqli_prepare($conexion, $sqlUp);
    mysqli_stmt_bind_param($stmtUp, "sii", $fecha, $idUsuario, $idPublicacion);
    mysqli_stmt_execute($stmtUp);
    mysqli_stmt_close($stmtUp);
  }
} else {
  // No existía => insertar LIKE
  $sqlIns = "INSERT INTO reaccion (id_usuario, id_publicacion, tipo, fecha_reaccion)
             VALUES (?, ?, 'LIKE', ?)";
  $stmtIns = mysqli_prepare($conexion, $sqlIns);
  mysqli_stmt_bind_param($stmtIns, "iis", $idUsuario, $idPublicacion, $fecha);
  mysqli_stmt_execute($stmtIns);
  mysqli_stmt_close($stmtIns);
}

$back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: " . $back);
exit();
