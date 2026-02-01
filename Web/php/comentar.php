<?php
require_once __DIR__ . "/require_login.php"; // Si no hay sesión, aquí me corta (no se puede comentar sin estar logueado)
require_once __DIR__ . "/conexion.php";      // Conexión a la base de datos

$conexion = conexionBD();                           // Abro conexión con la BD
$idYo = (int)($_SESSION["id_usuario"] ?? 0);        // Mi id de usuario (si por algo no existe, 0)

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapo texto para imprimirlo en HTML sin riesgos

function basePath(): string { // Esto sirve para montar URLs que funcionen tanto si el proyecto está en /web como en /Web o en raíz
  $uri = $_SERVER["REQUEST_URI"] ?? "/";            // URI actual de la petición
  if (stripos($uri, "/web/") !== false) return "/web"; // Si detecto /web/ en la ruta, devuelvo /web
  if (stripos($uri, "/Web/") !== false) return "/Web"; // Si detecto /Web/ en la ruta, devuelvo /Web
  return "";                                           // Si no detecto nada, asumo que está en raíz
}

/* ========= MODO LISTAR (GET) =========
   php/comentar.php?list=1&id_publicacion=XX
*/
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["list"])) { // Si es GET y viene list=1, este script SOLO devuelve la lista de comentarios en HTML
  header("Content-Type: text/html; charset=utf-8");                 // Devuelvo HTML (no JSON) porque esto se inserta directo en el modal

  $idPub = isset($_GET["id_publicacion"]) ? (int)$_GET["id_publicacion"] : 0; // Id de la publicación a listar
  if ($idPub <= 0) { // Si no hay id válido
    echo '<div class="c-item">Publicación inválida.</div>'; // Mensaje simple para el modal
    exit();
  }

  $base = basePath(); // Base de ruta para que los enlaces a perfil funcionen según dónde esté alojado el proyecto

  $sql = "SELECT c.texto, c.fecha_comentario, u.nombre_usuario, u.id_usuario
          FROM comentario c
          JOIN usuario u ON u.id_usuario = c.id_usuario
          WHERE c.id_publicacion = ?
          ORDER BY c.id_comentario ASC"; // Saco comentarios de la publicación, junto con datos del autor, ordenados del más viejo al más nuevo
  $stmt = mysqli_prepare($conexion, $sql);        // Preparo la consulta
  mysqli_stmt_bind_param($stmt, "i", $idPub);     // Paso el id de la publicación
  mysqli_stmt_execute($stmt);                     // Ejecuto
  $res = mysqli_stmt_get_result($stmt);           // Resultado

  $hay = false; // Bandera para saber si salió algún comentario
  while ($c = mysqli_fetch_assoc($res)) { // Recorro cada comentario
    $hay = true; // Si entro aquí, ya hay al menos uno
    $perfil = $base . "/perfil.php?id=" . (int)$c["id_usuario"]; // Construyo link al perfil del usuario del comentario

    echo '<div class="c-item">'; // Contenedor de un comentario
    echo '  <div class="c-top">'; // Parte superior: nombre + fecha
    echo '    <a class="c-name" href="'.h($perfil).'">'.h($c["nombre_usuario"]).'</a>'; // Nombre con link al perfil (todo escapado)
    echo '    <div class="c-date">'.h($c["fecha_comentario"]).'</div>'; // Fecha del comentario
    echo '  </div>';
    echo '  <div class="c-text">'.nl2br(h($c["texto"])).'</div>'; // Texto escapado y con saltos de línea convertidos a <br>
    echo '</div>';
  }
  mysqli_stmt_close($stmt); // Cierro el statement

  if (!$hay) echo '<div class="c-item">No hay comentarios todavía.</div>'; // Si no había ninguno, muestro mensaje
  exit(); // Importante: aquí termino, porque en modo listar no debo seguir con el POST
}

/* ========= MODO INSERTAR (POST) ========= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Si no es GET listar, entonces aquí solo acepto POST
  echo "Acceso no permitido";                // Mensaje simple (por si alguien entra a mano)
  exit();
}

$esAjax = isset($_POST["ajax"]) && $_POST["ajax"] === "1"; // Si ajax=1, significa que tengo que responder con JSON en vez de redirigir

$idPublicacion = (int)($_POST["id_publicacion"] ?? 0); // Id del post donde se comenta
$texto = trim($_POST["texto"] ?? "");                  // Texto del comentario sin espacios al principio y final

if ($idPublicacion <= 0 || $texto === "") { // Si falta el id o el texto está vacío
  if ($esAjax) { // Si es AJAX devuelvo JSON de error
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>false, "error"=>"Datos inválidos"]);
    exit();
  }
  $back = $_SERVER["HTTP_REFERER"] ?? "../index.php"; // Si no es AJAX, vuelvo a la página anterior
  header("Location: " . $back);
  exit();
}

if (mb_strlen($texto, "UTF-8") > 500) { // Si el comentario pasa de 500 caracteres
  $texto = mb_substr($texto, 0, 500, "UTF-8"); // Lo recorto a 500 para no petar la BD ni el UI
}

$dupSql = "SELECT id_comentario, fecha_comentario
           FROM comentario
           WHERE id_usuario = ?
             AND id_publicacion = ?
             AND texto = ?
             AND fecha_comentario >= (NOW() - INTERVAL 5 SECOND)
           ORDER BY id_comentario DESC
           LIMIT 1"; // Busco si ya existe un comentario igual, del mismo usuario, en el mismo post, en los últimos 5 segundos (para evitar doble envío)
$dupStmt = mysqli_prepare($conexion, $dupSql);                 // Preparo
mysqli_stmt_bind_param($dupStmt, "iis", $idYo, $idPublicacion, $texto); // Paso usuario, publicación y texto
mysqli_stmt_execute($dupStmt);                                 // Ejecuto
$dupRes = mysqli_stmt_get_result($dupStmt);                     // Resultado
$dupRow = mysqli_fetch_assoc($dupRes);                          // Si hay fila, es duplicado
mysqli_stmt_close($dupStmt);                                   // Cierro

if ($dupRow) { // Si detecto duplicado reciente
  // Ya existe uno igual muy reciente -> devolvemos OK sin insertar
  if ($esAjax) { // Si es AJAX, contesto ok=true igualmente para que el front no lo trate como error
    $stmtC = mysqli_prepare($conexion, "SELECT COUNT(*) c FROM comentario WHERE id_publicacion = ?"); // Recuento actualizado de comentarios
    mysqli_stmt_bind_param($stmtC, "i", $idPublicacion); // Paso id del post
    mysqli_stmt_execute($stmtC);                         // Ejecuto
    $rowC = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC)); // Leo resultado
    mysqli_stmt_close($stmtC);                           // Cierro
    $count = (int)($rowC["c"] ?? 0);                     // Total de comentarios

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>true, "count"=>$count, "dedup"=>true]); // dedup=true para saber que no inserté realmente
    exit();
  }

  $back = $_SERVER["HTTP_REFERER"] ?? "../index.php"; // Si no es AJAX, vuelvo atrás sin hacer nada
  header("Location: " . $back);
  exit();
}

/* Insert real (solo una vez) */
$ins = mysqli_prepare( // Inserto el comentario de verdad (ya sé que no es duplicado)
  $conexion,
  "INSERT INTO comentario (id_usuario, id_publicacion, texto, fecha_comentario)
   VALUES (?, ?, ?, NOW())"
);
mysqli_stmt_bind_param($ins, "iis", $idYo, $idPublicacion, $texto); // Paso id usuario, id publicación y texto
mysqli_stmt_execute($ins);                                          // Ejecuto insert
mysqli_stmt_close($ins);                                            // Cierro

if ($esAjax) { // Si es AJAX respondo con JSON para que el front actualice contador/lista sin recargar
  $stmtC = mysqli_prepare($conexion, "SELECT COUNT(*) c FROM comentario WHERE id_publicacion = ?"); // Vuelvo a contar comentarios para devolver el número actualizado
  mysqli_stmt_bind_param($stmtC, "i", $idPublicacion); // Paso id del post
  mysqli_stmt_execute($stmtC);                         // Ejecuto
  $rowC = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC)); // Leo resultado
  mysqli_stmt_close($stmtC);                           // Cierro
  $count = (int)($rowC["c"] ?? 0);                     // Total actualizado

  header("Content-Type: application/json; charset=utf-8");
  echo json_encode(["ok"=>true, "count"=>$count]);      // Devuelvo ok y count para actualizar UI
  exit();
}

$back = $_SERVER["HTTP_REFERER"] ?? "../index.php"; // Si no es AJAX, vuelvo a la página desde la que venía
header("Location: " . $back);
exit();
