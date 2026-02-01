<?php
require_once __DIR__ . "/require_login.php"; // Obliga a estar logueado para poder dar like
include_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Este archivo solo se debe ejecutar por POST
  echo "Acceso no permitido";                // Si entran por URL, corto
  exit();
}

$idUsuario = (int)$_SESSION['id_usuario'];               // Mi id de usuario
$idPublicacion = (int)($_POST['id_publicacion'] ?? 0);   // Id del post al que le doy like

if ($idPublicacion <= 0) {                               // Si no hay post válido
  $back = $_SERVER['HTTP_REFERER'] ?? '../index.php';    // Vuelvo a donde venía
  header("Location: " . $back);
  exit();
}

$conexion = conexionBD(); // Abro conexión con la BD

// Primero miro si ya existe una reacción de este usuario en este post.
// (Da igual si era LIKE o LOVE o lo que sea) porque normalmente hay una única reacción por usuario+post.
$sqlCheck = "SELECT tipo FROM reaccion WHERE id_usuario = ? AND id_publicacion = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conexion, $sqlCheck);                 // Preparo la consulta
mysqli_stmt_bind_param($stmtCheck, "ii", $idUsuario, $idPublicacion); // Paso mi id y el id del post
mysqli_stmt_execute($stmtCheck);                                   // Ejecuto
$res = mysqli_stmt_get_result($stmtCheck);                         // Resultado
$row = mysqli_fetch_assoc($res);                                   // Si hay fila, ya reaccioné antes
mysqli_stmt_close($stmtCheck);                                     // Cierro

$fecha = date("Y-m-d H:i:s"); // Fecha actual para guardar cuándo reaccioné

if ($row) { // Si ya existía una reacción previa
  // Si ya era LIKE, hago toggle: lo quito (borro la fila)
  if ($row["tipo"] === "LIKE") {
    $sqlDel = "DELETE FROM reaccion WHERE id_usuario = ? AND id_publicacion = ?"; // Borro la reacción
    $stmtDel = mysqli_prepare($conexion, $sqlDel);
    mysqli_stmt_bind_param($stmtDel, "ii", $idUsuario, $idPublicacion);
    mysqli_stmt_execute($stmtDel);
    mysqli_stmt_close($stmtDel);
  } else {
    // Si era otra reacción distinta, la cambio a LIKE (no inserto otra para no duplicar)
    $sqlUp = "UPDATE reaccion SET tipo = 'LIKE', fecha_reaccion = ? WHERE id_usuario = ? AND id_publicacion = ?";
    $stmtUp = mysqli_prepare($conexion, $sqlUp);
    mysqli_stmt_bind_param($stmtUp, "sii", $fecha, $idUsuario, $idPublicacion); // Actualizo fecha también
    mysqli_stmt_execute($stmtUp);
    mysqli_stmt_close($stmtUp);
  }
} else {
  // Si no existía reacción previa, inserto una nueva como LIKE
  $sqlIns = "INSERT INTO reaccion (id_usuario, id_publicacion, tipo, fecha_reaccion)
             VALUES (?, ?, 'LIKE', ?)";
  $stmtIns = mysqli_prepare($conexion, $sqlIns);
  mysqli_stmt_bind_param($stmtIns, "iis", $idUsuario, $idPublicacion, $fecha);
  mysqli_stmt_execute($stmtIns);
  mysqli_stmt_close($stmtIns);
}

$back = $_SERVER['HTTP_REFERER'] ?? '../index.php'; // Después vuelvo a la página desde la que venía
header("Location: " . $back);
exit();
