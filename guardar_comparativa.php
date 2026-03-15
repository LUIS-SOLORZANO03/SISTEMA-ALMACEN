<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['comparativa'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos"]);
    exit();
}

$comparativa = $data['comparativa'];
$fecha = date("Y-m-d H:i:s");

$stmt = $conexion->prepare("
    INSERT INTO comparativa_inventario (producto_id, inventario_fisico, diferencia, fecha_comparacion)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        inventario_fisico = VALUES(inventario_fisico),
        diferencia = VALUES(diferencia),
        fecha_comparacion = VALUES(fecha_comparacion)
");

foreach ($comparativa as $item) {
    $producto_id = intval($item['id']);
    $inventario_fisico = intval($item['inventario_fisico']);

    $res = $conexion->query("
        SELECT IFNULL(SUM(e.cantidad),0) - IFNULL(SUM(s.cantidad),0) AS stock
        FROM productos p
        LEFT JOIN entradas_productos e ON p.id = e.id_producto
        LEFT JOIN salidas_productos s ON p.id = s.id_producto
        WHERE p.id = $producto_id
        GROUP BY p.id
    ");
    $stock_digital = ($row = $res->fetch_assoc()) ? intval($row['stock']) : 0;

    $diferencia = $inventario_fisico - $stock_digital;

    $stmt->bind_param("iiis", $producto_id, $inventario_fisico, $diferencia, $fecha);
    $stmt->execute();
}

$stmt->close();
$conexion->close();

echo json_encode(["success" => true]);
