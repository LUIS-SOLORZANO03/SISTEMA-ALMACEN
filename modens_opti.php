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

    $stmt = $conexion->prepare("INSERT INTO modens_opti (modelo, serial) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $modelo, $serial);
        if($stmt->execute()){
            $mensaje_js = "Módem agregado correctamente";
            $tipo_alerta = "success";
        } else {
            $mensaje_js = "Error al agregar módem";
            $tipo_alerta = "error";
        }
        $stmt->close();
    }
}

// Actualizar técnico y fecha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $tecnico = $_POST['tecnico'];
    $fecha = $_POST['fecha'];

    $stmt = $conexion->prepare("UPDATE modens_opti SET tecnico = ?, fecha = ? WHERE id = ?");
    if($stmt){
        $stmt->bind_param("ssi", $tecnico, $fecha, $id);
        echo $stmt->execute() ? "ok" : "error";
        $stmt->close();
    }
    exit;
}

// Obtener datos
$result = $conexion->query("SELECT * FROM modens_opti ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Módems OPTI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    background-size: 400% 400%;
    animation: gradientBG 12s ease infinite;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 15px;
}
@keyframes gradientBG {
    0% {background-position:0% 50%;}
    50% {background-position:100% 50%;}
    100% {background-position:0% 50%;}
}
.container-custom {
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    padding: 35px;
    max-width: 1100px;
    width: 100%;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    animation: fadeIn 0.7s ease-in-out;
}
@keyframes fadeIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
h2 {
    text-align:center;
    margin-bottom:25px;
    color:#222;
    font-weight:600;
}
.table thead {
    background: linear-gradient(90deg,#2575fc,#6a11cb);
    color: #fff;
}
.table-hover tbody tr:hover {
    background: rgba(37,117,252,0.1);
    transition: 0.3s;
}
input:focus {
    border-color:#2575fc !important;
    box-shadow: 0 0 8px rgba(37,117,252,0.4) !important;
}
.btn {
    border-radius: 10px;
    transition: transform 0.2s;
}
.btn:hover {
    transform: scale(1.05);
}
.modal-content {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
</style>
</head>
<body>
<div class="container-custom">
    <h2>📡 Gestión de Módems OPTI</h2>

    <?php if($rol === 'admin' || $rol === 'almacenero'): ?>
    <button class="btn btn-success mb-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
        <i class="bi bi-plus-circle"></i> Agregar Módem
    </button>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Modelo</th>
                    <th>Serie</th>
                    <th>Técnico</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result && $result->num_rows > 0): $i=1; ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['modelo']) ?></td>
                    <td><?= htmlspecialchars($row['serial']) ?></td>
                    <td><input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($row['tecnico'] ?? '') ?>" id="tecnico_<?= $row['id'] ?>" placeholder="Escribir técnico"></td>
                    <td><input type="datetime-local" class="form-control form-control-sm" value="<?= isset($row['fecha']) ? date('Y-m-d\TH:i', strtotime($row['fecha'])) : '' ?>" id="fecha_<?= $row['id'] ?>"></td>
                    <td><?= htmlspecialchars($row['descripcion'] ?? '') ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="guardar(<?= $row['id'] ?>)" id="guardar_<?= $row['id'] ?>"><i class="bi bi-save"></i></button>
                        <button class="btn btn-warning btn-sm" onclick="editar(<?= $row['id'] ?>)" id="editar_<?= $row['id'] ?>" style="display:none;"><i class="bi bi-pencil-square"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">🚫 No hay módems registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="modem.php" class="btn btn-secondary mt-3 shadow-sm"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nuevo Módem OPTI</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Modelo</label><input type="text" name="modelo" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Serie</label><input type="text" name="serial" class="form-control" required></div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="agregar_modem" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
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

    fetch('modens_opti.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'update_id='+id+'&tecnico='+encodeURIComponent(tecnico)+'&fecha='+encodeURIComponent(fecha)
    }).then(res=>res.text()).then(data=>{
        if(data.trim() === 'ok'){
            document.getElementById('tecnico_'+id).readOnly = true;
            document.getElementById('fecha_'+id).readOnly = true;
            document.getElementById('guardar_'+id).style.display = 'none';
            document.getElementById('editar_'+id).style.display = 'inline-block';
            Swal.fire({icon:'success', title:'Datos guardados correctamente', timer:2000, showConfirmButton:false});
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

// Alerta después de agregar módem
<?php if($mensaje_js): ?>
Swal.fire({icon:'<?= $tipo_alerta ?>', title:'<?= $mensaje_js ?>', timer:2000, showConfirmButton:false});
<?php endif; ?>
</script>
</body>
</html>
