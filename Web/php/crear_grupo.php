<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: ../crear_grupo.php");
  exit();
}

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

$nombre = trim($_POST["nombre_grupo"] ?? "");
$seleccion = isset($_POST["usuarios"]) && is_array($_POST["usuarios"]) ? $_POST["usuarios"] : [];

if ($nombre === "") {
  header("Location: ../crear_grupo.php");
  exit();
}

// crear grupo
$fecha = date("Y-m-d H:i:s");
$sql = "INSERT INTO grupo (nombre_grupo, fecha_creacion, tamano_maximo) VALUES (?, ?, 15)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ss", $nombre, $fecha);
mysqli_stmt_execute($stmt);
$idGrupo = mysqli_insert_id($conexion);
mysqli_stmt_close($stmt);

// meterme yo
$stmtM = mysqli_prepare($conexion, "INSERT IGNORE INTO miembro (id_grupo, id_usuario) VALUES (?, ?)");
mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo);
mysqli_stmt_execute($stmtM);

// meter seleccionados
foreach ($seleccion as $idU) {
  $idU = (int)$idU;
  if ($idU > 0 && $idU !== $yo) {
    mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $idU);
    mysqli_stmt_execute($stmtM);
  }
}
mysqli_stmt_close($stmtM);

header("Location: ../chat.php?grupo=" . $idGrupo);
exit();
