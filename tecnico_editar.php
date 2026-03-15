<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');

if ($id <= 0 || strlen($nombre) < 2) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
  exit;
}

try {
  $check = $conexion->prepare("SELECT id FROM personal WHERE id = ?");
  $check->bind_param('i', $id);
  $check->execute();
  $res = $check->get_result();
  if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'El técnico no existe.']);
    exit;
  }

  $stmt = $conexion->prepare("UPDATE personal SET nombre = ? WHERE id = ?");
  $stmt->bind_param('si', $nombre, $id);

  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Técnico actualizado correctamente.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el técnico.']);
  }

  $stmt->close();
  $conexion->close();

} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
