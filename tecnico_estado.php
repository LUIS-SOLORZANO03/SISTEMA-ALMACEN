<?php
include 'conexion.php';
$id = $_POST['id'] ?? 0;
$estado = $_POST['estado'] ?? '';

if ($id == 0 || empty($estado)) {
    echo json_encode(['success' => false]);
    exit;
}

$sql = "UPDATE personal SET estado='$estado' WHERE id=$id";
if ($conexion->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
