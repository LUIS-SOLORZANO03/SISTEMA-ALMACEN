<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexion.php';

// Seguridad: verificar si hay usuario logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT r.id, c.nombre AS categoria, r.dni, t.nombre AS tecnico, 
               r.turno, r.cuadrilla, r.producto, r.cantidad, r.observacion, 
               r.fecha_registro, r.cliente
        FROM tecnicos_registros r
        LEFT JOIN categorias c ON r.id_categoria = c.id
        LEFT JOIN tecnicos t ON r.dni = t.dni
        ORDER BY r.fecha_registro DESC";
$registros = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>📜 Historial General | Data Online Perú SAC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        header {
            background: linear-gradient(90deg, #0062E6, #33AEFF);
            padding: 20px;
            text-align: center;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        header img {
            height: 90px;
        }

        header h2 {
            margin-top: 10px;
            font-weight: bold;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: #ffffffdd;
        }

        table.dataTable thead th {
            background: #0062E6;
            color: white;
            text-align: center;
        }

        table.dataTable tfoot th {
            background: #e9ecef;
            font-weight: bold;
        }

        .btn-volver {
            background: #6c757d;
            color: #fff;
            border-radius: 8px;
        }

        .btn-volver:hover {
            background: #5a6268;
        }
    </style>
</head>

<body>

    <header>
        <img src="logo.png" alt="Logo">
        <h2>DATA ONLINE PERÚ SAC</h2>
        <p>📜 Historial General de Reportes</p>
    </header>

    <div class="container my-5">
        <div class="card p-4">
            <table id="tablaHistorial" class="table table-striped table-hover table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Técnico</th>
                        <th>DNI</th>
                        <th>Turno</th>
                        <th>Cuadrilla</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $registros->fetch_assoc()): ?>
                        <tr>
                            <td><?= date("d/m/Y H:i", strtotime($r['fecha_registro'])) ?></td>
                            <td><?= htmlspecialchars($r['categoria']) ?></td>
                            <td><?= htmlspecialchars($r['tecnico']) ?></td>
                            <td><?= htmlspecialchars($r['dni']) ?></td>
                            <td><?= htmlspecialchars($r['turno']) ?></td>
                            <td><?= htmlspecialchars($r['cuadrilla']) ?></td>
                            <td><?= htmlspecialchars($r['cliente']) ?></td>
                            <td><?= htmlspecialchars($r['producto']) ?></td>
                            <td><?= (int)$r['cantidad'] ?></td>
                            <td title="<?= htmlspecialchars($r['observacion']) ?>">
                                <?= htmlspecialchars($r['observacion']) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-end">TOTAL GENERAL:</th>
                        <th id="totalCantidad"></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#tablaHistorial').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excel',
                        text: '📊 Excel'
                    },
                    {
                        extend: 'pdf',
                        text: '📄 PDF',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();
                    let total = api
                        .column(8, { page: 'current' })
                        .data()
                        .reduce((a, b) => a + parseInt(b || 0), 0);
                    $('#totalCantidad').html(total);
                }
            });
        });
    </script>

</body>
</html>
