<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$idYo = (int)$_SESSION['id_usuario'];

// Traer usuarios (menos yo) + si los sigo o no
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
while ($row = mysqli_fetch_assoc($res)) $usuarios[] = $row;
mysqli_stmt_close($stmt);
?>

<!-- ///////////////////////////////////////////////////////////////////// -->
 
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios</title>
  <link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>

<div id="app">

  <aside id="sidebar">
    <div id="sidebar-logo"><span class="logo-text">slothit</span></div>
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
        <input id="search-input" type="text" placeholder="Buscar (más adelante)">
      </div>

      <div id="topbar-actions">
        <a href="create.php"><button id="create-btn">Create</button></a>
        <a href="perfil.php" id="user-avatar-link" title="Perfil">
          <div id="user-avatar"></div>
        </a>
      </div>
    </header>

    <div id="content" style="grid-template-columns: 1fr;">
      <main id="feed">
        <h2 style="margin-bottom:18px;">Descubrir personas</h2>

        <?php foreach ($usuarios as $u): ?>
          <article class="post" style="align-items:center;">
            <div class="post-body">

              <div style="display:flex; align-items:center; gap:14px;">
                <?php
                  $foto = $u['foto_perfil'] ?? '';
                  $src = ($foto !== '') ? "uploads/perfiles/" . htmlspecialchars($foto) : "";
                ?>
                <div style="width:54px;height:54px;border-radius:50%;background:#ddd;overflow:hidden;flex:0 0 54px;">
                  <?php if ($src !== ''): ?>
                    <img src="<?= $src ?>" alt="perfil" style="width:100%;height:100%;object-fit:cover;">
                  <?php endif; ?>
                </div>

                <div style="flex:1;">
                  <div style="font-weight:700;">
                    <a href="perfil.php?id=<?= (int)$u['id_usuario'] ?>" style="color:inherit;text-decoration:none;">
                      <?= htmlspecialchars($u['nombre_usuario']) ?>
                    </a>
                  </div>
                  <div class="post-meta" style="margin:6px 0 0;">
                    <?= htmlspecialchars(mb_strimwidth($u['biografia'] ?? '', 0, 90, '...')) ?>
                  </div>
                </div>

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

      </main>
    </div>
  </div>

</div>

</body>
</html>
