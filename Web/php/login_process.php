<?php
    include_once __DIR__ . "/conexion.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $usuario = trim($_POST['usuario'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        if ($usuario === '' || $contrasena === '') {
            header("Location: ../login.php?error=1");
            exit();
        }

        $conexion = conexionBD();

        $sql = "SELECT id_usuario, nombre_usuario, contrasena
                FROM usuario
                WHERE nombre_usuario = ?";

        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {

            mysqli_stmt_bind_result($stmt, $idUsuarioBD, $usuarioBD, $hashGuardado);
            mysqli_stmt_fetch($stmt);

            if (password_verify($contrasena, $hashGuardado)) {

                session_start();
                $_SESSION['usuario'] = $usuarioBD;
                $_SESSION['id_usuario'] = $idUsuarioBD;

                header("Location: ../index.php");
                exit();
            }
        }

        header("Location: ../login.php?error=1");
        exit();

    } else {
        echo "Acceso no permitido";
    }
?>