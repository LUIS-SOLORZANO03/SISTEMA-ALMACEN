<?php
// rehusados.php
session_start();
require_once "conexion.php";

// --- Obtener registros de la tabla rehusados ---
$query = "SELECT * FROM rehusados ORDER BY id ASC";
$result = $conexion->query($query);

// Contadores rápidos
$total = $conexion->query("SELECT COUNT(*) AS c FROM rehusados")->fetch_assoc()['c'] ?? 0;
$pendientes = $conexion->query("SELECT COUNT(*) AS c FROM rehusados WHERE estado='Pendiente'")->fetch_assoc()['c'] ?? 0;
$operativos = $conexion->query("SELECT COUNT(*) AS c FROM rehusados WHERE estado='Operativo'")->fetch_assoc()['c'] ?? 0;
$inoperativos = $conexion->query("SELECT COUNT(*) AS c FROM rehusados WHERE estado='Inoperativo'")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Rehusados</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <style>
    body {
      background: #f4f6fb;
      font-family: 'Segoe UI', sans-serif;
    }

    header {
      background: linear-gradient(90deg, #6c63ff, #4e54c8);
      color: white;
      padding: 1.5rem;
      text-align: center;
      border-radius: 0 0 25px 25px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, .15);
    }

    header h1 {
      margin: 0;
      font-size: 2rem;
      font-weight: bold;
    }

    /* Cards resumen */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1rem;
      margin: 2rem 0;
    }

    .card-stat {
      padding: 1.5rem;
      border-radius: 16px;
      color: white;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .15);
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: transform .2s ease;
    }

    .card-stat:hover {
      transform: translateY(-5px);
    }

    .card-stat h3 {
      font-size: 1.2rem;
      margin-bottom: .5rem;
    }

    .card-stat p {
      font-size: 1.6rem;
      margin: 0;
      font-weight: bold;
    }

    .btn-floating-back {
      position: fixed;
      bottom: 20px;
      left: 20px;
      background: linear-gradient(135deg, #ff416c, #ff4b2b);
      color: white;
      border: none;
      padding: 18px;
      border-radius: 50%;
      font-size: 22px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, .25);
      cursor: pointer;
      transition: transform .2s ease, box-shadow .2s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-floating-back:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 16px rgba(0, 0, 0, .35);
    }

    .card-icon {
      font-size: 2.5rem;
      opacity: .8;
    }

    .bg-total {
      background: linear-gradient(135deg, #6c63ff, #8c7dff);
    }

    .bg-pendiente {
      background: linear-gradient(135deg, #f59e0b, #fbbf24);
    }

    .bg-operativo {
      background: linear-gradient(135deg, #10b981, #34d399);
    }

    .bg-inoperativo {
      background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    /* Tabla */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .1);
    }

    table th,
    table td {
      padding: .75rem;
      text-align: center;
    }

    table th {
      background: #6c63ff;
      color: white;
    }

    table tr:nth-child(even) {
      background: #f9fafb;
    }

    /* Buscador */
    .search-box {
      text-align: center;
      margin: 1rem 0;
    }

    .search-box input {
      width: 50%;
      padding: .7rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
    }

    /* Botón flotante */
    .btn-floating {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: linear-gradient(135deg, #6c63ff, #4e54c8);
      color: white;
      border: none;
      padding: 18px;
      border-radius: 50%;
      font-size: 22px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, .25);
      cursor: pointer;
      transition: transform .2s ease;
    }

    .btn-floating:hover {
      transform: scale(1.1);
    }

    /* Modal personalizado */
    .modal-custom {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, .6);
      align-items: center;
      justify-content: center;
      animation: fadeIn .3s ease;
    }

    .modal-custom.show {
      display: flex;
    }

    .dialog {
      background: white;
      padding: 2rem;
      border-radius: 16px;
      width: 500px;
      max-width: 95%;
      box-shadow: 0 8px 20px rgba(0, 0, 0, .25);
      animation: scaleUp .3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0
      }

      to {
        opacity: 1
      }
    }

    @keyframes scaleUp {
      from {
        transform: scale(.7)
      }

      to {
        transform: scale(1)
      }
    }

    .dialog h2 {
      margin-bottom: 1rem;
      font-weight: bold;
      color: #4e54c8;
    }

    .form-group label {
      font-weight: bold;
      margin-bottom: .3rem;
      display: block;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: .6rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 1rem;
    }

    .actions {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
    }
  </style>
</head>

<body>
  <header>
    <h1><i class="fa-solid fa-database"></i> Gestión de Rehusados</h1>
  </header>

  <div class="container">
    <!-- Cards resumen -->
    <div class="cards">
      <div class="card-stat bg-total">
        <div>
          <h3>Total registros</h3>
          <p><?= $total ?></p>
        </div>
        <i class="fa fa-database card-icon"></i>
      </div>
      <div class="card-stat bg-pendiente">
        <div>
          <h3>Pendientes</h3>
          <p><?= $pendientes ?></p>
        </div>
        <i class="fa fa-clock card-icon"></i>
      </div>
      <div class="card-stat bg-operativo">
        <div>
          <h3>Operativos</h3>
          <p><?= $operativos ?></p>
        </div>
        <i class="fa fa-check-circle card-icon"></i>
      </div>
      <div class="card-stat bg-inoperativo">
        <div>
          <h3>Inoperativos</h3>
          <p><?= $inoperativos ?></p>
        </div>
        <i class="fa fa-times-circle card-icon"></i>
      </div>
    </div>

    <!-- Buscador -->
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="🔍 Buscar por modelo, serie, técnico o estado...">
    </div>

    <!-- Tabla -->
    <div style="overflow-x:auto">
      <table id="tableData">
        <thead>
          <tr>
            <th>ID</th>
            <th>Modelo</th>
            <th>Serie</th>
            <th>Ingreso</th>
            <th>Salida</th>
            <th>Técnico</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Observación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['modelo']) ?></td>
                <td><?= htmlspecialchars($row['serie']) ?></td>
                <td><?= $row['fecha_ingreso'] ?></td>
                <td><?= $row['fecha_salida'] ?></td>
                <td><?= htmlspecialchars($row['tecnico']) ?></td>
                <td><?= htmlspecialchars($row['tipo']) ?></td>
                <td><?= htmlspecialchars($row['estado']) ?></td>
                <td><?= htmlspecialchars($row['observacion']) ?></td>
                <td class="actions-table">
                  <button class="btn btn-warning btn-edit"
                    data-id="<?= $row['id'] ?>"
                    data-modelo="<?= htmlspecialchars($row['modelo']) ?>"
                    data-serie="<?= htmlspecialchars($row['serie']) ?>"
                    data-fecha_ingreso="<?= $row['fecha_ingreso'] ?>"
                    data-fecha_salida="<?= $row['fecha_salida'] ?>"
                    data-tecnico="<?= htmlspecialchars($row['tecnico']) ?>"
                    data-tipo="<?= htmlspecialchars($row['tipo']) ?>"
                    data-estado="<?= htmlspecialchars($row['estado']) ?>"
                    data-observacion="<?= htmlspecialchars($row['observacion']) ?>">
                    <i class="fa fa-pen"></i>
                  </button>
                  <form method="post" action="rehusados_delete.php" onsubmit="return confirmDelete(this)">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" style="text-align:center">⚠️ No hay registros</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Botón flotante -->
  <button class="btn-floating" id="btnNew"><i class="fa fa-plus"></i></button>
  <a href="modem.php" class="btn-floating-back">
    <i class="fa fa-arrow-left"></i>
  </a>
  <!-- Modal -->
  <div class="modal-custom" id="modalForm">
    <div class="dialog">
      <h2 id="modalTitle">Nuevo Registro</h2>
      <form method="post" action="rehusados_save.php">
        <input type="hidden" name="id" id="id">
        <div class="form-group"><label>Modelo</label><input type="text" name="modelo" id="modelo" required></div>
        <div class="form-group"><label>Serie</label><input type="text" name="serie" id="serie" required></div>
        <div class="form-group"><label>Fecha Ingreso</label><input type="date" name="fecha_ingreso" id="fecha_ingreso"></div>
        <div class="form-group"><label>Fecha Salida</label><input type="date" name="fecha_salida" id="fecha_salida"></div>
        <div class="form-group"><label>Técnico</label><input type="text" name="tecnico" id="tecnico"></div>
        <div class="form-group"><label>Tipo</label><input type="text" name="tipo" id="tipo"></div>
        <div class="form-group"><label>Estado</label>
          <select name="estado" id="estado">
            <option value="Pendiente">Pendiente</option>
            <option value="Operativo">Operativo</option>
            <option value="Inoperativo">Inoperativo</option>
          </select>
        </div>
        <div class="form-group"><label>Observación</label><textarea name="observacion" id="observacion"></textarea></div>
        <div class="actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const btnNew = document.getElementById("btnNew");
    const modal = document.getElementById("modalForm");
    const modalTitle = document.getElementById("modalTitle");
    const fields = ["id", "modelo", "serie", "fecha_ingreso", "fecha_salida", "tecnico", "tipo", "estado", "observacion"];

    btnNew.addEventListener("click", () => {
      modalTitle.textContent = "Nuevo Registro";
      fields.forEach(f => document.getElementById(f).value = "");
      modal.classList.add("show");
    });

    document.querySelectorAll(".btn-edit").forEach(btn => {
      btn.addEventListener("click", () => {
        modalTitle.textContent = "Editar Registro";
        fields.forEach(f => document.getElementById(f).value = btn.dataset[f] || "");
        modal.classList.add("show");
      });
    });

    function closeModal() {
      modal.classList.remove("show");
    }

    function confirmDelete(form) {
      event.preventDefault();
      Swal.fire({
        title: "¿Eliminar?",
        text: "Se borrará este registro",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#6c63ff",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar"
      }).then((r) => {
        if (r.isConfirmed) {
          form.submit();
        }
      });
      return false;
    }

    // Buscador en tabla
    document.getElementById("searchInput").addEventListener("keyup", function() {
      const val = this.value.toLowerCase();
      document.querySelectorAll("#tableData tbody tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none";
      });
    });
  </script>
</body>

</html>