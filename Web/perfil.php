<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

// --- Qué perfil se está viendo ---
$idPerfil = isset($_GET['id']) ? (int)$_GET['id'] : $idYo;
if ($idPerfil <= 0) $idPerfil = $idYo;

$esMiPerfil = ($idPerfil === $idYo);

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
$srcFoto = (!empty($foto)) ? "uploads/perfiles/" . htmlspecialchars($foto) : "";

// --- Foto del usuario logueado para el avatar del topbar ---
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

// ===================== PUBLICACIONES DEL PERFIL (estilo index) =====================
// Traemos posts del usuario del perfil + autor + num likes + si yo le di like
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
  COUNT(r.id_usuario) AS likes_count,
  MAX(CASE WHEN r.id_usuario = ? THEN 1 ELSE 0 END) AS liked_by_me
FROM publicacion p
JOIN usuario u ON u.id_usuario = p.id_usuario
LEFT JOIN reaccion r
  ON r.id_publicacion = p.id_publicacion AND r.tipo = 'LIKE'
WHERE p.id_usuario = ?
GROUP BY p.id_publicacion
ORDER BY p.fecha_publicacion DESC
";

$stmtP = mysqli_prepare($conexion, $sqlP);
mysqli_stmt_bind_param($stmtP, "ii", $idYo, $idPerfil);
mysqli_stmt_execute($stmtP);
$resP = mysqli_stmt_get_result($stmtP);

$pubsPerfil = [];
while ($row = mysqli_fetch_assoc($resP)) {
  $pubsPerfil[] = $row;
}
mysqli_stmt_close($stmtP);
// ================================================================================

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
    <div id="sidebar-logo"><span class="logo-text">NOMBRE DE LA PÁGINA</span></div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item"><a href="index.php" style="color:inherit;text-decoration:none;">Home</a></li>
        <li class="nav-item"><a href="usuarios.php" style="color:inherit;text-decoration:none;">Usuarios</a></li>
        <li class="nav-item active">Perfil</li>
      </ul>

      <div class="nav-title">Account</div>
      <ul class="nav-section">
        <li class="nav-item">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?></li>
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
                <h2 style="margin-bottom:6px;"><?= htmlspecialchars($u['nombre_usuario']) ?></h2>

                <p class="post-meta" style="margin-bottom:10px;">
                  Seguidores: <b><?= $followers ?></b> · Seguidos: <b><?= $following ?></b>
                </p>
                  
                <?php if (!$esMiPerfil): ?>
                  <a href="chat.php?id=<?= (int)$idPerfil ?>" class="btn-chat"> Chat</a>

                  <form action="php/seguir_noseguir.php" method="post" style="margin:0;">
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
            <p style="margin-bottom:14px;">
              <?= nl2br(htmlspecialchars($u['biografia'] ?? '')) ?>
            </p>

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
                <textarea class="input" name="biografia" rows="4"><?= htmlspecialchars($u['biografia'] ?? '') ?></textarea>

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
          <article class="post">

            <div class="post-votes">
              <!-- LIKE (mismo que index) -->
              <form action="php/like.php" method="post" style="margin:0;">
                <input type="hidden" name="id_publicacion" value="<?= (int)$p['id_publicacion'] ?>">
                <button class="vote up" type="submit" title="Me gusta">
                  <?= ((int)$p['liked_by_me'] === 1) ? "♥" : "♡" ?>
                </button>
              </form>

              <span class="vote-count"><?= (int)$p['likes_count'] ?></span>

              <button class="vote down" type="button" disabled style="opacity:.3;cursor:not-allowed;">▼</button>
            </div>

            <div class="post-body">

              <h2 class="post-title">
                Publicación #<?= (int)$p['id_publicacion'] ?>
              </h2>

              <p class="post-meta">
                Posted by <span class="post-author">u/<?= htmlspecialchars($p['nombre_usuario']) ?></span>
                <?php if (!empty($p['ubicacion'])): ?>
                  · <?= htmlspecialchars($p['ubicacion']) ?>
                <?php endif; ?>
              </p>

              <?php if (!empty($p['imagen'])): ?>
                <div style="margin:10px 0;">
                  <img
                    src="uploads/<?= htmlspecialchars($p['imagen']) ?>"
                    alt="publicación"
                    style="max-width:100%;border-radius:12px;"
                  >
                </div>
              <?php endif; ?>

              <p class="post-text">
                <?= nl2br(htmlspecialchars($p['pie_de_foto'])) ?>
              </p>

              <?php if (!empty($p['etiquetas'])): ?>
                <p class="post-meta">
                  #<?= htmlspecialchars($p['etiquetas']) ?>
                </p>
              <?php endif; ?>

              <div class="post-actions">
                <button class="post-action" type="button" disabled style="opacity:.4">💬 Comentarios (próximamente)</button>
                <button class="post-action" type="button" disabled style="opacity:.4">↗ Share</button>
                <button class="post-action" type="button" disabled style="opacity:.4">💾 Save</button>
              </div>

            </div>

          </article>
        <?php endforeach; ?>

      </main>
    </div>
  </div>

</div>

</body>
</html>
