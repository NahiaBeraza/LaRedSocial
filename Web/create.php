<?php
require_once __DIR__ . "/php/require_login.php"; // Obliga a que el usuario esté logueado antes de poder crear una publicación
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

    <?php if (isset($_GET['error'])): ?> <!-- Si viene un error por GET, muestro el mensaje correspondiente -->
      <p class="error-msg">
        <?php
          $e = $_GET['error']; // Guardo el tipo de error recibido por la URL

          // Según el código de error, muestro un mensaje distinto
          if ($e === 'campos') echo "Debes subir una imagen y escribir un texto.";
          else if ($e === 'img') echo "Imagen no válida (JPG/PNG/WEBP) o demasiado grande.";
          else echo "Error al publicar.";
        ?>
      </p>
    <?php endif; ?>

    <form action="php/publicar_process.php" method="post" enctype="multipart/form-data">
      <!-- enctype multipart porque se envía una imagen -->

      <label>Imagen</label><br>
      <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp" required><br><br>
      <!-- Campo obligatorio para subir la imagen de la publicación -->

      <label>Texto (pie de foto)</label><br>
      <textarea name="pie_de_foto" rows="4" class="input" style="width:100%;" required></textarea><br><br>
      <!-- Texto principal de la publicación -->

      <label>Ubicación (opcional)</label><br>
      <input type="text" name="ubicacion" class="input" placeholder="Madrid, España"><br><br>
      <!-- Ubicación opcional -->

      <label>Etiquetas (opcional)</label><br>
      <input type="text" name="etiquetas" class="input" placeholder="viaje,amigos"><br><br>
      <!-- Etiquetas separadas por comas -->

      <button class="btn-primary" type="submit">Publicar</button>
      <a class="btn-secondary" href="index.php" style="margin-left:10px;">Volver</a>
    </form>
    </div>
  </div>

</body>
</html>
