<?php
    include_once __DIR__ . "/conexion.php"; // Incluyo la conexión a la BD (conexionBD())

    if ($_SERVER["REQUEST_METHOD"] === "POST") { // Solo acepto el login si viene por POST (desde el formulario)

        $usuario = trim($_POST['usuario'] ?? '');         // Cojo el usuario y le quito espacios
        $contrasena = trim($_POST['contrasena'] ?? '');   // Cojo la contraseña y le quito espacios

        // Si falta usuario o contraseña, vuelvo al login con error
        if ($usuario === '' || $contrasena === '') {
            header("Location: ../login.php?error=1");
            exit();
        }

        $conexion = conexionBD(); // Abro conexión con la BD

        // Busco al usuario por nombre (solo debería salir 1)
        $sql = "SELECT id_usuario, nombre_usuario, contrasena
                FROM usuario
                WHERE nombre_usuario = ?";

        $stmt = mysqli_prepare($conexion, $sql);           // Preparo la consulta
        mysqli_stmt_bind_param($stmt, "s", $usuario);      // Meto el nombre de usuario en el ?
        mysqli_stmt_execute($stmt);                        // Ejecuto
        mysqli_stmt_store_result($stmt);                   // Guardo el resultado para poder contar filas

        // Si existe exactamente un usuario con ese nombre
        if (mysqli_stmt_num_rows($stmt) === 1) {

            // Enlazo las columnas del SELECT a variables de PHP
            mysqli_stmt_bind_result($stmt, $idUsuarioBD, $usuarioBD, $hashGuardado);
            mysqli_stmt_fetch($stmt); // Traigo los datos del usuario

            // Comparo la contraseña escrita con el hash guardado en la BD
            if (password_verify($contrasena, $hashGuardado)) {

                session_start();                 // Inicio sesión (o la retomo)
                $_SESSION['usuario'] = $usuarioBD;     // Guardo el nombre en sesión
                $_SESSION['id_usuario'] = $idUsuarioBD; // Guardo el id en sesión (esto es lo importante)

                header("Location: ../index.php"); // Login correcto -> entro a la app
                exit();
            }
        }

        // Si no existe el usuario o la contraseña no coincide, vuelvo al login con error
        header("Location: ../login.php?error=1");
        exit();

    } else {
        echo "Acceso no permitido"; // Si alguien entra por URL directamente, no dejo ejecutar el login
    }
?>
