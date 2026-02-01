  <!--INICIAR SESION
      Se inicia sesion para saber si el usuario ya esta autenticado.
      Este código inicia o reanuda una sesion de php.
      Es decir, si yo he iniciado sesion, podre acceder a la aplicacion sin problema, pero si la sesion esta cerrada
      tendre que iniciar sesion de nuevo.
  -->
  <?php
  session_start();  
  if (isset($_SESSION['id_usuario'])) { 
                                        
    header("Location: index.php");
    exit();

  }
  /*
    Comprueba si existe la variable id_usuario en la sesión.
    Esa variable normalmente se guarda cuando el usuario hace login correctamente.
    Si existe,el usuario ya está logueado.
    Se redirige automáticamente a index.php.
    exit() detiene el script para que no se siga ejecutando el resto del código.
  */

  $showError = isset($_GET['error']);
  $registroOk = (isset($_GET['registro']) && $_GET['registro'] === 'ok');

  //se crean variables para mostrar mensajes según parámetros recibidos por la URL
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

        <!-- Si $registroOk es true, se muestra el mensaje.-->
        <?php if ($registroOk): ?>
          <p class="ok-msg">Registro completado. Ya puedes iniciar sesión ✅</p> 
        <?php endif; ?>

        <!-- Si no muestra mensaje de error-->
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

    <script src="js/login.js"></script> <!--CONECTAR CON EL JS -->
    </body>
  </html>
