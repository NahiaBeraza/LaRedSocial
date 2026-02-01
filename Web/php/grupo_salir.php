<?php
require_once __DIR__ . "/require_login.php"; // Asegura que el usuario esté logueado
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Este archivo solo se debe ejecutar por POST
  header("Location: ../chat.php");           // Si alguien entra por URL directamente, lo mando al chat
  exit();
}

$conexion = conexionBD();              // Abro conexión con la BD
$yo = (int)$_SESSION["id_usuario"];    // Mi id de usuario desde la sesión
$idGrupo = (int)($_POST["grupo"] ?? 0); // Id del grupo del que quiero salir

if ($idGrupo <= 0) {                   // Si no viene un id de grupo válido
  header("Location: ../chat.php");     // Vuelvo al chat sin hacer nada
  exit();
}

// verificar miembro: compruebo que realmente pertenezco a ese grupo
$stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1");
mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo); // Paso id del grupo y mi id
mysqli_stmt_execute($stmtM);                         // Ejecuto la consulta
mysqli_stmt_store_result($stmtM);                    // Guardo el resultado para poder contar filas
$esMiembro = (mysqli_stmt_num_rows($stmtM) === 1);   // Si hay 1 fila, soy miembro del grupo
mysqli_stmt_close($stmtM);                           // Cierro el statement

if ($esMiembro) { // Solo si soy miembro me permito salir
  $stmt = mysqli_prepare($conexion, "DELETE FROM miembro WHERE id_grupo = ? AND id_usuario = ?"); // Borro mi relación con el grupo
  mysqli_stmt_bind_param($stmt, "ii", $idGrupo, $yo); // Paso grupo y mi id
  mysqli_stmt_execute($stmt);                          // Ejecuto el DELETE
  mysqli_stmt_close($stmt);                            // Cierro el statement
}

header("Location: ../chat.php"); // Después de salir del grupo, vuelvo al chat general
exit();
