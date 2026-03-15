<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$rol = $_SESSION['rol'] ?? 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>⚡ Gestión de Almacén Pro</title>

  <!-- Bootstrap + DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Tipografía -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      padding-bottom: 50px;
      background: linear-gradient(-45deg, #4e54c8, #8f94fb, #43e97b, #38f9d7);
      background-size: 400% 400%;
      animation: gradientBG 18s ease infinite;
      overflow-x: hidden;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Navbar */
    .navbar {
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(12px);
      color: #fff;
      padding: 15px 25px;
      border-radius: 0 0 20px 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    }
    .navbar h1 {
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0;
      letter-spacing: 0.5px;
    }

    /* Tarjeta principal */
    .dashboard-card {
      background: rgba(255, 255, 255, 0.92);
      border-radius: 24px;
      padding: 30px;
      margin-top: 40px;
      box-shadow: 0 8px 35px rgba(0,0,0,0.25);
      animation: fadeIn 0.8s ease;
    }

    /* Botones */
    .btn-modern {
      border: none;
      padding: 12px 16px;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-modern:hover {
      transform: scale(1.06);
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }
    .btn-limpiar { background: #ffc107; color: #000; }
    .btn-excel { background: linear-gradient(90deg, #43e97b, #38f9d7); color: #fff; }
    .btn-pdf { background: linear-gradient(90deg, #ff6a00, #ee0979); color: #fff; }
    .btn-agregar { background: linear-gradient(90deg, #00c6ff, #0072ff); color: #fff; }
    .btn-volver { background: #6c757d; color: #fff; }

    /* Tabla */
    .table thead {
      background: linear-gradient(90deg, #4e54c8, #8f94fb);
      color: #fff;
    }
    .table thead th {
      position: sticky;
      top: 0;
      z-index: 10;
    }

    /* Footer */
    footer {
      text-align: center;
      margin-top: 50px;
      color: #fff;
      font-weight: 500;
    }

    /* Dark mode */
    .dark-mode {
      background: #121212 !important;
      color: #eaeaea !important;
    }
    .dark-mode .dashboard-card {
      background: rgba(25,25,25,0.92);
      color: #f1f1f1;
    }
    .dark-mode .table thead {
      background: #222;
      color: #fff;
    }

    /* Animaciones */
    @keyframes fadeIn { from{opacity:0; transform: translateY(20px);} to{opacity:1; transform: none;} }
  </style>
</head>
<body>

  <!-- NAV -->
  <div class="navbar d-flex justify-content-between align-items-center">
    <h1>📦 Gestión de Almacén</h1>
    <div>
      <span>👤 <?= htmlspecialchars($_SESSION['usuario']) ?></span>
      <button id="toggleDark" class="btn btn-sm btn-light ms-3">🌙</button>
    </div>
  </div>

  <!-- CARD -->
  <div class="container">
    <div class="dashboard-card">

      <!-- FORM -->
      <form class="row g-3 align-items-end mb-3" onsubmit="return false;">
        <div class="col-md-4">
          <label class="form-label">📂 Filtrar por Categoría</label>
          <select name="categoria" id="categoria" class="form-select">
            <option value="">Todas las categorías</option>
            <?php
              $cat_result = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
              while ($cat = $cat_result->fetch_assoc()) {
                  echo "<option value='".htmlspecialchars($cat['id'])."'>".htmlspecialchars($cat['nombre'])."</option>";
              }
            ?>
          </select>
        </div>

        <div class="col-md-2">
          <button type="button" id="btn-limpiar" class="btn btn-modern btn-limpiar w-100">🧹 Limpiar</button>
        </div>
        <div class="col-md-2">
          <button type="button" id="btnExcel" class="btn btn-modern btn-excel w-100">📊 Excel</button>
        </div>
        <div class="col-md-2">
          <button type="button" id="btnPDF" class="btn btn-modern btn-pdf w-100">📄 PDF</button>
        </div>
        <div class="col-md-2">
          <a href="descontinuados.php" class="btn btn-warning w-100">🚫 Descontinuados</a>
        </div>
        <div class="col-md-3">
          <?php if ($rol === 'almacenero' || $rol === 'admin'): ?>
            <a href="agregar_producto.php" class="btn btn-modern btn-agregar w-100">➕ Agregar Producto</a>
          <?php else: ?>
            <button class="btn btn-modern btn-agregar w-100" disabled title="Sin permisos">➕ Agregar Producto</button>
          <?php endif; ?>
        </div>
      </form>

      <!-- TABLA -->
      <div id="tabla-productos" class="table-responsive mt-4"></div>

      <!-- VOLVER -->
      <div class="text-end mt-4">
        <a href="panel_almacen.php" class="btn btn-volver">⬅ Volver</a>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer>
    <p>⚡ <?= date('Y') ?> Sistema Pro de Gestión de Almacén</p>
  </footer>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

  <script>
    let dt = null;
    let tablaDatos = [];

    function cargarDatos() {
      const categoria = $('#categoria').val();
      $.get('filtro_productos.php', { categoria }, function(data) {
        $('#tabla-productos').html(data);

        const $table = $('#tabla-productos').find('table').first();
        if ($.fn.DataTable.isDataTable($table)) {
          $table.DataTable().destroy();
        }
        dt = $table.DataTable({
          responsive: true,
          paging: false,
          searching: false,
          ordering: false,
          language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        tablaDatos = [];
        const headers = [];
        $table.find('thead th').each((i, th) => {
          if (i < $table.find('thead th').length - 1) {
            headers.push($(th).text().trim());
          }
        });
        $table.find('tbody tr').each(function() {
          const fila = [];
          $(this).find('td').each(function(i, td) {
            if (i < headers.length) fila.push($(td).text().trim());
          });
          tablaDatos.push(fila);
        });
        tablaDatos.unshift(headers);

      }).fail(() => Swal.fire('❌ Error', 'No se pudo cargar la tabla.', 'error'));
    }

    async function exportarPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'pt', 'a4');
      const margin = 40;
      doc.setFontSize(16);
      doc.text("📦 Inventario - Exportación", margin, 50);
      doc.setFontSize(10);
      doc.text(`Exportado: ${new Date().toLocaleString()}`, margin, 70);
      doc.autoTable({
        startY: 90,
        head: [tablaDatos[0]],
        body: tablaDatos.slice(1),
        theme: 'grid',
        headStyles: { fillColor: [78, 84, 200], textColor: 255 },
        styles: { fontSize: 9, cellPadding: 4 },
        margin: { left: margin, right: margin }
      });
      doc.save(`inventario_${new Date().toISOString().slice(0,19).replace(/[T:]/g,'-')}.pdf`);
    }

    $(function() {
      cargarDatos();
      $('#categoria').on('change', cargarDatos);
      $('#btn-limpiar').on('click', () => { $('#categoria').val(''); cargarDatos(); });
      $('#btnExcel').on('click', () => window.location.href = 'exc_exportar.php?categoria='+encodeURIComponent($('#categoria').val()));
      $('#btnPDF').on('click', exportarPDF);
      $('#toggleDark').on('click', () => $('body').toggleClass('dark-mode'));
    });
  </script>
</body>
</html>
