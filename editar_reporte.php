<?php
session_start();
include 'conexion.php';

// --- Verificar sesión ---
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// --- Validar ID ---
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: reportes_tecnicos.php");
    exit();
}

$success = false;
$successMessage = "";
$error = "";

// --- Procesar actualización ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente      = $conexion->real_escape_string($_POST['cliente']);
    $dni          = $conexion->real_escape_string($_POST['dni']);
    $id_categoria = (int) $_POST['id_categoria'];
    $turno        = $conexion->real_escape_string($_POST['turno']);
    $cuadrilla    = (int) $_POST['cuadrilla'];
    $cantidad     = (int) $_POST['cantidad'];
    $observacion  = $conexion->real_escape_string($_POST['observacion']);

    // Manejo materiales
    $producto = '';
    if (isset($_POST['producto'])) {
        $producto = is_array($_POST['producto'])
            ? $conexion->real_escape_string(implode(', ', array_map('trim', $_POST['producto'])))
            : $conexion->real_escape_string(trim($_POST['producto']));
    }

    $sql = "UPDATE tecnicos_registros 
            SET cliente='$cliente', dni='$dni', id_categoria=$id_categoria,
                turno='$turno', cuadrilla=$cuadrilla,
                producto='$producto', cantidad=$cantidad, observacion='$observacion'
            WHERE id=$id";

    if ($conexion->query($sql)) {
        $success = true;
        $successMessage = "✅ Reporte actualizado exitosamente";
    } else {
        $error = "Error al actualizar: " . $conexion->error;
    }
}

// --- Obtener datos ---
$sql = "SELECT * FROM tecnicos_registros WHERE id=$id LIMIT 1";
$res = $conexion->query($sql);
if ($res->num_rows === 0) {
    header("Location: reportes_tecnicos.php?msg=noregistro");
    exit();
}
$reporte = $res->fetch_assoc();

// --- Catálogo ---
$categorias = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$tecnicos   = $conexion->query("SELECT dni, nombre FROM tecnicos ORDER BY nombre ASC");
$productos_res = $conexion->query("SELECT DISTINCT nombre FROM productos WHERE TRIM(nombre) <> '' ORDER BY nombre ASC");

$nombre_list = [];
while ($row = $productos_res->fetch_assoc()) {
    $nombre_list[] = $row['nombre'];
}

// --- Materiales seleccionados ---
$producto_val_selected = [];
if (!empty($reporte['producto'])) {
    $producto_val_selected = array_map('trim', explode(',', $reporte['producto']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Reporte | Data Online Perú</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.5.2/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fff;
    margin: 0;
}
.topbar {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 24px; background: rgba(255,255,255,0.05); backdrop-filter: blur(6px);
}
.topbar h1 { font-size: 18px; margin: 0; color: #facc15; font-weight: 700; }
.card-glass {
    max-width: 1100px; margin: 40px auto; padding: 24px;
    background: rgba(255,255,255,0.05); border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.5);
}
.form-label { font-weight: 600; }
.form-control, .form-select {
    border-radius: 10px; border: none;
    background: rgba(255,255,255,0.9);
}
.btn-save {
    background: linear-gradient(90deg, #3b82f6, #2563eb); border: none; color: #fff;
    font-weight: 700; border-radius: 10px; padding: 10px 18px;
}
.btn-back {
    background: linear-gradient(90deg, #f59e0b, #fbbf24); border: none; color: #111;
    font-weight: 600; border-radius: 10px; padding: 8px 16px;
}
.summary-card {
    padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.04);
}
.badge-turno { padding: 6px 10px; border-radius: 8px; font-weight: 600; }
.badge-m { background: #fde047; color:#111; }
.badge-t { background: #38bdf8; color:#fff; }
.badge-n { background: #a78bfa; color:#fff; }
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <h1>DATA ONLINE PERÚ</h1>
    <a href="reportes_tecnicos.php" class="btn btn-back">⬅ Volver</a>
</div>

<!-- Card -->
<div class="card-glass">
    <div class="row g-4">
        <!-- Formulario -->
        <div class="col-lg-7">
            <h3 class="mb-3">✏ Editar Reporte</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="editForm" method="post">
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" name="cliente" class="form-control" value="<?= htmlspecialchars($reporte['cliente']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Técnico</label>
                    <select name="dni" class="form-select" required>
                        <?php while ($t = $tecnicos->fetch_assoc()): ?>
                            <option value="<?= $t['dni'] ?>" <?= $t['dni']==$reporte['dni']?'selected':'' ?>>
                                <?= htmlspecialchars($t['nombre']) ?> (<?= $t['dni'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="id_categoria" class="form-select" required>
                        <?php while ($c = $categorias->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id']==$reporte['id_categoria']?'selected':'' ?>>
                                <?= htmlspecialchars($c['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Turno</label>
                        <select name="turno" class="form-select" required>
                            <option <?= $reporte['turno']=="Mañana"?"selected":"" ?>>Mañana</option>
                            <option <?= $reporte['turno']=="Tarde"?"selected":"" ?>>Tarde</option>
                            <option <?= $reporte['turno']=="Noche"?"selected":"" ?>>Noche</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cuadrilla</label>
                        <input type="number" name="cuadrilla" class="form-control" value="<?= $reporte['cuadrilla'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" value="<?= $reporte['cantidad'] ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Materiales</label>
                    <select name="producto[]" id="materialSelect" class="form-select" multiple="multiple">
                        <?php foreach(array_unique($nombre_list) as $mat): ?>
                            <option value="<?= htmlspecialchars($mat) ?>" <?= in_array($mat,$producto_val_selected)?'selected':'' ?>>
                                <?= htmlspecialchars($mat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observación</label>
                    <textarea name="observacion" class="form-control" rows="3"><?= htmlspecialchars($reporte['observacion']) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnPreview" class="btn btn-outline-light flex-grow-1">🔍 Previsualizar</button>
                    <button type="submit" class="btn btn-save">💾 Guardar</button>
                </div>
            </form>
        </div>

        <!-- Resumen -->
        <div class="col-lg-5">
            <div class="summary-card">
                <p><strong>ID:</strong> <?= $reporte['id'] ?></p>
                <p><strong>DNI:</strong> <?= htmlspecialchars($reporte['dni']) ?></p>
                <p><strong>Fecha:</strong> <?= date("d/m/Y H:i", strtotime($reporte['fecha_registro'])) ?></p>
                <p><strong>Producto:</strong> <?= htmlspecialchars($reporte['producto'] ?: "No definido") ?></p>
                <p><strong>Turno:</strong> <span class="badge-turno <?= $reporte['turno']=="Mañana"?"badge-m":($reporte['turno']=="Tarde"?"badge-t":"badge-n") ?>"><?= $reporte['turno'] ?></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function(){
    $('#materialSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione materiales',
        width: '100%'
    });

    $('#btnPreview').on('click', function(){
        const form = $('#editForm').serializeArray();
        let html = '<ul style="text-align:left">';
        form.forEach(f => {
            html += `<li><strong>${f.name}:</strong> ${f.value}</li>`;
        });
        html += '</ul>';
        Swal.fire({ title:'Previsualización', html: html, showCancelButton:true, confirmButtonText:'Guardar' })
            .then(r => { if(r.isConfirmed) $('#editForm').submit(); });
    });
});
</script>

<?php if ($success): ?>
<script>
Swal.fire({icon:'success',title:'<?= addslashes($successMessage) ?>',timer:2500,showConfirmButton:false})
.then(()=> window.location='reportes_tecnicos.php');
</script>
<?php endif; ?>

</body>
</html>
