<?php
include('conexion.php');

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch();

if ($user && password_verify($clave, $user['clave'])) {
    $_SESSION['usuario'] = $usuario;
    header("Location: principal.php");
} else {
    header("Location: login.php?error=Usuario o contraseña incorrectos");
}
