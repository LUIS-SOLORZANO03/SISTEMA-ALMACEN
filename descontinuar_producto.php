<?php
include 'conexion.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "UPDATE productos SET estado = 'inactivo' WHERE id = $id";

    if ($conexion->query($sql)) {
        header("Location: almacen.php?mensaje=producto_descontinuado");
        exit();
    } else {
        echo "Error al descontinuar el producto: " . $conexion->error;
    }
} else {
    echo "ID de producto no válido.";
}
?>
