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

  <form action="php/publicar_process.php" method="post" enctype="multipart/form-data">
    <label>Imagen</label><br>
    <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp" required><br><br>

    <label>Texto (pie de foto)</label><br>
    <textarea name="pie_de_foto" rows="4" class="input" style="width:100%;" required></textarea><br><br>

    <label>Ubicación (opcional)</label><br>
    <input type="text" name="ubicacion" class="input" placeholder="Madrid, España"><br><br>

    <label>Etiquetas (opcional)</label><br>
    <input type="text" name="etiquetas" class="input" placeholder="viaje,amigos"><br><br>

    <button class="btn-primary" type="submit">Publicar</button>
    <a class="btn-secondary" href="index.php" style="margin-left:10px;">Volver</a>
  </form>
  </div>
</div>

</body>
</html>
