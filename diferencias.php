<?php
session_start();
include 'conexion.php';

// 🔐 Verificamos login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// 💾 Guardar comparación si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productos'])) {
    $stmt = $conexion->prepare("
        INSERT INTO comparativa_inventario 
        (producto_id, inventario_fisico, diferencia, fecha_comparacion)
        VALUES (?, ?, ?, NOW())
    ");
    foreach ($_POST['productos'] as $id => $prod) {
        if (isset($prod['seleccion'])) {
            $fisico = max(0, intval($prod['fisico']));
            $digital = intval($prod['digital']);
            $diferencia = $fisico - $digital;
            $stmt->bind_param("iii", $id, $fisico, $diferencia);
            $stmt->execute();
        }
    }
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: '✅ Comparación guardada',
            text: 'Las diferencias se registraron correctamente.',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd'
        }).then(() => window.location='diferencia.php');
    </script>";
    exit();
}

// 📦 Obtenemos productos con stock digital
$query = "
    SELECT p.id, p.nombre, 
           (IFNULL(SUM(e.cantidad),0) - IFNULL(SUM(s.cantidad),0)) AS stock_digital
    FROM productos p
    LEFT JOIN entradas_productos e ON p.id = e.id_producto
    LEFT JOIN salidas_productos s ON p.id = s.id_producto
    GROUP BY p.id, p.nombre
    ORDER BY p.nombre ASC
";
$resultado = $conexion->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comparación de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9f1fb);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            backdrop-filter: blur(8px);
            animation: fadeIn 1s ease;
        }
        h2 {
            font-weight: 800;
            background: linear-gradient(45deg,#0d6efd,#6610f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logo { max-height: 60px; }

        /* Tabla brutal */
        .table {
            border-collapse: separate;
            border-spacing: 0 12px;
        }
        .table thead th {
            background: linear-gradient(90deg, #0d6efd, #0dcaf0);
            color: #fff;
            border: none;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .table tbody tr {
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all .3s ease;
        }
        .table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .table td {
            vertical-align: middle;
            border-top: none;
        }

        /* Inputs */
        input[type=number] {
            border-radius: 12px;
            border: 2px solid #dee2e6;
            padding: 6px;
            width: 100px;
            text-align: center;
            transition: border-color .3s;
        }
        input[type=number]:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 6px rgba(13,110,253,0.3);
        }

        /* Diferencias con badges */
        .badge-diff {
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: 600;
            min-width: 70px;
            display: inline-block;
        }
        .positivo { background: #d1e7dd; color: #0f5132; }
        .negativo { background: #f8d7da; color: #842029; }
        .neutro { background: #e2e3e5; color: #41464b; }

        /* FAB */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(45deg,#0d6efd,#6610f2);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 75px;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
            transition: all .3s ease;
            animation: pulse 2s infinite;
            z-index: 1000;
        }
        .fab:hover { transform: scale(1.15) rotate(10deg); }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(13,110,253,0.7); }
            70% { box-shadow: 0 0 0 20px rgba(13,110,253,0); }
            100% { box-shadow: 0 0 0 0 rgba(13,110,253,0); }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="text-center mb-4">
        <img src="logo.png" alt="Logo" class="logo mb-2">
        <h2>📊 Comparación Digital vs Físico</h2>
    </div>

    <div class="card p-4">
        <!-- 🔍 Buscador -->
        <input type="text" id="buscador" class="form-control mb-3" placeholder="🔍 Buscar producto...">

        <form method="POST">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>✔</th>
                            <th>Producto</th>
                            <th>Stock Digital</th>
                            <th>Stock Físico</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input select-producto" name="productos[<?= $row['id'] ?>][seleccion]" value="1">
                            </td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td>
                                <input type="number" name="productos[<?= $row['id'] ?>][digital]" 
                                       value="<?= $row['stock_digital'] ?>" readonly 
                                       class="form-control-plaintext text-center digital">
                            </td>
                            <td>
                                <input type="number" name="productos[<?= $row['id'] ?>][fisico]" 
                                       value="0" min="0" 
                                       class="form-control fisico" disabled>
                            </td>
                            <td>
                                <span class="badge-diff neutro diferencia">0</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <a href="historial.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-journal-text"></i> Ver Historial
                </a>
                <a href="panel_almacen.php" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left-circle"></i> Volver
                </a>
            </div>

            <!-- 🟢 Botón flotante de guardar -->
            <button type="submit" class="fab"><i class="bi bi-save-fill"></i></button>
        </form>
    </div>
</div>

<script>
// 🔍 Buscador en tiempo real
document.getElementById("buscador").addEventListener("input", function(){
    let filtro = this.value.toLowerCase();
    document.querySelectorAll("tbody tr").forEach(row => {
        let prod = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
        row.style.display = prod.includes(filtro) ? "" : "none";
    });
});

// 🟢 Habilitar campo físico si se selecciona
document.querySelectorAll(".select-producto").forEach(chk => {
    chk.addEventListener("change", function() {
        let row = this.closest("tr");
        let fisico = row.querySelector(".fisico");
        let diffSpan = row.querySelector(".diferencia");
        fisico.disabled = !this.checked;
        if (!this.checked) {
            fisico.value = 0;
            diffSpan.textContent = "0";
            diffSpan.className = "badge-diff neutro diferencia";
        }
    });
});

// 🔄 Calcular diferencias dinámicamente
document.querySelectorAll(".fisico").forEach(input => {
    input.addEventListener("input", function() {
        let row = this.closest("tr");
        let digital = parseInt(row.querySelector(".digital").value) || 0;
        let fisico = parseInt(this.value) || 0;
        let diff = fisico - digital;
        let diffSpan = row.querySelector(".diferencia");

        diffSpan.textContent = diff;
        diffSpan.className = "badge-diff diferencia neutro";
        if(diff > 0) diffSpan.classList.replace("neutro","positivo");
        if(diff < 0) diffSpan.classList.replace("neutro","negativo");
    });
});
</script>
</body>
</html>
