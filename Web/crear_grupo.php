<?php
require_once __DIR__ . "/php/require_login.php";
require_once __DIR__ . "/php/conexion.php";

$conexion = conexionBD();
$yo = (int)$_SESSION["id_usuario"];
$usuarios = [];
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC");
while ($row = mysqli_fetch_assoc($resU)) $usuarios[] = $row;
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear grupo</title>
  <link rel="stylesheet" href="estilos/estilos.css">
  <link rel="stylesheet" href="estilos/chat.css">
</head>
<body>

  <div class="create-group-container">
    
    <div class="create-group-card">
        
        <div class="create-group-header">
            <h2>Nuevo Grupo</h2>
            <a href="chat.php" class="btn-cancel-small">Cancelar</a>
        </div>

        <form method="POST" action="php/crear_grupo.php" style="display:flex; flex-direction:column; gap:15px;">
            
            <div>
                <label style="font-weight:700; font-size:14px; color:#555; display:block; margin-bottom:8px;">Nombre del grupo</label>
                <input type="text" name="nombre_grupo" class="input" placeholder="Nombre del grupo" required style="width:100%;">
            </div>

            <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">
                <label style="font-weight:700; font-size:14px; color:#555; display:block; margin-bottom:8px;">Añadir participantes</label>
                
                <div class="group-users-list">
                    <?php if(empty($usuarios)): ?>
                        <div style="color:#999; text-align:center; padding:20px; font-size:13px;">No hay usuarios disponibles.</div>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <label class="user-check-item">
                                <input type="checkbox" name="usuarios[]" value="<?= (int)$u["id_usuario"] ?>">
                                <span><?= h($u["nombre_usuario"]) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align: center; margin-top: 10px;">
                <button type="submit" class="btn-primary" style="padding: 12px 40px; border-radius: 20px; font-size: 15px; width:100%;">Crear Grupo</button>
            </div>

        </form>
    </div>

  </div>

</body>
</html>