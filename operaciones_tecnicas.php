<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}
$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>⚡ Operaciones Técnicas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: 'Rubik', sans-serif;
      background: linear-gradient(-45deg, #141E30, #243B55, #0f2027, #203a43);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      color: white;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }
    .container {
      text-align: center;
    }
    h2 {
      margin-bottom: 40px;
      text-shadow: 0 0 15px rgba(0,255,204,0.9);
    }
    .card-option {
      background: rgba(255,255,255,0.1);
      border: none;
      border-radius: 20px;
      padding: 30px;
      color: white;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      transition: all 0.3s ease-in-out;
      height: 100%;
    }
    .card-option:hover {
      transform: translateY(-10px) scale(1.05);
      box-shadow: 0 20px 50px rgba(0,255,204,0.5);
    }
    .card-option h4 {
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>⚡ Módulos Técnicos - Bienvenido <?= htmlspecialchars($usuario) ?></h2>
    <div class="row justify-content-center g-4">

      <!-- Instalacion -->
      <div class="col-md-4">
        <div class="card card-option">
          <h4>📍 Instalación</h4>
          <p>Registra y consulta materiales usados en campo.</p>
          <a href="campo.php" class="btn btn-success w-100 rounded-pill fw-semibold">Entrar a Campo</a>
        </div>
      </div>

      <!-- Planta Externa -->
      <div class="col-md-4">
        <div class="card card-option">
          <h4>🌐 Planta Externa</h4>
          <p>Gestión de materiales y actividades en planta externa.</p>
          <a href="planta_externa.php" class="btn btn-info w-100 rounded-pill fw-semibold">Entrar a Planta Externa</a>
        </div>
      </div>

    </div>

    <div class="mt-4">
      <a href="principal.php" class="btn btn-secondary rounded-pill px-4">⬅ Volver al Panel Principal</a>
    </div>
  </div>
</body>
</html>
