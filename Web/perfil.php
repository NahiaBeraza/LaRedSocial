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

/* ===================== PUBLICACIONES DEL PERFIL ===================== */
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
  u.foto_perfil,
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

// Comentarios: Solo count
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

/* ===================== MODALES: seguidores / seguidos ===================== */
$listaModal = [];
$esFollowers = ($modal === 'followers');
$esFollowing = ($modal === 'following');

if ($modal !== '') {
  if ($esFollowers) {
    // personas que SIGUEN a idPerfil
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
    // personas a las que idPerfil SIGUE
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
  "LIKE" => "👍", "LOVE" => "❤️", "LAUGH" => "😂", "WOW" => "😮", "SAD" => "😢",
];
$orden = ["LOVE","LAUGH","WOW","SAD","LIKE"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="stylesheet" href="estilos/index.css">
  
  <link rel="stylesheet" href="estilos/perfil.css">
  
  <link rel="stylesheet" href="estilos/modal_comentario.css">
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

    <div id="content-perfil">
      
      <main id="feed">

        <article class="profile-header-card">
          
            <div class="profile-header-top">
              
              <div class="profile-avatar-large">
                <?php if ($srcFoto !== ''): ?>
                  <img src="<?= $srcFoto ?>" alt="foto perfil" style="width:100%;height:100%;object-fit:cover;">
                <?php endif; ?>
              </div>

              <div style="flex:1; min-width:240px;">
                <h2 style="margin-bottom:8px; font-size:26px;"><?= h($u['nombre_usuario']) ?></h2>

                <p class="post-meta" style="margin-bottom:14px; font-size:15px;">
                  <a class="perfil-link" href="perfil.php?id=<?= (int)$idPerfil ?>&modal=followers">
                    <b><?= (int)$followers ?></b> Seguidores
                  </a>
                  <span style="margin:0 8px;">·</span>
                  <a class="perfil-link" href="perfil.php?id=<?= (int)$idPerfil ?>&modal=following">
                    <b><?= (int)$following ?></b> Seguidos
                  </a>
                </p>

                <?php if (!$esMiPerfil): ?>
                  <div style="display:flex; gap:10px; align-items:center; margin-top:18px;">
                    <a href="chat.php?id=<?= (int)$idPerfil ?>" class="btn-chat" style="padding: 10px 16px; font-size:14px;">Chat</a>
                    <form action="php/seguir_noseguir.php" method="post" style="margin:0;">
                      <input type="hidden" name="id_seguido" value="<?= (int)$idPerfil ?>">
                      <button class="btn-primary" type="submit" style="padding:10px 20px; border-radius:12px; font-size:14px;">
                        <?= ($yoLoSigo === 1) ? "Dejar de seguir" : "Seguir" ?>
                      </button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <hr class="profile-divider">

            <h3>Biografía</h3>
            <p style="margin-bottom:14px; line-height:1.6; color:#4b5563;"><?= nl2br(h($u['biografia'] ?? '')) ?></p>

            <?php if ($esMiPerfil): ?>
              <h3>Editar perfil</h3>

              <?php if (isset($_GET['ok'])): ?>
                <div style="background:#dcfce7; color:#166534; padding:12px 16px; border-radius:12px; font-size:14px; margin-bottom:16px; border:1px solid #bbf7d0;">
                  Cambios guardados correctamente ✅
                </div>
              <?php endif; ?>
              
              <?php if (isset($_GET['error'])): ?>
                <div class="error-msg" style="margin-bottom:16px;">No se pudo guardar. Revisa la imagen o los campos.</div>
              <?php endif; ?>

              <div class="edit-profile-box">
                <form action="php/perfil_update.php" method="post" enctype="multipart/form-data" id="formEditProfile">
                  
                  <div class="edit-group">
                    <label class="edit-label">Cambiar foto de perfil</label>
                    <input type="file" id="filePhoto" name="foto_perfil" accept=".jpg,.jpeg,.png,.webp" class="input-file-hidden">
                    <label for="filePhoto" class="input-file-trigger" id="fileLabel">
                      <span style="font-size: 28px;">📷</span>
                      <span id="fileName" style="font-weight:500;">Haz click para seleccionar una nueva imagen...</span>
                    </label>
                  </div>

                  <div class="edit-group" style="margin-top: 24px;">
                    <label class="edit-label">Biografía</label>
                    <textarea class="input edit-textarea" name="biografia" placeholder="Escribe algo sobre ti..."><?= h($u['biografia'] ?? '') ?></textarea>
                  </div>

                  <div style="text-align: right; margin-top: 24px;">
                     <button class="btn-primary" type="submit" style="padding: 12px 32px; font-size: 15px;">Guardar cambios</button>
                  </div>
                </form>
              </div>
              
              <script>
                document.getElementById('filePhoto').addEventListener('change', function(e) {
                  const fileName = e.target.files[0]?.name || "Haz click para seleccionar una nueva imagen...";
                  document.getElementById('fileName').textContent = fileName;
                  document.getElementById('fileLabel').classList.add('file-selected');
                });
              </script>

            <?php endif; ?>

        </article>

        <h3 style="margin:18px 0;">Publicaciones</h3>

        <div class="profile-feed" style="max-width: 600px; margin: 0 auto;">

            <?php if (empty($pubsPerfil)): ?>
              <div style="text-align:center; padding: 40px; color:#666; background: #fff; border: 1px solid #dbdbdb; border-radius: 3px;">
                <p class="post-text">Este usuario aún no ha publicado nada.</p>
              </div>
            <?php else: ?>

              <?php foreach ($pubsPerfil as $p): ?>
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
                  $perfilLink = "perfil.php?id=" . (int)$p["id_usuario"];
                ?>

                <article class="post" id="post-<?= $postId ?>" style="margin-bottom: 30px;">
                  <div class="post-head">
                    <a class="post-avatar" href="<?= h($perfilLink) ?>">
                      <?php if ($avatarAutor): ?>
                        <img src="<?= h($avatarAutor) ?>" alt="">
                      <?php else: ?>
                        <span></span>
                      <?php endif; ?>
                    </a>
                    <div class="post-headInfo">
                      <a class="post-user" href="<?= h($perfilLink) ?>"><?= h($p["nombre_usuario"]) ?></a>
                      <?php if (!empty($p["ubicacion"])): ?>
                        <div class="post-loc"><?= h($p["ubicacion"]) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <?php if (!empty($p['imagen'])): ?>
                    <div class="post-media">
                      <img src="uploads/<?= h($p['imagen']) ?>" alt="publicación">
                    </div>
                  <?php endif; ?>

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

                    <button type="button" class="post-commentBtn js-open-comments" data-post="<?= $postId ?>">
                      💬 Comentarios (<span id="ccount-<?= $postId ?>"><?= (int)$numComentarios ?></span>)
                    </button>
                  </div>

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

                  <div class="post-textWrap">
                    <span class="post-userInline"><?= h($p["nombre_usuario"]) ?></span>
                    <span><?= nl2br(h($p["pie_de_foto"])) ?></span>
                  </div>

                  <?php if (!empty($p['etiquetas'])): ?>
                    <div class="post-tags">#<?= h($p['etiquetas']) ?></div>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
        </div>

      </main>
    </div>
  </div>
</div>

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

              $avatar = !empty($fotoP) ? "uploads/perfiles/" . h($fotoP) : "";
            ?>
            <div class="modal-row">
              <a class="modal-user" href="perfil.php?id=<?= $idU ?>">
                <div class="modal-avatar" style="<?= $avatar ? "background-image:url('".$avatar."')" : "" ?>"></div>
                <div class="modal-name"><?= h($nombre) ?></div>
              </a>
              <div class="modal-actions">
                <?php if ($esFollowers): ?>
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
                  <?php if ($esMiPerfil && $idU !== $idYo): ?>
                    <form action="php/eliminar_seguidor.php" method="post" style="margin:0;">
                      <input type="hidden" name="id_usuario" value="<?= $idU ?>">
                      <input type="hidden" name="id_perfil" value="<?= (int)$idPerfil ?>">
                      <input type="hidden" name="modal" value="followers">
                      <button class="modal-btn modal-btn-danger" type="submit">Suprimir</button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
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
    (function(){
      const bg = document.getElementById("modalBackdrop");
      if(!bg) return;
      bg.addEventListener("click", (e) => {
        if(e.target === bg) window.location.href = "perfil.php?id=<?= (int)$idPerfil ?>";
      });
      document.addEventListener("keydown", (e) => {
        if(e.key === "Escape") window.location.href = "perfil.php?id=<?= (int)$idPerfil ?>";
      });
    })();
  </script>
<?php endif; ?>

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
<script src="js/comentarios.js"></script>

</body>
</html>