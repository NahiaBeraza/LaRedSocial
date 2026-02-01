<?php
require_once __DIR__ . "/require_login.php"; // Asegura que el usuario esté logueado
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: ../chat.php"); exit(); } // Este archivo solo debe ejecutarse por POST

$conexion = conexionBD();              // Abro conexión con la BD
$yo = (int)$_SESSION["id_usuario"];    // Mi id desde la sesión

$idGrupo = (int)($_POST["grupo"] ?? 0);       // Id del grupo donde voy a expulsar a alguien
$idUsuario = (int)($_POST["id_usuario"] ?? 0); // Id del usuario a expulsar

// Validación básica:
// - tiene que haber grupo y usuario
// - no me puedo expulsar a mí mismo
if ($idGrupo <= 0 || $idUsuario <= 0 || $idUsuario === $yo) {
  header("Location: ../chat.php?grupo=" . $idGrupo . "&modal=miembros"); // Vuelvo al modal de miembros
  exit();
}

// comprobar creador: solo el creador del grupo puede expulsar
$stmt = mysqli_prepare($conexion, "SELECT id_creador FROM grupo WHERE id_grupo = ? LIMIT 1"); // Pido el id del creador del grupo
mysqli_stmt_bind_param($stmt, "i", $idGrupo); // Paso id del grupo
mysqli_stmt_execute($stmt);                   // Ejecuto
$res = mysqli_stmt_get_result($stmt);         // Resultado
$row = mysqli_fetch_assoc($res);              // Fila con id_creador
mysqli_stmt_close($stmt);                     // Cierro

// Si el grupo no existe o yo no soy el creador, no dejo expulsar
if (!$row || (int)$row["id_creador"] !== $yo) {
  header("Location: ../chat.php?grupo=" . $idGrupo); // Vuelvo al chat sin hacer nada
  exit();
}

// expulsar: borro la relación en la tabla miembro para sacar a ese usuario del grupo
$stmtD = mysqli_prepare($conexion, "DELETE FROM miembro WHERE id_grupo = ? AND id_usuario = ?"); // Borro el miembro del grupo
mysqli_stmt_bind_param($stmtD, "ii", $idGrupo, $idUsuario); // Paso grupo y usuario a eliminar
mysqli_stmt_execute($stmtD); // Ejecuto delete
mysqli_stmt_close($stmtD);   // Cierro

header("Location: ../chat.php?grupo=" . $idGrupo . "&modal=miembros"); // Vuelvo al chat con el modal abierto
exit();
