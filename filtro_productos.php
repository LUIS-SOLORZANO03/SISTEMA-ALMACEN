<?php
session_start();
include 'conexion.php';

$rol = $_SESSION['rol'] ?? 'usuario';
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

// Consulta
$sql = "
SELECT 
    p.id AS id_producto,
    c.nombre AS categoria,
    p.codigo,
    p.nombre AS materiales,
    COALESCE(e.total_entradas, 0) AS entradas,
    COALESCE(s.total_salidas, 0) AS salieron,
    (COALESCE(e.total_entradas, 0) - COALESCE(s.total_salidas, 0)) AS stock,
    p.unidad_medida,
    p.precio_unitario,
    (p.precio_unitario * (COALESCE(e.total_entradas, 0) - COALESCE(s.total_salidas, 0))) AS precio_total
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN (
    SELECT id_producto, SUM(cantidad) AS total_entradas FROM entradas_productos GROUP BY id_producto
) e ON e.id_producto = p.id
LEFT JOIN (
    SELECT id_producto, SUM(cantidad) AS total_salidas FROM salidas_productos GROUP BY id_producto
) s ON s.id_producto = p.id
WHERE p.estado = 'activo'
";

if ($categoria > 0) {
    $sql .= " AND c.id = {$categoria}";
}

$resultado = $conexion->query($sql);

if (!$resultado) {
    echo "<div class='alert alert-danger'>Error en la consulta SQL: " . $conexion->error . "</div>";
    exit;
}
?>

<style>
    /* Estilos modernos tabla */
    table.custom-table {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        animation: fadeIn 0.6s ease;
    }
    table.custom-table thead {
        background: linear-gradient(90deg, #4e54c8, #8f94fb);
        color: #fff;
    }
    table.custom-table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        position: sticky;
        top: 0;
        z-index: 5;
    }
    table.custom-table tbody tr {
        transition: all 0.25s ease;
    }
    table.custom-table tbody tr:hover {
        background: rgba(78,84,200,0.08);
        transform: scale(1.01);
    }
    .badge-custom {
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .btn-action {
        border: none;
        border-radius: 10px;
        padding: 6px 10px;
        font-size: 0.9rem;
        margin: 2px;
        transition: all 0.3s ease;
    }
    .btn-action:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    @keyframes fadeIn {
        from {opacity:0; transform:translateY(10px);}
        to {opacity:1; transform:none;}
    }
</style>

<!-- Tabla -->
<table class="table custom-table table-hover align-middle text-center bg-white">
    <thead>
        <tr>
            <th>ID</th>
            <th>Categoría</th>
            <th>Código</th>
            <th>Materiales</th>
            <th>Stock</th>
            <th>Unidad</th>
            <th>Salieron</th>
            <th>Precio Unit.</th>
            <th>Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <?php
                $stock = (int)$row['stock'];
                $salieron = (int)$row['salieron'];

                $stockClass = 'badge-custom bg-light text-dark';
                if ($stock <= 5) $stockClass = 'badge-custom bg-danger text-white';
                else if ($stock <= 15) $stockClass = 'badge-custom bg-warning text-dark';
                else $stockClass = 'badge-custom bg-success text-white';
                ?>
                <tr>
                    <td><?= $row['id_producto'] ?></td>
                    <td><span class="badge-custom bg-primary text-white"><?= htmlspecialchars($row['categoria']) ?></span></td>
                    <td><span class="badge-custom bg-dark text-white"><?= htmlspecialchars($row['codigo']) ?></span></td>
                    <td class="text-start"><?= htmlspecialchars($row['materiales']) ?></td>
                    <td><span class="<?= $stockClass ?>"><?= $stock ?></span></td>
                    <td><span class="badge-custom bg-info text-dark"><?= htmlspecialchars($row['unidad_medida']) ?></span></td>
                    <td><?= $salieron ?></td>
                    <td><span class="badge-custom bg-secondary text-white">S/. <?= number_format($row['precio_unitario'], 2) ?></span></td>
                    <td><strong>S/. <?= number_format($row['precio_total'], 2) ?></strong></td>
                    <td>
                        <a href="detalle_producto.php?id=<?= $row['id_producto'] ?>" class="btn-action btn btn-outline-primary" title="Ver detalle">🔍</a>

                        <?php if ($rol === 'almacenero' || $rol === 'admin'): ?>
                            <a href="editar_producto.php?id=<?= $row['id_producto'] ?>" class="btn-action btn btn-outline-warning" title="Editar">✏️</a>
                            <a href="eliminar_producto.php?id=<?= $row['id_producto'] ?>" class="btn-action btn btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar este producto?');">🗑️</a>
                            <a href="descontinuar_producto.php?id=<?= $row['id_producto'] ?>" class="btn-action btn btn-outline-secondary" title="Inactivar" onclick="return confirm('¿Descontinuar este producto?');">🚫</a>
                        <?php else: ?>
                            <span class="d-block small text-muted">🔒 Sin permisos</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center text-muted">🚫 No se encontraron productos activos.</td>
            </tr>
        <?php endif; ?>

    </tbody>
</table>
