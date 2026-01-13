<?php
require_once __DIR__ . "/php/require_login.php";
include_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

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

// Traemos posts + autor + num likes + si yo le di like
$sql = "
SELECT
  p.id_publicacion,
  p.id_usuario,
  p.pie_de_foto,
  p.imagen,
  p.ubicacion,
  p.etiquetas,
  u.nombre_usuario,
  COUNT(r.id_usuario) AS likes_count,
  MAX(CASE WHEN r.id_usuario = ? THEN 1 ELSE 0 END) AS liked_by_me
FROM publicacion p
JOIN usuario u ON u.id_usuario = p.id_usuario
LEFT JOIN reaccion r
  ON r.id_publicacion = p.id_publicacion AND r.tipo = 'LIKE'
GROUP BY p.id_publicacion
ORDER BY p.id_publicacion DESC
";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idYo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
  $posts[] = $row;
}
mysqli_stmt_close($stmt);
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

  <!-- ========== SIDEBAR IZQUIERDA ========== -->
  <aside id="sidebar">
    <div id="sidebar-logo">
      <span class="logo-text">NOMBRE DE LA PÁGINA</span>
    </div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item active">Home</li>
        <li class="nav-item">Popular</li>
        <li class="nav-item">All</li>
        <li class="nav-item">
          <a href="usuarios.php" style="color:inherit;text-decoration:none;">Usuarios</a>
        </li>
      </ul>

      <div class="nav-title">Account</div>
      <ul class="nav-section">
        <li class="nav-item">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?></li>

        <!-- PERFIL: acceso directo -->
        <li class="nav-item">
          <a href="perfil.php" style="color:inherit;text-decoration:none;">Mi perfil</a>
        </li>

        <li class="nav-item">
          <a href="logout.php" style="color:inherit;text-decoration:none;">Cerrar sesión</a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- ========== CONTENIDO PRINCIPAL ========== -->
  <div id="main-layout">

    <!-- ===== HEADER SUPERIOR ===== -->
    <header id="topbar">
      <div id="search-container">
        <input id="search-input" type="text" placeholder="Search stories">
      </div>

      <div id="topbar-actions">
        <a href="create.php"><button id="create-btn">Create</button></a>

        <!-- PERFIL: avatar clicable + con foto si existe -->
        <a href="perfil.php" title="Perfil" style="display:inline-block;">
          <div id="user-avatar" style="<?= $miAvatarStyle ?>"></div>
        </a>
      </div>
    </header>

    <!-- ===== CUERPO ===== -->
    <div id="content">

      <!-- ==== FEED CENTRAL ==== -->
      <main id="feed">

        <?php if (count($posts) === 0): ?>
          <article class="post">
            <div class="post-body">
              <h2 class="post-title">Todavía no hay publicaciones</h2>
              <p class="post-text">Pulsa en Create para subir la primera 📸</p>
            </div>
          </article>
        <?php endif; ?>

        <?php foreach ($posts as $p): ?>
          <article class="post">

            <div class="post-votes">
              <!-- LIKE -->
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
              <?php endif; ?> <!-- RELACIONAR BASE DE DATOS: COMPROBAR SI EXISTE EN LA BASE DE DATOS PARA MOSTRAR LA FOTO-->

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

      <!-- ==== COLUMNA DERECHA ==== -->
      <aside id="right-panel">
        <section class="panel-section">
          <h3 class="panel-title">Recent Posts</h3>

          <?php foreach (array_slice($posts, 0, 5) as $p): ?>
            <div class="panel-item">
              <p class="panel-item-title">
                Post #<?= (int)$p['id_publicacion'] ?>
              </p>
              <span class="panel-item-meta">
                u/<?= htmlspecialchars($p['nombre_usuario']) ?>
              </span>
            </div>
          <?php endforeach; ?>

        </section>
      </aside>

    </div>
  </div>

</div>

</body>
</html>
