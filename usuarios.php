<?php
session_start();
include 'conexion.php';

// --- Seguridad ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('<div class="container mt-5"><div class="alert alert-danger">Acceso denegado. Solo administradores pueden acceder aquí.</div></div>');
}

$mensaje_js = "";
$tipo_alerta = "info";

// --- Agregar usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']); 
    $id_rol = (int)$_POST['rol'];
    $activo = (int)$_POST['activo'];
    $dni = isset($_POST['dni']) ? trim($_POST['dni']) : null;

    // Verificar correo duplicado
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $mensaje_js = "El correo ya está registrado.";
        $tipo_alerta = "warning";
    } else {
        // Hash de la contraseña
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password, id_rol, activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $nombre, $correo, $hash, $id_rol, $activo);

        if ($stmt->execute()) {
            $id_usuario = $conexion->insert_id;

            // Verificar y registrar técnico con DNI
            if (!empty($dni)) {
                $stmt_check = $conexion->prepare("SELECT id_tecnico FROM tecnicos WHERE dni = ?");
                $stmt_check->bind_param('s', $dni);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    $mensaje_js = "El DNI ya está registrado.";
                    $tipo_alerta = "warning";
                    $conexion->query("DELETE FROM usuarios WHERE id = $id_usuario");
                } else {
                    $stmt2 = $conexion->prepare("INSERT INTO tecnicos (id_tecnico, nombre, dni) VALUES (?, ?, ?)");
                    $stmt2->bind_param('iss', $id_usuario, $nombre, $dni);
                    $stmt2->execute();
                    $mensaje_js = "Usuario agregado correctamente.";
                    $tipo_alerta = "success";
                }
            } else {
                $mensaje_js = "Usuario agregado correctamente.";
                $tipo_alerta = "success";
            }
        } else {
            $mensaje_js = "Error al agregar usuario.";
            $tipo_alerta = "error";
        }
    }
}

// --- Eliminar usuario ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    if ($id_eliminar !== $_SESSION['id_usuario']) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $id_eliminar);
        $stmt->execute();
        $mensaje_js = "Usuario eliminado correctamente.";
        $tipo_alerta = "success";
    } else {
        $mensaje_js = "No puedes eliminar tu propio usuario.";
        $tipo_alerta = "error";
    }
}

// --- Editar usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $id_usuario = (int)$_POST['id_usuario'];
    $nuevo_nombre = trim($_POST['nuevo_nombre']);
    $nuevo_correo = trim($_POST['nuevo_correo']);
    $nuevo_rol = (int)$_POST['nuevo_rol'];
    $nuevo_activo = (int)$_POST['nuevo_activo'];
    $nueva_pass = trim($_POST['nueva_pass']);

    if (!empty($nueva_pass)) {
        $hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ?, id_rol = ?, activo = ?, password = ? WHERE id = ?");
        $stmt->bind_param('ssissi', $nuevo_nombre, $nuevo_correo, $nuevo_rol, $nuevo_activo, $hash, $id_usuario);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ?, id_rol = ?, activo = ? WHERE id = ?");
        $stmt->bind_param('ssiii', $nuevo_nombre, $nuevo_correo, $nuevo_rol, $nuevo_activo, $id_usuario);
    }

    if ($stmt->execute()) {
        $mensaje_js = "Usuario actualizado correctamente.";
        $tipo_alerta = "success";
    } else {
        $mensaje_js = "Error al actualizar usuario.";
        $tipo_alerta = "error";
    }
}

// --- Consultas ---
$usuarios = $conexion->query("SELECT u.id, u.nombre, u.correo, u.password, r.nombre AS rol, u.id_rol, u.activo 
                              FROM usuarios u 
                              JOIN roles r ON u.id_rol = r.id 
                              ORDER BY u.id ASC");
$roles = $conexion->query("SELECT * FROM roles ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d47a1, #1976d2, #42a5f5);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
        }
        .table thead {
            background: #1565c0;
            color: white;
        }
        .btn-add {
            background: linear-gradient(45deg, #4caf50, #2e7d32);
            color: #fff;
        }
        .btn-edit {
            background: linear-gradient(45deg, #ff9100, #ff5722);
            color: #fff;
        }
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>

    <h1 class="text-center mb-4">🚀 Gestión de Usuarios</h1>

    <!-- Botón abrir modal Agregar -->
    <div class="text-end mb-3">
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="fa fa-plus"></i> Agregar Usuario
        </button>
    </div>

    <!-- Tabla usuarios -->
    <div class="card bg-light text-dark p-3 shadow-lg rounded-4">
        <h3>📋 Lista de Usuarios</h3>
        <div class="table-responsive mt-3">
            <table class="table table-bordered align-middle text-center">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Contraseña</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($u = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['correo']) ?></td>
                        <td><?= htmlspecialchars($u['rol']) ?></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="password" class="form-control" value="<?= htmlspecialchars($u['password']) ?>" id="pass_<?= $u['id'] ?>" readonly>
                                <span class="input-group-text password-toggle" onclick="togglePassword('pass_<?= $u['id'] ?>', this)">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?= $u['activo'] ? "<span class='badge bg-success'>Activo</span>" : "<span class='badge bg-danger'>Inactivo</span>" ?>
                        </td>
                        <td>
                            <button class="btn btn-edit btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $u['id'] ?>">
                                <i class="fa fa-pen"></i>
                            </button>
                            <?php if ($u['id'] != $_SESSION['id_usuario']): ?>
                                <a href="?eliminar=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?')"><i class="fa fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal Editar -->
                    <div class="modal fade" id="modalEditar<?= $u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <input type="hidden" name="editar_usuario" value="1">
                                    <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">✏️ Editar Usuario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2"><input type="text" name="nuevo_nombre" class="form-control" value="<?= htmlspecialchars($u['nombre']) ?>" required></div>
                                        <div class="mb-2"><input type="email" name="nuevo_correo" class="form-control" value="<?= htmlspecialchars($u['correo']) ?>" required></div>
                                        <div class="mb-2"><input type="password" name="nueva_pass" class="form-control" placeholder="Nueva contraseña (opcional)"></div>
                                        <div class="mb-2">
                                            <select name="nuevo_rol" class="form-select" required>
                                                <?php
                                                $roles->data_seek(0);
                                                while ($rol = $roles->fetch_assoc()):
                                                    $sel = ($rol['id'] == $u['id_rol']) ? "selected" : "";
                                                    echo "<option value='{$rol['id']}' $sel>" . htmlspecialchars($rol['nombre']) . "</option>";
                                                endwhile;
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <select name="nuevo_activo" class="form-select">
                                                <option value="1" <?= $u['activo'] ? "selected" : "" ?>>Activo</option>
                                                <option value="0" <?= !$u['activo'] ? "selected" : "" ?>>Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary">Actualizar</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Agregar -->
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="agregar_usuario" value="1">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">➕ Agregar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2"><input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required></div>
                        <div class="mb-2"><input type="email" name="correo" class="form-control" placeholder="Correo electrónico" required></div>
                        <div class="mb-2"><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
                        <div class="mb-2">
                            <select name="rol" id="rol_select" class="form-select" required>
                                <option value="">Seleccionar Rol</option>
                                <?php
                                $roles->data_seek(0);
                                while ($rol = $roles->fetch_assoc()):
                                    echo "<option value='{$rol['id']}'>" . htmlspecialchars($rol['nombre']) . "</option>";
                                endwhile;
                                ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <select name="activo" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="mb-2 d-none" id="dni_field">
                            <input type="text" name="dni" class="form-control" placeholder="DNI del técnico">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="principal.php" class="btn btn-secondary mt-3"><i class="fa fa-home"></i> Volver al inicio</a>

    <?php if (!empty($mensaje_js)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: "<?= $tipo_alerta ?>",
            title: "<?= $mensaje_js ?>",
            confirmButtonText: "OK",
            background: "#1e293b",
            color: "#fff"
        }).then(() => {
            window.location.href = "usuarios.php";
        });
    </script>
    <?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(id, el) {
    let input = document.getElementById(id);
    let icon = el.querySelector("i");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Mostrar campo DNI solo si el rol es Técnico
const rolSelect = document.getElementById('rol_select');
const dniField = document.getElementById('dni_field');
rolSelect.addEventListener('change', () => {
    if (rolSelect.options[rolSelect.selectedIndex].text.toLowerCase() === 'tecnico') {
        dniField.classList.remove('d-none');
    } else {
        dniField.classList.add('d-none');
    }
});
</script>
</body>
</html>
