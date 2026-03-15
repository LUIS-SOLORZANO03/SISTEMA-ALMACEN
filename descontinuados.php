<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (!in_array($_SESSION['rol'], ['usuario', 'almacenero', 'admin'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

// Consulta ingresos y salidas
$sql = "
SELECT p.id, p.codigo, p.nombre, p.unidad_medida, ep.cantidad, ep.fecha AS fecha_ingreso, NULL AS fecha_salida
FROM productos p
INNER JOIN entradas_productos ep ON p.id = ep.id_producto
WHERE p.estado = 'inactivo'
UNION ALL
SELECT p.id, p.codigo, p.nombre, p.unidad_medida, sp.cantidad, NULL AS fecha_ingreso, sp.fecha AS fecha_salida
FROM productos p
INNER JOIN salidas_productos sp ON p.id = sp.id_producto
WHERE p.estado = 'inactivo'
ORDER BY id, 
         CASE WHEN fecha_ingreso IS NULL THEN 1 ELSE 0 END, 
         fecha_ingreso ASC, 
         fecha_salida ASC
";
$resultado = $conexion->query($sql);
$productos = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos Descontinuados - Reporte de Stock</title>
<link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<style>
    body {
        font-family:'Rubik',sans-serif;
        background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
        min-height:100vh;
        padding:40px 20px;
        color:#f0f0f0;
    }
    h1,h2 {
        text-align:center;
        font-weight:700;
        margin:0;
        letter-spacing:1px;
    }
    h1 {font-size:2.5rem; text-transform:uppercase;}
    h2 {font-size:1.3rem; color:#ddd; margin-bottom:25px;}
    .container-custom {
        background:rgba(255,255,255,0.05);
        padding:30px;
        border-radius:16px;
        box-shadow:0 8px 25px rgba(0,0,0,0.4);
        backdrop-filter:blur(12px);
    }
    .table-responsive {border-radius:12px; overflow:hidden;}
    .table {
        color:#fff;
    }
    .table thead {
        background:rgba(255,255,255,0.08);
        backdrop-filter:blur(6px);
    }
    .table tbody tr:hover {
        background:rgba(0,200,255,0.1);
        transition:.3s;
    }
    .btn-back,.btn-export,.btn-activar {
        border-radius:30px;
        padding:10px 20px;
        font-weight:600;
        transition:all .3s;
        border:none;
    }
    .btn-back {
        background:#ff5e62;
        color:#fff;
    }
    .btn-back:hover {background:#ff9966; transform:scale(1.05);}
    .btn-export {
        background:linear-gradient(90deg,#00c6ff,#0072ff);
        color:#fff;
    }
    .btn-export:hover {
        transform:scale(1.07);
        box-shadow:0 0 12px rgba(0,114,255,0.6);
    }
    .btn-activar {
        background:linear-gradient(90deg,#28a745,#218838);
        color:#fff;
        padding:6px 15px;
    }
    .btn-activar:hover {
        transform:scale(1.05);
        box-shadow:0 0 10px rgba(40,167,69,0.6);
    }
    .alert {
        border-radius:12px;
        font-weight:500;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<a href="almacen.php" class="btn btn-back mb-3">⬅ Volver al panel</a>

<div class="container-custom">
    <div class="text-center mb-4">
        <img src="logo.png" alt="Logo" style="max-height:80px;border-radius:50%;box-shadow:0 0 15px rgba(0,200,255,0.6);margin-bottom:15px;">
        <h1>Productos Descontinuados</h1>
        <h2>Reporte de Stock</h2>
    </div>

    <?php if (count($productos) > 0): ?>
        <div class="d-flex justify-content-end gap-2 mb-3">
            <button class="btn btn-export" onclick="exportarExcel()">📤 Excel</button>
            <button class="btn btn-export" onclick="exportarPDF()">📄 PDF</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center" id="tabla-productos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Ingreso</th>
                        <th>Salida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['codigo']) ?></span></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['unidad_medida']) ?></td>
                        <td><?= htmlspecialchars($p['cantidad']) ?></td>
                        <td><?= $p['fecha_ingreso'] ? htmlspecialchars($p['fecha_ingreso']) : '-' ?></td>
                        <td><?= $p['fecha_salida'] ? htmlspecialchars($p['fecha_salida']) : '-' ?></td>
                        <td><button class="btn btn-activar btn-sm" data-id="<?= $p['id'] ?>">✔ Activar</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">⚠ No hay productos descontinuados.</div>
    <?php endif; ?>
</div>

<script>
function obtenerFechaHora(){
    const d=new Date();
    return d.toLocaleDateString()+" "+d.toLocaleTimeString();
}

function exportarPDF(){
    const { jsPDF }=window.jspdf;
    const doc=new jsPDF('p','pt');
    doc.setFontSize(18);
    doc.text("Productos Descontinuados",300,40,{align:"center"});
    doc.setFontSize(12);
    doc.text("Reporte de Stock - "+obtenerFechaHora(),300,60,{align:"center"});
    doc.autoTable({html:'#tabla-productos',startY:80,theme:'grid'});
    doc.save("productos_descontinuados.pdf");
}

function exportarExcel(){
    const tabla=document.getElementById("tabla-productos");
    const wb=XLSX.utils.book_new();
    const ws=XLSX.utils.table_to_sheet(tabla);
    XLSX.utils.book_append_sheet(wb,ws,"Reporte");
    XLSX.writeFile(wb,"productos_descontinuados.xlsx");
}

// Activar producto con fetch + Swal
document.querySelectorAll('.btn-activar').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const id=btn.dataset.id;
        Swal.fire({
            title:"¿Activar producto?",
            text:"El producto volverá a estar disponible",
            icon:"question",
            showCancelButton:true,
            confirmButtonText:"Sí, activar",
            cancelButtonText:"Cancelar"
        }).then(r=>{
            if(r.isConfirmed){
                fetch('activar_producto.php',{
                    method:"POST",
                    headers:{"Content-Type":"application/x-www-form-urlencoded"},
                    body:"id_producto="+id
                }).then(res=>res.json()).then(data=>{
                    if(data.success){
                        Swal.fire({icon:"success",title:"Activado",text:"Producto activado ✅",timer:2000,showConfirmButton:false})
                        .then(()=>location.reload());
                    }else{
                        Swal.fire("Error",data.message||"No se pudo activar","error");
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
