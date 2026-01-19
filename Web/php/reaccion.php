<?php
require_once __DIR__ . "/require_login.php";
include_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$idUsuario = (int)$_SESSION["id_usuario"];
$idPublicacion = (int)($_POST["id_publicacion"] ?? 0);
$tipo = strtoupper(trim($_POST["tipo"] ?? ""));

$tiposValidos = ["LIKE", "LOVE", "LAUGH", "WOW", "SAD"];

if ($idPublicacion <= 0 || !in_array($tipo, $tiposValidos, true)) {
  $back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
  header("Location: " . $back);
  exit();
}

$conexion = conexionBD();

// ¿ya reaccioné en esa publicación?
$sqlCheck = "SELECT tipo FROM reaccion WHERE id_usuario = ? AND id_publicacion = ? LIMIT 1";
$stmt = mysqli_prepare($conexion, $sqlCheck);
mysqli_stmt_bind_param($stmt, "ii", $idUsuario, $idPublicacion);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$fecha = date("Y-m-d H:i:s");

if ($row) {
  // si es distinta -> update
  if ($row["tipo"] !== $tipo) {
    $sqlUp = "UPDATE reaccion
              SET tipo = ?, fecha_reaccion = ?
              WHERE id_usuario = ? AND id_publicacion = ?";
    $stmtUp = mysqli_prepare($conexion, $sqlUp);
    mysqli_stmt_bind_param($stmtUp, "ssii", $tipo, $fecha, $idUsuario, $idPublicacion);
    mysqli_stmt_execute($stmtUp);
    mysqli_stmt_close($stmtUp);
  }
} else {
  // insert
  $sqlIns = "INSERT INTO reaccion (id_usuario, id_publicacion, tipo, fecha_reaccion)
             VALUES (?, ?, ?, ?)";
  $stmtIns = mysqli_prepare($conexion, $sqlIns);
  mysqli_stmt_bind_param($stmtIns, "iiss", $idUsuario, $idPublicacion, $tipo, $fecha);
  mysqli_stmt_execute($stmtIns);
  mysqli_stmt_close($stmtIns);
}

$back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
header("Location: " . $back);
exit();
