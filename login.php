<?php
session_start();
include 'conexion.php';

$mensaje = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    $stmt = $conexion->prepare("
        SELECT u.*, r.nombre AS rol_nombre 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id 
        WHERE u.correo = ?
    ");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($password, $usuario['password'])) {
        if ($usuario['activo'] == 0) {
            $mensaje = "Tu cuenta está inactiva ❌";
            $tipo = "error";
        } else {
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol_nombre'];
            $mensaje = "Bienvenido, " . $usuario['nombre'] . " 🚀";
            $tipo = "success";
            echo "<script>
                setTimeout(()=>{ window.location.href='principal.php'; }, 2000);
            </script>";
        }
    } else {
        $mensaje = "Correo o contraseña incorrectos ⚠️";
        $tipo = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Login Futurista</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Rubik:wght@400;600&display=swap" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'Rubik',sans-serif;
  height:100vh;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(-45deg,#141e30,#243b55,#0f2027,#2c5364);
  background-size:400% 400%;animation:gradientBG 12s ease infinite;
  overflow:hidden;color:white;
}
@keyframes gradientBG{0%{background-position:0% 50%;}50%{background-position:100% 50%;}100%{background-position:0% 50%;}}
#particles{position:absolute;width:100%;height:100%;z-index:-1;overflow:hidden;}
.circle{position:absolute;border-radius:50%;background:rgba(0,249,255,0.4);animation:float 20s infinite linear;}
@keyframes float{0%{transform:translateY(100vh) scale(0.5);}100%{transform:translateY(-10vh) scale(1);}}
.container{
  background:rgba(255,255,255,0.08);
  padding:50px 40px;border-radius:25px;backdrop-filter:blur(25px);
  box-shadow:0 0 40px rgba(0,255,255,0.4),inset 0 0 20px rgba(0,255,255,0.2);
  animation:fadeIn 1.4s ease-out,neonGlow 3s infinite alternate;
  max-width:420px;width:100%;text-align:center;
}
@keyframes fadeIn{0%{opacity:0;transform:translateY(40px);}100%{opacity:1;transform:translateY(0);}}
@keyframes neonGlow{0%{box-shadow:0 0 30px #00f9ff,inset 0 0 10px rgba(0,255,255,0.2);}100%{box-shadow:0 0 60px #00f9ff,inset 0 0 25px rgba(0,255,255,0.4);}}
.logo{width:120px;height:120px;border-radius:50%;margin-bottom:20px;box-shadow:0 0 25px #00f9ff;animation:pulse 2s infinite alternate;}
@keyframes pulse{0%{transform:scale(1);}100%{transform:scale(1.05);}}
h1{font-size:2rem;margin-bottom:20px;text-shadow:0 0 15px #00f9ff;}
form{display:flex;flex-direction:column;gap:15px;}
.input-box{position:relative;}
.input-box input{
  width:100%;padding:14px 45px 14px 18px;border:none;border-radius:50px;
  background:rgba(255,255,255,0.15);color:#fff;font-size:1rem;
  transition:0.3s ease;
}
.input-box i{
  position:absolute;right:18px;top:50%;transform:translateY(-50%);
  color:#00f9ff;font-size:18px;
}
.input-box input:focus{background:rgba(255,255,255,0.3);box-shadow:0 0 15px #00f9ff;outline:none;}
.btn{
  padding:14px;border:none;border-radius:50px;cursor:pointer;
  background:linear-gradient(90deg,#00f9ff,#0072ff);color:#fff;font-weight:600;
  font-size:1.1rem;transition:0.3s;
}
.btn:hover{transform:scale(1.08);box-shadow:0 0 30px #00f9ff;}
p{margin-top:15px;font-size:1rem;color:#ddd;}
a{color:#00c6ff;text-decoration:none;font-weight:600;}
a:hover{text-decoration:underline;}
.back-btn{
  margin-top:20px;display:inline-block;background:rgba(255,255,255,0.1);
  border:1px solid #00c6ff;color:#00c6ff;padding:10px 28px;border-radius:50px;
  transition:0.3s;
}
.back-btn:hover{background:#00c6ff;color:white;box-shadow:0 0 20px #00f9ff;}
@media(max-width:500px){.container{padding:35px 25px;}h1{font-size:1.6rem;}.logo{width:100px;height:100px;}}
</style>
</head>
<body>
<div id="particles"></div>
<div class="container">
  <img src="logo.png" alt="Logo" class="logo">
  <h1>Acceso al Sistema</h1>
  <form method="POST" action="">
    <div class="input-box">
      <input type="email" name="correo" placeholder="Correo electrónico" required autocomplete="email">
      <i class="fas fa-envelope"></i>
    </div>
    <div class="input-box">
      <input type="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
      <i class="fas fa-lock"></i>
    </div>
    <button type="submit" class="btn">🚀 Ingresar</button>
  </form>
  <p>¿No tienes cuenta? <a href="registrarse.php">Regístrate aquí</a></p>
  <a href="index.html" class="back-btn">⬅ Volver al inicio</a>
</div>

<script src="https://kit.fontawesome.com/1c020da525.js" crossorigin="anonymous"></script>
<script>
// partículas animadas
const particles=document.getElementById("particles");
for(let i=0;i<25;i++){
  let c=document.createElement("div");
  c.className="circle";
  let size=Math.random()*15+10;
  c.style.width=size+"px";c.style.height=size+"px";
  c.style.left=Math.random()*100+"%";
  c.style.animationDuration=(10+Math.random()*20)+"s";
  particles.appendChild(c);
}
</script>

<?php if (!empty($mensaje)): ?>
<script>
Swal.fire({
  icon: "<?= $tipo ?>",
  title: "<?= $mensaje ?>",
  showConfirmButton: false,
  timer: 2200,
  background: "rgba(20,20,20,0.9)",
  color: "#fff",
  toast: true,
  position: "top-end",
  timerProgressBar: true
});
</script>
<?php endif; ?>
</body>
</html>
