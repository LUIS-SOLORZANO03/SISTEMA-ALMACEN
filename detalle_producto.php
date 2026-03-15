<?php
session_start();
include 'conexion.php';

// --- Verificar sesión ---
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// --- Validar ID producto ---
$id = $_GET['id'] ?? 0;
if (!$id) {
    die("<div class='alert alert-danger'>ID de producto no válido.</div>");
}

// --- Datos del producto ---
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
if (!$producto) {
    die("<div class='alert alert-danger'>Producto no encontrado.</div>");
}

$alertJs = "";

// --- Procesar ENTRADA ---
if (isset($_POST['registrar_entrada'])) {
    $cantidad = intval($_POST['cantidad_entrada']);
    $motivoEntrada = $_POST['motivo_entrada'] ?? '';
    $fechaEntrada = $_POST['fecha_entrada'] ?: date('Y-m-d');

    if ($cantidad > 0 && $motivoEntrada !== '') {
        $stmt = $conexion->prepare("INSERT INTO entradas_productos (id_producto, fecha, cantidad, motivo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $id, $fechaEntrada, $cantidad, $motivoEntrada);
        $stmt->execute();
        $alertJs = "Swal.fire({icon:'success', title:'Entrada registrada', confirmButtonText:'OK'}).then(()=>{location='detalle_producto.php?id=$id';});";
    }
}

// --- Procesar SALIDA ---
if (isset($_POST['registrar_salida'])) {
    $cantidad = intval($_POST['cantidad_salida']);
    $motivoSalida = $_POST['motivo_salida'] ?? '';
    $id_tecnico = intval($_POST['id_tecnico']);
    $fechaSalida = $_POST['fecha_salida'] ?: date('Y-m-d');

    if ($cantidad > 0 && $motivoSalida !== '' && $id_tecnico > 0) {
        $stmt = $conexion->prepare("INSERT INTO salidas_productos (id_producto, fecha, motivo, cantidad, personal_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $id, $fechaSalida, $motivoSalida, $cantidad, $id_tecnico);
        $stmt->execute();
        $alertJs = "Swal.fire({icon:'success', title:'Salida registrada', confirmButtonText:'OK'}).then(()=>{location='detalle_producto.php?id=$id';});";
    }
}

// --- Eliminar registros ---
if (isset($_GET['eliminar_entrada'])) {
    $conexion->query("DELETE FROM entradas_productos WHERE id=" . intval($_GET['eliminar_entrada']));
    header("Location: detalle_producto.php?id=$id");
    exit();
}
if (isset($_GET['eliminar_salida'])) {
    $conexion->query("DELETE FROM salidas_productos WHERE id=" . intval($_GET['eliminar_salida']));
    header("Location: detalle_producto.php?id=$id");
    exit();
}

// --- Consultas para mostrar ---
$entradas = $conexion->query("SELECT * FROM entradas_productos WHERE id_producto=$id ORDER BY fecha DESC");
$salidas = $conexion->query("
    SELECT s.*, p.nombre AS tecnico
    FROM salidas_productos s
    LEFT JOIN personal p ON s.personal_id=p.id
    WHERE s.id_producto=$id
    ORDER BY fecha DESC
");

$stockEntradas = $conexion->query("SELECT COALESCE(SUM(cantidad),0) AS total FROM entradas_productos WHERE id_producto=$id")->fetch_assoc()['total'];
$stockSalidas = $conexion->query("SELECT COALESCE(SUM(cantidad),0) AS total FROM salidas_productos WHERE id_producto=$id")->fetch_assoc()['total'];
$disponible = $stockEntradas - $stockSalidas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(120deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
            animation: gradient 12s ease infinite;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
        }
        @keyframes gradient {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }
        .card { border-radius: 15px; box-shadow: 0 6px 20px rgba(0,0,0,.25); }
        .btn-custom { border-radius: 12px; transition: .3s; font-weight: bold; }
        .btn-custom:hover { transform: scale(1.05); }
        .stock-card { padding: 18px; border-radius: 15px; text-align: center; }
        .table th { background: #212529; color: white; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📦 <?= htmlspecialchars($producto['nombre']) ?></h2>
        <a href="almacen.php" class="btn btn-outline-light btn-custom">⬅ Volver</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4>Descripción</h4>
            <p><?= htmlspecialchars($producto['descripcion'] ?? 'Sin descripción') ?></p>
            <p><strong>Unidad de medida:</strong> <?= htmlspecialchars($producto['unidad_medida']) ?></p>

            <div class="row text-center mt-4">
                <div class="col-md-4">
                    <div class="stock-card bg-success text-white">
                        <h3><?= $stockEntradas ?></h3><p>Entradas</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stock-card bg-danger text-white">
                        <h3><?= $stockSalidas ?></h3><p>Salidas</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stock-card bg-warning text-dark">
                        <h3><?= $disponible ?></h3><p>Disponible</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formularios -->
    <div class="row">
        <!-- Entrada -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="text-success fw-bold">➕ Registrar Entrada</h4>
                    <form method="POST">
                        <select name="motivo_entrada" class="form-select mb-2" required>
                            <option value="">-- Motivo --</option>
                            <option value="Almacen">Almacén</option>
                        </select>
                        <input type="number" name="cantidad_entrada" min="1" class="form-control mb-2" placeholder="Cantidad" required>
                        <input type="date" name="fecha_entrada" class="form-control mb-2" value="<?= date('Y-m-d') ?>">
                        <button type="submit" name="registrar_entrada" class="btn btn-success w-100 btn-custom">Guardar Entrada</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Salida -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="text-danger fw-bold">➖ Registrar Salida</h4>
                    <form method="POST">
                        <select name="motivo_salida" class="form-select mb-2" required>
                            <option value="">-- Motivo --</option>
                            <option value="Planta Externa">Planta Externa</option>
                            <option value="Campo">Campo</option>
                            <option value="Oficina">Oficina</option>
                        </select>
                        <input type="number" name="cantidad_salida" min="1" class="form-control mb-2" placeholder="Cantidad" required>
                        <select name="id_tecnico" class="form-select mb-2" required>
                            <option value="">-- Técnico activo --</option>
                            <?php
                            $sql = "SELECT * FROM personal WHERE estado='Activo'";
                            $result = $conexion->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                        <input type="date" name="fecha_salida" class="form-control mb-2" value="<?= date('Y-m-d') ?>">
                        <button type="submit" name="registrar_salida" class="btn btn-danger w-100 btn-custom">Guardar Salida</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Entradas -->
    <h4 class="mt-4">📥 Historial de Entradas</h4>
    <div class="card mb-4">
        <div class="card-body p-0">
            <table class="table table-hover text-center align-middle m-0">
                <thead class="table-success"><tr><th>Fecha</th><th>Cantidad</th><th>Motivo</th><th>Acción</th></tr></thead>
                <tbody>
                    <?php while ($row = $entradas->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                            <td><?= $row['cantidad'] ?></td>
                            <td><?= htmlspecialchars($row['motivo']) ?></td>
                            <td><button class="btn btn-sm btn-outline-danger eliminar-btn" data-url="detalle_producto.php?id=<?= $id ?>&eliminar_entrada=<?= $row['id'] ?>">🗑️ Eliminar</button></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Salidas -->
    <h4 class="mt-4">📤 Historial de Salidas</h4>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover text-center align-middle m-0">
                <thead class="table-danger"><tr><th>Fecha</th><th>Cantidad</th><th>Motivo</th><th>Técnico</th><th>Acción</th></tr></thead>
                <tbody>
                    <?php while ($row = $salidas->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                            <td><?= $row['cantidad'] ?></td>
                            <td><?= htmlspecialchars($row['motivo']) ?></td>
                            <td><?= htmlspecialchars($row['tecnico'] ?? 'N/A') ?></td>
                            <td><button class="btn btn-sm btn-outline-danger eliminar-btn" data-url="detalle_producto.php?id=<?= $id ?>&eliminar_salida=<?= $row['id'] ?>">🗑️ Eliminar</button></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.eliminar-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const url = btn.getAttribute('data-url');
        Swal.fire({
            title:'¿Eliminar registro?',
            text:'Esta acción no se puede deshacer',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Sí, eliminar',
            cancelButtonText:'Cancelar',
            confirmButtonColor:'#d33'
        }).then(r=>{
            if(r.isConfirmed) location.href = url;
        });
    });
});
</script>

<?php if ($alertJs): ?>
<script><?= $alertJs ?></script>
<?php endif; ?>
</body>
</html>
