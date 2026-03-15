<?php
include 'conexion.php';

$mensaje = '';
$tipo = '';

// Obtener categorías
$categorias = [];
$result_cat = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
while ($row = $result_cat->fetch_assoc()) {
    $categorias[] = $row;
}

// Unidades disponibles
$unidades = ['par', 'unidad', 'metro', 'metro lineal', 'metro cuadrado', 'paquete', 'rollo', 'caja', 'cientos', 'docenas', 'otros'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $codigo = trim($_POST["codigo"]);
    $cantidad = intval($_POST["cantidad"]);
    $categoria_id = intval($_POST["categoria"]);
    $unidad_medida = $_POST["unidad_medida"];
    $precio_unitario = floatval($_POST["precio_unitario"]);
    $precio_total = $precio_unitario * $cantidad;
    $fecha = date('Y-m-d');

    if (!$nombre || !$codigo || $cantidad <= 0 || !$categoria_id || !$unidad_medida || !$precio_unitario) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo = "error";
    } else {
        $stmt = $conexion->prepare("SELECT id FROM productos WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $id_producto = $row['id'];

            $insert = $conexion->prepare("INSERT INTO entradas_productos (id_producto, cantidad, fecha) VALUES (?, ?, ?)");
            $insert->bind_param("iis", $id_producto, $cantidad, $fecha);
            $insert->execute();

            $update_precio = $conexion->prepare("UPDATE productos SET precio_total = ? WHERE id = ?");
            $update_precio->bind_param("di", $precio_total, $id_producto);
            $update_precio->execute();

            $mensaje = "Producto existente (por código), entrada registrada ✅";
            $tipo = "success";
        } else {
            $insert_producto = $conexion->prepare("INSERT INTO productos (nombre, codigo, id_categoria, unidad_medida, precio_unitario, precio_total) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_producto->bind_param("ssisdd", $nombre, $codigo, $categoria_id, $unidad_medida, $precio_unitario, $precio_total);
            $insert_producto->execute();

            $id_producto = $conexion->insert_id;

            $insert_entrada = $conexion->prepare("INSERT INTO entradas_productos (id_producto, cantidad, fecha) VALUES (?, ?, ?)");
            $insert_entrada->bind_param("iis", $id_producto, $cantidad, $fecha);
            $insert_entrada->execute();

            $mensaje = "Producto nuevo agregado con entrada inicial 🎉";
            $tipo = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Producto</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(-45deg, #00c6ff, #0072ff, #7f00ff, #e100ff);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      font-family: 'Segoe UI', sans-serif;
    }
    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }
    .card {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 600px;
      color: #fff;
    }
    h3 {
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
      text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    }
    label {
      font-weight: 500;
      margin-top: 10px;
    }
    .form-control, .form-select {
      border-radius: 12px;
      background: rgba(255,255,255,0.8);
    }
    .btn-grad {
      border: none;
      padding: 12px 20px;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .btn-grad:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    .btn-guardar {
      background: linear-gradient(90deg, #28a745, #20c997);
      color: white;
    }
    .btn-volver {
      background: linear-gradient(90deg, #6c757d, #495057);
      color: white;
    }
  </style>
</head>
<body>

  <div class="card">
    <h3>➕ Agregar Producto</h3>

    <form method="POST">
      <div class="mb-3">
        <label for="nombre"><i class="bi bi-box"></i> Nombre del producto</label>
        <input type="text" name="nombre" id="nombre" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="codigo"><i class="bi bi-upc-scan"></i> Código</label>
        <input type="text" name="codigo" id="codigo" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="categoria"><i class="bi bi-diagram-3"></i> Categoría</label>
        <select name="categoria" id="categoria" class="form-select" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= $cat['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="unidad_medida"><i class="bi bi-rulers"></i> Unidad de Medida</label>
        <select name="unidad_medida" id="unidad_medida" class="form-select" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($unidades as $unidad): ?>
            <option value="<?= $unidad ?>"><?= ucfirst($unidad) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="precio_unitario"><i class="bi bi-cash-coin"></i> Precio Unitario (S/.)</label>
        <input type="number" name="precio_unitario" id="precio_unitario" class="form-control" min="0" step="0.01" required>
      </div>

      <div class="mb-3">
        <label for="cantidad"><i class="bi bi-123"></i> Cantidad</label>
        <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <a href="almacen.php" class="btn btn-grad btn-volver">⬅ Volver</a>
        <button type="submit" class="btn btn-grad btn-guardar">💾 Guardar</button>
      </div>
    </form>
  </div>

  <?php if ($mensaje): ?>
    <script>
      Swal.fire({
        icon: '<?= $tipo ?>',
        title: '<?= $tipo === "success" ? "Éxito" : "Atención" ?>',
        text: '<?= $mensaje ?>',
        confirmButtonColor: '#007bff'
      });
    </script>
  <?php endif; ?>

</body>
</html>
