<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibiendo los datos del formulario
    $id = $_POST['id'];
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $id_categoria = $_POST['id_categoria'];
    $unidad_medida = $_POST['unidad_medida'];
    $precio_unitario = $_POST['precio_unitario'];  // Recibiendo el valor del precio_unitario

    // Actualiza el producto, incluyendo el precio_unitario
    $stmt = $conexion->prepare("UPDATE productos SET codigo=?, nombre=?, id_categoria=?, unidad_medida=?, precio_unitario=? WHERE id=?");
    $stmt->bind_param("ssisis", $codigo, $nombre, $id_categoria, $unidad_medida, $precio_unitario, $id);  // Asegúrate de vincular correctamente el precio_unitario

    if ($stmt->execute()) {
        // Redireccionar de vuelta con mensaje
        header("Location: editar_producto.php?id=$id&actualizado=1");
        exit;
    } else {
        echo "Error al actualizar el producto.";
    }
}
?>
