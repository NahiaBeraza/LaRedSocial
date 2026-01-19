<?php
require_once __DIR__ . "/require_login.php";
include_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$idUsuario = (int)$_SESSION["id_usuario"];
$idPublicacion = (int)($_POST["id_publicacion"] ?? 0);
$texto = trim($_POST["texto"] ?? "");

if ($idPublicacion <= 0 || $texto === "") {
  $back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
  header("Location: " . $back);
  exit();
}

if (mb_strlen($texto, "UTF-8") > 500) {
  $texto = mb_substr($texto, 0, 500, "UTF-8");
}

$conexion = conexionBD();
$fecha = date("Y-m-d H:i:s");

$sql = "INSERT INTO comentario (id_usuario, id_publicacion, texto, fecha_comentario)
        VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "iiss", $idUsuario, $idPublicacion, $texto, $fecha);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
header("Location: " . $back);
exit();
    