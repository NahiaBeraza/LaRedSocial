<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];
$idSeguido = (int)($_POST['id_seguido'] ?? 0);

if ($idSeguido <= 0 || $idSeguido === $idYo) {
  header("Location: ../usuarios.php");
  exit();
}

// ¿ya lo sigo?
$sqlCheck = "SELECT 1 FROM seguidor WHERE id_usuario = ? AND id_seguido = ? LIMIT 1";
$stmt = mysqli_prepare($conexion, $sqlCheck);
mysqli_stmt_bind_param($stmt, "ii", $idYo, $idSeguido);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$ya = (mysqli_stmt_num_rows($stmt) === 1);
mysqli_stmt_close($stmt);

if ($ya) {
  // dejar de seguir
  $sqlDel = "DELETE FROM seguidor WHERE id_usuario = ? AND id_seguido = ?";
  $stmtD = mysqli_prepare($conexion, $sqlDel);
  mysqli_stmt_bind_param($stmtD, "ii", $idYo, $idSeguido);
  mysqli_stmt_execute($stmtD);
  mysqli_stmt_close($stmtD);
} else {
  // seguir
  $sqlIns = "INSERT INTO seguidor (id_usuario, id_seguido) VALUES (?, ?)";
  $stmtI = mysqli_prepare($conexion, $sqlIns);
  mysqli_stmt_bind_param($stmtI, "ii", $idYo, $idSeguido);
  mysqli_stmt_execute($stmtI);
  mysqli_stmt_close($stmtI);
}

$back = $_SERVER['HTTP_REFERER'] ?? '../usuarios.php';
header("Location: " . $back);
exit();
