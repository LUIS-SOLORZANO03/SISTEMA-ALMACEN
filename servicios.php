<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "data_online");
if ($conexion->connect_error) {
  die("Error DB: " . $conexion->connect_error);
}

$cliente_id = $_GET['cliente_id'] ?? 0;

// Consultar cliente
$stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

// Insertar servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'nuevo_servicio') {
    $plan = $_POST['plan'];
    $direccion_serv = $_POST['direccion'];

    $sql = "INSERT INTO servicios (cliente_id, plan, direccion) VALUES (?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iss", $cliente_id, $plan, $direccion_serv);
    $stmt->execute();
    $stmt->close();

    echo "<script>
            window.onload = function() {
              Swal.fire({
                title: '✅ Servicio agregado',
                text: 'El servicio se registró correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
              }).then(() => { window.location = 'servicios.php?cliente_id=$cliente_id'; });
            }
          </script>";
}

// Consultar servicios del cliente
$resServicios = $conexion->query("SELECT * FROM servicios WHERE cliente_id = $cliente_id ORDER BY creado DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Servicios de Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: #f4f6f9; }
    .card-custom {
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      border: none;
    }
    .btn-main {
      background: linear-gradient(135deg, #28a745, #00c851);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 10px 25px;
      font-weight: bold;
      transition: 0.3s;
    }
    .btn-main:hover {
      background: linear-gradient(135deg, #1e7e34, #009c3f);
      transform: scale(1.05);
    }
  </style>
</head>
<body class="container py-4">

  <div class="mb-4">
    <a href="clientes.php" class="btn btn-secondary">⬅ Volver</a>
  </div>

  <div class="card card-custom p-4 mb-4">
    <h4>👤 Cliente</h4>
    <p><strong><?= $cliente['nombre'] ?: $cliente['razon_social'] ?></strong></p>
    <p>📄 <?= $cliente['tipo_doc'] ?>: <?= $cliente['nro_doc'] ?></p>
    <p>📱 <?= $cliente['celular1'] ?> | ✉ <?= $cliente['email'] ?></p>
    <p>📍 <?= $cliente['direccion'] ?></p>
  </div>

  <div class="card card-custom p-4 mb-4">
    <h4>➕ Agregar Servicio</h4>
    <form method="post">
      <input type="hidden" name="accion" value="nuevo_servicio">
      <div class="mb-3">
        <label class="form-label">Plan</label>
        <select class="form-select" name="plan" required>
          <option value="DUO">DUO</option>
          <option value="INTERNET">INTERNET</option>
          <option value="NEGOCIO">NEGOCIO</option>
          <option value="INTERNET DEDICADO">INTERNET DEDICADO</option>
          <option value="TV CABLE DATA">TV CABLE DATA</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Zona / Dirección</label>
        <select class="form-select" name="direccion" required>
          <option value="Barranca">Barranca</option>
          <option value="Paramonga">Paramonga</option>
          <option value="Pativilca">Pativilca</option>
          <option value="Supe">Supe</option>
          <option value="Supe Puerto">Supe Puerto</option>
          <option value="Anexos">Anexos</option>
        </select>
      </div>
      <button type="submit" class="btn btn-main">Guardar Servicio</button>
    </form>
  </div>

  <div class="card card-custom p-4">
    <h4>📋 Servicios Registrados</h4>
    <table class="table table-striped">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Plan</th>
          <th>Zona/Dirección</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while($s = $resServicios->fetch_assoc()): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= $s['plan'] ?></td>
          <td><?= $s['direccion'] ?></td>
          <td><?= $s['creado'] ?></td>
          <td>
            <a href="recibo.php?id=<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">🖨 Imprimir Recibo</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
