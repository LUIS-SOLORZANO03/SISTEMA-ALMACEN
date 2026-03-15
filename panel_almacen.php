<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Almacén</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Rubik', sans-serif;
      margin: 0;
      min-height: 100vh;
      color: white;
      overflow-x: hidden;
      background: radial-gradient(circle at top left, #0f2027, #203a43, #2c5364);
    }

    /* 🎆 Fondo partículas */
    canvas {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    h2 {
      text-align: center;
      margin: 30px 0;
      font-size: 2.8rem;
      font-weight: 700;
      background: linear-gradient(90deg, #00ffe7, #0088ff, #ff9100);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: textFlow 6s linear infinite;
    }
    @keyframes textFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* ✨ Cards */
    .card-custom {
      border: none;
      border-radius: 20px;
      padding: 2rem;
      text-align: center;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(12px);
      color: white;
      transition: all 0.4s ease;
      box-shadow: 0 6px 20px rgba(0,255,204,0.15);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.8s ease both;
    }
    .card-custom::before {
      content: "";
      position: absolute;
      top: -50%; left: -50%;
      width: 200%; height: 200%;
      background: conic-gradient(from 180deg, rgba(0,255,204,0.4), transparent 70%);
      animation: rotate 6s linear infinite;
      z-index: 0;
    }
    @keyframes rotate {
      100% { transform: rotate(360deg); }
    }
    .card-custom:hover {
      transform: translateY(-8px) scale(1.05);
      box-shadow: 0 10px 30px rgba(0,255,204,0.6);
    }
    .icon-box {
      font-size: 55px;
      margin-bottom: 15px;
      color: #00ffc3;
      text-shadow: 0 0 15px rgba(0,255,204,0.8);
      z-index: 1;
      position: relative;
      animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }
    .card-custom h5 {
      position: relative;
      z-index: 1;
    }

    /* 🔙 Botón Volver */
    .btn-volver {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 12px 25px;
      font-weight: 600;
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
      color: white;
      border: none;
      box-shadow: 0 4px 15px rgba(255,0,0,0.4);
      transition: all 0.3s ease;
    }
    .btn-volver:hover {
      transform: scale(1.1);
      box-shadow: 0 0 25px #ff416c, 0 0 15px #ff4b2b inset;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <!-- 🎆 Partículas -->
  <canvas id="particles"></canvas>

  <div class="container py-5">
    <h2><i class="bi bi-box-seam"></i> Panel de Almacén</h2>
    <div class="row g-4 justify-content-center">

      <!-- Almacén -->
      <div class="col-md-3">
        <a href="almacen.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-building"></i></div>
            <h5>Almacén</h5>
          </div>
        </a>
      </div>

      <!-- Módem -->
      <div class="col-md-3">
        <a href="modem.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-router"></i></div>
            <h5>Herramientas</h5>
          </div>
        </a>
      </div>

      <!-- Gestión de Módem -->
      <div class="col-md-3">
        <a href="gestion_modem.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-gear"></i></div>
            <h5>EPP</h5>
          </div>
        </a>
      </div>

      <!-- Diferencias 
      <div class="col-md-3">
        <a href="diferencias.php" class="text-decoration-none">
          <div class="card-custom">
            <div class="icon-box"><i class="bi bi-shuffle"></i></div>
            <h5>Diferencias</h5>
          </div>
        </a>
      </div>-->

    </div>
  </div>

  <!-- Botón Volver -->
  <a href="principal.php" class="btn-volver"><i class="bi bi-arrow-left-circle"></i> Volver</a>

  <script>
    // 🎆 Partículas animadas
    const canvas = document.getElementById("particles");
    const ctx = canvas.getContext("2d");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particles = [];
    for (let i = 0; i < 80; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        dx: (Math.random() - 0.5) * 1,
        dy: (Math.random() - 0.5) * 1,
        size: Math.random() * 3 + 1
      });
    }

    function drawParticles() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = "rgba(0,255,204,0.7)";
      particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fill();
        p.x += p.dx;
        p.y += p.dy;
        if (p.x < 0 || p.x > canvas.width) p.dx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.dy *= -1;
      });
      requestAnimationFrame(drawParticles);
    }
    drawParticles();

    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
  </script>
</body>
</html>
