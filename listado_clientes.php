<?php
$conexion = new mysqli("localhost", "root", "", "data_online");
if ($conexion->connect_error) {
  die("Error DB: " . $conexion->connect_error);
}
$result = $conexion->query("SELECT * FROM clientes ORDER BY creado DESC");
?>
<table class="table table-striped table-hover mb-0">
  <thead class="table-primary">
    <tr>
      <th>#</th>
      <th>Documento</th>
      <th>Nombre / Razón Social</th>
      <th>Celular</th>
      <th>Email</th>
      <th>Dirección</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= $row['tipo_doc'] ?>: <?= $row['nro_doc'] ?></td>
      <td><?= $row['nombre'] ?: $row['razon_social'] ?></td>
      <td><?= $row['celular1'] ?></td>
      <td><?= $row['email'] ?></td>
      <td><?= $row['direccion'] ?></td>
      <td>
        <a href="servicios.php?cliente_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">➕ Agregar Servicio</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
