<?php
// php/notificaciones_chat.php
// Funciones para contar mensajes NO LEÍDOS usando mensaje + estadomensaje
// (No modifica la BD, solo consulta)

if (!function_exists("contarNoLeidosTotal")) {
  function contarNoLeidosTotal($conexion, int $yo): int {
    // Privados no leídos (mensajes para mí y sin registro en estadomensaje)
    $sql = "
      SELECT COUNT(*) AS total
      FROM mensaje m
      LEFT JOIN estadomensaje e
        ON e.id_mensaje = m.id_mensaje
       AND e.id_usuario_receptor = ?
      WHERE m.id_usuario_receptor = ?
        AND m.id_grupo IS NULL
        AND e.id_mensaje IS NULL
    ";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $yo, $yo);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)($row["total"] ?? 0);
  }
}

if (!function_exists("noLeidosPorEmisor")) {
  function noLeidosPorEmisor($conexion, int $yo): array {
    // Devuelve array: [id_emisor => cantidad]
    $sql = "
      SELECT m.id_usuario_emisor, COUNT(*) AS total
      FROM mensaje m
      LEFT JOIN estadomensaje e
        ON e.id_mensaje = m.id_mensaje
       AND e.id_usuario_receptor = ?
      WHERE m.id_usuario_receptor = ?
        AND m.id_grupo IS NULL
        AND e.id_mensaje IS NULL
      GROUP BY m.id_usuario_emisor
    ";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $yo, $yo);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $map = [];
    while ($r = mysqli_fetch_assoc($res)) {
      $map[(int)$r["id_usuario_emisor"]] = (int)$r["total"];
    }
    mysqli_stmt_close($stmt);
    return $map;
  }
}

if (!function_exists("noLeidosPorGrupo")) {
  function noLeidosPorGrupo($conexion, int $yo): array {
    // Solo grupos donde soy miembro + mensajes no leídos que no son míos
    // Devuelve array: [id_grupo => cantidad]
    $sql = "
      SELECT m.id_grupo, COUNT(*) AS total
      FROM mensaje m
      JOIN miembro mi ON mi.id_grupo = m.id_grupo AND mi.id_usuario = ?
      LEFT JOIN estadomensaje e
        ON e.id_mensaje = m.id_mensaje
       AND e.id_usuario_receptor = ?
      WHERE m.id_grupo IS NOT NULL
        AND m.id_usuario_emisor <> ?
        AND e.id_mensaje IS NULL
      GROUP BY m.id_grupo
    ";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $yo, $yo, $yo);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $map = [];
    while ($r = mysqli_fetch_assoc($res)) {
      $map[(int)$r["id_grupo"]] = (int)$r["total"];
    }
    mysqli_stmt_close($stmt);
    return $map;
  }
}
