<?php
// Cierra la sesión del usuario y lo redirige al login
    session_start();//Se inicia la sesión para poder manipularla y cerrarla correctamente.
    session_unset();//Se vacían todas las variables almacenadas en la sesión.
    session_destroy();//Se elimina la sesión del usuario en el servidor.
    header("Location: login.php"); //Tras cerrar la sesión, el usuario es redirigido a la página de login
    exit();
    
?>