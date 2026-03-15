<?php
include 'conexion.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=productos_filtrados.xls");
header("Pragma: no-cache");
header("Expires: 0");

$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

$consulta = "SELECT 
                p.nombre,
                p.unidad_medida,
                p.precio,
                IFNULL(SUM(ep.cantidad), 0) AS stock
             FROM productos p
             LEFT JOIN entradas_productos ep ON p.id = ep.id_producto";

if ($categoria > 0) {
    $consulta .= " WHERE p.id_categoria = $categoria";
}

$consulta .= " GROUP BY p.id";

$result = $conexion->query($consulta);
?>

<table border="1">
    <tr>
        <th>Nombre</th>
        <th>Unidad de Medida</th>
        <th>Precio</th>
        <th>Stock</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['nombre'] ?></td>
            <td><?= $row['unidad_medida'] ?></td>
            <td><?= $row['precio'] ?></td>
            <td><?= $row['stock'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>
