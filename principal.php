<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'] ?? '';
$esAdmin = $rol === 'admin';
$esAlmacenero = $rol === 'almacenero';
$esReporte = $rol === 'reporte';
$esUsuario = $rol === 'usuario';
$esTecnico = $rol === 'tecnico';
$esCajero = $rol === 'cajero';

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Panel Principal</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      font-family: 'Rubik', sans-serif;
      background: #0d0d0d;
      color: white;
      min-height: 100vh;
      margin: 0;
      padding-top: 100px;
      overflow: hidden;
    }

    /* === Fondo de partículas === */
    canvas#bgCanvas {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    /* === Navbar futurista === */
    .navbar-custom {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: rgba(10, 10, 10, 0.6);
      backdrop-filter: blur(18px);
      padding: 15px 40px;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 20px rgba(0, 255, 204, 0.5);
      border-bottom: 1px solid rgba(0, 255, 255, 0.2);
      animation: slideDown 1.2s ease;
    }

    .navbar-custom h1 {
      font-size: 1.8rem;
      font-weight: 700;
      font-family: 'Orbitron', sans-serif;
      color: #00ffe7;
      text-shadow: 0 0 12px #00ffe7, 0 0 24px #0088ff;
    }

    .navbar-custom a {
      text-decoration: none;
      font-weight: 600;
      padding: 10px 24px;
      border-radius: 50px;
      background: linear-gradient(90deg, #ff0077, #ff0059, #ff4b2b);
      color: #fff;
      transition: all 0.4s ease;
      box-shadow: 0 0 15px #ff0077;
    }

    .navbar-custom a:hover {
      transform: scale(1.12);
      box-shadow: 0 0 25px #ff4b2b, 0 0 40px #ff0077 inset;
    }

    /* === Logo con efecto holograma === */
    .empresa-logo {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      object-fit: cover;
      display: block;
      margin: 30px auto;
      box-shadow: 0 0 40px #00ffe7, 0 0 80px rgba(0, 255, 204, 0.4);
      animation: floatLogo 5s ease-in-out infinite, fadeIn 2s ease-in-out;
    }

    @keyframes floatLogo {

      0%,
      100% {
        transform: translateY(0) rotate(0deg);
      }

      50% {
        transform: translateY(-15px) rotate(5deg);
      }
    }

    /* === Contenedor principal === */
    .container {
      max-width: 1200px;
      width: 90%;
      animation: fadeIn 1s ease-out;
      text-align: center;
    }

    h2 {
      font-size: 2.8rem;
      margin-bottom: 40px;
      font-weight: 700;
      font-family: 'Orbitron', sans-serif;
      background: linear-gradient(90deg, #00ffe7, #0088ff);
      -webkit-background-clip: text;
      background-clip: text;
      /* agregado para navegadores que lo soporten */
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 20px rgba(0, 255, 204, 0.8);
    }


    /* === Cards holográficas === */
    .card-option {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(0, 255, 255, 0.15);
      border-radius: 25px;
      box-shadow: 0 6px 30px rgba(0, 255, 204, 0.2);
      transition: all 0.4s ease;
      color: white;
      backdrop-filter: blur(20px);
      padding: 2rem;
      height: 100%;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      transform-style: preserve-3d;
      perspective: 1000px;
      opacity: 0;
      animation: cardAppear 1s ease forwards;
    }

    .card-option:nth-child(1) {
      animation-delay: 0.4s;
    }

    .card-option:nth-child(2) {
      animation-delay: 0.6s;
    }

    .card-option:nth-child(3) {
      animation-delay: 0.8s;
    }

    .card-option:nth-child(4) {
      animation-delay: 1s;
    }

    .card-option:hover {
      transform: translateY(-15px) scale(1.05);
      box-shadow: 0 0 40px #00ffe7, 0 0 60px rgba(0, 136, 255, 0.5);
    }

    .card-option h4 {
      font-size: 1.7rem;
      margin-bottom: 15px;
      font-family: 'Orbitron', sans-serif;
      color: #00ffe7;
      text-shadow: 0 0 10px #00ffe7, 0 0 20px #0088ff;
    }

    .card-option p {
      flex-grow: 1;
      margin-bottom: 15px;
      font-size: 1rem;
      color: #ccc;
    }

    .card-option a {
      font-weight: 600;
      border-radius: 50px;
      padding: 12px 20px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .card-option a:hover {
      transform: scale(1.1);
      box-shadow: 0 0 20px #00ffe7;
    }

    /* === Animaciones === */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(40px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes cardAppear {
      from {
        opacity: 0;
        transform: translateY(50px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    @keyframes slideDown {
      from {
        transform: translateY(-100%);
      }

      to {
        transform: translateY(0);
      }
    }

    @media (max-width: 500px) {
      h2 {
        font-size: 2rem;
      }

      .card-option h4 {
        font-size: 1.3rem;
      }

      .card-option p {
        font-size: 0.9rem;
      }

      .empresa-logo {
        width: 120px;
        height: 120px;
      }
    }
  </style>
</head>

<body>
  <canvas id="bgCanvas"></canvas>

  <div class="navbar-custom">
    <h1>🚀 Panel Futurista</h1>
    <a href="logout.php"><i class="fas fa-power-off me-2"></i> Cerrar sesión</a>
  </div>

  <img src="logo.png" alt="Logo de la empresa" class="empresa-logo" />

  <div class="container">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> </h2>

    <div class="row justify-content-center g-4">
      <?php if ($esAlmacenero || $esAdmin): ?>
        <div class="col-md-4">
          <div class="card card-option">
            <h4>📦 Almacén</h4>
            <p>Gestión de inventario, diferencias y dispositivos de red.</p>
            <a href="panel_almacen.php" class="btn btn-primary btn-lg shadow-sm rounded-pill px-4">
              <i class="fas fa-warehouse me-2"></i> Ir al Panel
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($esAdmin || $esAlmacenero): ?>
        <div class="col-md-4">
          <div class="card card-option">
            <h4>📊 Reportes</h4>
            <p>Accede a recibos, reportes técnicos y estadísticas.</p>
            <a href="panel_reporte.php" class="btn btn-warning btn-lg shadow-sm rounded-pill px-4">
              <i class="fas fa-chart-line me-2"></i> Ver Reportes
            </a>
          </div>
        </div>
      <?php endif; ?>
      <?php if ($esAdmin || $esAlmacenero): ?>
        <div class="col-md-4">
          <div class="card card-option">
            <h4>📊 Reportes Ingresos</h4>
            <p>Accede a recibos, reportes técnicos y estadísticas.</p>
            <a href="panel_reporte.php" class="btn btn-warning btn-lg shadow-sm rounded-pill px-4">
              <i class="fas fa-chart-line me-2"></i> Ver Reportes
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($esAdmin): ?>
        <div class="col-md-4">
          <div class="card card-option">
            <h4>👥 Usuarios</h4>
            <p>Gestión de usuarios del sistema.</p>
            <a href="usuarios.php" class="btn btn-success btn-lg shadow-sm rounded-pill px-4">
              <i class="fas fa-users me-2"></i> Ver Usuarios
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($esTecnico || $esAdmin): ?>
        <div class="col-md-4">
          <div class="card card-option">
            <h4>👷 Técnicos</h4>
            <p>Ingresa materiales, cuadrillas y turnos asignados.</p>
            <a href="tecnicos.php" class="btn btn-danger btn-lg shadow-sm rounded-pill px-4">
              <i class="fas fa-tools me-2"></i> Entrar al Módulo
            </a>
          </div>
        </div>
      <?php endif; ?>
      <?php if ($esAdmin || $esUsuario): ?>
        <div class="col-md-4">
          <div class="card card-option">
            <h4>🧾 Recibos</h4>
            <p>Gestiona, emite y consulta los recibos generados en el sistema.</p>
            <a href="recibos_login.php" class="btn btn-success btn-lg shadow-sm rounded-pill px-4">
              <i class="fas fa-file-invoice-dollar me-2"></i> Entrar al Módulo
            </a>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // === Fondo Partículas ===
    const canvas = document.getElementById("bgCanvas");
    const ctx = canvas.getContext("2d");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particles = [];

    class Particle {
      constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 2 + 1;
        this.speedX = (Math.random() - 0.5) * 1.5;
        this.speedY = (Math.random() - 0.5) * 1.5;
      }
      update() {
        this.x += this.speedX;
        this.y += this.speedY;
        if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
        if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = "#00ffe7";
        ctx.fill();
      }
    }

    function init() {
      particles = [];
      for (let i = 0; i < 100; i++) {
        particles.push(new Particle());
      }
    }

    function animate() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particles.forEach((p, i) => {
        p.update();
        p.draw();
        for (let j = i; j < particles.length; j++) {
          const dx = p.x - particles[j].x;
          const dy = p.y - particles[j].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < 120) {
            ctx.beginPath();
            ctx.strokeStyle = "rgba(0,255,231,0.1)";
            ctx.lineWidth = 1;
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(particles[j].x, particles[j].y);
            ctx.stroke();
          }
        }
      });
      requestAnimationFrame(animate);
    }

    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
      init();
    });

    init();
    animate();
  </script>
</body>

</html>