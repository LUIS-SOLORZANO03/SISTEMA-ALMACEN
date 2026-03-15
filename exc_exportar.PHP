<?php
include 'conexion.php';

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Configuración de cabeceras para Excel
$fecha_actual = date("d-m-Y");
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_{$fecha_actual}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Encabezados
echo "<h1>MATERIALES EN STOCK</h1>";
echo "<h2>MATERIALES DATA ONLINE PERU SAC</h2>";
echo "<h3>Fecha de exportacion: {$fecha_actual}</h3>";

// Tabla
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Categoria</th>
        <th>Codigo</th>
        <th>Materiales</th>
        <th>Stock</th>
        <th>Unidad de Medida</th>
        <th>Salieron</th>
        <th>Fecha de Ingreso</th>
        <th>Fecha de Salida</th>
    </tr>";

$sql = "
SELECT 
    p.id,
    c.nombre AS categoria,
    p.codigo,
    p.nombre AS materiales,
    COALESCE(SUM(e.cantidad), 0) AS stock,
    p.unidad_medida,
    COALESCE(SUM(s.cantidad), 0) AS salieron,
    MIN(e.fecha) AS fecha_ingreso,
    MAX(s.fecha) AS fecha_salida
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN entradas_productos e ON p.id = e.id_producto
LEFT JOIN salidas_productos s ON p.id = s.id_producto
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
    echo "<td>{$fila['categoria']}</td>";
    echo "<td>{$fila['codigo']}</td>";
    echo "<td>{$fila['materiales']}</td>";
    echo "<td>{$fila['stock']}</td>";
    echo "<td>{$fila['unidad_medida']}</td>";
    echo "<td>{$fila['salieron']}</td>";
    echo "<td>{$fila['fecha_ingreso']}</td>";
    echo "<td>{$fila['fecha_salida']}</td>";
    echo "</tr>";
}

echo "</table>";
?>
