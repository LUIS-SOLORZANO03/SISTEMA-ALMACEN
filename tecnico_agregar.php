<?php
// tecnico_agregar.php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$nombre = trim($_POST['nombre'] ?? '');
if (strlen($nombre) < 2) {
    echo json_encode(['success'=>false,'message'=>'Nombre inválido']); exit;
}

try {
    $stmt = $conexion->prepare("INSERT INTO personal (nombre, estado) VALUES (?, 'Activo')");
    $stmt->bind_param("s", $nombre);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success'=>true,'message'=>'Personal agregado']);
    else echo json_encode(['success'=>false,'message'=>'No se pudo insertar']);
} catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
