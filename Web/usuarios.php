<?php
require_once __DIR__ . "/php/require_login.php"; // Si no estoy logueado, este archivo me corta o me manda al login
require_once __DIR__ . "/php/conexion.php";      // Aquí está la función conexionBD()

$conexion = conexionBD();             // Abro conexión con la base de datos
$idYo = (int)$_SESSION['id_usuario']; // Mi id de usuario (lo paso a int por seguridad)

// Esta query saca todos los usuarios menos yo, y además marca si yo sigo a cada uno (0/1)
$sql = "
SELECT
  u.id_usuario,                               -- id del usuario
  u.nombre_usuario,                           -- nombre del usuario
  u.foto_perfil,                              -- foto del usuario
  u.biografia,                                -- biografía del usuario
  CASE WHEN s.id_usuario IS NULL THEN 0 ELSE 1 END AS yo_lo_sigo -- si existe relación en seguidor => 1, si no => 0
FROM usuario u
LEFT JOIN seguidor s
  ON s.id_seguido = u.id_usuario AND s.id_usuario = ?           -- busco si yo (s.id_usuario) sigo a este usuario (s.id_seguido)
WHERE u.id_usuario <> ?                                         -- quito mi propio usuario del listado
ORDER BY u.nombre_usuario ASC                                   -- ordeno alfabéticamente
";

$stmt = mysqli_prepare($conexion, $sql);          // Preparo la consulta para poder meter parámetros de forma segura
mysqli_stmt_bind_param($stmt, "ii", $idYo, $idYo); // Meto mi id en los dos ? (uno para el JOIN y otro para el WHERE)
mysqli_stmt_execute($stmt);                       // Ejecuto la consulta
$res = mysqli_stmt_get_result($stmt);             // Cojo el resultado

$usuarios = [];                                   // Array donde guardaré todos los usuarios para pintarlos luego
while ($row = mysqli_fetch_assoc($res)) {         // Recorro cada fila del resultado
  $usuarios[] = $row;                             // La guardo dentro del array
}
mysqli_stmt_close($stmt);                         // Cierro el statement
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

  <aside id="sidebar">
    <div id="sidebar-logo">
      <span class="logo-text">AÑA</span>
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

    <header id="topbar">
      <div id="search-container">
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

    <div id="content" style="grid-template-columns: 1fr;">
      <main id="feed">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; gap:12px;">
          <h2 style="margin:0;">Descubrir personas</h2>
          <div class="text-muted" id="contador-usuarios"></div>
        </div>

        <p class="text-muted" id="sin-resultados" style="display:none; margin: 10px 0 20px;">
          No se han encontrado usuarios con ese nombre.
        </p>

        <div id="lista-usuarios">
          <?php foreach ($usuarios as $u): ?> <!-- Pinto una tarjeta por cada usuario del array -->
            <?php
              $foto = $u['foto_perfil'] ?? ''; // Cojo su foto (si viene null/undefined, dejo vacío)

              // Si hay foto, monto la ruta completa; si no, dejo src vacío para que no se muestre imagen
              $src = ($foto !== '')
                ? "uploads/perfiles/" . htmlspecialchars($foto) // htmlspecialchars para que el nombre del archivo no meta cosas raras
                : "";

              $nombre = $u['nombre_usuario'] ?? ''; // Nombre del usuario (si falta, vacío)

              // Esto lo uso para el buscador del JS: guardo el nombre en minúsculas dentro del data-name
              $nombreLower = mb_strtolower($nombre, 'UTF-8'); // mb_ para que funcione bien con tildes y ñ
            ?>

            <article
              class="post usuario-card"
              data-name="<?= htmlspecialchars($nombreLower) ?>" <!-- Nombre en minúsculas para filtrar en JS -->
              style="align-items:center;"
            >
              <div class="post-body">
                <div style="display:flex; align-items:center; gap:14px;">

                  <div style="width:54px;height:54px;border-radius:50%;background:#ddd;overflow:hidden;flex:0 0 54px;">
                    <?php if ($src !== ''): ?> <!-- Solo meto el <img> si tengo una foto real -->
                      <img src="<?= $src ?>" alt="perfil" style="width:100%;height:100%;object-fit:cover;">
                    <?php endif; ?>
                  </div>

                  <div style="flex:1; min-width:0;">
                    <div style="font-weight:700;">
                      <a href="perfil.php?id=<?= (int)$u['id_usuario'] ?>" style="color:inherit;text-decoration:none;"> <!-- Link al perfil del usuario -->
                        <?= htmlspecialchars($nombre) ?> <!-- Escapo el nombre por si alguien intenta meter HTML -->
                      </a>
                    </div>

                    <div class="post-meta" style="margin:6px 0 0;">
                      <?= htmlspecialchars(mb_strimwidth($u['biografia'] ?? '', 0, 90, '...')) ?> <!-- Recorto bio a 90 chars y la escapo -->
                    </div>
                  </div>

                  <a href="chat.php?id=<?= (int)$u['id_usuario'] ?>" class="btn-chat">Chat</a> <!-- Abre chat privado con este usuario -->

                  <form action="php/seguir_noseguir.php" method="post" style="margin:0;"> <!-- Form para seguir/dejar de seguir -->
                    <input type="hidden" name="id_seguido" value="<?= (int)$u['id_usuario'] ?>"> <!-- El usuario objetivo (al que sigo o dejo de seguir) -->
                    <button class="btn-primary" type="submit" style="padding:10px 14px;border-radius:12px;">
                      <?= ((int)$u['yo_lo_sigo'] === 1) ? "Dejar de seguir" : "Seguir" ?> <!-- Si ya lo sigo, muestro “Dejar de seguir”; si no, “Seguir” -->
                    </button>
                  </form>

                </div>
              </div>
            </article>

          <?php endforeach; ?> <!-- Fin listado usuarios -->
        </div>

      </main>
    </div>
  </div>

</div>

<script src="js/usuarios.js"></script> <!-- JS que hace el buscador/filtrado -->
</body>
</html>
