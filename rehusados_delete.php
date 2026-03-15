<?php
// rehusados_delete.php
session_start();
require_once "conexion.php";

$id = $_POST['id'] ?? '';

$ok = false;
if ($id) {
    $stmt = $conexion->prepare("DELETE FROM rehusados WHERE id=?");
    $stmt->bind_param("i", $id);
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
  title: "<?= $ok ? '¡Eliminado!' : 'Error' ?>",
  text: "<?= $ok ? 'Registro eliminado correctamente' : 'No se pudo eliminar el registro' ?>",
  icon: "<?= $ok ? 'success' : 'error' ?>",
  confirmButtonColor: "#6c63ff"
}).then(()=>{ window.location.href="modens_rehusados.php"; });
</script>
</body>
</html>
