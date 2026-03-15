<?php
// traer_materiales.php
require_once 'conexion.php';
header('Content-Type: text/html; charset=UTF-8');

$categoria = $_POST['categoria'] ?? '';
if (!$categoria) {
    echo "<option value=''>Seleccione categoría</option>";
    exit;
}

// Buscamos id_categoria por nombre (si en tu esquema 'categoria' es texto en tabla productos)
$stmt = $conexion->prepare("SELECT id, nombre FROM productos WHERE id_categoria = (SELECT id FROM categorias WHERE nombre = ? LIMIT 1) AND estado = 'activo' ORDER BY nombre ASC");
$stmt->bind_param("s", $categoria);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    // Si no hay coincidencias por nombre-categoría, intentamos buscar productos cuyo campo 'nombre' contenga la categoría (fallback)
    $stmt2 = $conexion->prepare("SELECT id, nombre FROM productos WHERE nombre LIKE ? AND estado='activo' ORDER BY nombre ASC");
    $like = "%$categoria%";
    $stmt2->bind_param("s", $like);
    $stmt2->execute();
    $res = $stmt2->get_result();
}

echo "<option value=''>Seleccione material</option>";
while ($row = $res->fetch_assoc()) {
    // escapamos nombre
    $id = (int)$row['id'];
    $nombre = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
    echo "<option value=\"{$id}__" . $nombre . "\">{$nombre}</option>";
}
