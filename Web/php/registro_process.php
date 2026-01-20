<?php
    include_once __DIR__ . "/conexion.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $usuario = trim($_POST['usuario'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        if ($usuario === '' || $correo === '' || $contrasena === '') {
            header("Location: ../registro.php?error=campos");
            exit();
        }

        $conexion = conexionBD();

        // Comprobar si el usuario ya existe
        $sqlCheck = "SELECT id_usuario FROM usuario WHERE nombre_usuario = ?";
        $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, "s", $usuario);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);

        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
            header("Location: ../registro.php?error=usuario");
            exit();
        }
        mysqli_stmt_close($stmtCheck);

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Si en tu tabla NO existen rol y fecha_registro, elimina esas columnas de aquí:
        $rol = "user";
        $fechaRegistro = date("Y-m-d H:i:s");

        $sqlInsert = "INSERT INTO usuario (nombre_usuario, correo, contrasena, rol, fecha_registro)
                    VALUES (?, ?, ?, ?, ?)";

        $stmtInsert = mysqli_prepare($conexion, $sqlInsert);
        mysqli_stmt_bind_param($stmtInsert, "sssss", $usuario, $correo, $hash, $rol, $fechaRegistro);

        if (mysqli_stmt_execute($stmtInsert)) {
            header("Location: ../login.php?registro=ok");
            exit();
        }

        header("Location: ../registro.php?error=general");
        exit();

    } else {
        echo "Acceso no permitido";
    }
?>