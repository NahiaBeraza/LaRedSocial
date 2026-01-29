<?php
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/conexion.php";

$conexion = conexionBD();
$idYo = (int)($_SESSION["id_usuario"] ?? 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

function basePath(): string {
  $uri = $_SERVER["REQUEST_URI"] ?? "/";
  if (stripos($uri, "/web/") !== false) return "/web";
  if (stripos($uri, "/Web/") !== false) return "/Web";
  return "";
}

/* ========= MODO LISTAR (GET) =========
   php/comentar.php?list=1&id_publicacion=XX
*/
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["list"])) {
  header("Content-Type: text/html; charset=utf-8");

  $idPub = isset($_GET["id_publicacion"]) ? (int)$_GET["id_publicacion"] : 0;
  if ($idPub <= 0) {
    echo '<div class="c-item">Publicación inválida.</div>';
    exit();
  }

  $base = basePath();

  $sql = "SELECT c.texto, c.fecha_comentario, u.nombre_usuario, u.id_usuario
          FROM comentario c
          JOIN usuario u ON u.id_usuario = c.id_usuario
          WHERE c.id_publicacion = ?
          ORDER BY c.id_comentario ASC";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idPub);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  $hay = false;
  while ($c = mysqli_fetch_assoc($res)) {
    $hay = true;
    $perfil = $base . "/perfil.php?id=" . (int)$c["id_usuario"];

    echo '<div class="c-item">';
    echo '  <div class="c-top">';
    echo '    <a class="c-name" href="'.h($perfil).'">'.h($c["nombre_usuario"]).'</a>';
    echo '    <div class="c-date">'.h($c["fecha_comentario"]).'</div>';
    echo '  </div>';
    echo '  <div class="c-text">'.nl2br(h($c["texto"])).'</div>';
    echo '</div>';
  }
  mysqli_stmt_close($stmt);

  if (!$hay) echo '<div class="c-item">No hay comentarios todavía.</div>';
  exit();
}

/* ========= MODO INSERTAR (POST) ========= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "Acceso no permitido";
  exit();
}

$esAjax = isset($_POST["ajax"]) && $_POST["ajax"] === "1";

$idPublicacion = (int)($_POST["id_publicacion"] ?? 0);
$texto = trim($_POST["texto"] ?? "");

if ($idPublicacion <= 0 || $texto === "") {
  if ($esAjax) {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>false, "error"=>"Datos inválidos"]);
    exit();
  }
  $back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
  header("Location: " . $back);
  exit();
}

if (mb_strlen($texto, "UTF-8") > 500) {
  $texto = mb_substr($texto, 0, 500, "UTF-8");
}

/**
 * ✅ ANTI-DUPLICADO (servidor):
 * Si por cualquier motivo llega el mismo POST dos veces seguidas,
 * NO insertamos dos filas.
 *
 * Criterio: mismo usuario + misma publicación + mismo texto
 * y que exista uno en los últimos 5 segundos.
 */
$dupSql = "SELECT id_comentario, fecha_comentario
           FROM comentario
           WHERE id_usuario = ?
             AND id_publicacion = ?
             AND texto = ?
             AND fecha_comentario >= (NOW() - INTERVAL 5 SECOND)
           ORDER BY id_comentario DESC
           LIMIT 1";
$dupStmt = mysqli_prepare($conexion, $dupSql);
mysqli_stmt_bind_param($dupStmt, "iis", $idYo, $idPublicacion, $texto);
mysqli_stmt_execute($dupStmt);
$dupRes = mysqli_stmt_get_result($dupStmt);
$dupRow = mysqli_fetch_assoc($dupRes);
mysqli_stmt_close($dupStmt);

if ($dupRow) {
  // Ya existe uno igual muy reciente -> devolvemos OK sin insertar
  if ($esAjax) {
    // count actualizado
    $stmtC = mysqli_prepare($conexion, "SELECT COUNT(*) c FROM comentario WHERE id_publicacion = ?");
    mysqli_stmt_bind_param($stmtC, "i", $idPublicacion);
    mysqli_stmt_execute($stmtC);
    $rowC = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC));
    mysqli_stmt_close($stmtC);
    $count = (int)($rowC["c"] ?? 0);

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>true, "count"=>$count, "dedup"=>true]);
    exit();
  }

  $back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
  header("Location: " . $back);
  exit();
}

/* Insert real (solo una vez) */
$ins = mysqli_prepare(
  $conexion,
  "INSERT INTO comentario (id_usuario, id_publicacion, texto, fecha_comentario)
   VALUES (?, ?, ?, NOW())"
);
mysqli_stmt_bind_param($ins, "iis", $idYo, $idPublicacion, $texto);
mysqli_stmt_execute($ins);
mysqli_stmt_close($ins);

if ($esAjax) {
  // count actualizado
  $stmtC = mysqli_prepare($conexion, "SELECT COUNT(*) c FROM comentario WHERE id_publicacion = ?");
  mysqli_stmt_bind_param($stmtC, "i", $idPublicacion);
  mysqli_stmt_execute($stmtC);
  $rowC = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC));
  mysqli_stmt_close($stmtC);
  $count = (int)($rowC["c"] ?? 0);

  header("Content-Type: application/json; charset=utf-8");
  echo json_encode(["ok"=>true, "count"=>$count]);
  exit();
}

$back = $_SERVER["HTTP_REFERER"] ?? "../index.php";
header("Location: " . $back);
exit();
