<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("Cliente no especificado");
}

$id = intval($_GET['id']);

// Obtener cliente
$stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if (!$cliente) {
    die("Cliente no encontrado");
}

// Obtener materiales y técnico
$stmt_mat = $conexion->prepare("
    SELECT m.*, t.nombre AS tecnico_nombre, t.dni AS tecnico_dni
    FROM materiales m
    LEFT JOIN tecnicos t ON t.dni = m.tecnico_dni
    WHERE m.cliente_id = ?
");
$stmt_mat->bind_param("i", $id);
$stmt_mat->execute();
$result_mat = $stmt_mat->get_result();

$total = 0;
$rows = [];
$tecnicoAsignado = "No asignado";

while ($m = $result_mat->fetch_assoc()) {
    $subtotal = $m['cantidad'] * $m['precio'];
    $total += $subtotal;
    if (!isset($m['unidad'])) $m['unidad'] = '';
    if (!empty($m['tecnico_nombre'])) $tecnicoAsignado = $m['tecnico_nombre'];
    $rows[] = $m + ['subtotal' => $subtotal];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle Cliente - Data Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e0f7fa);
            font-family: 'Segoe UI', sans-serif;
            animation: fadeIn 0.8s ease-in-out;
            color: #333;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            border-radius: 18px;
            border: none;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: scale(1.01);
        }

        h2,
        h4 {
            font-weight: 700;
        }

        .logo {
            max-height: 70px;
            margin-bottom: 10px;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: #00796b;
            color: white;
            font-weight: 600;
        }

        .table tfoot {
            background: #ffe082;
            font-weight: bold;
        }

        .firma {
            margin-top: 25px;
            text-align: right;
            font-style: italic;
            color: #555;
        }

        /* Botones principales */
        .acciones .btn {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease-in-out;
        }

        .acciones .btn:hover {
            transform: translateY(-2px);
        }

        /* Print */
        @media print {
            .acciones,
            .btn-acciones {
                display: none !important;
            }

            body {
                background: white !important;
                font-size: 11px;
            }

            .print-area {
                width: 100%;
                padding: 5mm;
            }

            table {
                font-size: 11px;
            }

            th,
            td {
                padding: 3px !important;
            }
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    </style>
</head>

<body>
    <div class="print-area container py-4">

        <div class="text-center mb-4">
            <img src="logo.png" class="logo" alt="Logo">
            <h2 class="fw-bold text-primary"><i class="bi bi-file-person"></i> Detalle del Cliente</h2>
            <hr class="w-50 mx-auto border-2 border-primary">
        </div>

        <!-- DATOS DEL CLIENTE -->
        <div class="card p-4 mb-4">
            <h4 class="text-warning mb-3"><i class="bi bi-person-badge"></i> Datos del Cliente</h4>
            <div class="row">
                <div class="col-md-6 mb-2"><b>Nombre/Razón Social:</b> <?= htmlspecialchars($cliente['nombres']) ?></div>
                <div class="col-md-6 mb-2"><b>Documento:</b> <?= htmlspecialchars($cliente['tipo_doc']) ?> <?= htmlspecialchars($cliente['documento']) ?></div>
                <div class="col-md-6 mb-2"><b>Operación:</b> <?= htmlspecialchars($cliente['operacion']) ?></div>
                <div class="col-md-6 mb-2"><b>Celular:</b> <?= htmlspecialchars($cliente['celular']) ?></div>
                <div class="col-md-6 mb-2"><b>Plan:</b> <?= htmlspecialchars($cliente['plan']) ?> (<?= htmlspecialchars($cliente['tipo']) ?>)</div>
                <div class="col-md-6 mb-2"><b>Técnico:</b> <?= htmlspecialchars($tecnicoAsignado) ?></div>
                <div class="col-md-6 mb-2"><b>Fecha Registro:</b> <?= htmlspecialchars($cliente['fecha_registro']) ?></div>
            </div>
        </div>

        <!-- MATERIALES UTILIZADOS -->
        <div class="card p-4 mb-4">
            <h4 class="text-success mb-3"><i class="bi bi-tools"></i> Materiales Utilizados</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-hover">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                            <th>Técnico</th>
                            <th class="btn-acciones">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['material']) ?></td>
                                <td><?= htmlspecialchars($r['cantidad'] . ($r['unidad'] ? ' ' . $r['unidad'] : '')) ?></td>
                                <td>S/. <?= number_format($r['precio'], 2) ?></td>
                                <td>S/. <?= number_format($r['subtotal'], 2) ?></td>
                                <td><?= htmlspecialchars($r['tecnico_nombre'] ?? 'No asignado') ?></td>
                                <td class="text-center btn-acciones">
                                    <a href="editar_material.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="eliminar_material.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este material?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">TOTAL</th>
                            <th>S/. <?= number_format($total, 2) ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- NOTA -->
        <div class="card p-4 mb-4 bg-light">
            <h4 class="mb-3"><i class="bi bi-exclamation-triangle text-danger"></i> Nota</h4>
            <p class="mb-0">
                <b>
                    TODOS LOS MATERIALES PROPORCIONADOS AL CLIENTE SON EN CALIDAD DE PRÉSTAMO Y DEBERÁN
                    SER DEVUELTOS A DATAONLINE AL MOMENTO DE LA BAJA DEFINITIVA DEL SERVICIO. EN CASO DE QUE EL CLIENTE
                    SOLICITE LA CANCELACIÓN DEL MISMO, DATAONLINE PROCEDERÁ A RETIRAR DICHOS MATERIALES EN UN PLAZO MÁXIMO DE 3
                    (TRES) DÍAS HÁBILES. AGRADECEMOS AL CLIENTE SU COLABORACIÓN Y CONFORMIDAD CON ESTE PROCEDIMIENTO.
                </b>
            </p>
        </div>

        <div class="firma">
            <p>________________________</p>
            <p><b>CLIENTE</b></p>
        </div>

    </div>

    <!-- BOTONES -->
    <div class="acciones text-center mt-4">
        <button onclick="window.print()" class="btn btn-danger me-2">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="clientes.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</body>

</html>
