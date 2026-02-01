<?php
require_once __DIR__ . "/require_login.php"; // Obliga a estar logueado para poder leer mensajes
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

$conexion = conexionBD();              // Abro conexión con la BD
$yo = (int)$_SESSION["id_usuario"];    // Mi id de usuario

$esDirecto = (basename($_SERVER["SCRIPT_NAME"]) === "leer_chat.php"); // True si este archivo se está ejecutando directamente (por fetch o por URL)

// Esta función existe para poder hacer "exit" solo cuando el archivo se ejecuta directo.
// Si está incluido desde otro PHP (include), no quiero cortar la ejecución del padre.
if (!function_exists("terminar")) {
  function terminar($esDirecto) { if ($esDirecto) exit(); } // Si es directo -> salgo; si es include -> no hago exit
}

if (!function_exists("h")) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapo texto para imprimir HTML sin riesgos
}

function esVideo($ruta){ // Comprueba si una ruta parece un vídeo por la extensión
  $ruta = strtolower((string)$ruta); // Paso a minúsculas para comparar bien
  return (bool)preg_match('/\.(mp4|webm|ogg)$/', $ruta); // Devuelve true si termina en mp4/webm/ogg
}

$idOtro  = isset($_GET["id"]) ? (int)$_GET["id"] : 0;        // Si viene "id", estoy leyendo chat privado
$idGrupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : 0;  // Si viene "grupo", estoy leyendo chat de grupo

if ($idOtro <= 0 && $idGrupo <= 0) terminar($esDirecto); // Si no hay ni usuario ni grupo, no tengo nada que mostrar

/* ======================
   CHAT PRIVADO
====================== */
if ($idOtro > 0) { // Si hay idOtro, estamos en conversación 1 a 1

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
          LIMIT 80"; // Saco los últimos 80 mensajes del privado entre yo y el otro, y además miro si el otro ya leyó los míos

  $stmt = mysqli_prepare($conexion, $sql); // Preparo query
  mysqli_stmt_bind_param($stmt, "iiiii", $idOtro, $yo, $idOtro, $idOtro, $yo); // Parámetros: receptor del "leído" + 2 combinaciones del chat privado
  mysqli_stmt_execute($stmt); // Ejecuto
  $res = mysqli_stmt_get_result($stmt); // Resultado

  $idsParaLeer = [];   // Aquí guardo ids de mensajes que debo marcar como leídos (los del otro hacia mí)
  $hayMensajes = false; // Para saber si debo mostrar el mensaje de "no hay mensajes"

  while ($m = mysqli_fetch_assoc($res)) { // Recorro los mensajes
    $hayMensajes = true; // En cuanto entro, ya hay mensajes
    $esMio = ((int)$m["id_usuario_emisor"] === $yo); // True si el mensaje lo envié yo

    // Si NO es mío y el receptor soy yo, significa que este mensaje me lo han enviado a mí,
    // así que lo marco para ponerlo como leído después.
    if (!$esMio && (int)$m["id_usuario_receptor"] === $yo) {
      $idsParaLeer[] = (int)$m["id_mensaje"]; // Acumulo ids para insertarlos en estadomensaje
    }

    $ruta = $m["foto"] ?? ""; // En esta columna guardas el archivo (imagen o vídeo) como ruta relativa (ej: chat/msg_xxx.mp4)
    $tieneArchivo = !empty($ruta); // True si hay algo en foto
    $src = $tieneArchivo ? ("uploads/" . h($ruta)) : ""; // Ruta real para el navegador: uploads/chat/...

    echo '<div class="chat__fila ' . ($esMio ? 'chat__fila--mio' : '') . '">'; // Fila del mensaje, con clase distinta si es mío
    echo '  <div class="chat__burbuja">'; // Burbuja del mensaje

    if (!$esMio) echo '    <div class="chat__nombre">' . h($m["nombre_usuario"]) . '</div>'; // Si es del otro, muestro su nombre arriba

    if ($tieneArchivo) { // Si el mensaje trae archivo
      if (esVideo($ruta)) { // Si es vídeo, uso <video>
        echo '    <video controls style="max-width:320px; border-radius:12px; display:block; margin-bottom:8px;">';
        echo '      <source src="' . $src . '">';
        echo '    </video>';
      } else { // Si no, lo trato como imagen
        echo '    <img src="' . $src . '" alt="" style="max-width:320px;border-radius:12px;display:block;margin-bottom:8px;">';
      }
    }

    if (!empty($m["texto"])) { // Si el mensaje tiene texto
      echo '    <div>' . nl2br(h($m["texto"])) . '</div>'; // Lo imprimo escapado y respetando saltos de línea
    }

    echo '    <div class="chat__fecha">' . h($m["fecha"]) . '</div>'; // Fecha del mensaje

    if ($esMio) { // Solo para mis mensajes muestro estado (Enviado / Visto)
      $visto = !empty($m["fecha_leido_por_otro"]); // Si existe fecha_leido, significa que el otro lo leyó
      echo '    <div class="chat__estado">' . ($visto ? 'Visto' : 'Enviado') . '</div>'; // Texto simple de estado
    }

    echo '  </div>';
    echo '</div>';
  }
  mysqli_stmt_close($stmt); // Cierro statement

  if (!$hayMensajes) echo '<p class="chat__vacio">No hay mensajes todavía. Escribe el primero.</p>'; // Si no salió nada, muestro mensaje (esto es HTML que se inserta)

  // Si he acumulado mensajes del otro, aquí los marco como leídos
  if (!empty($idsParaLeer)) {
    $now = date("Y-m-d H:i:s"); // Fecha actual para guardar como "leído"
    $stmtRead = mysqli_prepare(
      $conexion,
      "INSERT IGNORE INTO estadomensaje (id_mensaje, id_usuario_receptor, fecha_leido) VALUES (?, ?, ?)"
    ); // Inserto en estadomensaje (IGNORE para que no duplique si ya estaba leído)
    foreach ($idsParaLeer as $idMsg) { // Voy mensaje por mensaje
      mysqli_stmt_bind_param($stmtRead, "iis", $idMsg, $yo, $now); // Mensaje leído, yo como receptor, fecha actual
      mysqli_stmt_execute($stmtRead); // Ejecuto insert
    }
    mysqli_stmt_close($stmtRead); // Cierro
  }

  terminar($esDirecto); // Si esto se ejecutó directo, salgo aquí para no seguir con el bloque de grupos
}

/* ======================
   CHAT GRUPO
====================== */
if ($idGrupo > 0) { // Si hay idGrupo, estoy en chat de grupo

  $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1"); // Compruebo que soy miembro del grupo
  mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo); // Paso id del grupo y mi id
  mysqli_stmt_execute($stmtM);                         // Ejecuto
  mysqli_stmt_store_result($stmtM);                    // Guardo resultado para contar filas
  $esMiembro = (mysqli_stmt_num_rows($stmtM) === 1);   // True si hay una fila (soy miembro)
  mysqli_stmt_close($stmtM);                           // Cierro

  if (!$esMiembro) { // Si no soy miembro, no debo ver mensajes
    echo '<p class="chat__vacio">No eres miembro de este grupo.</p>';
    terminar($esDirecto);
  }

  $sql = "SELECT m.id_mensaje, m.id_usuario_emisor, m.texto, m.foto, m.fecha,
                 u.nombre_usuario
          FROM mensaje m
          JOIN usuario u ON u.id_usuario = m.id_usuario_emisor
          WHERE m.id_grupo = ?
          ORDER BY m.id_mensaje ASC
          LIMIT 120"; // Saco mensajes del grupo (hasta 120), con el nombre del usuario que lo envía

  $stmt = mysqli_prepare($conexion, $sql); // Preparo
  mysqli_stmt_bind_param($stmt, "i", $idGrupo); // Paso id del grupo
  mysqli_stmt_execute($stmt); // Ejecuto
  $res = mysqli_stmt_get_result($stmt); // Resultado

  $hayMensajes = false; // Para saber si hay que mostrar texto de "no hay mensajes"

  while ($m = mysqli_fetch_assoc($res)) { // Recorro mensajes del grupo
    $hayMensajes = true;
    $esMio = ((int)$m["id_usuario_emisor"] === $yo); // Si el mensaje lo envié yo

    $ruta = $m["foto"] ?? ""; // Ruta de archivo si lo hay
    $tieneArchivo = !empty($ruta); // True si hay archivo
    $src = $tieneArchivo ? ("uploads/" . h($ruta)) : ""; // Ruta real de navegador

    echo '<div class="chat__fila ' . ($esMio ? 'chat__fila--mio' : '') . '">'; // Fila con clase especial si es mío
    echo '  <div class="chat__burbuja">';

    if (!$esMio) echo '    <div class="chat__nombre">' . h($m["nombre_usuario"]) . '</div>'; // En grupo, si no es mío muestro el nombre del autor

    if ($tieneArchivo) { // Si hay archivo, lo muestro como vídeo o imagen
      if (esVideo($ruta)) {
        echo '    <video controls style="max-width:320px; border-radius:12px; display:block; margin-bottom:8px;">';
        echo '      <source src="' . $src . '">';
        echo '    </video>';
      } else {
        echo '    <img src="' . $src . '" alt="" style="max-width:320px;border-radius:12px;display:block;margin-bottom:8px;">';
      }
    }

    if (!empty($m["texto"])) { // Si hay texto
      echo '    <div>' . nl2br(h($m["texto"])) . '</div>'; // Lo imprimo escapado y con saltos de línea
    }

    echo '    <div class="chat__fecha">' . h($m["fecha"]) . '</div>'; // Fecha del mensaje
    echo '  </div>';
    echo '</div>';
  }
  mysqli_stmt_close($stmt); // Cierro

  if (!$hayMensajes) echo '<p class="chat__vacio">No hay mensajes todavía en este grupo</p>'; // Si no había mensajes, muestro texto

  terminar($esDirecto); // Si se ejecutó directo, termino aquí
}

terminar($esDirecto); // Si no entró en ningún bloque, termino igualmente
