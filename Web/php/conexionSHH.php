<?php
function conexionBD() {
    $host = "127.0.0.1";
    $usuario = "admin";
    $contrasena = "RedSocial123!";
    $baseDeDatos = "red_social_db";
    $puerto = 3307;

    $conexion = mysqli_connect($host, $usuario, $contrasena, $baseDeDatos, $puerto)
        or die("Problemas con la conexión: " . mysqli_connect_error());

    $conexion->set_charset("utf8mb4");
    return $conexion;
}
?>
