<?php
// tecnico_update.php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$estado = ($_POST['estado'] ?? 'Activo') === 'Activo' ? 'Activo' : 'Inactivo';

if ($id <= 0 || strlen($nombre) < 2) {
    echo json_encode(['success'=>false,'message'=>'Datos inválidos']); exit;
}

try {
    $stmt = $conexion->prepare("UPDATE personal SET nombre = ?, estado = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $estado, $id);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success'=>true,'message'=>'Actualizado']);
    else echo json_encode(['success'=>false,'message'=>'No se pudo actualizar']);
} catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
