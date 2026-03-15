<?php
include 'conexion.php';

// Obtener categorías para el <select>
$categorias = [];
$result_cat = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
while ($row = $result_cat->fetch_assoc()) {
    $categorias[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Filtro de Productos por Categoría</title>
</head>
<body>
    <h2>Filtrar productos por categoría</h2>

    <form method="GET" action="filtro_productos.php">
        <select name="categoria">
            <option value="">-- Todas las categorías --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= isset($_GET['categoria']) && $_GET['categoria'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= $cat['nombre'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrar</button>
        <a href="exportar_excel.php?categoria=<?= isset($_GET['categoria']) ? $_GET['categoria'] : '' ?>" target="_blank">Exportar a Excel</a>
    </form>

    <br>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Nombre</th>
            <th>Unidad de Medida</th>
            <th>Precio</th>
            <th>Stock</th>
        </tr>

        <?php
        if (isset($_GET['categoria']) && $_GET['categoria'] != '') {
            $categoria = intval($_GET['categoria']);
            $consulta = "SELECT 
                            p.nombre,
                            p.unidad_medida,
                            p.precio,
                            IFNULL(SUM(ep.cantidad), 0) AS stock
                         FROM productos p
                         LEFT JOIN entradas_productos ep ON p.id = ep.id_producto
                         WHERE p.id_categoria = $categoria
                         GROUP BY p.id";

            $result = $conexion->query($consulta);

            while ($fila = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$fila['nombre']}</td>";
                echo "<td>{$fila['unidad_medida']}</td>";
                echo "<td>{$fila['precio']}</td>";
                echo "<td>{$fila['stock']}</td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
</body>
</html>
