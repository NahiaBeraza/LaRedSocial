<?php
require_once __DIR__ . "/require_login.php";
include_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Acceso no permitido";
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$pie = trim($_POST['pie_de_foto'] ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$etiquetas = trim($_POST['etiquetas'] ?? '');

if ($pie === '' || !isset($_FILES['imagen'])) {
    header("Location: ../create.php?error=campos");
    exit();
}

$img = $_FILES['imagen'];

if ($img['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../create.php?error=img");
    exit();
}

$ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $permitidas, true)) {
    header("Location: ../create.php?error=img");
    exit();
}

$uploadsDir = __DIR__ . "/../uploads";
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$nombreFinal = uniqid("img_", true) . "." . $ext;
$rutaFinal = $uploadsDir . "/" . $nombreFinal;

if (!move_uploaded_file($img['tmp_name'], $rutaFinal)) {
    header("Location: ../create.php?error=general");
    exit();
}

$conexion = conexionBD();
$fecha = date("Y-m-d H:i:s");

$sql = "INSERT INTO publicacion
        (id_usuario, pie_de_foto, imagen, ubicacion, etiquetas, fecha_publicacion)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "isssss",
    $idUsuario,
    $pie,
    $nombreFinal,
    $ubicacion,
    $etiquetas,
    $fecha
);

if (!mysqli_stmt_execute($stmt)) {
    @unlink($rutaFinal);
    header("Location: ../create.php?error=general");
    exit();
}

header("Location: ../index.php");
exit();
?>