<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    if (empty($nombre) || empty($correo) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo no válido.";
    } else {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "El correo ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $rol_usuario = 1;
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password, id_rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $nombre, $correo, $hash, $rol_usuario);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Error al registrar usuario. Intente de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Registro - Bodega</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Rubik:wght@400;600&display=swap" rel="stylesheet" />
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Rubik',sans-serif;
  height:100vh;
  display:flex;justify-content:center;align-items:center;
  background:radial-gradient(circle at top,#0f2027,#203a43,#2c5364);
  overflow:hidden;color:white;
}
body::before{
  content:"";position:absolute;top:0;left:0;width:100%;height:100%;
  background:linear-gradient(-45deg,#1d2b64,#f8cdda,#2c5364,#ff7eb9);
  background-size:400% 400%;
  animation:gradientBG 18s ease infinite;
  z-index:-2;opacity:0.8;
}
@keyframes gradientBG{
  0%{background-position:0% 50%}
  50%{background-position:100% 50%}
  100%{background-position:0% 50%}
}
.particles{
  position:absolute;top:0;left:0;width:100%;height:100%;
  z-index:-1;overflow:hidden;pointer-events:none;
}
.particles span{
  position:absolute;display:block;width:20px;height:20px;
  background:rgba(255,255,255,.2);
  animation:float 20s linear infinite;
  bottom:-150px;border-radius:50%;
}
.particles span:nth-child(1){left:25%;width:25px;height:25px;animation-delay:0s;animation-duration:12s}
.particles span:nth-child(2){left:10%;width:15px;height:15px;animation-delay:2s;animation-duration:18s}
.particles span:nth-child(3){left:70%;width:20px;height:20px;animation-delay:4s;animation-duration:15s}
.particles span:nth-child(4){left:40%;width:30px;height:30px;animation-delay:0s;animation-duration:20s}
.particles span:nth-child(5){left:65%;width:15px;height:15px;animation-delay:3s;animation-duration:25s}
@keyframes float{
  0%{transform:translateY(0) rotate(0deg);opacity:1}
  100%{transform:translateY(-1000px) rotate(720deg);opacity:0}
}

/* --- FORM --- */
.form-container{
  background:rgba(255,255,255,0.05);
  padding:45px 35px;border-radius:25px;
  backdrop-filter:blur(25px) saturate(180%);
  box-shadow:0 15px 50px rgba(0,255,255,.3), inset 0 0 25px rgba(0,255,255,.2);
  text-align:center;max-width:420px;width:100%;
  animation:fadeIn 1.5s ease-out;
}
@keyframes fadeIn{
  from{opacity:0;transform:translateY(40px) scale(.9)}
  to{opacity:1;transform:translateY(0) scale(1)}
}
.form-container img{
  width:120px;height:120px;border-radius:50%;object-fit:cover;
  margin-bottom:25px;
  animation:logoFloat 4s ease-in-out infinite,glowPulse 2s infinite alternate;
}
@keyframes logoFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes glowPulse{from{box-shadow:0 0 20px #00f9ff}to{box-shadow:0 0 40px #00f9ff,0 0 60px #0ff}}

/* --- INPUTS --- */
h2{margin-bottom:25px;font-size:2rem;font-family:'Orbitron',sans-serif;text-shadow:0 0 12px #00f9ff}
.form-container .input-box{position:relative;margin-bottom:18px}
.form-container input{
  width:100%;padding:14px 45px 14px 18px;border:none;border-radius:50px;
  background:rgba(255,255,255,.12);color:#fff;font-size:1rem;
  transition:.3s;outline:none;
}
.form-container input:focus{background:rgba(255,255,255,.25);box-shadow:0 0 15px #00f9ff}
.form-container .input-box i{
  position:absolute;right:15px;top:50%;transform:translateY(-50%);
  color:#00f9ff;font-size:18px;
}

/* --- BOTÓN --- */
.form-container button{
  width:100%;padding:14px 0;border:none;border-radius:50px;
  font-size:1.15rem;font-weight:600;cursor:pointer;
  background:linear-gradient(90deg,#00f9ff,#0072ff);color:#fff;
  box-shadow:0 0 15px #00f9ff;position:relative;overflow:hidden;
  transition:transform .3s;
}
.form-container button:hover{transform:scale(1.08)}
.form-container button::after{
  content:"";position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:rgba(255,255,255,.2);transform:skewX(-20deg);
  transition:.5s;
}
.form-container button:hover::after{left:200%}

/* --- LINKS --- */
.error{color:#ff6b6b;font-size:14px;margin-bottom:15px}
.form-container a{color:#00c6ff;text-decoration:none;font-weight:600}
.form-container a:hover{text-decoration:underline}
.volver{display:inline-block;margin-top:15px;padding:10px 20px;
  border-radius:50px;background:rgba(255,255,255,.15);color:white;
  transition:.3s}
.volver:hover{background:rgba(0,255,255,.3);box-shadow:0 0 15px #00f9ff}

/* --- RESPONSIVE --- */
@media(max-width:500px){
  .form-container{padding:35px 25px}
  h2{font-size:1.6rem}
  .form-container img{width:100px;height:100px}
}
</style>
</head>
<body>
<div class="particles">
  <span></span><span></span><span></span><span></span><span></span>
</div>

<div class="form-container">
    <img src="logo.png" alt="Logo" />
    <h2>Crear Cuenta</h2>

    <?php if (isset($error)) echo "<div class='error'>{$error}</div>"; ?>

    <form method="POST" action="">
        <div class="input-box">
            <input type="text" name="nombre" placeholder="Tu nombre" required />
            <i class="fas fa-user"></i>
        </div>
        <div class="input-box">
            <input type="email" name="correo" placeholder="Correo electrónico" required />
            <i class="fas fa-envelope"></i>
        </div>
        <div class="input-box">
            <input type="password" name="password" placeholder="Contraseña" required />
            <i class="fas fa-lock"></i>
        </div>
        <button type="submit">Registrarse</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    <a class="volver" href="index.html">← Volver</a>
</div>
<!-- ICONOS -->
<script src="https://kit.fontawesome.com/1c020da525.js" crossorigin="anonymous"></script>
</body>
</html>
