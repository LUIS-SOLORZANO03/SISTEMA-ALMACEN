<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Registrar cliente (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'nuevo_cliente') {
  $tipo_doc     = $_POST['tipo_doc'];
  $documento    = $_POST['documento'];
  $operacion    = $_POST['operacion'];
  $celular      = $_POST['celular'];
  $plan_tipo    = $_POST['plan'] ?? '';
  $plan_detalle = $_POST['plan_detalle'] ?? '';

  // Si es RUC: usar razon_social; si no: apellidos + nombres
  if ($tipo_doc === 'RUC') {
    $razon_social = trim($_POST['razon_social'] ?? '');
    $nombres_db   = $razon_social;
  } else {
    $ap_paterno = trim($_POST['ap_paterno'] ?? '');
    $ap_materno = trim($_POST['ap_materno'] ?? '');
    $nombres    = trim($_POST['nombres'] ?? '');
    $nombres_db = trim("$ap_paterno $ap_materno $nombres");
  }

  // Inserción segura (prepared)
  $sql = "INSERT INTO clientes (tipo_doc, documento, nombres, operacion, celular, plan, tipo, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
  $stmt = $conexion->prepare($sql);
  $stmt->bind_param("sssssss", $tipo_doc, $documento, $nombres_db, $operacion, $celular, $plan_detalle, $plan_tipo);

  if ($stmt->execute()) {
    header("Location: clientes.php?ok=1");
    exit;
  } else {
    header("Location: clientes.php?err=1");
    exit;
  }
}

// Obtener clientes
$clientes = $conexion->query("SELECT * FROM clientes ORDER BY fecha_registro DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Clientes - Data Online</title>

  <!-- Bootstrap + Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <!-- Animaciones -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4/animate.min.css" />
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      min-height: 100vh;
      background: radial-gradient(1200px 600px at 10% 10%, #fff4c2 0%, #ffe58f 40%, #ffd666 60%, #fff7e6 100%);
      background-attachment: fixed;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(6px);
      border-radius: 18px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
      border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .btn-magic {
      border-radius: 14px;
      box-shadow: 0 8px 16px rgba(255, 193, 7, 0.35);
      transition: transform .15s ease, box-shadow .15s ease;
    }

    .btn-magic:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 10px 20px rgba(255, 193, 7, 0.45);
    }

    .brand-badge {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border-radius: 16px;
      background: linear-gradient(135deg, #fff 0%, #fff8e1 100%);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    .brand-logo {
      width: 48px;
      height: 48px;
      object-fit: contain;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .1);
      background: white;
      padding: 6px;
    }

    .table thead th {
      background: #ffd666 !important;
    }

    .pulse {
      animation: pulse 1.6s infinite;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.03);
      }

      100% {
        transform: scale(1);
      }
    }

    .modal-header {
      border-top-left-radius: 16px !important;
      border-top-right-radius: 16px !important;
    }

    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 16px 40px rgba(0, 0, 0, .18);
    }

    .tag {
      font-size: .8rem;
      padding: .25rem .6rem;
      border-radius: 999px;
      background: #fff3cd;
      border: 1px solid #ffe69c;
      color: #7f5f00;
    }
  </style>
</head>

<body>

  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
      <div class="brand-badge">
        <img src="logo.png" alt="Logo" class="brand-logo">
        <div>
          <h2 class="m-0 fw-bold">Data Online</h2>
          <div class="text-muted small">Gestión de Clientes & Materiales</div>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="panel_reporte.php" class="btn btn-secondary rounded-3">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button class="btn btn-warning btn-magic pulse" data-bs-toggle="modal" data-bs-target="#modalCliente">
          <i class="bi bi-person-plus"></i> Nuevo Cliente
        </button>
      </div>
    </div>

    <div class="glass-card p-4 animate__animated animate__fadeInUp">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-lines-fill"></i> Clientes</h4>
        <span class="tag"><i class="bi bi-lightning-charge-fill"></i> Rápido y bonito</span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-warning">
            <tr>
              <th>#</th>
              <th>Nombre / Razón Social</th>
              <th>Documento</th>
              <th>Operación</th>
              <th>Celular</th>
              <th>Plan</th>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Material</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($c = $clientes->fetch_assoc()): ?>
              <tr class="animate__animated animate__fadeInUp">
                <td><?= htmlspecialchars($c['id']) ?></td>
                <td><?= htmlspecialchars($c['nombres']) ?></td>
                <td>
                  <span class="badge bg-dark-subtle text-dark">
                    <?= htmlspecialchars($c['tipo_doc']) ?>
                  </span>
                  <?= htmlspecialchars($c['documento']) ?>
                </td>
                <td><?= htmlspecialchars($c['operacion']) ?></td>
                <td><?= htmlspecialchars($c['celular']) ?></td>
                <td><?= htmlspecialchars($c['plan']) ?></td>
                <td><?= htmlspecialchars($c['tipo']) ?></td>
                <td><?= htmlspecialchars($c['fecha_registro']) ?></td>
                <td class="text-center">
                  <button class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalMaterial"
                    data-id="<?= $c['id'] ?>"
                    data-nombre="<?= $c['nombres'] ?>">
                    <i class="bi bi-tools"></i> Material
                  </button>

                  <a href="detalle_cliente.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-file-earmark-text"></i> Tecnico
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>

        </table>
      </div>
    </div>
  </div>

  <!-- Modal Nuevo Cliente -->
  <div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form method="POST" class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title"><i class="bi bi-person-plus"></i> Nuevo Cliente</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="accion" value="nuevo_cliente">

          <div class="col-md-4">
            <label class="form-label">Tipo Doc</label>
            <select name="tipo_doc" id="tipo_doc" class="form-select" required>
              <option value="">Seleccione</option>
              <option value="DNI">DNI</option>
              <option value="Carnet de extranjería">Carnet de extranjería</option>
              <option value="Pasaporte">Pasaporte</option>
              <option value="RUC">RUC</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Número de Documento</label>
            <input type="text" name="documento" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Celular</label>
            <input type="text" name="celular" class="form-control" required>
          </div>

          <!-- Campos para PERSONA -->
          <div class="col-md-4 persona-field">
            <label class="form-label">Apellido Paterno</label>
            <input type="text" name="ap_paterno" class="form-control">
          </div>

          <div class="col-md-4 persona-field">
            <label class="form-label">Apellido Materno</label>
            <input type="text" name="ap_materno" class="form-control">
          </div>

          <div class="col-md-4 persona-field">
            <label class="form-label">Nombres</label>
            <input type="text" name="nombres" class="form-control">
          </div>

          <!-- Campo para EMPRESA (RUC) -->
          <div class="col-md-12 empresa-field" style="display:none;">
            <label class="form-label">Razón Social</label>
            <input type="text" name="razon_social" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">Operación</label>
            <select name="operacion" class="form-select" required>
              <option>Instalación</option>
              <option>Retiro</option>
              <option>Traslado</option>
              <option>Planta externa</option>
              <option>Avería</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo de Plan</label>
            <select name="plan" id="tipo_plan" class="form-select" required>
              <option value="">Seleccione</option>
              <option>Internet</option>
              <option>DUO</option>
              <option>Cable TV</option>
            </select>
          </div>

          <div class="col-md-12" id="detalle_plan_container">
            <!-- Se llena con JS -->
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-warning btn-magic" type="submit"><i class="bi bi-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Material -->
  <div class="modal fade" id="modalMaterial" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <form method="POST" action="registrar_material.php" class="modal-content" id="formMaterial">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-tools"></i> Registrar Material</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="cliente_id" id="cliente_id">
          <input type="hidden" name="items_json" id="items_json">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Cliente</label>
              <input type="text" id="cliente_nombre" class="form-control" readonly>
            </div>

            <!-- Sección para agregar ítems al carrito -->
            <div class="glass-card p-3 mb-3">
              <div class="row g-3 align-items-end">
                <div class="col-md-3">
                  <label class="form-label">Material</label>
                  <select id="material" class="form-select">
                    <option value="">Seleccione</option>
                    <option value="MODEM">MODEM</option>
                    <option value="FIBRA">FIBRA</option>
                    <option value="CABLE COAXIAL">CABLE COAXIAL</option>
                    <option value="CINTA AISLANTE">CINTA AISLANTE</option>
                    <option value="GRAPAS">GRAPAS</option>
                    <option value="PIGTAIL">PIGTAIL</option>
                    <option value="CONECTOR RG6">CONECTOR RG6</option>
                    <option value="CONECTOR VERDE">CONECTOR VERDE</option>
                    <option value="CONECTOR AZUL">CONECTOR AZUL</option>
                  </select>
                </div>

                <!-- MODEM extra fields -->
                <div class="col-md-2 modem-only" style="display:none;">
                  <label class="form-label">Serie</label>
                  <input type="text" id="serie" class="form-control">
                </div>
                <div class="col-md-2 modem-only" style="display:none;">
                  <label class="form-label">Modelo</label>
                  <input type="text" id="modelo" class="form-control">
                </div>

                <!-- Unidad de medida -->
                <div class="col-md-2">
                  <label class="form-label">Unidad</label>
                  <select id="unidad" class="form-select">
                    <option value="">Seleccione</option>
                    <option value="m">Metros</option>
                    <option value="u">Unidades</option>
                    <option value="caja">Cajas</option>
                    <option value="rollo">Rollos</option>
                  </select>
                </div>

                <div class="col-md-2">
                  <label class="form-label">Cantidad</label>
                  <input type="number" id="cantidad" class="form-control" min="1" value="1">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Precio Unitario</label>
                  <input list="precios_sugeridos" type="number" id="precio" class="form-control" step="0.01">
                  <datalist id="precios_sugeridos"></datalist>
                </div>

                <div class="col-md-12">
                  <label class="form-label">Detalle (opcional)</label>
                  <input type="text" id="detalle" class="form-control" placeholder="Ej: 50m tendido / cambio de roseta">
                </div>

                <div class="col-md-12 d-flex justify-content-end">
                  <button type="button" id="btnAgregarItem" class="btn btn-success rounded-3">
                    <i class="bi bi-plus-circle"></i> Agregar a la lista
                  </button>
                </div>
              </div>
            </div>

            <!-- Tabla carrito -->
            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="tablaItems">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Material</th>
                    <th>Serie</th>
                    <th>Modelo</th>
                    <th>Unidad</th>
                    <th>Detalle</th>
                    <th>Cant.</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th colspan="8" class="text-end">Total</th>
                    <th id="totalCell">0.00</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary btn-magic">
              <i class="bi bi-save2"></i> Guardar Materiales
            </button>
          </div>
      </form>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ---------------- Alerts por querystring ----------------
    (() => {
      const params = new URLSearchParams(location.search);
      if (params.get('ok') === '1') {
        Swal.fire({
          icon: 'success',
          title: 'Guardado',
          text: 'Cliente registrado con éxito',
          timer: 1600,
          showConfirmButton: false
        });
      }
      if (params.get('err') === '1') {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo registrar el cliente'
        });
      }
      if (params.get('m_ok') === '1') {
        Swal.fire({
          icon: 'success',
          title: 'Materiales guardados',
          text: 'Se registraron los materiales',
          timer: 1600,
          showConfirmButton: false
        });
      }
      if (params.get('m_err') === '1') {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron registrar los materiales'
        });
      }
    })();

    // ---------------- Modal Material: cargar datos cliente ----------------
    const modalMaterial = document.getElementById('modalMaterial');
    modalMaterial.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const clienteId = button.getAttribute('data-id');
      const clienteNombre = button.getAttribute('data-nombre');
      document.getElementById('cliente_id').value = clienteId;
      document.getElementById('cliente_nombre').value = clienteNombre;

      // Reset carrito al abrir
      items = [];
      renderItems();
    });

    // ---------------- RUC vs Persona ----------------
    const tipoDoc = document.getElementById('tipo_doc');
    const personaFields = document.querySelectorAll('.persona-field');
    const empresaFields = document.querySelectorAll('.empresa-field');

    function togglePersonaEmpresa() {
      const isRuc = tipoDoc.value === 'RUC';
      personaFields.forEach(el => el.style.display = isRuc ? 'none' : '');
      empresaFields.forEach(el => el.style.display = isRuc ? '' : 'none');

      // Requeridos dinámicos
      document.querySelector('[name="razon_social"]').required = isRuc;
      ['ap_paterno', 'ap_materno', 'nombres'].forEach(nm => {
        const el = document.querySelector(`[name="${nm}"]`);
        if (el) el.required = !isRuc;
      });
    }
    tipoDoc.addEventListener('change', togglePersonaEmpresa);

    // ---------------- Plan Detalle dinámico ----------------
    const tipoPlan = document.getElementById('tipo_plan');
    const detalleContainer = document.getElementById('detalle_plan_container');

    function renderDetallePlan() {
      const val = tipoPlan.value;
      if (!val) {
        detalleContainer.innerHTML = '';
        return;
      }
      if (val === 'Internet') {
        detalleContainer.innerHTML = `
      <label class="form-label mt-2">Detalle de Plan (Internet)</label>
      <select class="form-select" name="plan_detalle" required>
        <option value="">Seleccione</option>
        <option>INTERNET 200 Mbps / S/50.00</option>
        <option>INTERNET 00 Mbps / S/60.00</option>
        <option>INTERNET 400 Mbps / S/70.00</option>
        <option>INTERNET 500 Mbps / S/80.00</option>
        <option>INTERNET 600 Mbps / S/90.00</option>
        <option>INTERNET 700 Mbps / S/100.00</option>
        <option>INTERNET 800 Mbps / S/110.00</option>
      </select>`;
      } else if (val === 'DUO') {
        detalleContainer.innerHTML = `
      <label class="form-label mt-2">Detalle de Plan (DUO)</label>
      <select class="form-select" name="plan_detalle" required>
        <option value="">Seleccione</option>
        <option>DUO 150 Mbps / S/75.00</option>
        <option>DUO 200 Mbps / S/85.00</option>
        <option>DUO 300 Mbps / S/95.00</option>
        <option>DUO 400 Mbps / S/105.00</option>
        <option>DUO 500 Mbps / S/115.00</option>
        <option>DUO 600 Mbps / S/125.00</option>
        <option>DUO 700 Mbps / S/135.00</option>
      </select>`;
      } else if (val === 'Cable TV') {
        detalleContainer.innerHTML = `
      <label class="form-label mt-2">Detalle de Plan (Cable TV)</label>
      <select class="form-select" name="plan_detalle" required>
        <option value="">Seleccione</option>
        <option>CABLE TV / S/50.00</option>
      </select>`;
      }
    }
    tipoPlan.addEventListener('change', renderDetallePlan);

    // ---------------- Material: UI dinámica y carrito ----------------
    const materialSel = document.getElementById('material');
    const preciosSugeridos = document.getElementById('precios_sugeridos');

    function refreshPrecioSugerido() {
      const v = materialSel.value;
      preciosSugeridos.innerHTML = '';
      const addOpts = (arr) => arr.forEach(x => {
        const o = document.createElement('option');
        o.value = x;
        preciosSugeridos.appendChild(o);
      });
      if (v === 'FIBRA') addOpts([100, 150, 220]);
      if (v === 'CABLE COAXIAL') addOpts([5, 10, 20]);
    }
    materialSel.addEventListener('change', () => {
      const isModem = materialSel.value === 'MODEM';
      document.querySelectorAll('.modem-only').forEach(el => el.style.display = isModem ? '' : 'none');
      refreshPrecioSugerido();
    });

    // Carrito
    let items = [];
    const tablaBody = document.querySelector('#tablaItems tbody');
    const totalCell = document.getElementById('totalCell');

    function renderItems() {
      tablaBody.innerHTML = '';
      let total = 0;
      items.forEach((it, idx) => {
        const tr = document.createElement('tr');
        const subtotal = (it.cantidad || 0) * (it.precio || 0);
        total += subtotal;
        tr.innerHTML = `
      <td>${idx+1}</td>
      <td>${it.material}</td>
      <td>${it.serie || ''}</td>
      <td>${it.modelo || ''}</td>
      <td>${it.detalle || ''}</td>
      <td>${it.cantidad}</td>
      <td>${Number(it.precio).toFixed(2)}</td>
      <td>${subtotal.toFixed(2)}</td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})">
          <i class="bi bi-trash"></i>
        </button>
      </td>`;
        tablaBody.appendChild(tr);
      });
      totalCell.textContent = total.toFixed(2);
    }
    window.removeItem = function(i) {
      items.splice(i, 1);
      renderItems();
    }

    document.getElementById('btnAgregarItem').addEventListener('click', () => {
      const material = materialSel.value;
      const cantidad = parseInt(document.getElementById('cantidad').value || '0', 10);
      const precio = parseFloat(document.getElementById('precio').value || '0');
      const detalle = document.getElementById('detalle').value.trim();
      const serie = document.getElementById('serie').value.trim();
      const modelo = document.getElementById('modelo').value.trim();

      if (!material) {
        Swal.fire({
          icon: 'warning',
          title: 'Seleccione un material'
        });
        return;
      }
      if (!cantidad || cantidad < 1) {
        Swal.fire({
          icon: 'warning',
          title: 'Cantidad inválida'
        });
        return;
      }
      if (isNaN(precio)) {
        Swal.fire({
          icon: 'warning',
          title: 'Ingrese precio'
        });
        return;
      }
      if (material === 'MODEM' && (!serie || !modelo)) {
        Swal.fire({
          icon: 'warning',
          title: 'Complete Serie y Modelo para MODEM'
        });
        return;
      }

      items.push({
        material,
        cantidad,
        precio,
        detalle,
        serie: material === 'MODEM' ? serie : null,
        modelo: material === 'MODEM' ? modelo : null
      });
      renderItems();

      // limpiar inputs (menos material)
      document.getElementById('cantidad').value = 1;
      document.getElementById('precio').value = '';
      document.getElementById('detalle').value = '';
      if (material === 'MODEM') {
        document.getElementById('serie').value = '';
        document.getElementById('modelo').value = '';
      }
    });

    // Enviar: empaquetar JSON
    document.getElementById('formMaterial').addEventListener('submit', (e) => {
      if (items.length === 0) {
        e.preventDefault();
        Swal.fire({
          icon: 'info',
          title: 'Agrega al menos un material'
        });
        return;
      }
      document.getElementById('items_json').value = JSON.stringify(items);
    });
  </script>
</body>

</html>