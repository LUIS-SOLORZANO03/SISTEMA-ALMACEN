<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("Material no especificado");
}
$id = intval($_GET['id']);

// Obtener cliente antes de eliminar (para redirigir bien)
$sql = "SELECT cliente_id FROM materiales WHERE id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();

if (!$material) {
    die("Material no encontrado");
}

$cliente_id = $material['cliente_id'];

// Eliminar material
$sql = "DELETE FROM materiales WHERE id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: detalle_cliente.php?id=$cliente_id&del_ok=1");
    exit;
} else {
    echo "Error al eliminar: " . $conexion->error;
}
