<?php
require_once __DIR__ . "/require_login.php"; // Si no estoy logueado, no dejo enviar mensajes
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Este archivo solo se debe ejecutar desde el form (POST)
  header("Location: ../usuarios.php");       // Si alguien entra por URL, lo mando fuera
  exit();
}

$conexion = conexionBD();              // Abro conexión con la BD
$yo = (int)$_SESSION["id_usuario"];    // Mi id desde la sesión

$idOtro  = isset($_POST["id"]) ? (int)$_POST["id"] : 0;         // Si viene, es chat privado (id del receptor)
$idGrupo = isset($_POST["grupo"]) ? (int)$_POST["grupo"] : 0;   // Si viene, es chat de grupo (id del grupo)
$texto = trim($_POST["texto"] ?? "");                           // Texto del mensaje (recortado)

$hayArchivo = isset($_FILES["archivo"]) && is_array($_FILES["archivo"]) && ($_FILES["archivo"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE; // True si han subido algo en "archivo"

// Si no hay texto y tampoco hay archivo, no mando nada (simplemente vuelvo al chat)
if ($texto === "" && !$hayArchivo) {
  if ($idOtro > 0) { header("Location: ../chat.php?id=" . $idOtro); exit(); }      // Vuelvo al privado
  if ($idGrupo > 0) { header("Location: ../chat.php?grupo=" . $idGrupo); exit(); } // Vuelvo al grupo
  header("Location: ../usuarios.php"); // Si no hay destino, vuelvo a usuarios
  exit();
}

$fecha = date("Y-m-d H:i:s"); // Fecha del mensaje en formato SQL
$rutaGuardada = null;         // Aquí guardo la ruta relativa del archivo si se sube correctamente

// ===== SUBIDA ARCHIVO =====
if ($hayArchivo) { // Solo entro aquí si el usuario ha seleccionado un archivo
  if (($_FILES["archivo"]["error"] ?? 1) !== UPLOAD_ERR_OK) { // Si PHP marca error en la subida
    $rutaGuardada = null; // Fallo de subida -> ignoro el archivo
  } else {
    $tmp = $_FILES["archivo"]["tmp_name"]; // Ruta temporal donde PHP guarda el archivo subido
    $size = (int)$_FILES["archivo"]["size"]; // Tamaño en bytes

    // limite 25MB
    if ($size > 25 * 1024 * 1024) { // Si pesa más de 25MB
      $rutaGuardada = null; // Lo ignoro directamente
    } else {
      $finfo = new finfo(FILEINFO_MIME_TYPE); // Objeto para detectar el tipo real del archivo (no fiarse del nombre)
      $mime = $finfo->file($tmp);             // Tipo MIME detectado (image/jpeg, video/mp4, etc.)

      $permitidos = [ // Tipos permitidos y su extensión final
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
        "image/gif"  => "gif",
        "video/mp4"  => "mp4",
        "video/webm" => "webm",
        "video/ogg"  => "ogg",
      ];

      if (isset($permitidos[$mime])) { // Si el archivo es de un tipo permitido
        $ext = $permitidos[$mime];     // Extensión que voy a poner en el nombre final

        $dir = __DIR__ . "/../uploads/chat"; // Carpeta destino absoluta (en el servidor)
        if (!is_dir($dir)) @mkdir($dir, 0775, true); // Si no existe, la creo (con @ para que no reviente por warnings)

        $nombre = "msg_" . $yo . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext; // Nombre único para evitar choques y no depender del nombre original
        $destAbs = $dir . "/" . $nombre; // Ruta absoluta final

        if (@move_uploaded_file($tmp, $destAbs)) { // Muevo el archivo desde temp a la carpeta final
          $rutaGuardada = "chat/" . $nombre; // Guardo la ruta relativa (pensando que luego se usará como uploads/chat/xxxx)
        }
      }
    }
  }
}

// ===== PRIVADO =====
if ($idOtro > 0) { // Si hay idOtro, esto es un mensaje privado
  $sql = "INSERT INTO mensaje (id_usuario_emisor, id_usuario_receptor, id_grupo, texto, foto, fecha)
          VALUES (?, ?, NULL, ?, ?, ?)"; // En privado: id_grupo va NULL y receptor tiene valor
  $stmt = mysqli_prepare($conexion, $sql); // Preparo query
  mysqli_stmt_bind_param($stmt, "iisss", $yo, $idOtro, $texto, $rutaGuardada, $fecha); // Emisor, receptor, texto, ruta archivo (o null), fecha
  mysqli_stmt_execute($stmt); // Inserto el mensaje
  mysqli_stmt_close($stmt);   // Cierro statement

  header("Location: ../chat.php?id=" . $idOtro); // Vuelvo al chat privado con ese usuario
  exit();
}

// ===== GRUPO =====
if ($idGrupo > 0) { // Si hay idGrupo, es un mensaje de grupo
  // verificar miembro
  $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1"); // Compruebo que realmente pertenezco al grupo
  mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo); // Paso grupo y mi id
  mysqli_stmt_execute($stmtM);                         // Ejecuto
  mysqli_stmt_store_result($stmtM);                    // Guardo resultado para poder contar filas
  $esMiembro = (mysqli_stmt_num_rows($stmtM) === 1);   // Si hay 1 fila, soy miembro
  mysqli_stmt_close($stmtM);                           // Cierro

  if ($esMiembro) { // Solo si soy miembro permito insertar el mensaje
    $sql = "INSERT INTO mensaje (id_usuario_emisor, id_usuario_receptor, id_grupo, texto, foto, fecha)
            VALUES (?, NULL, ?, ?, ?, ?)"; // En grupo: receptor va NULL y el id_grupo tiene valor
    $stmt = mysqli_prepare($conexion, $sql); // Preparo query
    mysqli_stmt_bind_param($stmt, "iisss", $yo, $idGrupo, $texto, $rutaGuardada, $fecha); // Emisor, grupo, texto, ruta archivo, fecha
    mysqli_stmt_execute($stmt); // Inserto mensaje
    mysqli_stmt_close($stmt);   // Cierro
  }

  header("Location: ../chat.php?grupo=" . $idGrupo); // Vuelvo al chat del grupo igualmente (haya insertado o no)
  exit();
}

header("Location: ../usuarios.php"); // Si no llegó ni idOtro ni idGrupo, vuelvo a usuarios
exit();
