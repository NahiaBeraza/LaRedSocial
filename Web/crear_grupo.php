<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

$usuarios = [];
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC");
while ($row = mysqli_fetch_assoc($resU)) $usuarios[] = $row;

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="estilos/estilos.css">
  <title>Crear grupo</title>
</head>
<body>
  <div class="chat" style="max-width:700px;">
    <div class="chat__main" style="height:auto;min-height:auto;">
      <div class="chat__topbar">
        <h2 class="chat__titulo">Crear grupo</h2>
        <a class="chat__sideBtn" href="index.php" style="max-width:160px;">Volver</a>
      </div>

      <form class="chat__form" method="POST" action="php/crear_grupo.php" style="flex-direction:column;">
        <input class="chat__input" type="text" name="nombre_grupo" placeholder="Nombre del grupo" required>

        <div style="padding:12px;">
          <p style="margin:0 0 10px 0; opacity:.8;">Selecciona usuarios:</p>
          <?php foreach ($usuarios as $u): ?>
            <label style="display:block; padding:8px 0;">
              <input type="checkbox" name="usuarios[]" value="<?= (int)$u["id_usuario"] ?>">
              <?= h($u["nombre_usuario"]) ?>
            </label>
          <?php endforeach; ?>
        </div>

        <div style="display:flex; gap:8px; padding:12px;">
          <button class="chat__btn" type="submit">Crear</button>
          <a class="chat__sideBtn" href="usuarios.php">Ir a usuarios</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
