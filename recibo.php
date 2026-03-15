<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "data_online");
if ($conexion->connect_error) { die("Error DB: " . $conexion->connect_error); }

$id = $_GET['id'] ?? 0;

$sql = "SELECT s.id, s.plan, s.direccion AS direccion_serv, s.creado,
               c.nombre, c.razon_social, c.nro_doc, c.direccion, c.celular1, c.email
        FROM servicios s
        INNER JOIN clientes c ON s.cliente_id = c.id
        WHERE s.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recibo</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 10px;
      display: flex;
      justify-content: center;
    }
    .recibo {
      width: 80mm; /* Ajuste para ticketera (80mm) */
      border: 1px dashed #000;
      padding: 15px;
      font-size: 12px;
      text-align: left;
    }
    .titulo {
      text-align: center;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .logo {
      display: block;
      margin: 0 auto 10px;
      max-width: 70px;
    }
    .info {
      margin-bottom: 5px;
    }
    .info strong {
      display: inline-block;
      width: 100px;
    }
    .linea {
      border-top: 1px dashed #000;
      margin: 10px 0;
    }
    .footer {
      text-align: center;
      margin-top: 10px;
      font-size: 12px;
    }
    @media print {
      body { margin:0; }
      .recibo { border:none; }
      .no-print { display:none; }
    }
  </style>
</head>
<body>
  <div class="recibo">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="titulo">📡 RECIBO DE SERVICIO</div>
    <div class="info"><strong>Recibo N°:</strong> <?= $data['id'] ?></div>
    <div class="info"><strong>Fecha:</strong> <?= $data['creado'] ?></div>
    <div class="linea"></div>
    <div class="info"><strong>Cliente:</strong> <?= $data['nombre'] ?: $data['razon_social'] ?></div>
    <div class="info"><strong>Documento:</strong> <?= $data['nro_doc'] ?></div>
    <div class="info"><strong>Dirección:</strong> <?= $data['direccion'] ?></div>
    <div class="info"><strong>Celular:</strong> <?= $data['celular1'] ?></div>
    <div class="info"><strong>Email:</strong> <?= $data['email'] ?></div>
    <div class="linea"></div>
    <div class="info"><strong>Plan:</strong> <?= $data['plan'] ?></div>
    <div class="info"><strong>Zona:</strong> <?= $data['direccion_serv'] ?></div>
    <div class="linea"></div>
    <div class="footer">Gracias por confiar en nosotros 💙</div>
  </div>

  <br>
  <button onclick="window.print()" class="no-print">🖨 Imprimir</button>
</body>
</html>
