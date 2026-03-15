<?php
session_start();
include 'conexion.php';

// 🔐 Verificamos login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// 📅 Filtro por fechas
$where = "";
if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $where = "WHERE c.fecha_comparacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
}

$query = "
    SELECT c.id, p.nombre, c.inventario_fisico, c.diferencia, c.fecha_comparacion
    FROM comparativa_inventario c
    JOIN productos p ON c.producto_id = p.id
    $where
    ORDER BY c.fecha_comparacion DESC
";
$resultado = $conexion->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Comparaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 20px; box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
        .table thead th { background: linear-gradient(90deg, #198754, #28a745); color: white; text-align: center; }
        td, th { text-align: center; vertical-align: middle; }
        .table-hover tbody tr:hover { background-color: #eafaf1; transition: 0.3s; }
        .btn { border-radius: 12px; transition: all 0.3s ease; }
        .btn:hover { transform: scale(1.05); }
        .diferencia-positiva { color: green; font-weight: bold; }
        .diferencia-negativa { color: red; font-weight: bold; }
        .diferencia-cero { color: gray; font-weight: bold; }
        .logo { height: 60px; }
        .titulo { font-size: 1.8rem; font-weight: 700; color: #198754; }
        .filtro-box { background: #ffffff; border-radius: 15px; padding: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); animation: fadeIn 0.8s ease; }
        .fade-in { animation: fadeIn 1s ease-in-out; }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Impresión */
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card { box-shadow: none; border: none; }
            table { font-size: 12px; }
            .logo { height: 50px; }
        }
    </style>
</head>
<body>
<div class="container mt-4 fade-in">

    <!-- ENCABEZADO -->
    <div class="text-center mb-4">
        <img src="logo.png" alt="Logo" class="logo mb-2">
        <h2 class="titulo">📑 Historial de Comparaciones</h2>
    </div>

    <!-- FILTRO DE FECHAS -->
    <div class="filtro-box mb-4 no-print">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label fw-semibold">Desde</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control shadow-sm" value="<?= $_GET['fecha_inicio'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label fw-semibold">Hasta</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control shadow-sm" value="<?= $_GET['fecha_fin'] ?? '' ?>">
            </div>
            <div class="col-md-4 text-end">
                <button type="submit" class="btn btn-success shadow-sm">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="historial.php" class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- CARD PRINCIPAL -->
    <div class="card p-4">

        <!-- BOTONES -->
        <div class="mb-3 text-end no-print">
            <button id="btnImprimir" class="btn btn-primary shadow-sm">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <a href="diferencias.php" class="btn btn-secondary shadow-sm">
                <i class="bi bi-arrow-left-circle"></i> Volver
            </a>
        </div>

        <!-- TABLA -->
        <div class="table-responsive">
            <table class="table table-striped table-hover shadow-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Stock Físico</th>
                        <th>Diferencia</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= $row['inventario_fisico'] ?></td>
                            <td class="<?php 
                                if ($row['diferencia'] > 0) echo 'diferencia-positiva';
                                elseif ($row['diferencia'] < 0) echo 'diferencia-negativa';
                                else echo 'diferencia-cero';
                            ?>">
                                <?= $row['diferencia'] ?>
                            </td>
                            <td><?= date("d/m/Y H:i", strtotime($row['fecha_comparacion'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted">⚠️ No se encontraron registros en este rango.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// 🎨 Imprimir con confirmación elegante
document.getElementById("btnImprimir").addEventListener("click", () => {
    Swal.fire({
        title: "¿Quieres imprimir el historial?",
        text: "Se abrirá la vista de impresión.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, imprimir"
    }).then((result) => {
        if (result.isConfirmed) {
            window.print();
        }
    });
});

// ✅ Notificación si hay filtro aplicado
<?php if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])): ?>
Swal.fire({
    icon: 'info',
    title: 'Filtro aplicado',
    text: 'Mostrando resultados desde <?= $fecha_inicio ?> hasta <?= $fecha_fin ?>',
    confirmButtonColor: '#198754'
});
<?php endif; ?>
</script>
</body>
</html>
