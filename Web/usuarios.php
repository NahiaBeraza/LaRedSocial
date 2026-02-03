<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

// --- 1. Obtener mi foto de perfil para el topbar ---
$miFoto = '';
$sqlYo = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
$stmtYo = mysqli_prepare($conexion, $sqlYo);
mysqli_stmt_bind_param($stmtYo, "i", $idYo);
mysqli_stmt_execute($stmtYo);
$resYo = mysqli_stmt_get_result($stmtYo);
if ($rowYo = mysqli_fetch_assoc($resYo)) {
    $miFoto = $rowYo['foto_perfil'] ?? '';
}
mysqli_stmt_close($stmtYo);

$miAvatarStyle = "background:#ccc;";
if (!empty($miFoto)) {
    $miAvatarStyle = "background-image:url('uploads/perfiles/" . htmlspecialchars($miFoto, ENT_QUOTES, 'UTF-8') . "');"
                   . "background-size:cover;background-position:center;";
}

// --- 2. Obtener usuarios ---
$sql = "
SELECT
  u.id_usuario, u.nombre_usuario, u.foto_perfil, u.biografia,
  CASE WHEN s.id_usuario IS NULL THEN 0 ELSE 1 END AS yo_lo_sigo
FROM usuario u
LEFT JOIN seguidor s ON s.id_seguido = u.id_usuario AND s.id_usuario = ?
WHERE u.id_usuario <> ?
ORDER BY u.nombre_usuario ASC
";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $idYo, $idYo);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$usuarios = [];
while ($row = mysqli_fetch_assoc($res)) $usuarios[] = $row;
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios</title>
  
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="stylesheet" href="estilos/index.css">
  <link rel="stylesheet" href="estilos/usuarios.css">


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
        <li class="nav-item"><a href="index.php" style="color:inherit;text-decoration:none;">Home</a></li>
        <li class="nav-item active">Usuarios</li>
        <li class="nav-item"><a href="perfil.php" style="color:inherit;text-decoration:none;">Mi perfil</a></li>
        <li class="nav-item"><a href="logout.php" style="color:inherit;text-decoration:none;">Cerrar sesión</a></li>
      </ul>
    </nav>
  </aside>

  <div id="main-layout">
    <header id="topbar">
      <div id="search-container">
        <input id="search-input" type="text" placeholder="Buscar usuarios..." autocomplete="off">
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

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
          <h2 style="margin:0; font-size: 22px; font-weight: 800;">Descubrir personas</h2>
          <div id="contador-usuarios">
            </div>
        </div>

        <div id="sin-resultados" style="display:none;">
          No se han encontrado usuarios con ese nombre.
        </div>

        <div id="lista-usuarios">
          <?php foreach ($usuarios as $u): ?>
            <?php
              $foto = $u['foto_perfil'] ?? '';
              $src = ($foto !== '') ? "uploads/perfiles/" . htmlspecialchars($foto) : "";
              $nombre = $u['nombre_usuario'] ?? '';
              $nombreLower = mb_strtolower($nombre, 'UTF-8');
            ?>

            <article class="post usuario-card" data-name="<?= htmlspecialchars($nombreLower) ?>">
              
              <div class="post-body">
                <div style="display:flex; align-items:center; gap:18px;">

                  <div style="width:54px; height:54px; border-radius:50%; background:#f0f0f5; overflow:hidden; flex-shrink:0; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <?php if ($src !== ''): ?>
                      <img src="<?= $src ?>" alt="perfil" style="width:100%; height:100%; object-fit:cover;">
                    <?php endif; ?>
                  </div>

                  <div style="flex:1; min-width:0;">
                    <div style="font-weight:800; font-size: 16px; margin-bottom: 4px;">
                      <a href="perfil.php?id=<?= (int)$u['id_usuario'] ?>" style="color:var(--txt); text-decoration:none;">
                        <?= htmlspecialchars($nombre) ?>
                      </a>
                    </div>
                    <div style="font-size: 13px; color: var(--mut); line-height: 1.4;">
                      <?= htmlspecialchars(mb_strimwidth($u['biografia'] ?? '', 0, 90, '...')) ?>
                    </div>
                  </div>

                  <div class="usuario-actions">
                    <a href="chat.php?id=<?= (int)$u['id_usuario'] ?>" class="btn-chat-user">Chat</a>

                    <form action="php/seguir_noseguir.php" method="post" style="margin:0;">
                      <input type="hidden" name="id_seguido" value="<?= (int)$u['id_usuario'] ?>">
                      <button class="btn-primary" type="submit" style="padding:10px 18px; border-radius:12px; font-size: 13px;">
                        <?= ((int)$u['yo_lo_sigo'] === 1) ? "Dejar de seguir" : "Seguir" ?>
                      </button>
                    </form>
                  </div>

                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      </main>
    </div>
  </div>
</div>

<script src="js/usuarios.js"></script>
</body>
</html>