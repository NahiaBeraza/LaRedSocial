
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Virtual - Login</title>
    <link rel="stylesheet" href="./css/estilos.css">
</head>

<body class="login-page">


    <!-- CONTENEDOR PRINCIPAL DEL LOGIN -->
    <div id="login-container" class="app-card">

        <!-- PANEL IZQUIERDO -->
        <div id="login-left">
            <h1>Librería Virtual</h1>
            <p>Accede a tu cuenta para continuar</p>
        </div>

        <!-- PANEL DERECHO -->
        <div id="login-right">
            <h2>Iniciar sesión</h2>

            <form id="login-form" action="SrvValidarEntrada" method="post">

                <div>
                    <label>Usuario</label>
                    <input class="input" type="text" name="usuario" required>
                </div>

                <div>
                    <label>Contraseña</label>
                    <input class="input" type="password" name="clave" required>
                </div>

                <button class="btn-primary" type="submit">Entrar</button>
            </form>

            <p class="text-muted" style="margin-top:20px;">
                ¿No estás registrado?
                <a href="registro_usuario.jsp" style="color:#6c63ff; font-weight:600;">Pincha aquí</a>
            </p>
        </div>

    </div>

</body>

</html>
