<?php
require_once __DIR__ . "/require_login.php"; // Asegura que el usuario esté logueado antes de poder crear grupos
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Este archivo solo debería entrar por POST (desde el formulario)
  header("Location: ../crear_grupo.php");    // Si alguien entra a mano por URL, lo mando de vuelta al formulario
  exit();
}

$conexion = conexionBD();              // Abro conexión con la base de datos
$yo = (int)$_SESSION["id_usuario"];    // Mi id de usuario desde sesión

$nombre = trim($_POST["nombre_grupo"] ?? ""); // Nombre del grupo (sin espacios al principio/final)
$seleccion = isset($_POST["usuarios"]) && is_array($_POST["usuarios"]) ? $_POST["usuarios"] : []; // Lista de ids marcados en los checkbox (si no hay, array vacío)

if ($nombre === "") {                  // Si no hay nombre, no creo nada
  header("Location: ../crear_grupo.php");
  exit();
}

$fecha = date("Y-m-d H:i:s");          // Fecha de creación del grupo en formato SQL

// crear grupo (con creador)
$sql = "INSERT INTO grupo (nombre_grupo, fecha_creacion, tamano_maximo, id_creador)
        VALUES (?, ?, 15, ?)";         // Inserto grupo con tamaño máximo fijo (15) y guardo quién lo creó
$stmt = mysqli_prepare($conexion, $sql);              // Preparo query
mysqli_stmt_bind_param($stmt, "ssi", $nombre, $fecha, $yo); // Paso nombre, fecha, y mi id como creador
mysqli_stmt_execute($stmt);                           // Ejecuto insert
$idGrupo = mysqli_insert_id($conexion);               // Me quedo con el id del grupo recién creado
mysqli_stmt_close($stmt);                             // Cierro el statement

// meterme yo
$stmtM = mysqli_prepare($conexion, "INSERT IGNORE INTO miembro (id_grupo, id_usuario) VALUES (?, ?)"); // Preparo insert para tabla miembro (IGNORE para que no reviente si ya existiera)
mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo);  // Meto el grupo y mi id
mysqli_stmt_execute($stmtM);                          // Me añado como miembro del grupo

// meter seleccionados
foreach ($seleccion as $idU) {        // Recorro los usuarios seleccionados en el formulario
  $idU = (int)$idU;                   // Paso a int por seguridad
  if ($idU > 0 && $idU !== $yo) {     // Evito ids raros y evito añadir dos veces a mí mismo
    mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $idU); // Reutilizo el mismo statement cambiando el id_usuario
    mysqli_stmt_execute($stmtM);                          // Ejecuto insert para este usuario
  }
}
mysqli_stmt_close($stmtM);            // Cierro el statement de miembros

header("Location: ../chat.php?grupo=" . $idGrupo); // Cuando termino, lo mando directo al chat del grupo creado
exit();
