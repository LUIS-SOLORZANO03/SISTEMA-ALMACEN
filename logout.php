<?php
session_start();              // Inicia la sesión
session_unset();              // Elimina todas las variables de sesión
session_destroy();            // Destruye la sesión

// Redirige al login (ajusta la ruta si tu login está en otro lugar)
header("Location: index.html");
exit();
?>
