<?php
require_once __DIR__ . "/php/require_login.php";
include_once __DIR__ . "/php/conexion.php";
require_once __DIR__ . "/php/notificaciones_chat.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];
$noLeidosTotal = contarNoLeidosTotal($conexion, $idYo);

/* ===================== PERFIL: cargar foto_perfil ===================== */
$miFoto = '';
$sqlFoto = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
$stmtFoto = mysqli_prepare($conexion, $sqlFoto);
mysqli_stmt_bind_param($stmtFoto, "i", $idYo);
mysqli_stmt_execute($stmtFoto);
$resFoto = mysqli_stmt_get_result($stmtFoto);
if ($rowFoto = mysqli_fetch_assoc($resFoto)) {
  $miFoto = $rowFoto['foto_perfil'] ?? '';
}
mysqli_stmt_close($stmtFoto);

$miAvatarStyle = "background:#ccc;";
if (!empty($miFoto)) {
  $miAvatarStyle = "background-image:url('uploads/perfiles/" . htmlspecialchars($miFoto, ENT_QUOTES) . "');"
                 . "background-size:cover;background-position:center;";
}
/* ===================================================================== */

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

// Posts + total reacciones
$sql = "
SELECT
  p.id_publicacion,
  p.id_usuario,
  p.pie_de_foto,
  p.imagen,
  p.ubicacion,
  p.etiquetas,
  u.nombre_usuario,
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
  while ($row = mysqli_fetch_assoc($res)) {
    $out[$row["tipo"]] = (int)$row["total"];
  }
  mysqli_stmt_close($stmt);
  return $out; // solo aparecen tipos con >0 (porque GROUP BY)
}

// Comentarios: count + últimos 3
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

function ultimosComentarios($conexion, int $idPublicacion, int $limite = 3): array {
  $sql = "SELECT c.texto, c.fecha_comentario, u.nombre_usuario
          FROM comentario c
          JOIN usuario u ON u.id_usuario = c.id_usuario
          WHERE c.id_publicacion = ?
          ORDER BY c.id_comentario DESC
          LIMIT $limite";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idPublicacion);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
  mysqli_stmt_close($stmt);
  return array_reverse($out);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Índice</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

<div id="app">

  <aside id="sidebar">
    <div id="sidebar-logo">
      <span class="logo-text">NOMBRE DE LA PÁGINA</span>
    </div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item active">Home</li>
        <li class="nav-item">All</li>
        <li class="nav-item">
          <a href="usuarios.php" style="color:inherit;text-decoration:none;">Usuarios</a>
        </li>
        <li class="nav-item">
          <a href="chat.php" style="color:inherit;text-decoration:none;">
            Chat
            <?php if ($noLeidosTotal > 0): ?>
              <span class="btn-chat" style="padding:4px 10px;border-radius:999px;"><?= (int)$noLeidosTotal ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <div class="nav-title">Account</div>
      <ul class="nav-section">
        <li class="nav-item">Hola, <?= h($_SESSION['usuario']) ?></li>
        <li class="nav-item">
          <a href="perfil.php" style="color:inherit;text-decoration:none;">Mi perfil</a>
        </li>
        <li class="nav-item">
          <a href="logout.php" style="color:inherit;text-decoration:none;">Cerrar sesión</a>
        </li>
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

        <?php if (count($posts) === 0): ?>
          <article class="post">
            <div class="post-body">
              <h2 class="post-title">Todavía no hay publicaciones</h2>
              <p class="post-text">Pulsa en Create para subir la primera 📸</p>
            </div>
          </article>
        <?php endif; ?>

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

            $resumen = resumenReacciones($conexion, $postId); // solo tipos con >0
            $numComentarios = contarComentarios($conexion, $postId);
            $comentarios = ultimosComentarios($conexion, $postId, 3);
          ?>

          <article class="post">

            <div class="post-votes">

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

              <!-- TOTAL de reacciones -->
              <span class="vote-count"><?= (int)$p["reacciones_count"] ?></span>

            </div>

            <div class="post-body">

              <h2 class="post-title">Publicación #<?= $postId ?></h2>

              <p class="post-meta">
                Posted by <span class="post-author">u/<?= h($p['nombre_usuario']) ?></span>
                <?php if (!empty($p['ubicacion'])): ?>
                  · <?= h($p['ubicacion']) ?>
                <?php endif; ?>
              </p>

              <?php if (!empty($p['imagen'])): ?>
                <div style="margin:10px 0;">
                  <img src="uploads/<?= h($p['imagen']) ?>" alt="publicación" style="max-width:100%;border-radius:12px;">
                </div>
              <?php endif; ?>

              <p class="post-text"><?= nl2br(h($p['pie_de_foto'])) ?></p>

              <?php if (!empty($p['etiquetas'])): ?>
                <p class="post-meta">#<?= h($p['etiquetas']) ?></p>
              <?php endif; ?>

              <!-- ===== RESUMEN SOLO >0 ===== -->
              <div class="post-meta" style="margin-top:10px;">
                <?php
                  $trozos = [];
                  foreach ($orden as $t) {
                    if (!empty($resumen[$t])) {
                      $trozos[] = $emoji[$t] . " " . (int)$resumen[$t];
                    }
                  }
                  echo !empty($trozos) ? implode(" · ", $trozos) : "Sin reacciones todavía";
                ?>
              </div>

              <!-- ===== COMENTARIOS ===== -->
              <div style="margin-top:14px;">
                <div class="post-meta" style="margin-bottom:8px;">
                  💬 Comentarios (<?= $numComentarios ?>)
                </div>

                <?php if ($numComentarios === 0): ?>
                  <div class="post-meta" style="margin-bottom:10px;">Sé el primero en comentar 😊</div>
                <?php else: ?>
                  <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;">
                    <?php foreach ($comentarios as $c): ?>
                      <div style="background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:12px;padding:10px;">
                        <div style="font-weight:700;font-size:13px;">
                          u/<?= h($c["nombre_usuario"]) ?>
                          <span class="post-meta" style="margin-left:8px;">
                            <?= h($c["fecha_comentario"]) ?>
                          </span>
                        </div>
                        <div style="margin-top:6px;font-size:14px;">
                          <?= nl2br(h($c["texto"])) ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <form action="php/comentar.php" method="post" style="display:flex; gap:10px; align-items:center;">
                  <input type="hidden" name="id_publicacion" value="<?= $postId ?>">
                  <input class="input" type="text" name="texto" placeholder="Añade un comentario..." required>
                  <button class="btn-primary" type="submit" style="padding:10px 14px;border-radius:12px;">Enviar</button>
                </form>
              </div>

            </div>
          </article>
        <?php endforeach; ?>

      </main>

      <aside id="right-panel">
        <section class="panel-section">
          <h3 class="panel-title">Recent Posts</h3>

          <?php foreach (array_slice($posts, 0, 5) as $p): ?>
            <div class="panel-item">
              <p class="panel-item-title">Post #<?= (int)$p['id_publicacion'] ?></p>
              <span class="panel-item-meta">u/<?= h($p['nombre_usuario']) ?></span>
            </div>
          <?php endforeach; ?>

        </section>
      </aside>

    </div>
  </div>

</div>

<script src="js/reacciones.js"></script>
</body>
</html>
