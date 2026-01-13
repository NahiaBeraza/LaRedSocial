<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
  header("Location: index.php");
  exit();
}
$showError = isset($_GET['error']);
$registroOk = (isset($_GET['registro']) && $_GET['registro'] === 'ok');
?>

<!--==========================================================-->

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

  <div id="login-container" class="app-card">

    <section id="login-left">
      <h1>Bienvenido</h1>
      <p>Inicia sesión para acceder a nuestra web.</p>
    </section>

    <section id="login-right">
      <h2>Iniciar sesión</h2>

      <?php if ($registroOk): ?>
        <p class="ok-msg">Registro completado. Ya puedes iniciar sesión ✅</p>
      <?php endif; ?>

      <?php if ($showError): ?>
        <p class="error-msg">Usuario o contraseña incorrectos</p>
      <?php endif; ?>

      <form id="login-form" action="php/login_process.php" method="post">
        <input id="usuario" class="input" type="text" name="usuario" placeholder="Usuario">
        <input id="contrasena" class="input" type="password" name="contrasena" placeholder="Contraseña">
        <button class="btn-primary" type="submit">Entrar</button>
      </form>

      <p class="text-muted" style="margin-top:20px">
        ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
      </p>
    </section>

  </div>

  <script src="js/login.js"></script>
  </body>
</html>
