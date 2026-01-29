<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

$conexion = conexionBD();
mysqli_set_charset($conexion, "utf8mb4");

$idYo = (int)($_SESSION["id_usuario"] ?? 0);
$esAjax = isset($_POST["ajax"]) && $_POST["ajax"] === "1";

// Respuesta JSON segura (sin basura delante)
function json_out(array $arr, int $status = 200): void {
  if (ob_get_length()) { @ob_clean(); }
  http_response_code($status);
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

$idPublicacion = (int)($_POST["id_publicacion"] ?? 0);
$tipo = trim((string)($_POST["tipo"] ?? ""));

if ($idYo <= 0) {
  if ($esAjax) json_out(["ok"=>false, "error"=>"No autenticado."], 401);
  header("Location: ../index.php"); exit;
}

if ($idPublicacion <= 0 || $tipo === "") {
  if ($esAjax) json_out(["ok"=>false, "error"=>"Datos inválidos (id_publicacion/tipo)."], 400);
  header("Location: ../index.php"); exit;
}

$permitidos = ["LIKE","LOVE","LAUGH","WOW","SAD"];
if (!in_array($tipo, $permitidos, true)) {
  if ($esAjax) json_out(["ok"=>false, "error"=>"Tipo de reacción inválido."], 400);
  header("Location: ../index.php"); exit;
}

$emoji = [
  "LIKE" => "👍",
  "LOVE" => "❤️",
  "LAUGH" => "😂",
  "WOW" => "😮",
  "SAD" => "😢",
];
$orden = ["LOVE","LAUGH","WOW","SAD","LIKE"];

try {
  // 1 reacción por usuario y post: si pulsa la misma => toggle (borra), si pulsa otra => cambia
  $stmt = mysqli_prepare($conexion, "SELECT tipo FROM reaccion WHERE id_usuario=? AND id_publicacion=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "ii", $idYo, $idPublicacion);
  mysqli_stmt_execute($stmt);
  $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);

  $tipoActual = $row["tipo"] ?? "";

  if ($tipoActual === $tipo) {
    // toggle off
    $del = mysqli_prepare($conexion, "DELETE FROM reaccion WHERE id_usuario=? AND id_publicacion=?");
    mysqli_stmt_bind_param($del, "ii", $idYo, $idPublicacion);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);
    $tipoActual = "";
  } elseif ($tipoActual !== "") {
    // update reacción existente (incluye fecha)
    $up = mysqli_prepare($conexion, "UPDATE reaccion SET tipo=?, fecha_reaccion=NOW() WHERE id_usuario=? AND id_publicacion=?");
    mysqli_stmt_bind_param($up, "sii", $tipo, $idYo, $idPublicacion);
    mysqli_stmt_execute($up);
    mysqli_stmt_close($up);
    $tipoActual = $tipo;
  } else {
    // insertar nueva (incluye fecha)
    $ins = mysqli_prepare($conexion, "INSERT INTO reaccion (id_usuario, id_publicacion, tipo, fecha_reaccion) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($ins, "iis", $idYo, $idPublicacion, $tipo);
    mysqli_stmt_execute($ins);
    mysqli_stmt_close($ins);
    $tipoActual = $tipo;
  }

  // total reacciones
  $stTotal = mysqli_prepare($conexion, "SELECT COUNT(*) c FROM reaccion WHERE id_publicacion=?");
  mysqli_stmt_bind_param($stTotal, "i", $idPublicacion);
  mysqli_stmt_execute($stTotal);
  $rowT = mysqli_fetch_assoc(mysqli_stmt_get_result($stTotal));
  mysqli_stmt_close($stTotal);
  $total = (int)($rowT["c"] ?? 0);

  // resumen por tipo
  $stSum = mysqli_prepare($conexion, "SELECT tipo, COUNT(*) c FROM reaccion WHERE id_publicacion=? GROUP BY tipo");
  mysqli_stmt_bind_param($stSum, "i", $idPublicacion);
  mysqli_stmt_execute($stSum);
  $resS = mysqli_stmt_get_result($stSum);

  $mapCounts = [];
  while ($r = mysqli_fetch_assoc($resS)) $mapCounts[$r["tipo"]] = (int)$r["c"];
  mysqli_stmt_close($stSum);

  $trozos = [];
  foreach ($orden as $t) {
    if (!empty($mapCounts[$t])) $trozos[] = ($emoji[$t] ?? "") . " " . (int)$mapCounts[$t];
  }
  $resumenTexto = !empty($trozos) ? implode(" · ", $trozos) : "Sin reacciones todavía";

  $iconoPrincipal = $emoji[$tipoActual] ?? "♡";

  if ($esAjax) {
    json_out([
      "ok" => true,
      "postId" => $idPublicacion,
      "total" => $total,
      "iconoPrincipal" => $iconoPrincipal,
      "resumenTexto" => $resumenTexto
    ]);
  }

  header("Location: ../index.php");
  exit;

} catch (Throwable $e) {
  if ($esAjax) {
    json_out(["ok"=>false, "error"=>"Error servidor: ".$e->getMessage()], 500);
  }
  header("Location: ../index.php");
  exit;
}
