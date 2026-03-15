<?php
session_start();
include 'conexion.php';

// Buscar
$busqueda = isset($_GET['busqueda']) ? $conexion->real_escape_string($_GET['busqueda']) : "";

// Consulta (se agregó r.observacion)
$sql = "SELECT r.id, c.nombre AS cliente, r.rotulo, r.foto_caja, r.codigo_caja, r.fecha_registro, r.observacion
        FROM rotulos r
        INNER JOIN clientes c ON r.cliente_id = c.id";

if (!empty($busqueda)) {
    $sql .= " WHERE c.nombre LIKE '%$busqueda%' 
              OR r.rotulo LIKE '%$busqueda%' 
              OR r.codigo_caja LIKE '%$busqueda%'";
}

$sql .= " ORDER BY r.fecha_registro DESC";

$result = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de Rótulos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f1f3f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            padding: 1rem 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header img {
            height: 50px;
        }

        header h2 {
            color: #fff;
            font-weight: bold;
            margin: 0;
        }

        .card-custom {
            border: none;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease-in-out;
        }

        .card-custom:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .img-rotulo {
            max-height: 160px;
            object-fit: cover;
            border-radius: 0.8rem;
            cursor: pointer;
            border: 3px solid #f1f3f6;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .img-rotulo:hover {
            transform: scale(1.07);
            border-color: #0d6efd;
        }

        .search-box {
            max-width: 500px;
        }

        .btn-clean {
            margin-left: 8px;
        }

        .modal-content {
            border-radius: 1rem;
        }

        .modal-img {
            max-width: 100%;
            border-radius: 1rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border: none;
            color: white;
            transition: opacity 0.2s ease;
        }

        .btn-gradient:hover {
            opacity: 0.9;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Encabezado con logo -->
    <header class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <img src="logo.png" alt="Logo">
            <h2 class="ms-3"><i class="bi bi-archive"></i> Historial de Rótulos</h2>
        </div>
        <a href="rotulos.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
    </header>

    <div class="container py-5">
        <!-- Buscador -->
        <form method="get" class="d-flex mb-5 search-box mx-auto">
            <input type="text" name="busqueda" class="form-control shadow-sm" placeholder="Buscar por cliente, rótulo o código..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit" class="btn btn-gradient ms-2"><i class="bi bi-search"></i></button>
            <a href="historial_rotulos.php" class="btn btn-outline-secondary btn-clean"><i class="bi bi-x-circle"></i></a>
        </form>

        <!-- Grid de tarjetas -->
        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-custom h-100">
                            <div class="p-3 text-center">
                                <img src="<?php echo htmlspecialchars($row['foto_caja']); ?>"
                                    alt="Rótulo"
                                    class="img-fluid img-rotulo"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalImagen<?php echo $row['id']; ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo htmlspecialchars($row['rotulo']); ?></h5>
                                <p class="card-text mb-1"><strong>Cliente:</strong> <?php echo htmlspecialchars($row['cliente']); ?></p>
                                <p class="card-text mb-1"><strong>Código Caja:</strong> <?php echo htmlspecialchars($row['codigo_caja']); ?></p>
                                <p class="card-text mb-1"><strong>Observación:</strong> 
                                    <?php echo htmlspecialchars($row['observacion']); ?>
                                </p>
                                <p class="card-text text-muted"><i class="bi bi-calendar-event"></i> <?php echo date("d/m/Y", strtotime($row['fecha_registro'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de imagen -->
                    <div class="modal fade" id="modalImagen<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content p-3">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title"><?php echo htmlspecialchars($row['rotulo']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="<?php echo htmlspecialchars($row['foto_caja']); ?>" class="modal-img">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center shadow-sm">
                        <i class="bi bi-info-circle"></i> No se encontraron rótulos en el historial.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
