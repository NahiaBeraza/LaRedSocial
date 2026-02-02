<?php
require_once __DIR__ . "/php/require_login.php"; // Comprueba que haya sesión iniciada (si no, no dejo entrar al index)
include_once __DIR__ . "/php/conexion.php";      // Cargo la conexión a la base de datos (include_once para no repetirla si ya se incluyó)
require_once __DIR__ . "/php/notificaciones_chat.php"; // Cargo funciones de notificaciones del chat (no leídos, etc.)

$conexion = conexionBD();                 // Abro conexión a la base de datos
$idYo = (int)$_SESSION['id_usuario'];     // Guardo mi id desde sesión (lo paso a int por seguridad)
$noLeidosTotal = contarNoLeidosTotal($conexion, $idYo); // Calculo el total de mensajes no leídos que tengo (para el contador general)

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapo texto antes de imprimirlo en HTML

// Avatar del usuario logueado (topbar)
$miFoto = ''; // Aquí guardaré el nombre/ruta de mi foto de perfil (si existe)
$sqlFoto = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?"; // Query para sacar mi foto
$stmtFoto = mysqli_prepare($conexion, $sqlFoto); // Preparo la consulta
mysqli_stmt_bind_param($stmtFoto, "i", $idYo);   // Paso mi id
mysqli_stmt_execute($stmtFoto);                  // Ejecuto
$resFoto = mysqli_stmt_get_result($stmtFoto);    // Obtengo el resultado
if ($rowFoto = mysqli_fetch_assoc($resFoto)) $miFoto = $rowFoto['foto_perfil'] ?? ''; // Si hay fila, guardo foto_perfil (si viene null, lo dejo vacío)
mysqli_stmt_close($stmtFoto); // Cierro el statement

$miAvatarStyle = "background:#ccc;"; // Estilo por defecto si no hay foto (un fondo gris)
if (!empty($miFoto)) { // Si tengo foto guardada
  $miAvatarStyle = "background-image:url('uploads/perfiles/" . h($miFoto) . "');" // Pongo la foto como background (escapo el nombre por seguridad)
                 . "background-size:cover;background-position:center;"; // Ajusto la imagen para que se vea bien tipo avatar
}

// Posts + total reacciones + FOTO PERFIL autor
$sql = " 
SELECT
  p.id_publicacion,                 -- id del post
  p.id_usuario,                     -- autor del post
  p.pie_de_foto,                    -- texto del post
  p.imagen,                         -- imagen del post
  p.ubicacion,                      -- ubicación (si existe)
  p.etiquetas,                      -- etiquetas (si existen)
  u.nombre_usuario,                 -- nombre del autor (viene de usuario)
  u.foto_perfil,                    -- foto del autor (viene de usuario)
  COUNT(r.id_usuario) AS reacciones_count -- total de reacciones en ese post (cuenta filas en reaccion)
FROM publicacion p
JOIN usuario u ON u.id_usuario = p.id_usuario              -- uno los posts con su autor
LEFT JOIN reaccion r ON r.id_publicacion = p.id_publicacion -- left join para que salgan posts aunque no tengan reacciones
GROUP BY p.id_publicacion                                  -- agrupo por post para que COUNT funcione por publicación
ORDER BY p.id_publicacion DESC                             -- muestro primero los más nuevos
";
$res = mysqli_query($conexion, $sql); // Ejecuto la query de posts
$posts = []; // Aquí guardo todos los posts
while ($row = mysqli_fetch_assoc($res)) $posts[] = $row; // Recorro resultados y los meto en el array

// Mi reacción en un post
function miReaccion($conexion, int $idYo, int $idPublicacion): string { // Devuelve el tipo de reacción que yo hice en un post (o vacío si no reaccioné)
  $sql = "SELECT tipo FROM reaccion WHERE id_usuario = ? AND id_publicacion = ? LIMIT 1"; // Busco mi reacción en ese post
  $stmt = mysqli_prepare($conexion, $sql);            // Preparo
  mysqli_stmt_bind_param($stmt, "ii", $idYo, $idPublicacion); // Paso mi id y el id del post
  mysqli_stmt_execute($stmt);                         // Ejecuto
  $res = mysqli_stmt_get_result($stmt);               // Resultado
  $row = mysqli_fetch_assoc($res);                    // Primera fila (si existe)
  mysqli_stmt_close($stmt);                           // Cierro
  return $row["tipo"] ?? "";                          // Si existe, devuelvo tipo; si no, devuelvo vacío
}

// Resumen por tipo SOLO >0
function resumenReacciones($conexion, int $idPublicacion): array { // Devuelve un array tipo ["like"=>3,"love"=>1] con las reacciones de un post
  $sql = "SELECT tipo, COUNT(*) AS total
          FROM reaccion
          WHERE id_publicacion = ?
          GROUP BY tipo"; // Agrupo por tipo para sacar conteos separados
  $stmt = mysqli_prepare($conexion, $sql);      // Preparo
  mysqli_stmt_bind_param($stmt, "i", $idPublicacion); // Paso id del post
  mysqli_stmt_execute($stmt);                   // Ejecuto
  $res = mysqli_stmt_get_result($stmt);         // Resultado
  $out = [];                                    // Aquí guardo el resumen
  while ($row = mysqli_fetch_assoc($res)) $out[$row["tipo"]] = (int)$row["total"]; // Guardo cada tipo con su total (a int)
  mysqli_stmt_close($stmt);                     // Cierro
  return $out;                                  // Devuelvo el array final
}

// Comentarios: count
function contarComentarios($conexion, int $idPublicacion): int { // Devuelve cuántos comentarios tiene un post
  $sql = "SELECT COUNT(*) AS total FROM comentario WHERE id_publicacion = ?"; // Cuento comentarios por publicación
  $stmt = mysqli_prepare($conexion, $sql);       // Preparo
  mysqli_stmt_bind_param($stmt, "i", $idPublicacion); // Paso id del post
  mysqli_stmt_execute($stmt);                    // Ejecuto
  $res = mysqli_stmt_get_result($stmt);          // Resultado
  $row = mysqli_fetch_assoc($res);               // Leo la fila con el total
  mysqli_stmt_close($stmt);                      // Cierro
  return (int)($row["total"] ?? 0);              // Devuelvo el total (si no existe, 0)
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Índice</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="stylesheet" href="estilos/index.css">

  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

<div id="app">

  <aside id="sidebar">
    <div id="sidebar-logo">
      <img src="estilos/imagenes/sin_fondo_con_letras.png" alt="Logo" class="logo-img">
    </div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item active">Home</li>
        <li class="nav-item">
          <a href="usuarios.php">Usuarios</a>
        </li>
        <li class="nav-item">
          <a href="chat.php">
            Chat
            <?php if ($noLeidosTotal > 0): ?> <!-- Si hay mensajes no leídos en total, muestro el numerito -->
              <span class="btn-chat"><?= (int)$noLeidosTotal ?></span> <!-- Fuerzo a int para imprimir solo número -->
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <div class="nav-title">Account</div>
      <ul class="nav-section">
        <li class="nav-item">Hola, <?= h($_SESSION['usuario']) ?></li> <!-- Muestro el nombre del usuario logueado escapado -->
        <li class="nav-item"><a href="perfil.php">Mi perfil</a></li>
        <li class="nav-item"><a href="logout.php">Cerrar sesión</a></li>
      </ul>
    </nav>
  </aside>

  <div id="main-layout">

    <header id="topbar">
      <div id="search-container">
        <input id="search-input" type="text" placeholder="Buscar publicación">
      </div>

      <div id="topbar-actions">
        <a href="create.php"><button id="create-btn">Create</button></a>
        <a href="perfil.php" title="Perfil" style="display:inline-block;">
          <div id="user-avatar" style="<?= $miAvatarStyle ?>"></div> <!-- Avatar del usuario logueado, ya viene preparado en PHP -->
        </a>
      </div>
    </header>

    <div id="content">
      <main id="feed">

        <?php
          $emoji = [ // Mapa: tipo de reacción -> emoji que voy a mostrar
            "LIKE" => "👍",
            "LOVE" => "❤️",
            "LAUGH" => "😂",
            "WOW" => "😮",
            "SAD" => "😢",
          ];
          $orden = ["LOVE","LAUGH","WOW","SAD","LIKE"]; // Orden en el que quiero mostrar el resumen de reacciones
        ?>

        <?php foreach ($posts as $p): ?> <!-- Recorro todos los posts que ya he cargado desde la BD -->
          <?php
            $postId = (int)$p["id_publicacion"]; // Id del post actual (a int)
            $miTipo = miReaccion($conexion, $idYo, $postId); // Tipo de reacción que yo he puesto en este post (si no hay, vuelve vacío)
            $iconoPrincipal = $emoji[$miTipo] ?? "♡"; // Emoji principal: el mío si reaccioné, o un corazón vacío si no
            $resumen = resumenReacciones($conexion, $postId); // Array con conteo por tipo (LOVE => 2, LIKE => 5, etc.)
            $numComentarios = contarComentarios($conexion, $postId); // Número total de comentarios del post

            $avatarAutor = ""; // Ruta del avatar del autor (si existe)
            if (!empty($p["foto_perfil"])) { // Si el autor tiene foto guardada
              $avatarAutor = "uploads/perfiles/" . h($p["foto_perfil"]); // Construyo la ruta y escapo el nombre del archivo
            }

            $perfilAutor = "perfil.php?id=" . (int)$p["id_usuario"]; // Link al perfil del autor (con su id)
          ?>

          <article class="post" id="post-<?= $postId ?>"> <!-- id para poder localizar el post por JS o anchors -->

            <div class="post-head">
              <a class="post-avatar" href="<?= h($perfilAutor) ?>"> <!-- Enlace al perfil del autor -->
                <?php if ($avatarAutor): ?> <!-- Si hay avatar del autor, lo muestro -->
                  <img src="<?= h($avatarAutor) ?>" alt="">
                <?php else: ?> <!-- Si no hay avatar, muestro un placeholder -->
                  <span></span>
                <?php endif; ?>
              </a>

              <div class="post-headInfo">
                <a class="post-user" href="<?= h($perfilAutor) ?>"><?= h($p["nombre_usuario"]) ?></a> <!-- Nombre del autor escapado -->
                <?php if (!empty($p["ubicacion"])): ?> <!-- Si el post tiene ubicación, la muestro -->
                  <div class="post-loc"><?= h($p["ubicacion"]) ?></div> <!-- Ubicación escapada -->
                <?php endif; ?>
              </div>
            </div>

            <!-- IMAGEN -->
            <?php if (!empty($p['imagen'])): ?> <!-- Solo muestro la imagen si existe -->
              <div class="post-media">
                <img src="uploads/<?= h($p['imagen']) ?>" alt="publicación"> <!-- Ruta de la imagen del post (escapada) -->
              </div>
            <?php endif; ?>
            <!-- ACCIONES (reacciones + comentarios) -->
            <div class="post-actionsRow">

              <div class="reac">
                <button class="vote up reac-btn" type="button" data-post="<?= $postId ?>" title="Reaccionar">
                  <?= $iconoPrincipal ?> <!-- Emoji que representa mi reacción actual (o el icono por defecto si no he reaccionado) -->
                </button>

                <div class="reac-menu" id="reac-menu-<?= $postId ?>"> <!-- Menú de reacciones para este post (id único por post) -->
                  <?php foreach ($orden as $t): ?> <!-- Recorro los tipos de reacción en el orden definido -->
                    <form action="php/reaccion.php" method="post" style="margin:0;"> <!-- Cada opción es un form que envía la reacción -->
                      <input type="hidden" name="id_publicacion" value="<?= $postId ?>"> <!-- Post al que voy a reaccionar -->
                      <input type="hidden" name="tipo" value="<?= $t ?>"> <!-- Tipo de reacción que estoy enviando -->
                      <button class="reac-opt" type="submit" title="<?= h($t) ?>"> <!-- Botón con el emoji del tipo -->
                        <?= $emoji[$t] ?> <!-- Emoji asociado a ese tipo de reacción -->
                      </button>
                    </form>
                  <?php endforeach; ?>
                </div>
              </div>

              <button type="button"
                      class="post-commentBtn js-open-comments"
                      data-post="<?= $postId ?>">
                💬 Comentarios (<span id="ccount-<?= $postId ?>"><?= (int)$numComentarios ?></span>) <!-- Muestro el contador de comentarios (id único por post) -->
              </button>

            </div>

            <!-- RESUMEN REACCIONES -->
            <div class="post-meta" style="margin-top:8px;">
              <span id="reac-meta-<?= $postId ?>"> <!-- Aquí se imprime el resumen por tipos (LOVE 2 · LIKE 3...) -->
                <?php
                  $trozos = []; // Array donde voy guardando cada "emoji + número" para luego unirlos
                  foreach ($orden as $t) if (!empty($resumen[$t])) $trozos[] = $emoji[$t] . " " . (int)$resumen[$t]; // Solo meto tipos que tengan cantidad > 0
                  echo !empty($trozos) ? implode(" · ", $trozos) : "Sin reacciones todavía"; // Si hay trozos, los junto; si no, muestro texto por defecto
                ?>
              </span>
              · Total: <b id="reac-total-<?= $postId ?>"><?= (int)$p["reacciones_count"] ?></b> <!-- Total de reacciones del post (cuenta general) -->
            </div>

            <!-- TEXTO -->
            <div class="post-textWrap">
              <span class="post-userInline"><?= h($p["nombre_usuario"]) ?></span> <!-- Nombre del autor escapado -->
              <span><?= nl2br(h($p["pie_de_foto"])) ?></span> <!-- Texto del post escapado + nl2br para respetar saltos de línea -->
            </div>

            <?php if (!empty($p['etiquetas'])): ?> <!-- Solo muestro etiquetas si existen -->
              <div class="post-tags">#<?= h($p['etiquetas']) ?></div> <!-- Etiquetas escapadas -->
            <?php endif; ?>

          </article>
        <?php endforeach; ?>

      </main>

      <!-- RIGHT PANEL (lo de la derecha NO lo tocamos) -->
      <aside id="right-panel">
        <section class="panel-section">
          <h3 class="panel-title">Recent Posts</h3>

          <?php foreach (array_slice($posts, 0, 6) as $p): ?> <!-- Cojo solo los 6 primeros posts (los más recientes) -->
            <a class="panel-item" href="#post-<?= (int)$p['id_publicacion'] ?>"> <!-- Link que baja al post usando el id del article -->
              <p class="panel-item-title"><?= h($p['nombre_usuario']) ?></p> <!-- Autor escapado -->
              <span class="panel-item-meta"><?= h(mb_substr($p['pie_de_foto'] ?? '', 0, 40, 'UTF-8')) ?></span> <!-- Corto el texto a 40 chars en UTF-8 y lo escapo -->
            </a>
          <?php endforeach; ?>

        </section>
      </aside>
    </div>
  </div>
</div>

<!-- ===== MODAL COMENTARIOS ===== -->
<div id="commentsModal" class="modal-backdrop" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-label="Comentarios">
    <div class="modal-head">
      <div class="modal-title">Comentarios</div>
      <button id="closeModal" type="button" class="modal-close">Cerrar</button>
    </div>

    <div id="modalBody" class="modal-body">
      <div id="modalComments" class="modal-list">
        <div class="c-item">Cargando…</div>
      </div>
    </div>

    <div class="modal-foot">
      <form id="modalCommentForm" class="modal-form" action="php/comentar.php" method="post">
        <input type="hidden" id="modalPostId" name="id_publicacion" value="">
        <input id="modalTexto" class="input" type="text" name="texto" placeholder="Añade un comentario..." required>
        <button class="btn-primary" type="submit">Enviar</button>
      </form>
    </div>
  </div>
</div>

<script src="js/reacciones_ajax.js"></script>
<script src="js/reacciones.js"></script>
<script src="js/index_funcion.js?v=999"></script>
<script src="js/comentarios.js"></script>

</body>
</html>
