<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";
require_once __DIR__ . "/php/notificaciones_chat.php";

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

$idOtro  = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$idGrupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : 0;

$modoInbox = ($idOtro <= 0 && $idGrupo <= 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

// Datos cabecera chat
$otro = null;
$grupo = null;

$titulo = "Chat";
$subtitulo = $modoInbox ? "Selecciona un usuario o grupo" : "";

if ($idOtro > 0) {
  $sql = "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario = ? LIMIT 1";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idOtro);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $otro = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);

  if ($otro) {
    $titulo = $otro["nombre_usuario"];
    $subtitulo = "Conversación privada";
  }
}

if ($idGrupo > 0) {
  $sql = "SELECT id_grupo, nombre_grupo FROM grupo WHERE id_grupo = ? LIMIT 1";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $idGrupo);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $grupo = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);

  if ($grupo) {
    $titulo = $grupo["nombre_grupo"];
    $subtitulo = "Chat de grupo";
  }
}

// Usuarios sidebar (menos yo)
$usuarios = [];
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC");
while ($row = mysqli_fetch_assoc($resU)) $usuarios[] = $row;

// Grupos donde soy miembro
$grupos = [];
$sqlG = "SELECT g.id_grupo, g.nombre_grupo
         FROM grupo g
         JOIN miembro m ON m.id_grupo = g.id_grupo
         WHERE m.id_usuario = ?
         ORDER BY g.nombre_grupo ASC";
$stmtG = mysqli_prepare($conexion, $sqlG);
mysqli_stmt_bind_param($stmtG, "i", $yo);
mysqli_stmt_execute($stmtG);
$resGr = mysqli_stmt_get_result($stmtG);
while ($row = mysqli_fetch_assoc($resGr)) $grupos[] = $row;
mysqli_stmt_close($stmtG);

// NOTIFICACIONES
$noLeidosEmisor = noLeidosPorEmisor($conexion, $yo);  // [id_usuario => total]
$noLeidosGrupo  = noLeidosPorGrupo($conexion, $yo);   // [id_grupo => total]

// Foto cabecera
$fotoCabecera = "";
if ($otro && !empty($otro["foto_perfil"])) $fotoCabecera = "uploads/" . $otro["foto_perfil"];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="estilos/estilos.css">
  <title>Chat privado: <?= h($titulo) ?></title>
</head>
<body>
  <div class="chat">
    <div class="chat__layout">

      <!-- SIDEBAR IZQUIERDA -->
      <aside class="chat__side">
        <div class="chat__sideTop">
          <a class="chat__sideBtn" href="index.php">Volver</a>
          <a class="chat__sideBtn" href="crear_grupo.php">Crear grupo</a>
        </div>

        <div class="chat__panel">
          <h3>Usuarios</h3>

          <?php foreach ($usuarios as $u): ?>
            <?php
              $uid = (int)$u["id_usuario"];
              $activo = ($idOtro > 0 && $uid === $idOtro);
              $clase = "chat__item" . ($activo ? " chat__item--activo" : "");

              $foto = "";
              if (!empty($u["foto_perfil"])) $foto = "uploads/" . $u["foto_perfil"];

              $cant = $noLeidosEmisor[$uid] ?? 0;
            ?>
            <a class="<?= $clase ?>" href="chat.php?id=<?= $uid ?>">
              <?php if ($foto): ?>
                <img class="chat__avatar" src="<?= h($foto) ?>" alt="">
              <?php else: ?>
                <div class="chat__avatar"></div>
              <?php endif; ?>

              <div>
                <div class="chat__itemName"><?= h($u["nombre_usuario"]) ?></div>
                <div class="chat__itemSub">Toca para abrir chat</div>
              </div>

              <?php if ($cant > 0): ?>
                <span class="chat__badge"><?= (int)$cant ?></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>

          <h3 style="margin-top:14px;">Grupos</h3>

          <?php if (empty($grupos)): ?>
            <p class="chat__vacio">Aún no estás en ningún grupo.</p>
          <?php else: ?>
            <?php foreach ($grupos as $g): ?>
              <?php
                $gid = (int)$g["id_grupo"];
                $activo = ($idGrupo > 0 && $gid === $idGrupo);
                $clase = "chat__item" . ($activo ? " chat__item--activo" : "");

                $cantG = $noLeidosGrupo[$gid] ?? 0;
              ?>
              <a class="<?= $clase ?>" href="chat.php?grupo=<?= $gid ?>">
                <div class="chat__avatar"></div>
                <div>
                  <div class="chat__itemName"><?= h($g["nombre_grupo"]) ?></div>
                  <div class="chat__itemSub">Chat de grupo</div>
                </div>

                <?php if ($cantG > 0): ?>
                  <span class="chat__badge"><?= (int)$cantG ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </aside>

      <!-- MAIN DERECHA -->
      <div class="chat__main">

        <div class="chat__topbar">
          <div class="chat__topLeft">

            <?php if ($otro): ?>
              <?php if ($fotoCabecera): ?>
                <img class="chat__avatar chat__avatar--top" src="<?= h($fotoCabecera) ?>" alt="">
              <?php else: ?>
                <div class="chat__avatar chat__avatar--top"></div>
              <?php endif; ?>

              <div>
                <!-- Click al perfil -->
                <a class="chat__tituloLink" href="perfil.php?id=<?= (int)$otro["id_usuario"] ?>">
                  <div class="chat__titulo"><?= h($titulo) ?></div>
                </a>
                <div class="chat__subtitle"><?= h($subtitulo) ?></div>
              </div>

            <?php elseif ($grupo): ?>
              <div class="chat__avatar chat__avatar--top"></div>
              <div>
                <div class="chat__titulo"><?= h("Grupo: " . $titulo) ?></div>
                <div class="chat__subtitle"><?= h($subtitulo) ?></div>
              </div>

            <?php else: ?>
              <div>
                <div class="chat__titulo">Chat</div>
                <div class="chat__subtitle"><?= h($subtitulo) ?></div>
              </div>
            <?php endif; ?>

          </div>
        </div>

        <div id="cajaMensajes">
          <?php if ($modoInbox): ?>
            <p class="chat__vacio">Selecciona un usuario o un grupo para empezar a chatear 💬</p>
          <?php else: ?>
            <?php include __DIR__ . "/php/leer_chat.php"; ?>
          <?php endif; ?>
        </div>

        <?php if (!$modoInbox): ?>
          <form class="chat__form" method="POST" action="php/enviar_chat.php">
            <?php if ($idOtro > 0): ?>
              <input type="hidden" name="id" value="<?= (int)$idOtro ?>">
            <?php endif; ?>
            <?php if ($idGrupo > 0): ?>
              <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>">
            <?php endif; ?>

            <input class="chat__input" type="text" name="texto" id="texto" autocomplete="off"
                   placeholder="Escribe un mensaje..." required>
            <button class="chat__btn" type="submit">Enviar</button>
          </form>
        <?php endif; ?>

      </div>

    </div>
  </div>

<script>
const caja = document.getElementById("cajaMensajes");
const idOtro = <?= (int)$idOtro ?>;
const idGrupo = <?= (int)$idGrupo ?>;

function cargarMensajes(){
  if (idOtro <= 0 && idGrupo <= 0) return;

  const params = new URLSearchParams();
  if (idOtro > 0) params.set("id", idOtro);
  if (idGrupo > 0) params.set("grupo", idGrupo);

  fetch("php/leer_chat.php?" + params.toString(), { cache: "no-store" })
    .then(r => r.text())
    .then(html => {
      const estabaAbajo = (caja.scrollTop + caja.clientHeight >= caja.scrollHeight - 30);
      caja.innerHTML = html;
      if (estabaAbajo) caja.scrollTop = caja.scrollHeight;
    })
    .catch(() => {});
}

if (idOtro > 0 || idGrupo > 0) {
  setInterval(cargarMensajes, 2000);
  setTimeout(() => { caja.scrollTop = caja.scrollHeight; }, 200);
}
</script>
</body>
</html>
