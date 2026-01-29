<?php
require_once __DIR__ . "/php/require_login.php"; // Asegura que el usuario esté logueado antes de dejarle crear un grupo
require_once __DIR__ . "/php/conexion.php";      // Cargo la función para conectarme a la base de datos

$conexion = conexionBD();              // Abro conexión con la base de datos
$yo = (int)$_SESSION["id_usuario"];    // Guardo mi id de usuario desde la sesión (a int por seguridad)

$usuarios = []; // Aquí voy a guardar los usuarios que puedo seleccionar para meterlos en el grupo

$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC"); // Saco todos los usuarios menos yo, ordenados por nombre
while ($row = mysqli_fetch_assoc($resU)) $usuarios[] = $row; // Recorro el resultado y lo meto dentro del array $usuarios

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapo texto antes de imprimirlo en HTML para evitar cosas raras (xss)
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
          <?php foreach ($usuarios as $u): ?> <!-- Recorro los usuarios para pintar un checkbox por cada uno -->
            <label style="display:block; padding:8px 0;">
              <input type="checkbox" name="usuarios[]" value="<?= (int)$u["id_usuario"] ?>"> <!-- name usuarios[] para que en POST llegue como array -->
              <?= h($u["nombre_usuario"]) ?> <!-- Imprimo el nombre escapado -->
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