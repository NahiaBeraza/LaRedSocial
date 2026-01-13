<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];
$bio = trim($_POST['biografia'] ?? '');

$nuevaFoto = null;

// Si sube imagen, la guardamos
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {

  $img = $_FILES['foto_perfil'];
  if ($img['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../perfil.php?error=1");
    exit();
  }

  $maxBytes = 5 * 1024 * 1024;
  if ($img['size'] > $maxBytes) {
    header("Location: ../perfil.php?error=1");
    exit();
  }

  $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
  $permitidas = ['jpg','jpeg','png','webp'];
  if (!in_array($ext, $permitidas, true)) {
    header("Location: ../perfil.php?error=1");
    exit();
  }

  $dir = __DIR__ . "/../uploads/perfiles";
  if (!is_dir($dir)) mkdir($dir, 0755, true);

  $nuevaFoto = uniqid("pf_", true) . "." . $ext;
  $ruta = $dir . "/" . $nuevaFoto;

  if (!move_uploaded_file($img['tmp_name'], $ruta)) {
    header("Location: ../perfil.php?error=1");
    exit();
  }
}

// Update usuario
if ($nuevaFoto !== null) {
  $sql = "UPDATE usuario SET biografia = ?, foto_perfil = ? WHERE id_usuario = ?";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "ssi", $bio, $nuevaFoto, $idYo);
} else {
  $sql = "UPDATE usuario SET biografia = ? WHERE id_usuario = ?";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "si", $bio, $idYo);
}

if (!mysqli_stmt_execute($stmt)) {
  // si falla y subimos archivo, podrías borrarlo:
  // if ($nuevaFoto !== null) @unlink(__DIR__ . "/../uploads/perfiles/" . $nuevaFoto);
  header("Location: ../perfil.php?error=1");
  exit();
}

mysqli_stmt_close($stmt);
header("Location: ../perfil.php?ok=1");
exit();
