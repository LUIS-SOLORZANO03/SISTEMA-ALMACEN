<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Reportes</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Rubik', sans-serif;
      background: linear-gradient(-45deg, #141e30, #243b55, #1e3c72, #2a5298);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      min-height: 100vh;
      color: white;
      overflow-x: hidden;
      position: relative;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    h2 {
      text-align: center;
      margin: 30px 0;
      font-size: 2.8rem;
      font-weight: 700;
      text-shadow: 0 0 20px rgba(0,200,255,1);
      letter-spacing: 1.5px;
      animation: fadeInDown 1s ease;
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-50px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .card-custom {
      border: none;
      border-radius: 25px;
      padding: 2rem;
      text-align: center;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(12px);
      color: white;
      transition: all 0.4s ease;
      box-shadow: 0 6px 25px rgba(0,200,255,0.25);
      transform-style: preserve-3d;
      animation: fadeInUp 1s ease;
    }
    .card-custom:hover {
      transform: translateY(-10px) rotateX(10deg) rotateY(-5deg) scale(1.05);
      box-shadow: 0 12px 35px rgba(0,200,255,0.7);
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(50px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .icon-box {
      font-size: 60px;
      margin-bottom: 20px;
      color: #00e5ff;
      text-shadow: 0 0 20px rgba(0,229,255,0.9);
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.15); }
    }
    .btn-volver {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 14px 28px;
      font-weight: 700;
      font-size: 1.1rem;
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
      color: white;
      border: none;
      box-shadow: 0 6px 20px rgba(255,0,0,0.5);
      transition: all 0.3s ease;
      z-index: 1000;
    }
    .btn-volver:hover {
      transform: scale(1.15);
      box-shadow: 0 0 25px #ff416c, 0 0 15px #ff4b2b inset;
    }
    /* Partículas de fondo */
    #particles-js {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 0;
    }
    .container {
      position: relative;
      z-index: 1;
    }
  </style>
</head>
<body>
  <!-- Fondo animado con partículas -->
  <div id="particles-js"></div>

  <div class="container py-5">
    <h2><i class="bi bi-clipboard-data"></i> Panel de Reportes</h2>
    <div class="row g-4 justify-content-center">

      <!-- Recibos -->
      <div class="col-md-4">
        <a href="clientes.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-receipt"></i></div>
            <h5>Tecnicos</h5>
          </div>
        </a>
      </div>

      <!-- Reportes -->
      <div class="col-md-4">
        <a href="reportes.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-bar-chart"></i></div>
            <h5>Reportes</h5>
          </div>
        </a>
      </div>

      <!-- Reportes Técnicos 
      <div class="col-md-4">
        <a href="reportes_tecnicos.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-tools"></i></div>
            <h5>Reportes Técnicos</h5>
          </div>
        </a>
      </div> -->

    </div>
  </div>

  <!-- Botón Volver -->
  <a href="principal.php" class="btn-volver"><i class="bi bi-arrow-left-circle"></i> Volver</a>

  <!-- Script partículas -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
  <script>
    particlesJS("particles-js", {
      particles: {
        number: { value: 80 },
        size: { value: 3 },
        move: { speed: 2 },
        line_linked: { enable: true, distance: 150, color: "#00e5ff" },
        color: { value: "#00e5ff" }
      }
    });
  </script>
</body>
</html>
