<?php
// tecnico_delete.php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID invalido']); exit; }

try {
    $stmt = $conexion->prepare("DELETE FROM personal WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success'=>true,'message'=>'Eliminado']);
    else echo json_encode(['success'=>false,'message'=>'No se pudo eliminar']);
} catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
