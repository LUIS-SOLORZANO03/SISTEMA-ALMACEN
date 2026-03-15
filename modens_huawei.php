<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'usuario';
$mensaje_js = '';
$tipo_alerta = '';

// Agregar modem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_modem'])) {
    $modelo = $_POST['modelo'] ?? '';
    $serial = $_POST['serial'] ?? '';

    $stmt = $conexion->prepare("INSERT INTO modens_huawei (modelo, serial) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $modelo, $serial);
        if($stmt->execute()){
            $mensaje_js = "Modem agregado correctamente";
            $tipo_alerta = "success";
        } else {
            $mensaje_js = "Error al agregar modem";
            $tipo_alerta = "error";
        }
        $stmt->close();
    }
}

// Actualizar tecnico y fecha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $tecnico = $_POST['tecnico'];
    $fecha = $_POST['fecha'];

    $stmt = $conexion->prepare("UPDATE modens_huawei SET tecnico = ?, fecha = ? WHERE id = ?");
    if($stmt){
        $stmt->bind_param("ssi", $tecnico, $fecha, $id);
        if($stmt->execute()){
            echo "ok";
        } else { echo "error"; }
        $stmt->close();
    }
    exit;
}

// Obtener todos los modems
$result = $conexion->query("SELECT * FROM modens_huawei ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Modems HUAWEI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
body, html {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
}
@keyframes gradientBG {
    0% {background-position:0% 50%;}
    50% {background-position:100% 50%;}
    100% {background-position:0% 50%;}
}
.container {
    background-color: rgba(255,255,255,0.95);
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    margin-top: 50px;
}
h2 { text-align:center; margin-bottom: 35px; color:#343a40;}
.table-hover tbody tr:hover { background-color: rgba(255,75,43,0.15);}
input { width: 100%; border-radius:5px; }
.btn { border-radius: 30px; transition: all 0.3s ease; }
.btn:hover { transform: scale(1.05);}
.btn-success { background: linear-gradient(45deg, #28a745, #218838); border:none; }
.btn-primary { background: linear-gradient(45deg, #007bff, #0056b3); border:none; }
.btn-warning { background: linear-gradient(45deg, #ffc107, #e0a800); border:none; color:#fff; }
.btn-secondary { background: #6c757d; border:none; color:#fff; }
</style>
</head>
<body>
<div class="container">
<h2>🌐 Modems HUAWEI</h2>

<?php if($rol === 'admin' || $rol === 'almacenero'): ?>
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar"><i class="bi bi-plus-circle"></i> Agregar Modem</button>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Modelo</th>
<th>Serie</th>
<th>Técnico</th>
<th>Fecha</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php if($result && $result->num_rows > 0): ?>
<?php $i=1; while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['modelo']) ?></td>
<td><?= htmlspecialchars($row['serial']) ?></td>
<td>
<input type="text" value="<?= htmlspecialchars($row['tecnico'] ?? '') ?>" id="tecnico_<?= $row['id'] ?>" placeholder="Escribir técnico">
</td>
<td>
<input type="datetime-local" value="<?= isset($row['fecha']) ? date('Y-m-d\TH:i', strtotime($row['fecha'])) : '' ?>" id="fecha_<?= $row['id'] ?>">
</td>
<td>
<button class="btn btn-primary btn-sm" onclick="guardar(<?= $row['id'] ?>)" id="guardar_<?= $row['id'] ?>">Guardar</button>
<button class="btn btn-warning btn-sm" onclick="editar(<?= $row['id'] ?>)" id="editar_<?= $row['id'] ?>" style="display:none;">Editar</button>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6" class="text-center">No hay modems registrados.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<a href="modem.php" class="btn btn-secondary mt-3">⬅ Volver al Panel</a>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
<div class="modal-header">
<h5 class="modal-title">Agregar Modem HUAWEI</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="mb-3"><label>Modelo</label><input type="text" name="modelo" class="form-control" required></div>
<div class="mb-3"><label>Serie</label><input type="text" name="serial" class="form-control" required></div>
</div>
<div class="modal-footer">
<button type="submit" name="agregar_modem" class="btn btn-success">Agregar</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
</div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function guardar(id){
    const tecnico = document.getElementById('tecnico_'+id).value;
    const fecha = document.getElementById('fecha_'+id).value;

    if(!tecnico || !fecha){
        Swal.fire({icon:'warning', title:'Completa todos los campos'});
        return;
    }

    fetch('modens_huawei.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'update_id='+id+'&tecnico='+encodeURIComponent(tecnico)+'&fecha='+encodeURIComponent(fecha)
    }).then(res=>res.text()).then(data=>{
        if(data.trim() === 'ok'){
            document.getElementById('tecnico_'+id).readOnly = true;
            document.getElementById('fecha_'+id).readOnly = true;
            document.getElementById('guardar_'+id).style.display = 'none';
            document.getElementById('editar_'+id).style.display = 'inline-block';
            Swal.fire({icon:'success', title:'Datos guardados correctamente'});
        } else {
            Swal.fire({icon:'error', title:'Error al guardar'});
        }
    });
}

function editar(id){
    document.getElementById('tecnico_'+id).readOnly = false;
    document.getElementById('fecha_'+id).readOnly = false;
    document.getElementById('guardar_'+id).style.display = 'inline-block';
    document.getElementById('editar_'+id).style.display = 'none';
}

// Mostrar alerta si agregaste modem
<?php if($mensaje_js): ?>
Swal.fire({icon:'<?= $tipo_alerta ?>', title:'<?= $mensaje_js ?>'});
<?php endif; ?>
</script>
</body>
</html>
