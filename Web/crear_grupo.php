<?php
require_once __DIR__ . "/php/require_login.php"; // Obliga a que el usuario esté logueado para poder crear un grupo
require_once __DIR__ . "/php/conexion.php"; // Incluye la conexión a la base de datos

$conexion = conexionBD(); // Se crea la conexión con la base de datos
$yo = (int)$_SESSION["id_usuario"]; // Se guarda el id del usuario logueado
$usuarios = []; // Array donde se guardarán los usuarios que se pueden añadir al grupo
$resU = mysqli_query($conexion, "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_usuario <> $yo ORDER BY nombre_usuario ASC"); // Obtiene todos los usuarios excepto yo, ordenados alfabéticamente para mostrarlos en la lista
while ($row = mysqli_fetch_assoc($resU)) $usuarios[] = $row; // Guarda cada usuario obtenido en el array
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); } // Escapa texto para evitar XSS al mostrar nombres
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
  <title>Crear grupo</title> 
  <link rel="stylesheet" href="estilos/estilos.css"> 
  <link rel="stylesheet" href="estilos/chat.css"> 
  <link rel="icon" href="estilos/imagenes/sin_fondo_con_letras.png">
</head>
<body>

  <div class="create-group-container"> <!-- Contenedor principal de la página -->
    
    <div class="create-group-card"> <!-- Tarjeta centrada del formulario -->
        
        <div class="create-group-header"> 
            <h2>Nuevo Grupo</h2> 
            <a href="chat.php" class="btn-cancel-small">Cancelar</a> <!-- Botón para volver al chat sin crear grupo -->
        </div>

        <form method="POST" action="php/crear_grupo.php" style="display:flex; flex-direction:column; gap:15px;"> 
            
            <div>
                <label style="font-weight:700; font-size:14px; color:#555; display:block; margin-bottom:8px;">Nombre del grupo</label> 
                <input type="text" name="nombre_grupo" class="input" placeholder="Nombre del grupo" required style="width:100%;"> 
            </div>

            <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;"> <!-- Contenedor de la lista de usuarios -->
                <label style="font-weight:700; font-size:14px; color:#555; display:block; margin-bottom:8px;">Añadir participantes</label> <!-- Etiqueta de la lista -->
                
                <div class="group-users-list"> <!-- Lista scrollable de usuarios -->
                    <?php if(empty($usuarios)): ?> <!-- Si no hay usuarios disponibles -->
                        <div style="color:#999; text-align:center; padding:20px; font-size:13px;">No hay usuarios disponibles.</div> <!-- Mensaje informativo -->
                    <?php else: ?> <!-- Si hay usuarios -->
                        <?php foreach ($usuarios as $u): ?> <!-- Recorre todos los usuarios -->
                            <label class="user-check-item"> <!-- Cada usuario es un checkbox -->
                                <input type="checkbox" name="usuarios[]" value="<?= (int)$u["id_usuario"] ?>"> <!-- Checkbox con el id del usuario -->
                                <span><?= h($u["nombre_usuario"]) ?></span> <!-- Nombre del usuario -->
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align: center; margin-top: 10px;"> <!-- Contenedor del botón -->
                <button type="submit" class="btn-primary" style="padding: 12px 40px; border-radius: 20px; font-size: 15px; width:100%;">Crear Grupo</button> <!-- Envía el formulario para crear el grupo -->
            </div>

        </form>
    </div>

  </div>

</body>
</html>