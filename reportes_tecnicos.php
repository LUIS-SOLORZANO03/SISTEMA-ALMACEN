<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// --- Eliminar (marcar como eliminado sin borrar del historial) ---
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $conexion->query("UPDATE tecnicos_registros SET estado = 'eliminado' WHERE id = $id");
    header("Location: reportes_tecnicos.php");
    exit();
}

// --- Registros activos ---
$sql = "SELECT r.id, c.nombre AS categoria, r.cliente, r.dni, t.nombre AS tecnico, 
               r.turno, r.cuadrilla, r.producto, r.modelo, r.serie,
               r.cantidad, r.observacion, r.fecha_registro
        FROM tecnicos_registros r
        INNER JOIN categorias c ON r.id_categoria = c.id
        INNER JOIN tecnicos t ON r.dni = t.dni
        WHERE r.estado != 'eliminado'
        ORDER BY r.fecha_registro DESC";
$registros = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>📑 Reportes Técnicos | Data Online Perú SAC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: radial-gradient(circle at 20% 20%, #1e3c72, #2a5298, #ff6a00, #ffcc00);
            background-size: 400% 400%;
            animation: auroraBG 18s ease infinite;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
        }

        @keyframes auroraBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        header {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            padding: 30px;
            text-align: center;
            border-bottom: 5px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            border-radius: 0 0 40px 40px;
            animation: fadeIn 1.2s ease;
        }

        header img {
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.8);
            transition: transform 0.5s ease;
        }

        header img:hover {
            transform: scale(1.1) rotate(-5deg);
        }

        header h2 {
            margin-top: 15px;
            font-weight: 700;
            text-shadow: 0 0 15px rgba(0, 229, 255, 0.9);
        }

        header p {
            font-size: 18px;
            font-weight: 500;
        }

        .card {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(14px);
            padding: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 1s ease;
        }

        table.dataTable thead th {
            background: linear-gradient(90deg, #ff6a00, #ffcc00);
            color: white;
            text-align: center;
            font-weight: 700;
        }

        table.dataTable tbody tr {
            transition: all 0.4s ease;
        }

        table.dataTable tbody tr:hover {
            background: rgba(255, 255, 255, 0.12) !important;
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-modern {
            border-radius: 14px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
        }

        .btn-modern:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            border: none;
            color: #fff;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffcc00, #ff8800);
            border: none;
            color: #000;
            font-weight: bold;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #56ab2f, #a8e063);
            border: none;
        }

        .badge-turno {
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        .turno-m {
            background: #f1c40f;
        }

        .turno-t {
            background: #00bfff;
        }

        .turno-n {
            background: #8e44ad;
        }

        .volver-btn {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: 0.3s;
        }

        .volver-btn:hover {
            background: #ff6a00;
            color: #fff;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <header>
        <img src="logo.png" alt="Logo">
        <h2>DATA ONLINE PERÚ SAC</h2>
        <p class="fw-bold">📑 Panel de Reportes Técnicos</p>
    </header>

    <div class="container my-4">
        <div class="d-flex justify-content-between mb-3 flex-wrap">
            <h4 class="fw-bold">📋 Reportes Registrados</h4>
            <button class="btn btn-info btn-modern mb-2" onclick="verHistorial()">📜 Ver Historial General</button>
        </div>

        <div class="card mt-4">
            <div class="table-responsive">
                <table id="tablaReportes" class="table table-bordered table-hover text-center align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Cliente</th>
                            <th>Técnico</th>
                            <th>DNI</th>
                            <th>Turno</th>
                            <th>Cuadrilla</th>
                            <th>Producto</th>
                            <th>Modelo</th>
                            <th>Serie</th>
                            <th>Cantidad</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $registros->fetch_assoc()):
                            $claseTurno = $r['turno'] == "Mañana" ? "turno-m" : ($r['turno'] == "Tarde" ? "turno-t" : "turno-n");
                        ?>
                            <tr>
                                <td><?= date("d/m/Y H:i", strtotime($r['fecha_registro'])) ?></td>
                                <td><?= htmlspecialchars($r['categoria']) ?></td>
                                <td><?= htmlspecialchars($r['cliente']) ?></td>
                                <td><?= htmlspecialchars($r['tecnico']) ?></td>
                                <td><?= htmlspecialchars($r['dni']) ?></td>
                                <td><span class="badge-turno <?= $claseTurno ?>"><?= $r['turno'] ?></span></td>
                                <td><?= $r['cuadrilla'] ?></td>
                                <td><?= htmlspecialchars($r['producto']) ?></td>
                                <td><?= htmlspecialchars($r['modelo'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['serie'] ?? '') ?></td>
                                <td><?= $r['cantidad'] ?></td>
                                <td><?= htmlspecialchars($r['observacion']) ?></td>
                                <td>
                                    <a href="editar_reporte.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm mb-1">✏ Editar</a>
                                    <button class="btn btn-danger btn-sm" onclick="eliminarRegistro(<?= $r['id'] ?>)">🗑 Eliminar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="panel_reporte.php" class="volver-btn shadow-sm">⬅ Volver</a>
        </div>
    </div>

    <!-- Scripts -->
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
            $('#tablaReportes').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excel',
                        text: '📊 Excel',
                        className: 'btn btn-success btn-modern'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '📄 PDF',
                        className: 'btn btn-danger btn-modern',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function(doc) {
                            doc.styles.tableHeader.fillColor = '#ff6a00';
                            doc.styles.tableHeader.color = 'white';
                            doc.styles.tableHeader.alignment = 'center';
                            doc.content.splice(0, 0, {
                                text: '📑 Reportes Técnicos - Data Online Perú SAC',
                                style: 'header',
                                alignment: 'center',
                                margin: [0, 0, 0, 20]
                            });
                            doc.styles.header = {
                                fontSize: 14,
                                bold: true
                            };
                        }
                    }
                ],
                paging: true,
                info: false
            });
        });

        function eliminarRegistro(id) {
            Swal.fire({
                title: '¿Eliminar reporte?',
                text: "El reporte no se borrará del historial",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "reportes_tecnicos.php?eliminar=" + id;
                }
            });
        }

        function verHistorial() {
            Swal.fire({
                title: '📜 Historial General',
                width: '90%',
                html: '<iframe src="historial_general.php" style="width:100%;height:500px;border:none;border-radius:8px;"></iframe>',
                showCloseButton: true,
                showConfirmButton: false
            });
        }
    </script>
</body>

</html>