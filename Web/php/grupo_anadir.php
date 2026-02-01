<?php
require_once __DIR__ . "/require_login.php"; // Asegura que el usuario esté logueado
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: ../chat.php"); exit(); } // Este archivo solo se usa por POST (si entran a mano, fuera)

$conexion = conexionBD();              // Abro conexión con la BD
$yo = (int)$_SESSION["id_usuario"];    // Mi id desde la sesión

$idGrupo = (int)($_POST["grupo"] ?? 0);       // Id del grupo al que voy a añadir a alguien
$idUsuario = (int)($_POST["id_usuario"] ?? 0); // Id del usuario que voy a meter en el grupo

// Validación rápida: si falta grupo o usuario, vuelvo al modal de miembros
if ($idGrupo <= 0 || $idUsuario <= 0) {
  header("Location: ../chat.php?grupo=" . $idGrupo . "&modal=miembros");
  exit();
}

// comprobar creador: solo el creador del grupo puede añadir gente
$stmt = mysqli_prepare($conexion, "SELECT id_creador FROM grupo WHERE id_grupo = ? LIMIT 1"); // Pido el id del creador del grupo
mysqli_stmt_bind_param($stmt, "i", $idGrupo); // Paso id del grupo
mysqli_stmt_execute($stmt);                   // Ejecuto
$res = mysqli_stmt_get_result($stmt);         // Resultado
$row = mysqli_fetch_assoc($res);              // Fila con id_creador
mysqli_stmt_close($stmt);                     // Cierro

// Si no existe el grupo, o si yo no soy el creador, no dejo añadir
if (!$row || (int)$row["id_creador"] !== $yo) {
  header("Location: ../chat.php?grupo=" . $idGrupo); // Vuelvo al chat del grupo sin abrir el modal
  exit();
}

// añadir (ignore por si existe): si ya es miembro, no falla ni duplica
$stmtI = mysqli_prepare($conexion, "INSERT IGNORE INTO miembro (id_grupo, id_usuario) VALUES (?, ?)"); // Inserto relación grupo-usuario
mysqli_stmt_bind_param($stmtI, "ii", $idGrupo, $idUsuario); // Paso grupo y usuario
mysqli_stmt_execute($stmtI); // Ejecuto insert
mysqli_stmt_close($stmtI);   // Cierro

header("Location: ../chat.php?grupo=" . $idGrupo . "&modal=miembros"); // Vuelvo al chat del grupo con el modal de miembros abierto
exit();
