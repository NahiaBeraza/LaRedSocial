<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

$sql = "
SELECT
  u.id_usuario,
  u.nombre_usuario,
  u.foto_perfil,
  u.biografia,
  CASE WHEN s.id_usuario IS NULL THEN 0 ELSE 1 END AS yo_lo_sigo
FROM usuario u
LEFT JOIN seguidor s
  ON s.id_seguido = u.id_usuario AND s.id_usuario = ?
WHERE u.id_usuario <> ?
ORDER BY u.nombre_usuario ASC
";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $idYo, $idYo);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$usuarios = [];
while ($row = mysqli_fetch_assoc($res)) {
  $usuarios[] = $row;
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios</title>
  <link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>

<div id="app">

  <!-- Barra lateral de navegación -->
  <aside id="sidebar">
    <div id="sidebar-logo">
      <span class="logo-text">slothit</span>
    </div>

    <nav id="sidebar-nav">
      <ul class="nav-section">
        <li class="nav-item">
          <a href="index.php" style="color:inherit;text-decoration:none;">Home</a>
        </li>
        <li class="nav-item active">Usuarios</li>
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

    <!-- Barra superior -->
    <header id="topbar">
      <div id="search-container">
        <!-- BUSCADOR REAL -->
        <input id="search-input" type="text" placeholder="Buscar usuarios..." autocomplete="off">
      </div>

      <div id="topbar-actions">
        <a href="create.php">
          <button id="create-btn">Create</button>
        </a>

        <a href="perfil.php" id="user-avatar-link" title="Perfil">
          <div id="user-avatar"></div>
        </a>
      </div>
    </header>

    <!-- Contenido principal -->
    <div id="content" style="grid-template-columns: 1fr;">
      <main id="feed">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; gap:12px;">
          <h2 style="margin:0;">Descubrir personas</h2>
          <div class="text-muted" id="contador-usuarios"></div>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <p class="text-muted" id="sin-resultados" style="display:none; margin: 10px 0 20px;">
          No se han encontrado usuarios con ese nombre.
        </p>

        <!-- LISTA DE USUARIOS -->
        <div id="lista-usuarios">
          <?php foreach ($usuarios as $u): ?>
            <?php
              $foto = $u['foto_perfil'] ?? '';
              $src = ($foto !== '')
                ? "uploads/perfiles/" . htmlspecialchars($foto)
                : "";

              $nombre = $u['nombre_usuario'] ?? '';
              $nombreLower = mb_strtolower($nombre, 'UTF-8');
            ?>

            <article
              class="post usuario-card"
              data-name="<?= htmlspecialchars($nombreLower) ?>"
              style="align-items:center;"
            >
              <div class="post-body">
                <div style="display:flex; align-items:center; gap:14px;">

                  <!-- Foto perfil -->
                  <div style="width:54px;height:54px;border-radius:50%;background:#ddd;overflow:hidden;flex:0 0 54px;">
                    <?php if ($src !== ''): ?>
                      <img src="<?= $src ?>" alt="perfil" style="width:100%;height:100%;object-fit:cover;">
                    <?php endif; ?>
                  </div>

                  <!-- Info -->
                  <div style="flex:1; min-width:0;">
                    <div style="font-weight:700;">
                      <a href="perfil.php?id=<?= (int)$u['id_usuario'] ?>" style="color:inherit;text-decoration:none;">
                        <?= htmlspecialchars($nombre) ?>
                      </a>
                    </div>

                    <div class="post-meta" style="margin:6px 0 0;">
                      <?= htmlspecialchars(mb_strimwidth($u['biografia'] ?? '', 0, 90, '...')) ?>
                    </div>
                  </div>

                  <!-- Chat -->
                  <a href="chat.php?id=<?= (int)$u['id_usuario'] ?>" class="btn-chat">Chat</a>

                  <!-- Seguir -->
                  <form action="php/seguir_noseguir.php" method="post" style="margin:0;">
                    <input type="hidden" name="id_seguido" value="<?= (int)$u['id_usuario'] ?>">
                    <button class="btn-primary" type="submit" style="padding:10px 14px;border-radius:12px;">
                      <?= ((int)$u['yo_lo_sigo'] === 1) ? "Dejar de seguir" : "Seguir" ?>
                    </button>
                  </form>

                </div>
              </div>
            </article>

          <?php endforeach; ?>
        </div>

      </main>
    </div>
  </div>

</div>

<!-- JS externo -->
<script src="js/usuarios.js"></script>
</body>
</html>
