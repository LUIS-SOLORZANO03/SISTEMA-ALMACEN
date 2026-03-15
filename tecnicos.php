<?php
// ✅ Conexión
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Gestión de Personal | DataOnline</title>

  <!-- 🔹 Librerías -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>


  <style>
    /* 🎨 Estilos base */
    :root {
      --accent: #f26522;
      --accent-dark: #c94a0e;
      --light: #fff7f2;
      --success: #28a745;
      --danger: #dc3545;
    }

    body {
      background: radial-gradient(circle at 20% 10%, #fff0e3 0%, #fff 70%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      padding: 40px 20px;
    }

    /* 🌐 Panel principal */
    .container-panel {
      max-width: 1200px;
      margin: auto;
      animation: fadeInUp .8s ease;
    }

    .btn-danger {
      background: linear-gradient(135deg, #ff512f, #dd2476);
      color: #fff;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-danger:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(255, 81, 47, 0.4);
    }

    .card-panel {
      border: none;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.97);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      transition: all .3s ease;
    }

    /* Header */
    .header-panel {
      background: linear-gradient(90deg, var(--accent), var(--accent-dark));
      color: white;
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-title {
      font-size: 1.4rem;
      font-weight: 700;
    }

    .header-buttons button {
      border-radius: 10px;
      font-weight: 600;
    }

    /* Tabla */
    .table thead {
      background: linear-gradient(90deg, var(--accent), var(--accent-dark));
      color: white;
    }

    .table-hover tbody tr {
      transition: all .2s ease;
    }

    .table-hover tbody tr:hover {
      background-color: rgba(242, 101, 34, 0.08);
      transform: scale(1.01);
    }

    /* Estados */
    .estado-chip {
      padding: 6px 12px;
      border-radius: 999px;
      font-weight: 600;
      font-size: 13px;
    }

    .estado-activo {
      color: #0f8a5b;
      background: rgba(31, 184, 129, 0.15);
    }

    .estado-inactivo {
      color: #b71c1c;
      background: rgba(255, 0, 0, 0.1);
    }

    /* Botones */
    .btn-orange {
      background: linear-gradient(90deg, var(--accent), var(--accent-dark));
      color: white;
      border: none;
      transition: all .2s ease;
    }

    .btn-orange:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(242, 101, 34, 0.4);
    }

    .btn-back {
      border: 2px solid var(--accent);
      color: var(--accent);
      background: white;
      font-weight: 600;
    }

    .btn-back:hover {
      background: var(--accent);
      color: white;
      transform: translateY(-2px);
    }

    /* Modal */
    .modal-content {
      border-radius: 16px;
      overflow: hidden;
    }

    .modal-header {
      background: linear-gradient(90deg, var(--accent), var(--accent-dark));
      color: white;
      border-bottom: none;
    }

    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 0.2rem rgba(242, 101, 34, 0.25);
    }

    /* Animación */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>

  <div class="container-panel">
    <div class="card card-panel shadow-sm">

      <!-- Encabezado -->
      <div class="header-panel">
        <div class="header-title"><i class="fa-solid fa-user-gear me-2"></i> Gestión de Técnicos</div>
        <div class="header-buttons d-flex gap-2">
          <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#modalPersonal">
            <i class="fa fa-plus"></i> Nuevo
          </button>
          <button class="btn btn-back" onclick="volver()">
            <i class="fa fa-arrow-left"></i> Volver
          </button>
        </div>
      </div>

      <!-- Filtros -->
      <div class="p-3 d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex gap-2 align-items-center">
          <input id="search" class="form-control" placeholder="Buscar técnico..." oninput="renderTabla()">
          <select id="filtro" class="form-select" onchange="renderTabla()">
            <option value="Todos">Todos</option>
            <option value="Activo">Activos</option>
            <option value="Inactivo">Inactivos</option>
          </select>
        </div>
        <button class="btn btn-danger shadow-lg px-4 py-2 rounded-pill" onclick="exportPDF()">
          <i class="fas fa-file-pdf me-2"></i> Exportar PDF
        </button>

      </div>

      <!-- Tabla -->
      <div class="table-responsive px-3">
        <table class="table table-hover align-middle text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <!-- dinámico -->
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <!-- Modal agregar -->
  <div class="modal fade" id="modalPersonal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content animate__animated animate__fadeInDown">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i> Nuevo Personal</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formPersonal" onsubmit="return guardar(event)">
            <input type="hidden" id="idPersonal">
            <div class="mb-3">
              <label class="form-label">Nombre completo</label>
              <input id="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
            </div>
            <div class="text-end">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-orange">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const tecnicos = <?php
                      $data = [];
                      $res = $conexion->query("SELECT id, nombre, estado FROM personal ORDER BY id ASC");
                      while ($r = $res->fetch_assoc()) $data[] = $r;
                      echo json_encode($data, JSON_UNESCAPED_UNICODE);
                      ?>;

    function renderTabla() {
      const filtro = document.getElementById('filtro').value;
      const busqueda = document.getElementById('search').value.toLowerCase();
      const tbody = document.getElementById('tbody');
      tbody.innerHTML = '';

      const filtrado = tecnicos.filter(t =>
        (filtro === 'Todos' || t.estado === filtro) &&
        t.nombre.toLowerCase().includes(busqueda)
      );

      if (filtrado.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-muted">Sin resultados</td></tr>`;
        return;
      }

      filtrado.forEach((t, i) => {
        const estadoClass = t.estado === 'Activo' ? 'estado-activo' : 'estado-inactivo';
        tbody.innerHTML += `
      <tr class="animate__animated animate__fadeIn">
        <td>${i + 1}</td>
        <td class="text-start">${t.nombre}</td>
        <td><span class="estado-chip ${estadoClass}">${t.estado}</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary" onclick="editar(${t.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm ${t.estado === 'Activo' ? 'btn-danger' : 'btn-success'}" onclick="cambiarEstado(${t.id})">
            ${t.estado === 'Activo' ? 'Desactivar' : 'Activar'}
          </button>
          <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${t.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    `;
      });
    }

    function guardar(e) {
      e.preventDefault();
      const nombre = document.getElementById('nombre').value.trim();
      if (nombre.length < 2) return Swal.fire('Atención', 'El nombre es demasiado corto', 'warning');

      const form = new URLSearchParams();
      form.append('nombre', nombre);

      fetch('tecnico_agregar.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: form
      }).then(r => r.json()).then(j => {
        if (j.success) Swal.fire('Éxito', j.message, 'success').then(() => location.reload());
        else Swal.fire('Error', j.message, 'error');
      });
    }

    function editar(id) {
      // Buscar el técnico en el array
      const tec = tecnicos.find(t => t.id == id);
      if (!tec) return Swal.fire('Error', 'Técnico no encontrado', 'error');

      // Mostrar modal de edición con SweetAlert
      Swal.fire({
        title: 'Editar Técnico',
        html: `
      <input id="nombreEditar" class="swal2-input" placeholder="Nombre" value="${tec.nombre}">
    `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar cambios',
        preConfirm: () => {
          const nombre = document.getElementById('nombreEditar').value.trim();
          if (nombre.length < 2) {
            Swal.showValidationMessage('El nombre es demasiado corto');
            return false;
          }
          return nombre;
        }
      }).then(res => {
        if (!res.isConfirmed) return;

        const form = new URLSearchParams();
        form.append('id', id);
        form.append('nombre', res.value);

        fetch('tecnico_editar.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: form
          })
          .then(r => r.json())
          .then(j => {
            if (j.success) {
              Swal.fire('Actualizado', j.message, 'success').then(() => location.reload());
            } else {
              Swal.fire('Error', j.message, 'error');
            }
          });
      });
    }

    function cambiarEstado(id) {
      const tec = tecnicos.find(t => t.id == id);
      const nuevo = tec.estado === 'Activo' ? 'Inactivo' : 'Activo';
      Swal.fire({
        title: `¿Deseas cambiar el estado a ${nuevo}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar'
      }).then(res => {
        if (!res.isConfirmed) return;
        fetch('tecnico_estado.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${id}&estado=${nuevo}`
        }).then(r => r.json()).then(j => {
          if (j.success) Swal.fire('Actualizado', j.message, 'success').then(() => location.reload());
        });
      });
    }

    function eliminar(id) {
      Swal.fire({
        title: '¿Eliminar este técnico?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        confirmButtonColor: '#dc3545'
      }).then(res => {
        if (!res.isConfirmed) return;
        fetch('tecnico_delete.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${id}`
        }).then(r => r.json()).then(j => {
          if (j.success) Swal.fire('Eliminado', j.message, 'success').then(() => location.reload());
        });
      });
    }
    async function exportPDF() {
      // ✅ Importar jsPDF
      const {
        jsPDF
      } = window.jspdf;
      const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });

      // 🧾 Encabezado principal
      doc.setFont("helvetica", "bold");
      doc.setFontSize(18);
      doc.setTextColor(242, 101, 34); // naranja profesional
      doc.text("LISTADO DE TÉCNICOS", 105, 20, {
        align: "center"
      });

      // 🕒 Fecha
      doc.setFontSize(11);
      doc.setFont("helvetica", "normal");
      const fecha = new Date().toLocaleDateString("es-PE", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
      doc.text(`📅 Generado el ${fecha}`, 105, 28, {
        align: "center"
      });

      // 💼 Línea divisoria elegante
      doc.setDrawColor(242, 101, 34);
      doc.setLineWidth(0.6);
      doc.line(20, 32, 190, 32);

      // 📋 Datos de tabla (sin botones)
      const body = tecnicos.map(t => [t.id, t.nombre, t.estado]);

      // 📊 Estilo de tabla
      doc.autoTable({
        startY: 40,
        head: [
          ['ID', 'NOMBRE', 'ESTADO']
        ],
        body: body,
        theme: 'grid',
        headStyles: {
          fillColor: [242, 101, 34],
          textColor: 255,
          fontStyle: 'bold',
          halign: 'center'
        },
        bodyStyles: {
          fontSize: 11,
          halign: 'center',
          valign: 'middle'
        },
        alternateRowStyles: {
          fillColor: [245, 245, 245]
        },
        margin: {
          left: 15,
          right: 15
        },
        styles: {
          lineColor: [220, 220, 220],
          lineWidth: 0.2,
        }
      });

      // 🖋️ Pie de página
      const totalPagesExp = "{total_pages_count_string}";
      const pageCount = doc.internal.getNumberOfPages();

      for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(9);
        doc.setTextColor(120);
        doc.text(`Página ${i} de ${pageCount}`, 190, 290, {
          align: "right"
        });
        doc.text("© Sistema DATAONLINE", 15, 290);
      }

      // 💾 Guardar archivo
      doc.save(`tecnicos_${fecha.replace(/\s/g, "_")}.pdf`);
    }


    function volver() {
      Swal.fire({
        title: '¿Volver al panel principal?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, volver'
      }).then(res => {
        if (res.isConfirmed) window.location.href = 'principal.php';
      });
    }

    renderTabla();
  </script>
</body>

</html>