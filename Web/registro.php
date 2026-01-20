<?php
  session_start();
  if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
  }

  $error = $_GET['error'] ?? '';
  $msg = '';
  if ($error === 'campos') $msg = 'Rellena todos los campos.';
  if ($error === 'usuario') $msg = 'Ese nombre de usuario ya existe.';
  if ($error === 'general') $msg = 'Error al registrar. Inténtalo de nuevo.';
?>

<!-- =================================================================-->

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

  <div id="login-container" class="app-card">

    <section id="login-left">
      <h1>Bienvenido</h1>
      <p>Crea una cuenta para acceder a nuestra aplicación.</p>
    </section>

    <section id="login-right">
      <h2>Registro</h2>

      <?php if ($msg !== ''): ?>
        <p class="error-msg"><?= htmlspecialchars($msg) ?></p>
      <?php endif; ?>

      <form id="login-form" action="php/registro_process.php" method="post">
        <input id="usuario" class="input" type="text" name="usuario" placeholder="Nombre de usuario">
        <input id="correo" class="input" type="email" name="correo" placeholder="Correo electrónico">
        <input id="contrasena" class="input" type="password" name="contrasena" placeholder="Contraseña">
        <button class="btn-primary" type="submit">Registrar</button>
      </form>

      <p class="text-muted" style="margin-top:20px">
        ¡Ya tengo una cuenta! <a href="login.php">Iniciar sesión</a>
      </p>
    </section>

  </div>

  <script src="js/registro.js"></script>
</body>
</html>
