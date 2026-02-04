<?php
require_once __DIR__ . "/php/require_login.php"; // Comprueba que el usuario esté logueado antes de permitir acceso al chat
require_once __DIR__ . "/php/conexion.php"; // Incluye el archivo con la función de conexión a la base de datos
require_once __DIR__ . "/php/notificaciones_chat.php"; // Incluye funciones para calcular mensajes no leídos

$conexion = conexionBD(); // Se establece la conexión con la base de datos
$yo = (int)$_SESSION["id_usuario"]; // Se guarda el id del usuario logueado desde la sesión

// --- 1. FUNCIONES HELPERS (Al principio para evitar errores) ---
function fotoPerfilUrl(?string $foto): string { // Función que normaliza la ruta de la foto de perfil
  $foto = trim((string)$foto); // Se asegura de que sea string y elimina espacios
  if ($foto === "") return ""; // Si no hay foto, devuelve vacío
  if (strpos($foto, "uploads/") === 0) return $foto; // Si ya incluye la carpeta uploads, se devuelve tal cual
  if (strpos($foto, "perfiles/") === 0) return "uploads/" . $foto; // Si empieza por perfiles/, se añade uploads delante
  return "uploads/perfiles/" . $foto; // En cualquier otro caso, construye la ruta completa por defecto
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapa texto para evitar inyección HTML/XSS

// --- 2. GESTIÓN DE PARÁMETROS ---
$idOtro  = isset($_GET["id"]) ? (int)$_GET["id"] : 0; // Id del usuario con el que se abre un chat privado
$idGrupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : 0; // Id del grupo con el que se abre un chat grupal
$modoInbox = ($idOtro <= 0 && $idGrupo <= 0); // Indica si no hay ningún chat seleccionado (vista inicial)
$claseBody = $modoInbox ? "" : "chat-active"; // Clase CSS usada para cambiar el diseño cuando hay chat activo

$modal = $_GET["modal"] ?? ""; // Parámetro que indica si hay que abrir un modal
if (!in_array($modal, ["miembros"], true)) $modal = ""; // Solo se permite el modal de miembros, cualquier otro se ignora

// --- 3. MARCAR MENSAJES COMO LEÍDOS (Lógica corregida) ---
if ($idOtro > 0) {
    // Chat Privado: Insertar en estadomensaje si no existe
    $sqlMark = "
        INSERT INTO estadomensaje (id_mensaje, id_usuario_receptor, fecha_leido)
        SELECT m.id_mensaje, ?, NOW()
        FROM mensaje m
        LEFT JOIN estadomensaje e ON e.id_mensaje = m.id_mensaje AND e.id_usuario_receptor = ?
        WHERE m.id_usuario_emisor = ? 
          AND m.id_usuario_receptor = ? 
          AND m.id_grupo IS NULL
          AND e.id_mensaje IS NULL
    "; // Inserta registros de lectura para todos los mensajes privados que me enviaron y que aún no estaban marcados como leídos
    $stmtM = mysqli_prepare($conexion, $sqlMark); // Prepara la consulta SQL
    mysqli_stmt_bind_param($stmtM, "iiii", $yo, $yo, $idOtro, $yo); // Asigna los valores: yo como lector, el otro como emisor y receptor correcto
    mysqli_stmt_execute($stmtM); // Ejecuta la inserción de mensajes leídos
    mysqli_stmt_close($stmtM); // Cierra el statement
} 
elseif ($idGrupo > 0) {
    // Chat Grupo: Insertar en estadomensaje si no existe
    $sqlMark = "
        INSERT INTO estadomensaje (id_mensaje, id_usuario_receptor, fecha_leido)
        SELECT m.id_mensaje, ?, NOW()
        FROM mensaje m
        LEFT JOIN estadomensaje e ON e.id_mensaje = m.id_mensaje AND e.id_usuario_receptor = ?
        WHERE m.id_grupo = ? 
          AND m.id_usuario_emisor != ? 
          AND e.id_mensaje IS NULL
    "; // Inserta registros de lectura para mensajes del grupo que no son míos y que aún no había leído
    $stmtM = mysqli_prepare($conexion, $sqlMark); // Prepara la consulta SQL
    mysqli_stmt_bind_param($stmtM, "iiii", $yo, $yo, $idGrupo, $yo); // Asigna el lector, el grupo y excluye mis propios mensajes
    mysqli_stmt_execute($stmtM); // Ejecuta la inserción de mensajes leídos
    mysqli_stmt_close($stmtM); // Cierra el statement
}

// --- 4. OBTENER DATOS DE CABECERA (Usuario o Grupo) ---
$otro = null; $grupo = null; // Variables para almacenar datos del usuario o grupo activo
$titulo = "Chat"; $subtitulo = "Selecciona una conversación"; // Textos por defecto de la cabecera
$esMiembroGrupo = false; $soyCreadorGrupo = false; // Flags para permisos dentro del grupo
$miembrosGrupo = []; $usuariosNoEnGrupo = []; // Arrays para el listado de miembros y posibles nuevos usuarios

if ($idOtro > 0) {
  $stmt = mysqli_prepare($conexion, "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario = ? LIMIT 1"); // Obtiene los datos del usuario del chat privado
  mysqli_stmt_bind_param($stmt, "i", $idOtro); // Asigna el id del otro usuario
  mysqli_stmt_execute($stmt); // Ejecuta la consulta
  $otro = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); // Guarda los datos del usuario
  mysqli_stmt_close($stmt); // Cierra el statement
  if ($otro) {
    $titulo = $otro["nombre_usuario"]; // Nombre mostrado en la cabecera del chat
    $subtitulo = "Conversación privada"; // Subtítulo indicando chat privado
  }
}

if ($idGrupo > 0) {
  $stmt = mysqli_prepare($conexion, "SELECT id_grupo, nombre_grupo, id_creador FROM grupo WHERE id_grupo = ? LIMIT 1"); // Obtiene los datos básicos del grupo
  mysqli_stmt_bind_param($stmt, "i", $idGrupo); // Asigna el id del grupo
  mysqli_stmt_execute($stmt); // Ejecuta la consulta
  $grupo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); // Guarda los datos del grupo
  mysqli_stmt_close($stmt); // Cierra el statement

  if ($grupo) {
    $titulo = $grupo["nombre_grupo"]; // Nombre del grupo en la cabecera
    $subtitulo = "Chat de grupo"; // Subtítulo indicando chat grupal
    
    // Verificar si soy miembro
    $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1"); // Comprueba si el usuario logueado pertenece al grupo
    mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo); // Asigna el grupo y mi id de usuario
    mysqli_stmt_execute($stmtM); // Ejecuta la consulta
    mysqli_stmt_store_result($stmtM); // Almacena el resultado para poder contar filas
    $esMiembroGrupo = (mysqli_stmt_num_rows($stmtM) === 1); // Si hay una fila, soy miembro del grupo
    mysqli_stmt_close($stmtM); // Cierra el statement
    
    $soyCreadorGrupo = ((int)$grupo["id_creador"] === $yo); // Comprueba si yo soy el creador del grupo

    // Cargar miembros solo si el modal está activo
    if ($esMiembroGrupo && $modal === "miembros") {
       $sqlMem = "SELECT u.id_usuario, u.nombre_usuario, u.foto_perfil FROM miembro m JOIN usuario u ON u.id_usuario = m.id_usuario WHERE m.id_grupo = ? ORDER BY u.nombre_usuario ASC"; // Obtiene la lista de miembros del grupo
       $stmt = mysqli_prepare($conexion, $sqlMem); // Prepara la consulta SQL
       mysqli_stmt_bind_param($stmt, "i", $idGrupo); // Asigna el id del grupo
       mysqli_stmt_execute($stmt); // Ejecuta la consulta
       $res = mysqli_stmt_get_result($stmt); // Obtiene el resultado
       while ($r = mysqli_fetch_assoc($res)) $miembrosGrupo[] = $r; // Guarda cada miembro en el array
       mysqli_stmt_close($stmt); // Cierra el statement

       if ($soyCreadorGrupo) {
         $sqlNo = "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_usuario <> ? AND id_usuario NOT IN (SELECT id_usuario FROM miembro WHERE id_grupo = ?) ORDER BY nombre_usuario ASC"; // Obtiene usuarios que no están en el grupo para poder añadirlos
         $stmt = mysqli_prepare($conexion, $sqlNo); // Prepara la consulta SQL
         mysqli_stmt_bind_param($stmt, "ii", $yo, $idGrupo); // Excluye mi usuario y los que ya están en el grupo
         mysqli_stmt_execute($stmt); // Ejecuta la consulta
         $res = mysqli_stmt_get_result($stmt); // Obtiene el resultado
         while ($r = mysqli_fetch_assoc($res)) $usuariosNoEnGrupo[] = $r; // Guarda usuarios disponibles para añadir
         mysqli_stmt_close($stmt); // Cierra el statement
       }
    }
  }
}
// --- 5. CARGAR LISTAS DEL SIDEBAR ---
$usuarios = []; // Array donde se guardarán los usuarios para el chat privado
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC"); // Obtiene todos los usuarios menos yo, ordenados alfabéticamente
while ($r = mysqli_fetch_assoc($resU)) $usuarios[] = $r; // Guarda cada usuario en el array

$grupos = []; // Array donde se guardarán los grupos del usuario
$stmtG = mysqli_prepare($conexion, "SELECT g.id_grupo, g.nombre_grupo FROM grupo g JOIN miembro m ON m.id_grupo = g.id_grupo WHERE m.id_usuario = ? ORDER BY g.nombre_grupo ASC"); // Obtiene los grupos a los que pertenezco
mysqli_stmt_bind_param($stmtG, "i", $yo); // Asigna mi id de usuario
mysqli_stmt_execute($stmtG); // Ejecuta la consulta
$resGr = mysqli_stmt_get_result($stmtG); // Obtiene el resultado
while ($r = mysqli_fetch_assoc($resGr)) $grupos[] = $r; // Guarda cada grupo en el array
mysqli_stmt_close($stmtG); // Cierra el statement

// Calcular notificaciones (ahora ya actualizadas)
$noLeidosEmisor = noLeidosPorEmisor($conexion, $yo); // Calcula mensajes privados no leídos por cada usuario
$noLeidosGrupo  = noLeidosPorGrupo($conexion, $yo); // Calcula mensajes no leídos por cada grupo

$fotoCabecera = ""; // Variable para la foto que se mostrará en la cabecera
if ($otro) $fotoCabecera = fotoPerfilUrl($otro["foto_perfil"] ?? ""); // Si es chat privado, obtiene la foto del otro usuario
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
  <title>Chat</title> 
  
  <link rel="stylesheet" href="estilos/estilos.css"> 
  <link rel="stylesheet" href="estilos/chat.css">
  <link rel="stylesheet" href="estilos/modal.css"> 

  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body class="<?= $claseBody ?>"> <!-- Aplica clase según si hay chat activo o no -->

  <div id="app" style="padding: 20px;"> <!-- Contenedor principal de la app -->
    
    <div class="chat-container"> <!-- Contenedor general del chat -->

      <aside class="chat-sidebar"> <!-- Sidebar izquierdo -->
        <div class="chat-sidebar-header"> <!-- Cabecera del sidebar -->
          <a href="index.php" class="btn-small btn-outline">← Inicio</a> <!-- Botón para volver al inicio -->
          <a href="crear_grupo.php" class="btn-small btn-fill">Crear Grupo</a> <!-- Botón para crear un nuevo grupo -->
        </div>

        <div class="chat-list"> <!-- Lista de chats -->
          <div class="chat-section-title">Personas</div> <!-- Sección de chats privados -->
          <?php foreach ($usuarios as $u): ?> <!-- Recorre todos los usuarios -->
            <?php
              $uid = (int)$u["id_usuario"]; // Id del usuario listado
              $activo = ($idOtro > 0 && $uid === $idOtro); // Comprueba si este chat está activo
              $foto = fotoPerfilUrl($u["foto_perfil"] ?? ""); // Obtiene la foto de perfil
              $cant = $noLeidosEmisor[$uid] ?? 0; // Cantidad de mensajes no leídos con ese usuario
            ?>
            <a href="chat.php?id=<?= $uid ?>" class="chat-item <?= $activo ? 'active' : '' ?>"> <!-- Enlace al chat privado -->
              <div class="chat-avatar" style="<?= $foto ? "background-image:url('".h($foto)."')" : "" ?>"></div> <!-- Avatar del usuario -->
              <div class="chat-info">
                <div class="chat-name"><?= h($u["nombre_usuario"]) ?></div> <!-- Nombre del usuario -->
                <div class="chat-preview">Haz clic para chatear</div> <!-- Texto informativo -->
              </div>
              <?php if ($cant > 0): ?><div class="chat-badge"><?= $cant ?></div><?php endif; ?> <!-- Badge con mensajes no leídos -->
            </a>
          <?php endforeach; ?>

          <div class="chat-section-title">Grupos</div> <!-- Sección de chats de grupo -->
          <?php foreach ($grupos as $g): ?> <!-- Recorre todos los grupos -->
            <?php
               $gid = (int)$g["id_grupo"]; // Id del grupo
               $activo = ($idGrupo > 0 && $gid === $idGrupo); // Comprueba si este grupo está activo
               $cant = $noLeidosGrupo[$gid] ?? 0; // Cantidad de mensajes no leídos del grupo
            ?>
            <a href="chat.php?grupo=<?= $gid ?>" class="chat-item <?= $activo ? 'active' : '' ?>"> <!-- Enlace al chat del grupo -->
               <div class="chat-avatar" style="background:#e0e7ff; color:var(--p); display:grid; place-items:center; font-weight:800; border:none;">#</div> <!-- Avatar genérico de grupo -->
               <div class="chat-info">
                 <div class="chat-name"><?= h($g["nombre_grupo"]) ?></div> <!-- Nombre del grupo -->
                 <div class="chat-preview">Grupo</div> <!-- Texto informativo -->
               </div>
               <?php if ($cant > 0): ?><div class="chat-badge"><?= $cant ?></div><?php endif; ?> <!-- Badge de no leídos -->
            </a>
          <?php endforeach; ?>
        </div>
      </aside>

      <main class="chat-main"> <!-- Zona principal del chat -->
        <?php if ($modoInbox): ?> <!-- Si no hay chat seleccionado -->
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#aaa; text-align:center; padding:20px;"> <!-- Vista inicial -->
             <div style="font-size:50px; margin-bottom:10px;">💬</div> <!-- Icono decorativo -->
             <h2 style="color:var(--txt); margin-bottom:5px;">Tus Mensajes</h2> <!-- Título -->
             <p>Selecciona un usuario para empezar.</p> <!-- Texto informativo -->
          </div>
        <?php else: ?> <!-- Si hay un chat activo -->
          
          <div class="chat-header"> <!-- Cabecera del chat -->
             <div style="display:flex; align-items:center; gap:10px;">
               <a href="chat.php" class="btn-back-mobile">←</a> <!-- Botón volver (mobile) -->
               <?php 
                 // Si es grupo, el link lleva al modal de miembros; si es privado, al perfil del usuario
                 $linkHeader = ($idGrupo > 0) ? "chat.php?grupo=$idGrupo&modal=miembros" : "perfil.php?id=$idOtro"; 
                 $imgHeader = ($idGrupo > 0) ? "" : $fotoCabecera; // En grupos no se usa foto
               ?>
               <a href="<?= $linkHeader ?>" class="chat-header-user"> <!-- Enlace de la cabecera -->
                 <?php if($imgHeader): ?> <!-- Si hay foto -->
                    <div class="chat-avatar" style="background-image:url('<?= h($imgHeader) ?>')"></div> <!-- Avatar del usuario -->
                 <?php else: ?> <!-- Si es grupo -->
                    <div class="chat-avatar" style="background:#ccc; display:grid; place-items:center;">#</div> <!-- Avatar de grupo -->
                 <?php endif; ?>
                 <div>
                   <div class="chat-header-name"><?= h($titulo) ?></div> <!-- Título del chat -->
                   <div class="chat-header-status"><?= h($subtitulo) ?></div> <!-- Subtítulo -->
                 </div>
               </a>
             </div>
          </div>

          <div id="cajaMensajes">
             <?php include __DIR__ . "/php/leer_chat.php"; ?> <!-- Carga inicial de los mensajes del chat -->
          </div>

          <form class="chat-footer" method="POST" action="php/enviar_chat.php" enctype="multipart/form-data"> <!-- Formulario de envío de mensajes -->
             <?php if ($idOtro > 0): ?><input type="hidden" name="id" value="<?= $idOtro ?>"><?php endif; ?> <!-- Id del usuario receptor -->
             <?php if ($idGrupo > 0): ?><input type="hidden" name="grupo" value="<?= $idGrupo ?>"><?php endif; ?> <!-- Id del grupo -->

             <label class="btn-clip" title="Adjuntar">
               📎 <input type="file" name="archivo" accept="image/*,video/*" style="display:none;"> <!-- Input para adjuntar archivos -->
             </label>
             <input type="text" name="texto" class="chat-input" placeholder="Escribe un mensaje..." autocomplete="off"> <!-- Campo de texto -->
             <button type="submit" class="btn-send">➤</button> <!-- Botón enviar -->
          </form>
        <?php endif; ?>
      </main>

    </div>
  </div>

  <?php if ($grupo && $esMiembroGrupo && $modal === "miembros"): ?> <!-- Modal solo si es grupo y soy miembro -->
    <div class="modal-backdrop" id="modalBackdrop">
      <div class="modal-card">
        
        <div class="modal-head">
          <div class="modal-title">Miembros del grupo</div> <!-- Título del modal -->
          <a class="modal-close" href="chat.php?grupo=<?= (int)$idGrupo ?>">✕</a> <!-- Cerrar modal -->
        </div>

        <div class="modal-body">
          
          <div class="modal-list">
            <?php foreach ($miembrosGrupo as $m): ?> <!-- Recorre los miembros del grupo -->
              <?php
                 $muid = (int)$m["id_usuario"]; // Id del miembro
                 $mfoto = fotoPerfilUrl($m["foto_perfil"] ?? ""); // Foto del miembro
                 $esYo = ($muid === $yo); // Comprueba si el miembro soy yo
                 $esCreadorMiembro = ($grupo && (int)$grupo["id_creador"] === $muid); // Comprueba si es el creador
              ?>
              <div class="modal-row">
                 <a href="perfil.php?id=<?= $muid ?>" class="modal-user-info"> <!-- Enlace al perfil -->
                   <div class="modal-avatar" style="<?= $mfoto ? "background-image:url('".h($mfoto)."')" : "" ?>"></div> <!-- Avatar -->
                   <div class="modal-name">
                       <?= h($m["nombre_usuario"]) ?> <!-- Nombre del miembro -->
                       <?php if($esYo): ?> <span style="font-weight:400; color:#999;">(tú)</span> <?php endif; ?> <!-- Marca si soy yo -->
                       <?php if($esCreadorMiembro): ?> <span style="color:var(--p);">👑</span> <?php endif; ?> <!-- Marca si es creador -->
                   </div>
                 </a>
                 
                 <?php if($soyCreadorGrupo && !$esYo): ?> <!-- Solo el creador puede expulsar -->
                   <form action="php/grupo_expulsar.php" method="post" style="margin:0;">
                     <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>"> <!-- Id del grupo -->
                     <input type="hidden" name="id_usuario" value="<?= $muid ?>"> <!-- Id del usuario a expulsar -->
                     <button type="submit" class="modal-btn-danger">Expulsar</button> <!-- Botón expulsar -->
                   </form>
                 <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          
          <?php if($soyCreadorGrupo): ?> <!-- Solo el creador puede añadir usuarios -->
            <form action="php/grupo_anadir.php" method="post" class="modal-add-form">
                <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>"> <!-- Id del grupo -->
                <select name="id_usuario" class="modal-select" required> <!-- Selector de usuarios -->
                  <option value="">Añadir participante...</option>
                  <?php foreach($usuariosNoEnGrupo as $ug): ?> <!-- Usuarios disponibles -->
                    <option value="<?= $ug['id_usuario'] ?>"><?= h($ug['nombre_usuario']) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="modal-btn-primary">Añadir</button> <!-- Botón añadir -->
            </form>
          <?php endif; ?>
          
          <div class="modal-footer">
             <form action="php/grupo_salir.php" method="post"> <!-- Formulario para salir del grupo -->
             <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>"> <!-- Id del grupo -->
             
             <button type="submit" class="btn-block-danger" onclick="return confirm('¿Seguro que quieres salir del grupo?');">
                 Salir del grupo <!-- Botón salir -->
             </button>
             
          </form>
          </div>

        </div> </div>
    </div>

    <script>
      const bg = document.getElementById("modalBackdrop"); // Fondo del modal
      if(bg) bg.addEventListener("click", e => { if(e.target===bg) window.location.href="chat.php?grupo=<?= (int)$idGrupo ?>"; }); // Cierra el modal al hacer clic fuera
    </script>
  <?php endif; ?>

  <script>
    const caja = document.getElementById("cajaMensajes"); // Contenedor de los mensajes
    const idOtro = <?= (int)$idOtro ?>; // Id del usuario del chat privado
    const idGrupo = <?= (int)$idGrupo ?>; // Id del grupo

    function cargarMensajes(){
      if(idOtro<=0 && idGrupo<=0) return; // Si no hay chat activo, no hace nada
      const p = new URLSearchParams(); // Parámetros para la petición
      if(idOtro>0) p.set("id", idOtro); // Añade id de usuario si es chat privado
      if(idGrupo>0) p.set("grupo", idGrupo); // Añade id de grupo si es chat grupal

      fetch("php/leer_chat.php?"+p.toString(), {cache: "no-store"}) // Pide los mensajes actualizados al servidor
        .then(r=>r.text()) // Convierte la respuesta en texto HTML
        .then(html => {
           if(!caja) return; // Si no existe la caja, sale
           const estabaAbajo = (caja.scrollTop + caja.clientHeight >= caja.scrollHeight - 60); // Comprueba si el scroll estaba abajo
           caja.innerHTML = html; // Actualiza los mensajes
           if(estabaAbajo) caja.scrollTop = caja.scrollHeight; // Mantiene el scroll abajo si ya lo estaba
        });
    }

    if(idOtro>0 || idGrupo>0){
      setInterval(cargarMensajes, 2000); // Recarga mensajes cada 2 segundos
      if(caja) setTimeout(() => caja.scrollTop = caja.scrollHeight, 100); // Baja el scroll al cargar
    }
  </script>
</body>
</html>