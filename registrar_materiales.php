<?php
require_once 'conexion.php';

// Obtener clientes
$clientes = $conexion->query("SELECT id, nombre FROM clientes ORDER BY nombre");

// Obtener productos (columna materiales)
$productos = $conexion->query("SELECT id, materiales, unidad, precio FROM productos ORDER BY materiales");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Materiales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #d4fc79, #96e6a1);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .table thead {
            background: #00c6ff;
            color: #fff;
        }
        .btn-add {
            background: #007bff;
            color: white;
            border-radius: 12px;
            transition: 0.3s;
        }
        .btn-add:hover {
            background: #0056b3;
        }
        .btn-remove {
            background: #ff4d4d;
            color: white;
            border-radius: 12px;
            transition: 0.3s;
        }
        .btn-remove:hover {
            background: #b30000;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card p-4">
        <h2 class="text-center mb-4">📦 Registrar Materiales</h2>

        <?php if (isset($_GET['m_ok'])): ?>
            <div class="alert alert-success">✅ Materiales guardados correctamente</div>
        <?php elseif (isset($_GET['m_err'])): ?>
            <div class="alert alert-danger">⚠️ Error al registrar materiales</div>
        <?php endif; ?>

        <form id="materialesForm" method="POST" action="guardar_materiales.php">
            <!-- Cliente -->
            <div class="mb-3">
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select" required>
                    <option value="">Seleccione un cliente</option>
                    <?php while($c = $clientes->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Selección de producto -->
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Producto</label>
                    <select id="materialSelect" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php while($p = $productos->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($p['materiales']) ?>"
                                    data-unidad="<?= htmlspecialchars($p['unidad']) ?>"
                                    data-precio="<?= htmlspecialchars($p['precio']) ?>">
                                <?= htmlspecialchars($p['materiales']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unidad</label>
                    <input type="text" id="unidadInput" class="form-control" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" id="cantidadInput" class="form-control" min="1" value="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Precio</label>
                    <input type="number" id="precioInput" class="form-control" step="0.01">
                </div>
                <div class="col-md-2 text-center">
                    <button type="button" class="btn btn-add w-100" onclick="agregarMaterial()">➕ Agregar</button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="mt-4">
                <table class="table table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMateriales"></tbody>
                </table>
            </div>

            <input type="hidden" name="items_json" id="itemsJson">

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success btn-lg px-5">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const materialSelect = document.getElementById('materialSelect');
    const unidadInput = document.getElementById('unidadInput');
    const precioInput = document.getElementById('precioInput');
    const cantidadInput = document.getElementById('cantidadInput');
    const tablaMateriales = document.getElementById('tablaMateriales');
    const itemsJson = document.getElementById('itemsJson');

    let items = [];

    materialSelect.addEventListener('change', () => {
        const option = materialSelect.options[materialSelect.selectedIndex];
        unidadInput.value = option.dataset.unidad || '';
        precioInput.value = option.dataset.precio || '';
    });

    function agregarMaterial() {
        const material = materialSelect.value;
        const unidad = unidadInput.value;
        const cantidad = parseInt(cantidadInput.value);
        const precio = parseFloat(precioInput.value);

        if (!material || cantidad <= 0 || precio <= 0) {
            alert("⚠️ Complete todos los campos correctamente");
            return;
        }

        const total = cantidad * precio;
        items.push({ material, unidad, cantidad, precio });

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${material}</td>
            <td>${unidad}</td>
            <td>${cantidad}</td>
            <td>S/ ${precio.toFixed(2)}</td>
            <td>S/ ${total.toFixed(2)}</td>
            <td><button type="button" class="btn btn-remove btn-sm" onclick="eliminarMaterial(this)">🗑️</button></td>
        `;
        tablaMateriales.appendChild(row);

        actualizarJson();
    }

    function eliminarMaterial(btn) {
        const row = btn.closest('tr');
        const index = Array.from(tablaMateriales.children).indexOf(row);
        items.splice(index, 1);
        row.remove();
        actualizarJson();
    }

    function actualizarJson() {
        itemsJson.value = JSON.stringify(items);
    }
</script>

</body>
</html>
