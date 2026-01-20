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

$hayArchivo = isset($_FILES["archivo"]) && is_array($_FILES["archivo"]) && ($_FILES["archivo"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($texto === "" && !$hayArchivo) {
  if ($idOtro > 0) { header("Location: ../chat.php?id=" . $idOtro); exit(); }
  if ($idGrupo > 0) { header("Location: ../chat.php?grupo=" . $idGrupo); exit(); }
  header("Location: ../usuarios.php");
  exit();
}

$fecha = date("Y-m-d H:i:s");
$rutaGuardada = null;

// ===== SUBIDA ARCHIVO =====
if ($hayArchivo) {
  if (($_FILES["archivo"]["error"] ?? 1) !== UPLOAD_ERR_OK) {
    // fallo subida -> ignoramos archivo
    $rutaGuardada = null;
  } else {
    $tmp = $_FILES["archivo"]["tmp_name"];
    $size = (int)$_FILES["archivo"]["size"];

    // limite 25MB
    if ($size > 25 * 1024 * 1024) {
      $rutaGuardada = null;
    } else {
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($tmp);

      $permitidos = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
        "image/gif"  => "gif",
        "video/mp4"  => "mp4",
        "video/webm" => "webm",
        "video/ogg"  => "ogg",
      ];

      if (isset($permitidos[$mime])) {
        $ext = $permitidos[$mime];

        $dir = __DIR__ . "/../uploads/chat";
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $nombre = "msg_" . $yo . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
        $destAbs = $dir . "/" . $nombre;

        if (@move_uploaded_file($tmp, $destAbs)) {
          // guardamos relativo desde uploads/
          $rutaGuardada = "chat/" . $nombre;
        }
      }
    }
  }
}

// ===== PRIVADO =====
if ($idOtro > 0) {
  $sql = "INSERT INTO mensaje (id_usuario_emisor, id_usuario_receptor, id_grupo, texto, foto, fecha)
          VALUES (?, ?, NULL, ?, ?, ?)";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "iisss", $yo, $idOtro, $texto, $rutaGuardada, $fecha);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: ../chat.php?id=" . $idOtro);
  exit();
}

// ===== GRUPO =====
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
            VALUES (?, NULL, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "iisss", $yo, $idGrupo, $texto, $rutaGuardada, $fecha);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  header("Location: ../chat.php?grupo=" . $idGrupo);
  exit();
}

header("Location: ../usuarios.php");
exit();
