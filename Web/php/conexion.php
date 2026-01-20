<?php
    function conexionBD() {
        $host = "localhost";
        $usuario = "root";
        $contrasena = "admin";
        $baseDeDatos = "red_social_db";

        $conexion = mysqli_connect($host, $usuario, $contrasena, $baseDeDatos) or die("Problemas con la conexión");

        //Esta línea es clave para que los acentos y la ñ se vean bien
        $conexion->set_charset("utf8");
        return $conexion;

    }
?>