<?php
include 'conexion.php';

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Cabeceras para Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte.xls");
header("Pragma: no-cache");
header("Expires: 0");

$fecha_actual = date("d-m-Y");
header("Content-Disposition: attachment; filename=reporte_{$fecha_actual}.xls");

echo "<h1>MATERIALES EN STOCK</h1>";
echo "<h2>MATERIALES DATA ONLINE PERU SAC</h2>";
echo "<h3>Fecha de exportacion: {$fecha_actual}</h3>";
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Codigo</th>
        <th>Nombre</th>
        <th>Categoria</th>
        <th>Unidad de Medida</th>
        <th>Stock</th>
    </tr>";

$sql = "
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    c.nombre AS categoria,
    p.unidad_medida,
    COALESCE(SUM(e.cantidad), 0) AS stock
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN entradas_productos e ON p.id = e.id_producto
WHERE c.nombre LIKE ?
GROUP BY p.id
ORDER BY p.id ASC
";

$stmt = $conexion->prepare($sql);
$param = "%" . $categoria . "%";
$stmt->bind_param("s", $param);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$fila['id']}</td>";
    echo "<td>{$fila['codigo']}</td>";
    echo "<td>{$fila['nombre']}</td>";
    echo "<td>{$fila['categoria']}</td>";
    echo "<td>{$fila['unidad_medida']}</td>";
    echo "<td>{$fila['stock']}</td>";
    echo "</tr>";
}

echo "</table>";
?>
