<?php
// rehusados_save.php
session_start();
require_once "conexion.php";

$id = $_POST['id'] ?? '';
$modelo = $_POST['modelo'] ?? '';
$serie = $_POST['serie'] ?? '';
$fecha_ingreso = $_POST['fecha_ingreso'] ?? null;
$fecha_salida = $_POST['fecha_salida'] ?? null;
$tecnico = $_POST['tecnico'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$estado = $_POST['estado'] ?? '';
$observacion = $_POST['observacion'] ?? '';

if ($id) {
    // UPDATE
    $stmt = $conexion->prepare("UPDATE rehusados SET modelo=?, serie=?, fecha_ingreso=?, fecha_salida=?, tecnico=?, tipo=?, estado=?, observacion=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $modelo, $serie, $fecha_ingreso, $fecha_salida, $tecnico, $tipo, $estado, $observacion, $id);
    $ok = $stmt->execute();
} else {
    // INSERT
    $stmt = $conexion->prepare("INSERT INTO rehusados (modelo, serie, fecha_ingreso, fecha_salida, tecnico, tipo, estado, observacion) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $modelo, $serie, $fecha_ingreso, $fecha_salida, $tecnico, $tipo, $estado, $observacion);
    $ok = $stmt->execute();
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
  title: "<?= $ok ? '¡Éxito!' : 'Error' ?>",
  text: "<?= $ok ? 'Registro guardado correctamente' : 'Ocurrió un problema al guardar' ?>",
  icon: "<?= $ok ? 'success' : 'error' ?>",
  confirmButtonColor: "#6c63ff"
}).then(()=>{ window.location.href="modens_rehusados.php"; });
</script>
</body>
</html>
