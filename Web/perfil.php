<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

// --- Qué perfil se está viendo ---
$idPerfil = isset($_GET['id']) ? (int)$_GET['id'] : $idYo;
if ($idPerfil <= 0) $idPerfil = $idYo;

$esMiPerfil = ($idPerfil === $idYo);

// modal: followers | following | ''
$modal = $_GET['modal'] ?? '';
if (!in_array($modal, ['followers','following'], true)) $modal = '';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

// --- Cargar usuario del perfil ---
$sqlU = "SELECT id_usuario, nombre_usuario, correo, biografia, foto_perfil
         FROM usuario
         WHERE id_usuario = ?";
$stmtU = mysqli_prepare($conexion, $sqlU);
mysqli_stmt_bind_param($stmtU, "i", $idPerfil);
mysqli_stmt_execute($stmtU);
$resU = mysqli_stmt_get_result($stmtU);
$u = mysqli_fetch_assoc($resU);
mysqli_stmt_close($stmtU);

if (!$u) {
  header("Location: usuarios.php");
  exit();
}

// --- Contadores seguidores/seguidos ---
$sqlFollowers = "SELECT COUNT(*) c FROM seguidor WHERE id_seguido = ?";
$stmtF = mysqli_prepare($conexion, $sqlFollowers);
mysqli_stmt_bind_param($stmtF, "i", $idPerfil);
mysqli_stmt_execute($stmtF);
$followers = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtF))['c'];
mysqli_stmt_close($stmtF);

$sqlFollowing = "SELECT COUNT(*) c FROM seguidor WHERE id_usuario = ?";
$stmtG = mysqli_prepare($conexion, $sqlFollowing);
mysqli_stmt_bind_param($stmtG, "i", $idPerfil);
mysqli_stmt_execute($stmtG);
$following = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtG))['c'];
mysqli_stmt_close($stmtG);

// --- ¿Yo lo sigo? (solo si no es mi perfil) ---
$yoLoSigo = 0;
if (!$esMiPerfil) {
  $sqlS = "SELECT 1 FROM seguidor WHERE id_usuario = ? AND id_seguido = ? LIMIT 1";
  $stmtS = mysqli_prepare($conexion, $sqlS);
  mysqli_stmt_bind_param($stmtS, "ii", $idYo, $idPerfil);
  mysqli_stmt_execute($stmtS);
  mysqli_stmt_store_result($stmtS);
  $yoLoSigo = (mysqli_stmt_num_rows($stmtS) === 1) ? 1 : 0;
  mysqli_stmt_close($stmtS);
}

// --- Foto del perfil que se está viendo ---
$foto = $u['foto_perfil'] ?? '';
$srcFoto = (!empty($foto)) ? "uploads/perfiles/" . h($foto) : "";

// --- Foto del usuario logueado para el avatar del topbar ---
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

/* ===================== PUBLICACIONES DEL PERFIL (igual que index) ===================== */
// Posts del usuario del perfil + total reacciones (todas)
$sqlP = "
SELECT
  p.id_publicacion,
  p.id_usuario,
  p.pie_de_foto,
  p.imagen,
  p.ubicacion,
  p.etiquetas,
  p.fecha_publicacion,
  u.nombre_usuario,
  COUNT(r.id_usuario) AS reacciones_count
FROM publicacion p
JOIN usuario u ON u.id_usuario = p.id_usuario
LEFT JOIN reaccion r ON r.id_publicacion = p.id_publicacion
WHERE p.id_usuario = ?
GROUP BY p.id_publicacion
ORDER BY p.fecha_publicacion DESC
";
$stmtP = mysqli_prepare($conexion, $sqlP);
mysqli_stmt_bind_param($stmtP, "i", $idPerfil);
mysqli_stmt_execute($stmtP);
$resP = mysqli_stmt_get_result($stmtP);
$pubsPerfil = [];
while ($row = mysqli_fetch_assoc($resP)) $pubsPerfil[] = $row;
mysqli_stmt_close($stmtP);

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
/* ================================================================================ */

/* ===================== MODALES: seguidores / seguidos ===================== */
$listaModal = [];
$esFollowers = ($modal === 'followers');
$esFollowing = ($modal === 'following');

if ($modal !== '') {
  if ($esFollowers) {
    // personas que SIGUEN a idPerfil (seguidor.id_usuario -> usuario)
    $sqlL = "
      SELECT u.id_usuario, u.nombre_usuario, u.foto_perfil,
             CASE WHEN s2.id_usuario IS NULL THEN 0 ELSE 1 END AS yo_sigo
      FROM seguidor s
      JOIN usuario u ON u.id_usuario = s.id_usuario
      LEFT JOIN seguidor s2 ON s2.id_usuario = ? AND s2.id_seguido = u.id_usuario
      WHERE s.id_seguido = ?
      ORDER BY u.nombre_usuario ASC
    ";
    $stmtL = mysqli_prepare($conexion, $sqlL);
    mysqli_stmt_bind_param($stmtL, "ii", $idYo, $idPerfil);
  } else {
    // personas a las que idPerfil SIGUE (seguidor.id_seguido -> usuario)
    $sqlL = "
      SELECT u.id_usuario, u.nombre_usuario, u.foto_perfil,
             CASE WHEN s2.id_usuario IS NULL THEN 0 ELSE 1 END AS yo_sigo
      FROM seguidor s
      JOIN usuario u ON u.id_usuario = s.id_seguido
      LEFT JOIN seguidor s2 ON s2.id_usuario = ? AND s2.id_seguido = u.id_usuario
      WHERE s.id_usuario = ?
      ORDER BY u.nombre_usuario ASC
    ";
    $stmtL = mysqli_prepare($conexion, $sqlL);
    mysqli_stmt_bind_param($stmtL, "ii", $idYo, $idPerfil);
  }

  mysqli_stmt_execute($stmtL);
  $resL = mysqli_stmt_get_result($stmtL);
  while ($row = mysqli_fetch_assoc($resL)) $listaModal[] = $row;
  mysqli_stmt_close($stmtL);
}
/* ====================================================================== */

$emoji = [
  "LIKE" => "👍",
  "LOVE" => "❤️",
  "LAUGH" => "😂",
  "WOW" => "😮",
  "SAD" => "😢",
];
$orden = ["LOVE","LAUGH","WOW","SAD","LIKE"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

<div id="app">

  <aside id="sidebar">
    <div id="sidebar-logo"><span class="logo-text">añap</span></div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item"><a href="index.php" style="color:inherit;text-decoration:none;">Home</a></li>
        <li class="nav-item"><a href="usuarios.php" style="color:inherit;text-decoration:none;">Usuarios</a></li>
        <li class="nav-item active">Perfil</li>
      </ul>

      <div class="nav-title">Account</div>
      <ul class="nav-section">
        <li class="nav-item">Hola, <?= h($_SESSION['usuario']) ?></li>
        <li class="nav-item"><a href="perfil.php" style="color:inherit;text-decoration:none;">Mi perfil</a></li>
        <li class="nav-item"><a href="logout.php" style="color:inherit;text-decoration:none;">Cerrar sesión</a></li>
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

    <div id="content" style="grid-template-columns: 1fr;">
      <main id="feed">

        <!-- ====== CABECERA PERFIL ====== -->
        <article class="post" style="display:block;">
          <div class="post-body">

            <div style="display:flex; gap:18px; align-items:center; flex-wrap:wrap;">
              <div style="width:110px;height:110px;border-radius:50%;background:#ddd;overflow:hidden;">
                <?php if ($srcFoto !== ''): ?>
                  <img src="<?= $srcFoto ?>" alt="foto perfil" style="width:100%;height:100%;object-fit:cover;">
                <?php endif; ?>
              </div>

              <div style="flex:1; min-width:240px;">
                <h2 style="margin-bottom:6px;"><?= h($u['nombre_usuario']) ?></h2>

                <p class="post-meta" style="margin-bottom:10px;">
                  <a class="perfil-link" href="perfil.php?id=<?= (int)$idPerfil ?>&modal=followers">
                    Seguidores: <b><?= (int)$followers ?></b>
                  </a>
                  ·
                  <a class="perfil-link" href="perfil.php?id=<?= (int)$idPerfil ?>&modal=following">
                    Seguidos: <b><?= (int)$following ?></b>
                  </a>
                </p>

                <?php if (!$esMiPerfil): ?>
                  <a href="chat.php?id=<?= (int)$idPerfil ?>" class="btn-chat">Chat</a>

                  <form action="php/seguir_noseguir.php" method="post" style="margin:0; display:inline-block;">
                    <input type="hidden" name="id_seguido" value="<?= (int)$idPerfil ?>">
                    <button class="btn-primary" type="submit" style="padding:10px 14px;border-radius:12px;">
                      <?= ($yoLoSigo === 1) ? "Dejar de seguir" : "Seguir" ?>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>

            <hr style="border:none;border-top:1px solid #eee;margin:18px 0;">

            <h3 style="margin-bottom:10px;">Biografía</h3>
            <p style="margin-bottom:14px;"><?= nl2br(h($u['biografia'] ?? '')) ?></p>

            <?php if ($esMiPerfil): ?>
              <h3 style="margin:20px 0 10px;">Editar perfil</h3>

              <?php if (isset($_GET['ok'])): ?>
                <p style="color:#2ecc71;margin-bottom:10px;">Cambios guardados ✅</p>
              <?php endif; ?>
              <?php if (isset($_GET['error'])): ?>
                <p class="error-msg">No se pudo guardar. Revisa la imagen o los campos.</p>
              <?php endif; ?>

              <form action="php/perfil_update.php" method="post" enctype="multipart/form-data"
                    style="display:flex; flex-direction:column; gap:12px;">
                <label>Nueva foto de perfil (opcional)</label>
                <input class="input" type="file" name="foto_perfil" accept=".jpg,.jpeg,.png,.webp">

                <label>Biografía</label>
                <textarea class="input" name="biografia" rows="4"><?= h($u['biografia'] ?? '') ?></textarea>

                <button class="btn-primary" type="submit" style="width:fit-content;">Guardar</button>
              </form>
            <?php endif; ?>

          </div>
        </article>

        <!-- ====== PUBLICACIONES DEL PERFIL (IGUAL QUE INDEX) ====== -->
        <h3 style="margin:18px 0;">Publicaciones</h3>

        <?php if (count($pubsPerfil) === 0): ?>
          <article class="post">
            <div class="post-body">
              <h2 class="post-title">Todavía no hay publicaciones</h2>
              <p class="post-text">Este usuario aún no ha publicado nada.</p>
            </div>
          </article>
        <?php endif; ?>

        <?php foreach ($pubsPerfil as $p): ?>
          <?php
            $postId = (int)$p["id_publicacion"];
            $miTipo = miReaccion($conexion, $idYo, $postId);
            $iconoPrincipal = $emoji[$miTipo] ?? "♡";

            $resumen = resumenReacciones($conexion, $postId);
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

              <div class="post-meta" style="margin-top:10px;">
                <?php
                  $trozos = [];
                  foreach ($orden as $t) {
                    if (!empty($resumen[$t])) $trozos[] = $emoji[$t] . " " . (int)$resumen[$t];
                  }
                  echo !empty($trozos) ? implode(" · ", $trozos) : "Sin reacciones todavía";
                ?>
              </div>

              <div style="margin-top:14px;">
                <div class="post-meta" style="margin-bottom:8px;">💬 Comentarios (<?= $numComentarios ?>)</div>

                <?php if ($numComentarios === 0): ?>
                  <div class="post-meta" style="margin-bottom:10px;">Sé el primero en comentar 😊</div>
                <?php else: ?>
                  <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;">
                    <?php foreach ($comentarios as $c): ?>
                      <div style="background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:12px;padding:10px;">
                        <div style="font-weight:700;font-size:13px;">
                          u/<?= h($c["nombre_usuario"]) ?>
                          <span class="post-meta" style="margin-left:8px;"><?= h($c["fecha_comentario"]) ?></span>
                        </div>
                        <div style="margin-top:6px;font-size:14px;"><?= nl2br(h($c["texto"])) ?></div>
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
    </div>
  </div>
</div>

<!-- ===================== MODAL ===================== -->
<?php if ($modal !== ''): ?>
  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal-card" role="dialog" aria-modal="true">
      <div class="modal-head">
        <div class="modal-title">
          <?= $esFollowers ? "Seguidores" : "Seguidos" ?>
        </div>
        <a class="modal-close" href="perfil.php?id=<?= (int)$idPerfil ?>">✕</a>
      </div>

      <div class="modal-body">
        <?php if (count($listaModal) === 0): ?>
          <div class="modal-empty">No hay usuarios aquí todavía.</div>
        <?php else: ?>
          <?php foreach ($listaModal as $row): ?>
            <?php
              $idU = (int)$row['id_usuario'];
              $nombre = $row['nombre_usuario'] ?? '';
              $fotoP = $row['foto_perfil'] ?? '';
              $yoSigoA = (int)($row['yo_sigo'] ?? 0);

              $avatar = !empty($fotoP)
                ? "uploads/perfiles/" . h($fotoP)
                : "";
            ?>
            <div class="modal-row">
              <a class="modal-user" href="perfil.php?id=<?= $idU ?>">
                <div class="modal-avatar" style="<?= $avatar ? "background-image:url('".$avatar."')" : "" ?>"></div>
                <div class="modal-name"><?= h($nombre) ?></div>
              </a>

              <div class="modal-actions">
                <?php if ($esFollowers): ?>
                  <!-- Si es SEGUIDOR: botón seguir si no lo sigo -->
                  <?php if ($idU !== $idYo): ?>
                    <?php if ($yoSigoA === 0): ?>
                      <form action="php/seguir_noseguir.php" method="post" style="margin:0;">
                        <input type="hidden" name="id_seguido" value="<?= $idU ?>">
                        <button class="modal-btn modal-btn-primary" type="submit">Seguir</button>
                      </form>
                    <?php else: ?>
                      <button class="modal-btn modal-btn-muted" type="button" disabled>Siguiendo</button>
                    <?php endif; ?>
                  <?php endif; ?>

                  <!-- Suprimir seguidor (solo si es mi perfil) -->
                  <?php if ($esMiPerfil && $idU !== $idYo): ?>
                    <form action="php/eliminar_seguidor.php" method="post" style="margin:0;">
                      <input type="hidden" name="id_usuario" value="<?= $idU ?>">
                      <input type="hidden" name="id_perfil" value="<?= (int)$idPerfil ?>">
                      <input type="hidden" name="modal" value="followers">
                      <button class="modal-btn modal-btn-danger" type="submit">Suprimir</button>
                    </form>
                  <?php endif; ?>

                <?php else: ?>
                  <!-- Seguidos: suprimir = dejar de seguir (solo si es mi perfil) -->
                  <?php if ($esMiPerfil && $idU !== $idYo): ?>
                    <form action="php/dejar_de_seguir.php" method="post" style="margin:0;">
                      <input type="hidden" name="id_seguido" value="<?= $idU ?>">
                      <input type="hidden" name="id_perfil" value="<?= (int)$idPerfil ?>">
                      <input type="hidden" name="modal" value="following">
                      <button class="modal-btn modal-btn-danger" type="submit">Suprimir</button>
                    </form>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // cerrar al clicar fuera
    (function(){
      const bg = document.getElementById("modalBackdrop");
      if(!bg) return;
      bg.addEventListener("click", (e) => {
        if(e.target === bg){
          window.location.href = "perfil.php?id=<?= (int)$idPerfil ?>";
        }
      });
      document.addEventListener("keydown", (e) => {
        if(e.key === "Escape"){
          window.location.href = "perfil.php?id=<?= (int)$idPerfil ?>";
        }
      });
    })();
  </script>
<?php endif; ?>

<script src="js/reacciones.js"></script>
</body>
</html>