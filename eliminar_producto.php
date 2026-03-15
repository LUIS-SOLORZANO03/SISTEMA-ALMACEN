<?php
include 'conexion.php'; // Asegúrate que $conexion esté bien definido

if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];

    // Verificar si el producto tiene salidas asociadas
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM salidas_productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script>
                alert('❌ No se puede eliminar el producto porque tiene salidas asociadas.');
                window.location.href='almacen.php';
              </script>";
    } else {
        // Eliminar el producto porque no tiene salidas asociadas
        $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id_producto);

        if ($stmt->execute()) {
            echo "<script>
                    alert('✅ Producto eliminado correctamente.');
                    window.location.href='almacen.php';
                  </script>";
        } else {
            echo "<script>
                    alert('⚠️ Error al eliminar el producto.');
                    window.location.href='almacen.php';
                  </script>";
        }

        $stmt->close();
    }
} else {
    echo "<script>
            alert('⚠️ ID de producto no especificado.');
            window.location.href='almacen.php';
          </script>";
}
?>
