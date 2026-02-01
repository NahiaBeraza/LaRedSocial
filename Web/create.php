<?php
require_once __DIR__ . "/php/require_login.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear publicación</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

  <div class="create-post-container">
    <div class="create-post-card">
      <h2>Crear publicación</h2>

      <?php if (isset($_GET['error'])): ?>
        <p class="error-msg">
          <?php
            $e = $_GET['error'];
            if ($e === 'campos') echo "Debes subir una imagen y escribir un texto.";
            else if ($e === 'img') echo "Imagen no válida (JPG/PNG/WEBP) o demasiado grande.";
            else echo "Error al publicar.";
          ?>
        </p>
      <?php endif; ?>

      <form class="create-post-form" action="php/publicar_process.php" method="post" enctype="multipart/form-data">

        <div class="form-group">
          <label for="imagen">Imagen</label>
          <input class="file-input" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp" required>
        </div>

        <div class="form-group">
          <label for="pie_de_foto">Texto (pie de foto)</label>
          <textarea id="pie_de_foto" name="pie_de_foto" rows="4" class="input" required></textarea>
        </div>

        <div class="form-group">
          <label for="ubicacion">Ubicación (opcional)</label>
          <input id="ubicacion" type="text" name="ubicacion" class="input" placeholder="Madrid, España">
        </div>

        <div class="form-group">
          <label for="etiquetas">Etiquetas (opcional)</label>
          <input id="etiquetas" type="text" name="etiquetas" class="input" placeholder="viaje,amigos">
        </div>

        <div class="form-actions">
          <button class="btn-primary" type="submit">Publicar</button>
          <a class="btn-secondary" href="index.php">Volver</a>
        </div>

      </form>
    </div>
  </div>

</body>
</html>
