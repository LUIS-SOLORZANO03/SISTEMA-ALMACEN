<?php
include 'conexion.php';

if (!isset($_GET['id_categoria'])) {
    exit('<option value="">Seleccione una categoría</option>');
}

$id_categoria = (int) $_GET['id_categoria'];

$stmt = $conexion->prepare("SELECT id, nombre FROM productos WHERE id_categoria= ? ORDER BY nombre ASC");
$stmt->bind_param("i", $id_categoria);
$stmt->execute();
$res = $stmt->get_result();

echo '<option value="">Seleccione</option>';
while ($row = $res->fetch_assoc()) {
    $nombre = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
    // value = nombre del producto (texto), porque tu columna se llama 'producto'
    echo "<option value=\"{$nombre}\">{$nombre}</option>";
}
