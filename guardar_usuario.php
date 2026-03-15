<?php
include('conexion.php');

$usuario = $_POST['usuario'];
$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);
if ($stmt->rowCount() > 0) {
    header("Location: registrar.php?error=El usuario ya existe");
    exit;
}

$stmt = $pdo->prepare("INSERT INTO usuarios (usuario, clave) VALUES (?, ?)");
$stmt->execute([$usuario, $clave]);

header("Location: login.php");
exit;
