<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";
require_once __DIR__ . "/php/notificaciones_chat.php";

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];

// --- 1. FUNCIONES HELPERS (Al principio para evitar errores) ---
function fotoPerfilUrl(?string $foto): string {
  $foto = trim((string)$foto);
  if ($foto === "") return "";
  if (strpos($foto, "uploads/") === 0) return $foto;
  if (strpos($foto, "perfiles/") === 0) return "uploads/" . $foto;
  return "uploads/perfiles/" . $foto;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

// --- 2. GESTIÓN DE PARÁMETROS ---
$idOtro  = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$idGrupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : 0;
$modoInbox = ($idOtro <= 0 && $idGrupo <= 0);
$claseBody = $modoInbox ? "" : "chat-active";

$modal = $_GET["modal"] ?? "";
if (!in_array($modal, ["miembros"], true)) $modal = "";

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
    ";
    $stmtM = mysqli_prepare($conexion, $sqlMark);
    mysqli_stmt_bind_param($stmtM, "iiii", $yo, $yo, $idOtro, $yo);
    mysqli_stmt_execute($stmtM);
    mysqli_stmt_close($stmtM);
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
    ";
    $stmtM = mysqli_prepare($conexion, $sqlMark);
    mysqli_stmt_bind_param($stmtM, "iiii", $yo, $yo, $idGrupo, $yo);
    mysqli_stmt_execute($stmtM);
    mysqli_stmt_close($stmtM);
}

// --- 4. OBTENER DATOS DE CABECERA (Usuario o Grupo) ---
$otro = null; $grupo = null;
$titulo = "Chat"; $subtitulo = "Selecciona una conversación";
$esMiembroGrupo = false; $soyCreadorGrupo = false;
$miembrosGrupo = []; $usuariosNoEnGrupo = [];

if ($idOtro > 0) {
  $stmt = mysqli_prepare($conexion, "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "i", $idOtro);
  mysqli_stmt_execute($stmt);
  $otro = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);
  if ($otro) {
    $titulo = $otro["nombre_usuario"];
    $subtitulo = "Conversación privada";
  }
}

if ($idGrupo > 0) {
  $stmt = mysqli_prepare($conexion, "SELECT id_grupo, nombre_grupo, id_creador FROM grupo WHERE id_grupo = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "i", $idGrupo);
  mysqli_stmt_execute($stmt);
  $grupo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);

  if ($grupo) {
    $titulo = $grupo["nombre_grupo"];
    $subtitulo = "Chat de grupo";
    
    // Verificar si soy miembro
    $stmtM = mysqli_prepare($conexion, "SELECT 1 FROM miembro WHERE id_grupo = ? AND id_usuario = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtM, "ii", $idGrupo, $yo);
    mysqli_stmt_execute($stmtM);
    mysqli_stmt_store_result($stmtM);
    $esMiembroGrupo = (mysqli_stmt_num_rows($stmtM) === 1);
    mysqli_stmt_close($stmtM);
    
    $soyCreadorGrupo = ((int)$grupo["id_creador"] === $yo);

    // Cargar miembros solo si el modal está activo
    if ($esMiembroGrupo && $modal === "miembros") {
       $sqlMem = "SELECT u.id_usuario, u.nombre_usuario, u.foto_perfil FROM miembro m JOIN usuario u ON u.id_usuario = m.id_usuario WHERE m.id_grupo = ? ORDER BY u.nombre_usuario ASC";
       $stmt = mysqli_prepare($conexion, $sqlMem);
       mysqli_stmt_bind_param($stmt, "i", $idGrupo);
       mysqli_stmt_execute($stmt);
       $res = mysqli_stmt_get_result($stmt);
       while ($r = mysqli_fetch_assoc($res)) $miembrosGrupo[] = $r;
       mysqli_stmt_close($stmt);

       if ($soyCreadorGrupo) {
         $sqlNo = "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_usuario <> ? AND id_usuario NOT IN (SELECT id_usuario FROM miembro WHERE id_grupo = ?) ORDER BY nombre_usuario ASC";
         $stmt = mysqli_prepare($conexion, $sqlNo);
         mysqli_stmt_bind_param($stmt, "ii", $yo, $idGrupo);
         mysqli_stmt_execute($stmt);
         $res = mysqli_stmt_get_result($stmt);
         while ($r = mysqli_fetch_assoc($res)) $usuariosNoEnGrupo[] = $r;
         mysqli_stmt_close($stmt);
       }
    }
  }
}

// --- 5. CARGAR LISTAS DEL SIDEBAR ---
$usuarios = [];
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario, foto_perfil FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC");
while ($r = mysqli_fetch_assoc($resU)) $usuarios[] = $r;

$grupos = [];
$stmtG = mysqli_prepare($conexion, "SELECT g.id_grupo, g.nombre_grupo FROM grupo g JOIN miembro m ON m.id_grupo = g.id_grupo WHERE m.id_usuario = ? ORDER BY g.nombre_grupo ASC");
mysqli_stmt_bind_param($stmtG, "i", $yo);
mysqli_stmt_execute($stmtG);
$resGr = mysqli_stmt_get_result($stmtG);
while ($r = mysqli_fetch_assoc($resGr)) $grupos[] = $r;
mysqli_stmt_close($stmtG);

// Calcular notificaciones (ahora ya actualizadas)
$noLeidosEmisor = noLeidosPorEmisor($conexion, $yo);
$noLeidosGrupo  = noLeidosPorGrupo($conexion, $yo);

$fotoCabecera = "";
if ($otro) $fotoCabecera = fotoPerfilUrl($otro["foto_perfil"] ?? "");
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
</head>
<body class="<?= $claseBody ?>">

  <div id="app" style="padding: 20px;">
    
    <div class="chat-container">

      <aside class="chat-sidebar">
        <div class="chat-sidebar-header">
          <a href="index.php" class="btn-small btn-outline">← Inicio</a>
          <a href="crear_grupo.php" class="btn-small btn-fill">+ Crear Grupo</a>
        </div>

        <div class="chat-list">
          <div class="chat-section-title">Personas</div>
          <?php foreach ($usuarios as $u): ?>
            <?php
              $uid = (int)$u["id_usuario"];
              $activo = ($idOtro > 0 && $uid === $idOtro);
              $foto = fotoPerfilUrl($u["foto_perfil"] ?? "");
              $cant = $noLeidosEmisor[$uid] ?? 0;
            ?>
            <a href="chat.php?id=<?= $uid ?>" class="chat-item <?= $activo ? 'active' : '' ?>">
              <div class="chat-avatar" style="<?= $foto ? "background-image:url('".h($foto)."')" : "" ?>"></div>
              <div class="chat-info">
                <div class="chat-name"><?= h($u["nombre_usuario"]) ?></div>
                <div class="chat-preview">Haz clic para chatear</div>
              </div>
              <?php if ($cant > 0): ?><div class="chat-badge"><?= $cant ?></div><?php endif; ?>
            </a>
          <?php endforeach; ?>

          <div class="chat-section-title">Grupos</div>
          <?php foreach ($grupos as $g): ?>
            <?php
               $gid = (int)$g["id_grupo"];
               $activo = ($idGrupo > 0 && $gid === $idGrupo);
               $cant = $noLeidosGrupo[$gid] ?? 0;
            ?>
            <a href="chat.php?grupo=<?= $gid ?>" class="chat-item <?= $activo ? 'active' : '' ?>">
               <div class="chat-avatar" style="background:#e0e7ff; color:var(--p); display:grid; place-items:center; font-weight:800; border:none;">#</div>
               <div class="chat-info">
                 <div class="chat-name"><?= h($g["nombre_grupo"]) ?></div>
                 <div class="chat-preview">Grupo</div>
               </div>
               <?php if ($cant > 0): ?><div class="chat-badge"><?= $cant ?></div><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </aside>

      <main class="chat-main">
        <?php if ($modoInbox): ?>
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#aaa; text-align:center; padding:20px;">
             <div style="font-size:50px; margin-bottom:10px;">💬</div>
             <h2 style="color:var(--txt); margin-bottom:5px;">Tus Mensajes</h2>
             <p>Selecciona un contacto para empezar.</p>
          </div>
        <?php else: ?>
          
          <div class="chat-header">
             <div style="display:flex; align-items:center; gap:10px;">
               <a href="chat.php" class="btn-back-mobile">←</a>
               <?php 
                 // Si es grupo, el link lleva al modal. Si es usuario, al perfil.
                 $linkHeader = ($idGrupo > 0) ? "chat.php?grupo=$idGrupo&modal=miembros" : "perfil.php?id=$idOtro"; 
                 $imgHeader = ($idGrupo > 0) ? "" : $fotoCabecera;
               ?>
               <a href="<?= $linkHeader ?>" class="chat-header-user">
                 <?php if($imgHeader): ?>
                    <div class="chat-avatar" style="background-image:url('<?= h($imgHeader) ?>')"></div>
                 <?php else: ?>
                    <div class="chat-avatar" style="background:#ccc; display:grid; place-items:center;">#</div>
                 <?php endif; ?>
                 <div>
                   <div class="chat-header-name"><?= h($titulo) ?></div>
                   <div class="chat-header-status"><?= h($subtitulo) ?></div>
                 </div>
               </a>
             </div>
          </div>

          <div id="cajaMensajes">
             <?php include __DIR__ . "/php/leer_chat.php"; ?>
          </div>

          <form class="chat-footer" method="POST" action="php/enviar_chat.php" enctype="multipart/form-data">
             <?php if ($idOtro > 0): ?><input type="hidden" name="id" value="<?= $idOtro ?>"><?php endif; ?>
             <?php if ($idGrupo > 0): ?><input type="hidden" name="grupo" value="<?= $idGrupo ?>"><?php endif; ?>

             <label class="btn-clip" title="Adjuntar">
               📎 <input type="file" name="archivo" accept="image/*,video/*" style="display:none;">
             </label>
             <input type="text" name="texto" class="chat-input" placeholder="Escribe un mensaje..." autocomplete="off">
             <button type="submit" class="btn-send">➤</button>
          </form>
        <?php endif; ?>
      </main>

    </div>
  </div>

  <?php if ($grupo && $esMiembroGrupo && $modal === "miembros"): ?>
    <div class="modal-backdrop" id="modalBackdrop">
      <div class="modal-card">
        
        <div class="modal-head">
          <div class="modal-title">Miembros del grupo</div>
          <a class="modal-close" href="chat.php?grupo=<?= (int)$idGrupo ?>">✕</a>
        </div>

        <div class="modal-body">
          
          <div class="modal-list">
            <?php foreach ($miembrosGrupo as $m): ?>
              <?php
                 $muid = (int)$m["id_usuario"];
                 $mfoto = fotoPerfilUrl($m["foto_perfil"] ?? "");
                 $esYo = ($muid === $yo);
                 $esCreadorMiembro = ($grupo && (int)$grupo["id_creador"] === $muid);
              ?>
              <div class="modal-row">
                 <a href="perfil.php?id=<?= $muid ?>" class="modal-user-info">
                   <div class="modal-avatar" style="<?= $mfoto ? "background-image:url('".h($mfoto)."')" : "" ?>"></div>
                   <div class="modal-name">
                       <?= h($m["nombre_usuario"]) ?>
                       <?php if($esYo): ?> <span style="font-weight:400; color:#999;">(tú)</span> <?php endif; ?>
                       <?php if($esCreadorMiembro): ?> <span style="color:var(--p);">👑</span> <?php endif; ?>
                   </div>
                 </a>
                 
                 <?php if($soyCreadorGrupo && !$esYo): ?>
                   <form action="php/grupo_expulsar.php" method="post" style="margin:0;">
                     <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>">
                     <input type="hidden" name="id_usuario" value="<?= $muid ?>">
                     <button type="submit" class="modal-btn-danger">Expulsar</button>
                   </form>
                 <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          
          <?php if($soyCreadorGrupo): ?>
            <form action="php/grupo_anadir.php" method="post" class="modal-add-form">
                <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>">
                <select name="id_usuario" class="modal-select" required>
                  <option value="">Añadir participante...</option>
                  <?php foreach($usuariosNoEnGrupo as $ug): ?>
                    <option value="<?= $ug['id_usuario'] ?>"><?= h($ug['nombre_usuario']) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="modal-btn-primary">Añadir</button>
            </form>
          <?php endif; ?>
          
          <div class="modal-footer">
             <form action="php/grupo_salir.php" method="post">
             <input type="hidden" name="grupo" value="<?= (int)$idGrupo ?>">
             
             <button type="submit" class="btn-block-danger" onclick="return confirm('¿Seguro que quieres salir del grupo?');">
                 Salir del grupo
             </button>
             
          </form>
          </div>

        </div> </div>
    </div>

    <script>
      const bg = document.getElementById("modalBackdrop");
      if(bg) bg.addEventListener("click", e => { if(e.target===bg) window.location.href="chat.php?grupo=<?= (int)$idGrupo ?>"; });
    </script>
  <?php endif; ?>

  <script>
    const caja = document.getElementById("cajaMensajes");
    const idOtro = <?= (int)$idOtro ?>;
    const idGrupo = <?= (int)$idGrupo ?>;

    function cargarMensajes(){
      if(idOtro<=0 && idGrupo<=0) return;
      const p = new URLSearchParams();
      if(idOtro>0) p.set("id", idOtro);
      if(idGrupo>0) p.set("grupo", idGrupo);

      fetch("php/leer_chat.php?"+p.toString(), {cache: "no-store"})
        .then(r=>r.text())
        .then(html => {
           if(!caja) return;
           const estabaAbajo = (caja.scrollTop + caja.clientHeight >= caja.scrollHeight - 60);
           caja.innerHTML = html;
           if(estabaAbajo) caja.scrollTop = caja.scrollHeight;
        });
    }

    if(idOtro>0 || idGrupo>0){
      setInterval(cargarMensajes, 2000);
      if(caja) setTimeout(() => caja.scrollTop = caja.scrollHeight, 100);
    }
  </script>
</body>
</html>