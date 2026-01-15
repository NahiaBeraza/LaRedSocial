<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: ../usuarios.php");
  exit();
}

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

$idOtro  = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$idGrupo = isset($_POST["grupo"]) ? (int)$_POST["grupo"] : 0;
$texto = trim($_POST["texto"] ?? "");

if ($texto === "") {
  if ($idOtro > 0) { header("Location: ../chat.php?id=" . $idOtro); exit(); }
  if ($idGrupo > 0) { header("Location: ../chat.php?grupo=" . $idGrupo); exit(); }
  header("Location: ../usuarios.php");
  exit();
}

$fecha = date("Y-m-d H:i:s");

// Privado
if ($idOtro > 0) {
  $sql = "INSERT INTO mensaje (id_usuario_emisor, id_usuario_receptor, id_grupo, texto, foto, fecha)
          VALUES (?, ?, NULL, ?, NULL, ?)";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "iiss", $yo, $idOtro, $texto, $fecha);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: ../chat.php?id=" . $idOtro);
  exit();
}

// Grupo
if ($idGrupo > 0) {
  // verificar miembro
  $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1");
  mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo);
  mysqli_stmt_execute($stmtM);
  mysqli_stmt_store_result($stmtM);
  $esMiembro = (mysqli_stmt_num_rows($stmtM) === 1);
  mysqli_stmt_close($stmtM);

  if ($esMiembro) {
    $sql = "INSERT INTO mensaje (id_usuario_emisor, id_usuario_receptor, id_grupo, texto, foto, fecha)
            VALUES (?, NULL, ?, ?, NULL, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "iiss", $yo, $idGrupo, $texto, $fecha);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  header("Location: ../chat.php?grupo=" . $idGrupo);
  exit();
}

header("Location: ../usuarios.php");
exit();
