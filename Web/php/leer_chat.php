<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

$esDirecto = (basename($_SERVER["SCRIPT_NAME"]) === "leer_chat.php");

if (!function_exists("terminar")) {
  function terminar($esDirecto) { if ($esDirecto) exit(); }
}
if (!function_exists("h")) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }
}

function esVideo($ruta){
  $ruta = strtolower((string)$ruta);
  return (bool)preg_match('/\.(mp4|webm|ogg)$/', $ruta);
}

$idOtro  = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$idGrupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : 0;

if ($idOtro <= 0 && $idGrupo <= 0) terminar($esDirecto);

/* ======================
   CHAT PRIVADO
====================== */
if ($idOtro > 0) {

  $sql = "SELECT m.id_mensaje, m.id_usuario_emisor, m.id_usuario_receptor, m.texto, m.foto, m.fecha,
                 u.nombre_usuario,
                 em.fecha_leido AS fecha_leido_por_otro
          FROM mensaje m
          JOIN usuario u ON u.id_usuario = m.id_usuario_emisor
          LEFT JOIN estadomensaje em
                 ON em.id_mensaje = m.id_mensaje
                AND em.id_usuario_receptor = ?
          WHERE m.id_grupo IS NULL
            AND (
              (m.id_usuario_emisor = ? AND m.id_usuario_receptor = ?)
              OR
              (m.id_usuario_emisor = ? AND m.id_usuario_receptor = ?)
            )
          ORDER BY m.id_mensaje ASC
          LIMIT 80";

  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "iiiii", $idOtro, $yo, $idOtro, $idOtro, $yo);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  $idsParaLeer = [];
  $hayMensajes = false;

  while ($m = mysqli_fetch_assoc($res)) {
    $hayMensajes = true;
    $esMio = ((int)$m["id_usuario_emisor"] === $yo);

    if (!$esMio && (int)$m["id_usuario_receptor"] === $yo) {
      $idsParaLeer[] = (int)$m["id_mensaje"];
    }

    $ruta = $m["foto"] ?? "";
    $tieneArchivo = !empty($ruta);
    $src = $tieneArchivo ? ("uploads/" . h($ruta)) : "";

    echo '<div class="chat__fila ' . ($esMio ? 'chat__fila--mio' : '') . '">';
    echo '  <div class="chat__burbuja">';
    if (!$esMio) echo '    <div class="chat__nombre">' . h($m["nombre_usuario"]) . '</div>';

    if ($tieneArchivo) {
      if (esVideo($ruta)) {
        echo '    <video controls style="max-width:320px; border-radius:12px; display:block; margin-bottom:8px;">';
        echo '      <source src="' . $src . '">';
        echo '    </video>';
      } else {
        echo '    <img src="' . $src . '" alt="" style="max-width:320px;border-radius:12px;display:block;margin-bottom:8px;">';
      }
    }

    if (!empty($m["texto"])) {
      echo '    <div>' . nl2br(h($m["texto"])) . '</div>';
    }

    echo '    <div class="chat__fecha">' . h($m["fecha"]) . '</div>';

    if ($esMio) {
      $visto = !empty($m["fecha_leido_por_otro"]);
      echo '    <div class="chat__estado">' . ($visto ? 'Visto' : 'Enviado') . '</div>';
    }

    echo '  </div>';
    echo '</div>';
  }
  mysqli_stmt_close($stmt);

  if (!$hayMensajes) echo '<p class="chat__vacio">No hay mensajes todavía. Escribe el primero 😊</p>';

  if (!empty($idsParaLeer)) {
    $now = date("Y-m-d H:i:s");
    $stmtRead = mysqli_prepare(
      $conexion,
      "INSERT IGNORE INTO estadomensaje (id_mensaje, id_usuario_receptor, fecha_leido) VALUES (?, ?, ?)"
    );
    foreach ($idsParaLeer as $idMsg) {
      mysqli_stmt_bind_param($stmtRead, "iis", $idMsg, $yo, $now);
      mysqli_stmt_execute($stmtRead);
    }
    mysqli_stmt_close($stmtRead);
  }

  terminar($esDirecto);
}

/* ======================
   CHAT GRUPO
====================== */
if ($idGrupo > 0) {

  $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1");
  mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo);
  mysqli_stmt_execute($stmtM);
  mysqli_stmt_store_result($stmtM);
  $esMiembro = (mysqli_stmt_num_rows($stmtM) === 1);
  mysqli_stmt_close($stmtM);

  if (!$esMiembro) {
    echo '<p class="chat__vacio">No eres miembro de este grupo.</p>';
    terminar($esDirecto);
  }

  $sql = "SELECT m.id_mensaje, m.id_usuario_emisor, m.texto, m.foto, m.fecha,
                 u.nombre_usuario
          FROM mensaje m
          JOIN usuario u ON u.id_usuario = m.id_usuario_emisor
          WHERE m.id_grupo = ?
          ORDER BY m.id_mensaje ASC
          LIMIT 120";

  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idGrupo);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  $hayMensajes = false;

  while ($m = mysqli_fetch_assoc($res)) {
    $hayMensajes = true;
    $esMio = ((int)$m["id_usuario_emisor"] === $yo);

    $ruta = $m["foto"] ?? "";
    $tieneArchivo = !empty($ruta);
    $src = $tieneArchivo ? ("uploads/" . h($ruta)) : "";

    echo '<div class="chat__fila ' . ($esMio ? 'chat__fila--mio' : '') . '">';
    echo '  <div class="chat__burbuja">';
    if (!$esMio) echo '    <div class="chat__nombre">' . h($m["nombre_usuario"]) . '</div>';

    if ($tieneArchivo) {
      if (esVideo($ruta)) {
        echo '    <video controls style="max-width:320px; border-radius:12px; display:block; margin-bottom:8px;">';
        echo '      <source src="' . $src . '">';
        echo '    </video>';
      } else {
        echo '    <img src="' . $src . '" alt="" style="max-width:320px;border-radius:12px;display:block;margin-bottom:8px;">';
      }
    }

    if (!empty($m["texto"])) {
      echo '    <div>' . nl2br(h($m["texto"])) . '</div>';
    }

    echo '    <div class="chat__fecha">' . h($m["fecha"]) . '</div>';
    echo '  </div>';
    echo '</div>';
  }
  mysqli_stmt_close($stmt);

  if (!$hayMensajes) echo '<p class="chat__vacio">No hay mensajes todavía en este grupo 😊</p>';

  terminar($esDirecto);
}

terminar($esDirecto);
