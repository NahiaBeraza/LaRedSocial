<?php
function conexionBD() {
    $host = "localhost";
    $usuario = "laredsocial";
    $contrasena = "";   // SIN contraseña
    $baseDeDatos = "red_social_db";

    $conexion = mysqli_connect($host, $usuario, $contrasena, $baseDeDatos)
        or die("Problemas con la conexión");

    $conexion->set_charset("utf8");
    return $conexion;
}
?>