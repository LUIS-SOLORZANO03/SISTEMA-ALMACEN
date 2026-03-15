<?php
session_start();
include 'conexion.php';

if (!isset($_POST['id_producto'])) {
    echo json_encode(['success' => false, 'message' => 'ID no recibido']);
    exit;
}

$id = intval($_POST['id_producto']);
$sql = "UPDATE productos SET estado = 'activo' WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
