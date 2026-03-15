<?php
// Establecer zona horaria de Perú en PHP
date_default_timezone_set('America/Lima');

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "data_online");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Ajustar zona horaria también en MySQL
$conexion->query("SET time_zone = '-05:00'");
?>
