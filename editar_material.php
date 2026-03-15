<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $material_nombre = $_POST['material'] ?? '';
    $cantidad = (float)($_POST['cantidad'] ?? 0);
    $unidad = $_POST['unidad'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0);
    $tecnico_dni = $_POST['tecnico_dni'] ?? null;
    $serie = $_POST['serie'] ?? null;
    $modelo = $_POST['modelo'] ?? null;

    $sql = "UPDATE materiales 
            SET material=?, cantidad=?, unidad=?, precio=?, tecnico_dni=?, serie=?, modelo=? 
            WHERE id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sdsdsssi", $material_nombre, $cantidad, $unidad, $precio, $tecnico_dni, $serie, $modelo, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Material actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $conexion->error]);
    }
    exit;
}

if (!isset($_GET['id'])) {
    die("Material no especificado");
}
$id = intval($_GET['id']);

$sql = "SELECT m.*, t.nombre AS tecnico_nombre 
        FROM materiales m
        LEFT JOIN tecnicos t ON t.dni = m.tecnico_dni
        WHERE m.id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();

if (!$material) {
    die("Material no encontrado");
}

$tecnicos = $conexion->query("SELECT dni, nombre FROM tecnicos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Material</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }
        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 800px;
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h3 {
            font-weight: 700;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #ddd;
            transition: all 0.3s ease-in-out;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0072ff;
            box-shadow: 0 0 0 0.25rem rgba(0,114,255,0.25);
        }
        .btn-magic {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 14px;
            transition: all 0.3s ease;
        }
        .btn-magic:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }
        .btn-secondary {
            border-radius: 14px;
            font-weight: 600;
            padding: 12px 25px;
        }
        .modem-only {
            display: none;
        }
    </style>
</head>
<body>

    <div class="card p-5">
        <h3 class="mb-4 text-center text-primary">
            <i class="bi bi-pencil-square"></i> Editar Material
        </h3>

        <form id="formEditar">
            <input type="hidden" name="id" value="<?= $material['id'] ?>">

            <div class="mb-3">
                <label class="form-label">Material</label>
                <select name="material" id="material" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php
                    $materiales = ["MODEM", "FIBRA", "CABLE COAXIAL", "CINTA AISLANTE", "GRAPAS", "PIGTAIL", "CONECTOR RG6", "CONECTOR VERDE", "CONECTOR AZUL"];
                    foreach ($materiales as $m) {
                        $selected = ($material['material'] == $m) ? "selected" : "";
                        echo "<option value='$m' $selected>$m</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3 modem-only">
                    <label class="form-label">Serie</label>
                    <input type="text" name="serie" class="form-control" value="<?= htmlspecialchars($material['serie'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3 modem-only">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($material['modelo'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" step="0.01" name="cantidad" class="form-control" 
                           value="<?= htmlspecialchars($material['cantidad']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Unidad</label>
                    <select name="unidad" class="form-select">
                        <option value="">Seleccione</option>
                        <?php
                        $unidades = ["m" => "Metros", "u" => "Unidades", "caja" => "Cajas", "rollo" => "Rollos"];
                        foreach ($unidades as $val => $label) {
                            $selected = ($material['unidad'] == $val) ? "selected" : "";
                            echo "<option value='$val' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio</label>
                    <input type="number" step="0.01" name="precio" class="form-control" 
                           value="<?= htmlspecialchars($material['precio']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Técnico Asignado</label>
                <select name="tecnico_dni" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php while ($t = $tecnicos->fetch_assoc()): ?>
                        <option value="<?= $t['dni'] ?>" 
                            <?= ($t['dni'] == $material['tecnico_dni']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="detalle_cliente.php?id=<?= $material['cliente_id'] ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-magic" id="btnGuardar">
                    <i class="bi bi-save2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script>
        // Mostrar/Ocultar serie y modelo si es MODEM
        function toggleModemFields() {
            const material = document.getElementById("material").value;
            const modemFields = document.querySelectorAll(".modem-only");
            modemFields.forEach(field => {
                field.style.display = (material === "MODEM") ? "block" : "none";
            });
        }
        document.getElementById("material").addEventListener("change", toggleModemFields);
        toggleModemFields();

        // Guardar con AJAX y SweetAlert2
        document.getElementById("formEditar").addEventListener("submit", function(e){
            e.preventDefault();

            Swal.fire({
                title: '¿Guardar cambios?',
                text: "Se actualizará la información del material",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00b09b',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(e.target);
                    fetch("editar_material.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "¡Guardado!",
                                text: data.message,
                                confirmButtonColor: "#28a745"
                            }).then(() => {
                                window.location.href = "detalle_cliente.php?id=<?= $material['cliente_id'] ?>";
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: data.message,
                                confirmButtonColor: "#dc3545"
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo conectar con el servidor.",
                            confirmButtonColor: "#dc3545"
                        });
                    });
                }
            });
        });
    </script>

</body>
</html>
