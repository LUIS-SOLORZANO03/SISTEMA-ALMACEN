<?php
include 'conexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<h3 style='color: red;'>ID no especificado.</h3>";
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    echo "<h3 style='color: red;'>Producto no encontrado.</h3>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            background: linear-gradient(-45deg,#1e3c72,#2a5298,#6a11cb,#2575fc);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 15px;
        }
        @keyframes gradientBG {
            0%{background-position:0% 50%;}
            50%{background-position:100% 50%;}
            100%{background-position:0% 50%;}
        }
        .card-custom {
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 35px;
            width: 100%;
            max-width: 750px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff;
            animation: fadeIn 0.9s ease-in-out;
        }
        h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
            text-shadow: 0 3px 12px rgba(0,0,0,0.5);
        }
        .empresa-logo {
            display: block;
            margin: 0 auto 20px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.6);
            box-shadow: 0 0 20px rgba(0,255,255,0.5);
            animation: pulse 2.5s infinite;
        }
        @keyframes pulse {
            0% {box-shadow:0 0 0 0 rgba(0,255,255,0.7);}
            70% {box-shadow:0 0 0 20px rgba(0,255,255,0);}
            100% {box-shadow:0 0 0 0 rgba(0,255,255,0);}
        }
        .form-label {
            font-weight: 600;
            color: #00e6e6;
        }
        .form-control, .form-select {
            background-color: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            color: #fff;
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(255,255,255,0.2);
            border-color: #00e6e6;
            box-shadow: 0 0 12px rgba(0,255,255,0.6);
        }
        .btn-grad {
            padding: 12px 28px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-grad:hover {
            transform: scale(1.07);
            box-shadow: 0 0 20px rgba(0,0,0,0.4);
        }
        .btn-primary {
            background: linear-gradient(90deg,#00c6ff,#0072ff);
        }
        .btn-secondary {
            background: linear-gradient(90deg,#6c757d,#495057);
            color: #fff;
        }
        @keyframes fadeIn {
            from {opacity:0;transform:translateY(40px);}
            to {opacity:1;transform:translateY(0);}
        }
        select option {color:#000;}
    </style>
</head>
<body>

<div class="card-custom">
    <img src="logo.png" alt="Logo de la empresa" class="empresa-logo">
    <h3><i class="bi bi-pencil-square"></i> Editar Producto</h3>

    <form action="actualizar_producto.php" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($producto['id']) ?>">

        <div class="mb-3">
            <label class="form-label"><i class="bi bi-upc-scan"></i> Código</label>
            <input type="text" name="codigo" class="form-control" required value="<?= htmlspecialchars($producto['codigo']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="bi bi-box"></i> Material</label>
            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($producto['nombre']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="bi bi-cash-coin"></i> Precio</label>
            <input type="number" step="0.01" name="precio_unitario" class="form-control" required value="<?= htmlspecialchars($producto['precio_unitario']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="bi bi-diagram-3"></i> Categoría</label>
            <select name="id_categoria" class="form-select" required>
                <?php
                $cats = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                while ($cat = $cats->fetch_assoc()) {
                    $sel = $cat['id'] == $producto['id_categoria'] ? 'selected' : '';
                    echo "<option value='{$cat['id']}' $sel>{$cat['nombre']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label"><i class="bi bi-rulers"></i> Unidad de Medida</label>
            <select name="unidad_medida" class="form-select" required>
                <?php
                $unidades = ['carrete', 'rollos', 'unidades', 'metros', 'cajas', 'paquetes','cientos','docenas','otros'];
                foreach ($unidades as $unidad) {
                    $sel = ($unidad == $producto['unidad_medida']) ? 'selected' : '';
                    echo "<option value='$unidad' $sel>$unidad</option>";
                }
                ?>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="almacen.php" class="btn btn-grad btn-secondary">← Volver</a>
            <button type="submit" class="btn btn-grad btn-primary">💾 Guardar Cambios</button>
        </div>
    </form>
</div>

<?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1): ?>
<script>
Swal.fire({icon:'success',title:'Producto actualizado',text:'Se guardaron los cambios correctamente ✅',confirmButtonColor:'#0072ff'});
</script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<script>
Swal.fire({icon:'error',title:'Error',text:'Hubo un problema al actualizar el producto ❌',confirmButtonColor:'#d33'});
</script>
<?php endif; ?>

</body>
</html>
