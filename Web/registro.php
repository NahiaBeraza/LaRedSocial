<?php
  session_start();
  if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
  }
  /*
  Se inicia la sesión.
  Se comprueba si el usuario ya está logueado.
  Si lo está, se le redirige a la página principal.
  exit() corta la ejecución del script.
  */

  //Evita que un usuario con sesión iniciada pueda acceder a la página de registro.

  $error = $_GET['error'] ?? '';
  $msg = '';
  //Sirve para evitar errores

  if ($error === 'campos') $msg = 'Rellena todos los campos.';
  if ($error === 'usuario') $msg = 'Ese nombre de usuario ya existe.';
  if ($error === 'general') $msg = 'Error al registrar. Inténtalo de nuevo.';
  //Se traduce un código de error recibido por la URL en un mensaje comprensible para el usuario.
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

      <!--MOSTRAR ERRORES-->
      <?php if ($msg !== ''): ?>
        <p class="error-msg"><?= htmlspecialchars($msg) ?></p>
      <?php endif; ?>
      <!--
      Solo muestra el <p> si hay mensaje, evita mostrar un parrafo vacio.

      htmlspecialchars($msg): Convierte caracteres especiales en HTML seguro.
                              Protege contra XSS (inyección de código)
      En resumen, muestra el mensaje de error de forma segura evitando inyección de código HTML.
      
      -->


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

  <script src="js/registro.js"></script> <!-- JAVA SCRIPT -->
</body>
</html>
