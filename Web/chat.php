<?php
require_once __DIR__ . "/php/require_login.php"; // Obliga a que el usuario esté logueado
require_once __DIR__ . "/php/conexion.php";      // Archivo con la conexión a la base de datos
require_once __DIR__ . "/php/notificaciones_chat.php"; // Funciones para mensajes no leídos

$conexion = conexionBD();              // Creo la conexión a la base de datos
$yo = (int)$_SESSION["id_usuario"];    // Guardo mi id de usuario desde la sesión

$idOtro  = isset($_GET["id"]) ? (int)$_GET["id"] : 0;        // Id del usuario en chat privado
$idGrupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : 0; // Id del grupo en chat de grupo

$modoInbox = ($idOtro <= 0 && $idGrupo <= 0); // True si no hay ningún chat abierto

$modal = $_GET["modal"] ?? "";                     // Leo si se quiere abrir algún modal
if (!in_array($modal, ["miembros"], true)) $modal = ""; // Solo permito el modal "miembros"

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapa texto para HTML

/**
 * Normaliza la ruta de foto_perfil venga como:
 * - "foto.jpg"
 * - "perfiles/foto.jpg"
 * - "uploads/perfiles/foto.jpg"
 */
function fotoPerfilUrl(?string $foto): string {
  $foto = trim((string)$foto);            // Convierto a string y elimino espacios
  if ($foto === "") return "";             // Si no hay foto, devuelvo vacío

  if (strpos($foto, "uploads/") === 0) return $foto;          // Ruta ya completa
  if (strpos($foto, "perfiles/") === 0) return "uploads/" . $foto; // Le añado uploads/

  return "uploads/perfiles/" . $foto;     // Si solo viene el nombre, asumo esta carpeta
}

// Datos cabecera chat
$otro = null;   // Usuario del chat privado
$grupo = null;  // Grupo del chat de grupo

$titulo = "Chat";                                           // Título por defecto
$subtitulo = $modoInbox ? "Selecciona un usuario o grupo" : ""; // Subtítulo inicial

$esMiembroGrupo = false;   // Indica si pertenezco al grupo
$soyCreadorGrupo = false;  // Indica si soy creador del grupo
$miembrosGrupo = [];       // Lista de miembros del grupo
$usuariosNoEnGrupo = [];   // Usuarios que no están en el grupo

if ($idOtro > 0) { // Si es un chat privado
  $sql = "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario = ? LIMIT 1"; // Query usuario
  $stmt = mysqli_prepare($conexion, $sql);        // Preparo la consulta
  mysqli_stmt_bind_param($stmt, "i", $idOtro);    // Paso el id del usuario
  mysqli_stmt_execute($stmt);                     // Ejecuto la consulta
  $res = mysqli_stmt_get_result($stmt);            // Obtengo el resultado
  $otro = mysqli_fetch_assoc($res);                // Guardo el usuario
  mysqli_stmt_close($stmt);                        // Cierro el statement

  if ($otro) {                                     // Si el usuario existe
    $titulo = $otro["nombre_usuario"];             // Título con su nombre
    $subtitulo = "Conversación privada";           // Subtítulo del chat
  }
}

if ($idGrupo > 0) { // Si es un chat de grupo
  $sql = "SELECT id_grupo, nombre_grupo, id_creador FROM grupo WHERE id_grupo = ? LIMIT 1"; // Query grupo
  $stmt = mysqli_prepare($conexion, $sql);        // Preparo la consulta
  mysqli_stmt_bind_param($stmt, "i", $idGrupo);   // Paso el id del grupo
  mysqli_stmt_execute($stmt);                     // Ejecuto
  $res = mysqli_stmt_get_result($stmt);            // Resultado
  $grupo = mysqli_fetch_assoc($res);               // Guardo el grupo
  mysqli_stmt_close($stmt);                        // Cierro

  if ($grupo) {                                    // Si el grupo existe
    $titulo = $grupo["nombre_grupo"];              // Título con el nombre del grupo
    $subtitulo = "Chat de grupo";                  // Subtítulo
  }

  // verificar miembro
  $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1"); // Comprueba membresía
  mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo); // Paso grupo y mi id
  mysqli_stmt_execute($stmtM);                     // Ejecuto
  mysqli_stmt_store_result($stmtM);                // Guardo resultado
  $esMiembroGrupo = (mysqli_stmt_num_rows($stmtM) === 1); // True si soy miembro
  mysqli_stmt_close($stmtM);                       // Cierro

  $soyCreadorGrupo = ($grupo && (int)$grupo["id_creador"] === $yo); // True si yo creé el grupo

  // modal miembros
  if ($grupo && $esMiembroGrupo && $modal === "miembros") { // Solo si puedo ver el modal
    $sqlMem = "
      SELECT u.id_usuario, u.nombre_usuario, u.foto_perfil
      FROM miembro m
      JOIN usuario u ON u.id_usuario = m.id_usuario
      WHERE m.id_grupo = ?
      ORDER BY u.nombre_usuario ASC
    "; // Query para listar miembros del grupo
    $stmt = mysqli_prepare($conexion, $sqlMem);   // Preparo
    mysqli_stmt_bind_param($stmt, "i", $idGrupo); // Paso id del grupo
    mysqli_stmt_execute($stmt);                   // Ejecuto
    $res = mysqli_stmt_get_result($stmt);          // Resultado
    while ($row = mysqli_fetch_assoc($res)) $miembrosGrupo[] = $row; // Guardo miembros
    mysqli_stmt_close($stmt);                      // Cierro

    if ($soyCreadorGrupo) { // Si soy el creador, cargo usuarios no incluidos
      $sqlNo = "
        SELECT u.id_usuario, u.nombre_usuario, u.foto_perfil
        FROM usuario u
        WHERE u.id_usuario <> ?
          AND u.id_usuario NOT IN (SELECT id_usuario FROM miembro WHERE id_grupo = ?)
        ORDER BY u.nombre_usuario ASC
      "; // Usuarios que no están en el grupo
      $stmt = mysqli_prepare($conexion, $sqlNo);   // Preparo
      mysqli_stmt_bind_param($stmt, "ii", $yo, $idGrupo); // Paso parámetros
      mysqli_stmt_execute($stmt);                  // Ejecuto
      $res = mysqli_stmt_get_result($stmt);         // Resultado
      while ($row = mysqli_fetch_assoc($res)) $usuariosNoEnGrupo[] = $row; // Guardo usuarios
      mysqli_stmt_close($stmt);                     // Cierro
    }
  }
}

// Usuarios sidebar (menos yo)
$usuarios = []; // Array de usuarios
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC"); // Query usuarios
while ($row = mysqli_fetch_assoc($resU)) $usuarios[] = $row; // Guardo usuarios

// Grupos donde soy miembro
$grupos = []; // Array de grupos
$sqlG = "SELECT g.id_grupo, g.nombre_grupo
         FROM grupo g
         JOIN miembro m ON m.id_grupo = g.id_grupo
         WHERE m.id_usuario = ?
         ORDER BY g.nombre_grupo ASC"; // Query grupos del usuario
$stmtG = mysqli_prepare($conexion, $sqlG);         // Preparo
mysqli_stmt_bind_param($stmtG, "i", $yo);          // Paso mi id
mysqli_stmt_execute($stmtG);                       // Ejecuto
$resGr = mysqli_stmt_get_result($stmtG);            // Resultado
while ($row = mysqli_fetch_assoc($resGr)) $grupos[] = $row; // Guardo grupos
mysqli_stmt_close($stmtG);                         // Cierro

// NOTIFICACIONES
$noLeidosEmisor = noLeidosPorEmisor($conexion, $yo); // Mensajes no leídos por usuario
$noLeidosGrupo  = noLeidosPorGrupo($conexion, $yo);  // Mensajes no leídos por grupo

// Foto cabecera privado
$fotoCabecera = ""; // Por defecto no hay foto
if ($otro) $fotoCabecera = fotoPerfilUrl($otro["foto_perfil"] ?? ""); // Foto del otro usuario
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="estilos/estilos.css">
  <title>Chat: <?= h($titulo) ?></title>
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

          <?php foreach ($usuarios as $u): ?> <!-- Recorro la lista de usuarios para pintarlos en el sidebar -->
            <?php
              $uid = (int)$u["id_usuario"]; // Id del usuario del listado
              $activo = ($idOtro > 0 && $uid === $idOtro); // True si este usuario es el chat privado abierto ahora
              $clase = "chat__item" . ($activo ? " chat__item--activo" : ""); // Clase para marcarlo como seleccionado

              $foto = fotoPerfilUrl($u["foto_perfil"] ?? ""); // Arreglo la ruta de la foto para que siempre funcione

              $cant = $noLeidosEmisor[$uid] ?? 0; // Mensajes no leídos que tengo de este usuario (si no hay, 0)
            ?>
            <a class="<?= $clase ?>" href="chat.php?id=<?= $uid ?>"> <!-- Link para abrir el chat privado con este usuario -->
              <?php if ($foto): ?> <!-- Si hay foto, la muestro -->
                <img class="chat__avatar" src="<?= h($foto) ?>" alt=""> <!-- Escapo la ruta por seguridad -->
              <?php else: ?> <!-- Si no hay foto, muestro un avatar vacío -->
                <div class="chat__avatar"></div>
              <?php endif; ?>

              <div>
                <div class="chat__itemName"><?= h($u["nombre_usuario"]) ?></div> <!-- Nombre del usuario escapado -->
                <div class="chat__itemSub">Toca para abrir chat</div>
              </div>

              <?php if ($cant > 0): ?> <!-- Si hay no leídos, muestro el contador -->
                <span class="chat__badge"><?= (int)$cant ?></span> <!-- (int) para asegurar que solo se imprime número -->
              <?php endif; ?>
            </a>
          <?php endforeach; ?>

          <h3 style="margin-top:14px;">Grupos</h3>

          <?php if (empty($grupos)): ?> <!-- Si no estoy en ningún grupo, muestro mensaje -->
            <p class="chat__vacio">Aún no estás en ningún grupo.</p>
          <?php else: ?> <!-- Si sí tengo grupos, los recorro -->
            <?php foreach ($grupos as $g): ?> <!-- Pinto cada grupo en el sidebar -->
              <?php
                $gid = (int)$g["id_grupo"]; // Id del grupo del listado
                $activo = ($idGrupo > 0 && $gid === $idGrupo); // True si este grupo es el chat abierto ahora
                $clase = "chat__item" . ($activo ? " chat__item--activo" : ""); // Clase para marcarlo como seleccionado

                $cantG = $noLeidosGrupo[$gid] ?? 0; // Mensajes no leídos que tengo en este grupo (si no hay, 0)
              ?>
              <a class="<?= $clase ?>" href="chat.php?grupo=<?= $gid ?>"> <!-- Link para abrir el chat de este grupo -->
                <div class="chat__avatar"></div>
                <div>
                  <div class="chat__itemName"><?= h($g["nombre_grupo"]) ?></div> <!-- Nombre del grupo escapado -->
                  <div class="chat__itemSub">Chat de grupo</div>
                </div>

                <?php if ($cantG > 0): ?> <!-- Si hay no leídos, muestro el contador -->
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

            <?php if ($otro): ?> <!-- Si $otro existe, estoy en chat privado -->
              <?php if ($fotoCabecera): ?> <!-- Si el otro usuario tiene foto, la muestro -->
                <img class="chat__avatar chat__avatar--top" src="<?= h($fotoCabecera) ?>" alt="">
              <?php else: ?> <!-- Si no tiene foto, avatar vacío -->
                <div class="chat__avatar chat__avatar--top"></div>
              <?php endif; ?>

              <div>
                <a class="chat__tituloLink" href="perfil.php?id=<?= (int)$otro["id_usuario"] ?>"> <!-- Link al perfil del otro -->
                  <div class="chat__titulo"><?= h($titulo) ?></div> <!-- Título: nombre del otro usuario -->
                </a>
                <div class="chat__subtitle"><?= h($subtitulo) ?></div> <!-- Subtítulo: “Conversación privada” -->
              </div>

            <?php elseif ($grupo): ?> <!-- Si no hay $otro pero hay $grupo, estoy en chat de grupo -->
              <div class="chat__avatar chat__avatar--top"></div>
              <div>
                <a class="chat__tituloLink" href="chat.php?grupo=<?= (int)$idGrupo ?>&modal=miembros"> <!-- Abre el modal de miembros -->
                  <div class="chat__titulo"><?= h("Grupo: " . $titulo) ?></div> <!-- Título: nombre del grupo -->
                </a>
                <div class="chat__subtitle"><?= h($subtitulo) ?></div> <!-- Subtítulo: “Chat de grupo” -->
              </div>

            <?php else: ?> <!-- Si no hay ni usuario ni grupo, estoy en el inbox -->
              <div>
                <div class="chat__titulo">Chat</div>
                <div class="chat__subtitle"><?= h($subtitulo) ?></div> <!-- Subtítulo: “Selecciona un usuario o grupo” -->
              </div>
            <?php endif; ?>

          </div>
        </div>

        <div id="cajaMensajes">
          <?php if ($modoInbox): ?> <!-- Si no hay chat seleccionado, muestro mensaje -->
            <p class="chat__vacio">Selecciona un usuario o un grupo para empezar a chatear 💬</p>
          <?php else: ?> <!-- Si hay chat seleccionado, cargo el archivo que imprime los mensajes -->
            <?php include __DIR__ . "/php/leer_chat.php"; ?> <!-- Este PHP devuelve el HTML del chat -->
          <?php endif; ?>
        </div>

        <?php if (!$modoInbox): ?> <!-- Solo muestro el formulario si hay un chat abierto -->
          <form class="chat__form" method="POST" action="php/enviar_chat.php" enctype="multipart/form-data"> <!-- multipart para permitir archivo -->
            <?php if ($idOtro > 0): ?> <!-- Si es privado, mando el id del otro -->
              <input type="hidden" name="id" value="<?= (int)$idOtro ?>">
            <?php endif; ?>
            <?php if ($idGrupo > 0): ?> <!-- Si es grupo, mando el id del grupo -->
              <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>">
            <?php endif; ?>

            <label class="chat__clip" title="Enviar foto o vídeo">
              📎
              <input type="file" name="archivo" accept="image/*,video/mp4,video/webm,video/ogg" style="display:none;">
            </label>

            <input class="chat__input" type="text" name="texto" id="texto" autocomplete="off"
                   placeholder="Escribe un mensaje...">

            <button class="chat__btn" type="submit">Enviar</button>
          </form>
        <?php endif; ?>

      </div>

    </div>
  </div>

  <!-- MODAL miembros grupo -->
  <?php if ($grupo && $esMiembroGrupo && $modal === "miembros"): ?> <!-- Solo muestro el modal si el grupo existe, soy miembro y la URL pide modal=miembros -->
    <div class="modal-backdrop" id="modalBackdrop">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-head">
          <div class="modal-title">Miembros · <?= h($grupo["nombre_grupo"]) ?></div> <!-- Nombre del grupo escapado -->
          <a class="modal-close" href="chat.php?grupo=<?= (int)$idGrupo ?>">✕</a> <!-- Cierra el modal volviendo al chat del grupo -->
        </div>

        <div class="modal-body">
          <?php if (empty($miembrosGrupo)): ?> <!-- Si no hay miembros cargados -->
            <div class="modal-empty">No hay miembros.</div>
          <?php else: ?> <!-- Si hay miembros -->
            <?php foreach ($miembrosGrupo as $m): ?> <!-- Recorro cada miembro para pintarlo -->
              <?php
                $uid = (int)$m["id_usuario"]; // Id del miembro
                $nombre = $m["nombre_usuario"] ?? ""; // Nombre del miembro (si falta, vacío)
                $foto = fotoPerfilUrl($m["foto_perfil"] ?? ""); // Normalizo la foto del miembro
                $esYo = ($uid === $yo); // True si este miembro soy yo
                $esCreador = ($grupo && (int)$grupo["id_creador"] === $uid); // True si este miembro es el creador del grupo
              ?>
              <div class="modal-row">
                <a class="modal-user" href="perfil.php?id=<?= $uid ?>"> <!-- Link al perfil del miembro -->
                  <div class="modal-avatar" style="<?= $foto ? "background-image:url('".h($foto)."')" : "" ?>"></div> <!-- Si hay foto, la pongo como fondo -->
                  <div class="modal-name">
                    <?= h($nombre) ?> <!-- Nombre del miembro escapado -->
                    <?php if ($esCreador): ?> <!-- Si es el creador, lo marco -->
                      <span style="font-weight:800; opacity:.7;">(creador)</span>
                    <?php endif; ?>
                    <?php if ($esYo): ?> <!-- Si soy yo, lo marco -->
                      <span style="font-weight:800; opacity:.7;">(tú)</span>
                    <?php endif; ?>
                  </div>
                </a>

                <div class="modal-actions">
                  <?php if ($soyCreadorGrupo && !$esYo): ?> <!-- Solo el creador puede expulsar y nunca se puede expulsar a sí mismo -->
                    <form action="php/grupo_expulsar.php" method="post" style="margin:0;">
                      <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>"> <!-- Grupo del que se expulsa -->
                      <input type="hidden" name="id_usuario" value="<?= $uid ?>"> <!-- Usuario a expulsar -->
                      <button class="modal-btn modal-btn-danger" type="submit">Suprimir</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <div style="margin-top:14px; border-top:1px solid rgba(15,23,42,.10); padding-top:12px;">
            <?php if ($soyCreadorGrupo): ?> <!-- Si soy el creador, muestro el formulario para añadir -->
              <div style="font-weight:900; margin-bottom:8px;">Añadir usuario</div>

              <form action="php/grupo_anadir.php" method="post" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>"> <!-- Grupo al que se añadirá -->
                <select class="input" name="id_usuario" required style="min-width:240px;">
                  <option value="">Selecciona usuario...</option>
                  <?php foreach ($usuariosNoEnGrupo as $u): ?> <!-- Usuarios disponibles para añadir -->
                    <option value="<?= (int)$u["id_usuario"] ?>"><?= h($u["nombre_usuario"]) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn-primary" type="submit" style="padding:10px 14px;border-radius:12px;">Añadir</button>
              </form>
            <?php endif; ?>

            <div style="margin-top:14px;">
              <form action="php/grupo_salir.php" method="post" style="margin:0;">
                <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>"> <!-- Grupo del que quiero salir -->
                <button class="modal-btn modal-btn-danger" type="submit">Salir del grupo</button>
              </form>
            </div>

          </div>

        </div>
      </div>
    </div>
  <script>
  (function(){ // IIFE: se ejecuta sola al cargar, para no dejar variables sueltas en el global
    const bg = document.getElementById("modalBackdrop"); // Cojo el fondo del modal (la capa oscura)
    if(!bg) return; // Si no existe, no hago nada (significa que el modal no está abierto)

    bg.addEventListener("click", (e) => { // Si hago click en el fondo del modal...
      if(e.target === bg) window.location.href = "chat.php?grupo=<?= (int)$idGrupo ?>"; // ...y el click es justo en el fondo (no dentro de la tarjeta), cierro el modal volviendo al chat del grupo
    });

    document.addEventListener("keydown", (e) => { // Escucho teclas del teclado
      if(e.key === "Escape") window.location.href = "chat.php?grupo=<?= (int)$idGrupo ?>"; // Si pulso Escape, cierro el modal volviendo al chat del grupo
    });
  })();
  </script>
  
  <?php endif; ?> <!-- Cierro el if del modal -->

  <script>
  const caja = document.getElementById("cajaMensajes"); // Div donde se pintan los mensajes
  const idOtro = <?= (int)$idOtro ?>; // Id del chat privado actual (0 si no hay)
  const idGrupo = <?= (int)$idGrupo ?>; // Id del grupo actual (0 si no hay)

  function cargarMensajes(){ // Función que recarga los mensajes del chat actual
    if (idOtro <= 0 && idGrupo <= 0) return; // Si no hay chat abierto, no hago nada

    const params = new URLSearchParams(); // Creo parámetros para la URL de leer_chat.php
    if (idOtro > 0) params.set("id", idOtro); // Si es privado, mando id del otro usuario
    if (idGrupo > 0) params.set("grupo", idGrupo); // Si es grupo, mando id del grupo

    fetch("php/leer_chat.php?" + params.toString(), { cache: "no-store" }) // Pido el HTML del chat, sin cache para que venga actualizado
      .then(r => r.text()) // Convierto la respuesta a texto (HTML)
      .then(html => {
        const estabaAbajo = (caja.scrollTop + caja.clientHeight >= caja.scrollHeight - 30); // Compruebo si el usuario estaba ya abajo del todo (para no fastidiarle si estaba leyendo arriba)
        caja.innerHTML = html; // Reemplazo el contenido de mensajes por el nuevo HTML
        if (estabaAbajo) caja.scrollTop = caja.scrollHeight; // Si estaba abajo, lo vuelvo a bajar para que vea el último mensaje
      })
      .catch(() => {}); // Si falla el fetch, no rompo nada (simplemente no actualiza)
  }

  if (idOtro > 0 || idGrupo > 0) { // Solo activo el refresco automático si hay un chat abierto
    setInterval(cargarMensajes, 2000); // Cada 2 segundos recargo mensajes
    setTimeout(() => { caja.scrollTop = caja.scrollHeight; }, 200); // Al entrar, bajo al final después de un pequeño delay para que ya esté pintado
  }
  </script>
</body>
</html>
