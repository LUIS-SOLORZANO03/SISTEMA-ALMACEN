<?php
include 'conexion.php';

if(isset($_POST['id_categoria'])){
    $id_categoria = intval($_POST['id_categoria']);
    $sql = "SELECT id_producto, material FROM productos WHERE id_categoria = $id_categoria ORDER BY material ASC";
    $result = $conexion->query($sql);

    if($result->num_rows > 0){
        echo '<option value="">-- Selecciona Material --</option>';
        while($row = $result->fetch_assoc()){
            echo '<option value="'.$row['id_producto'].'">'.$row['material'].'</option>';
        }
    }else{
        echo '<option value="">No hay materiales</option>';
    }
}
?>
