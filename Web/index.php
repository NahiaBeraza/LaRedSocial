<?php
require_once __DIR__ . "/php/require_login.php";
include_once __DIR__ . "/php/conexion.php";
require_once __DIR__ . "/php/notificaciones_chat.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];
$noLeidosTotal = contarNoLeidosTotal($conexion, $idYo);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

// Avatar del usuario logueado (topbar)
$miFoto = '';
$sqlFoto = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
$stmtFoto = mysqli_prepare($conexion, $sqlFoto);
mysqli_stmt_bind_param($stmtFoto, "i", $idYo);
mysqli_stmt_execute($stmtFoto);
$resFoto = mysqli_stmt_get_result($stmtFoto);
if ($rowFoto = mysqli_fetch_assoc($resFoto)) $miFoto = $rowFoto['foto_perfil'] ?? '';
mysqli_stmt_close($stmtFoto);

$miAvatarStyle = "background:#ccc;";
if (!empty($miFoto)) {
  $miAvatarStyle = "background-image:url('uploads/perfiles/" . h($miFoto) . "');"
                 . "background-size:cover;background-position:center;";
}

// Posts + total reacciones + FOTO PERFIL autor
$sql = "
SELECT
  p.id_publicacion,
  p.id_usuario,
  p.pie_de_foto,
  p.imagen,
  p.ubicacion,
  p.etiquetas,
  u.nombre_usuario,
  u.foto_perfil,
  COUNT(r.id_usuario) AS reacciones_count
FROM publicacion p
JOIN usuario u ON u.id_usuario = p.id_usuario
LEFT JOIN reaccion r ON r.id_publicacion = p.id_publicacion
GROUP BY p.id_publicacion
ORDER BY p.id_publicacion DESC
";
$res = mysqli_query($conexion, $sql);
$posts = [];
while ($row = mysqli_fetch_assoc($res)) $posts[] = $row;

// Mi reacción en un post
function miReaccion($conexion, int $idYo, int $idPublicacion): string {
  $sql = "SELECT tipo FROM reaccion WHERE id_usuario = ? AND id_publicacion = ? LIMIT 1";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "ii", $idYo, $idPublicacion);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  return $row["tipo"] ?? "";
}

// Resumen por tipo SOLO >0
function resumenReacciones($conexion, int $idPublicacion): array {
  $sql = "SELECT tipo, COUNT(*) AS total
          FROM reaccion
          WHERE id_publicacion = ?
          GROUP BY tipo";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idPublicacion);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[$row["tipo"]] = (int)$row["total"];
  mysqli_stmt_close($stmt);
  return $out;
}

// Comentarios: count
function contarComentarios($conexion, int $idPublicacion): int {
  $sql = "SELECT COUNT(*) AS total FROM comentario WHERE id_publicacion = ?";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idPublicacion);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  return (int)($row["total"] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Índice</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="stylesheet" href="estilos/index.css">
  <link rel="stylesheet" href="estilos/modal_comentario.css">
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

<div id="app">

  <aside id="sidebar">
    <div id="sidebar-logo">
      <span class="logo-text">AÑA AÑA</span>
    </div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item active">Home</li>
        <li class="nav-item">All</li>
        <li class="nav-item">
          <a href="usuarios.php">Usuarios</a>
        </li>
        <li class="nav-item">
          <a href="chat.php">
            Chat
            <?php if ($noLeidosTotal > 0): ?>
              <span class="btn-chat"><?= (int)$noLeidosTotal ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <div class="nav-title">Account</div>
      <ul class="nav-section">
        <li class="nav-item">Hola, <?= h($_SESSION['usuario']) ?></li>
        <li class="nav-item"><a href="perfil.php">Mi perfil</a></li>
        <li class="nav-item"><a href="logout.php">Cerrar sesión</a></li>
      </ul>
    </nav>
  </aside>

  <div id="main-layout">

    <header id="topbar">
      <div id="search-container">
        <input id="search-input" type="text" placeholder="Search stories">
      </div>

      <div id="topbar-actions">
        <a href="create.php"><button id="create-btn">Create</button></a>
        <a href="perfil.php" title="Perfil" style="display:inline-block;">
          <div id="user-avatar" style="<?= $miAvatarStyle ?>"></div>
        </a>
      </div>
    </header>

    <div id="content">
      <main id="feed">

        <?php
          $emoji = [
            "LIKE" => "👍",
            "LOVE" => "❤️",
            "LAUGH" => "😂",
            "WOW" => "😮",
            "SAD" => "😢",
          ];
          $orden = ["LOVE","LAUGH","WOW","SAD","LIKE"];
        ?>

        <?php foreach ($posts as $p): ?>
          <?php
            $postId = (int)$p["id_publicacion"];
            $miTipo = miReaccion($conexion, $idYo, $postId);
            $iconoPrincipal = $emoji[$miTipo] ?? "♡";
            $resumen = resumenReacciones($conexion, $postId);
            $numComentarios = contarComentarios($conexion, $postId);

            $avatarAutor = "";
            if (!empty($p["foto_perfil"])) {
              $avatarAutor = "uploads/perfiles/" . h($p["foto_perfil"]);
            }
            $perfilAutor = "perfil.php?id=" . (int)$p["id_usuario"];
          ?>

          <article class="post" id="post-<?= $postId ?>">

            <!-- CABECERA INSTAGRAM -->
            <div class="post-head">
              <a class="post-avatar" href="<?= h($perfilAutor) ?>">
                <?php if ($avatarAutor): ?>
                  <img src="<?= h($avatarAutor) ?>" alt="">
                <?php else: ?>
                  <span></span>
                <?php endif; ?>
              </a>

              <div class="post-headInfo">
                <a class="post-user" href="<?= h($perfilAutor) ?>"><?= h($p["nombre_usuario"]) ?></a>
                <?php if (!empty($p["ubicacion"])): ?>
                  <div class="post-loc"><?= h($p["ubicacion"]) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- IMAGEN -->
            <?php if (!empty($p['imagen'])): ?>
              <div class="post-media">
                <img src="uploads/<?= h($p['imagen']) ?>" alt="publicación">
              </div>
            <?php endif; ?>

            <!-- ACCIONES (reacciones + comentarios) -->
            <div class="post-actionsRow">

              <div class="reac">
                <button class="vote up reac-btn" type="button" data-post="<?= $postId ?>" title="Reaccionar">
                  <?= $iconoPrincipal ?>
                </button>

                <div class="reac-menu" id="reac-menu-<?= $postId ?>">
                  <?php foreach ($orden as $t): ?>
                    <form action="php/reaccion.php" method="post" style="margin:0;">
                      <input type="hidden" name="id_publicacion" value="<?= $postId ?>">
                      <input type="hidden" name="tipo" value="<?= $t ?>">
                      <button class="reac-opt" type="submit" title="<?= h($t) ?>">
                        <?= $emoji[$t] ?>
                      </button>
                    </form>
                  <?php endforeach; ?>
                </div>
              </div>

              <button type="button"
                      class="post-commentBtn js-open-comments"
                      data-post="<?= $postId ?>">
                💬 Comentarios (<span id="ccount-<?= $postId ?>"><?= (int)$numComentarios ?></span>)
              </button>

            </div>

            <!-- RESUMEN REACCIONES -->
            <div class="post-meta" style="margin-top:8px;">
              <span id="reac-meta-<?= $postId ?>">
                <?php
                  $trozos = [];
                  foreach ($orden as $t) if (!empty($resumen[$t])) $trozos[] = $emoji[$t] . " " . (int)$resumen[$t];
                  echo !empty($trozos) ? implode(" · ", $trozos) : "Sin reacciones todavía";
                ?>
              </span>
              · Total: <b id="reac-total-<?= $postId ?>"><?= (int)$p["reacciones_count"] ?></b>
            </div>


            <!-- TEXTO -->
            <div class="post-textWrap">
              <span class="post-userInline"><?= h($p["nombre_usuario"]) ?></span>
              <span><?= nl2br(h($p["pie_de_foto"])) ?></span>
            </div>

            <?php if (!empty($p['etiquetas'])): ?>
              <div class="post-tags">#<?= h($p['etiquetas']) ?></div>
            <?php endif; ?>

          </article>
        <?php endforeach; ?>

      </main>

      <!-- RIGHT PANEL (lo de la derecha NO lo tocamos) -->
      <aside id="right-panel">
        <section class="panel-section">
          <h3 class="panel-title">Recent Posts</h3>

          <?php foreach (array_slice($posts, 0, 6) as $p): ?>
            <a class="panel-item" href="#post-<?= (int)$p['id_publicacion'] ?>">
              <p class="panel-item-title"><?= h($p['nombre_usuario']) ?></p>
              <span class="panel-item-meta"><?= h(mb_substr($p['pie_de_foto'] ?? '', 0, 40, 'UTF-8')) ?></span>
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
