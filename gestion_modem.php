<?php
// rotulos.php
session_start();
include 'conexion.php';

///////////////////////
// CONFIGURACIONES
///////////////////////
$LOGO_PATH   = 'assets/logo.png';
$UPLOAD_DIR  = 'uploads';
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$MAX_SIZE_MB = 8;

///////////////////////
// UTILS
///////////////////////
function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function ensure_upload_dir($dir)
{
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

function validate_and_move_image($file, $destDir, $allowedMime, $maxSizeMb)
{
    if (empty($file['name'])) return [true, null];
    if ($file['error'] !== UPLOAD_ERR_OK) return [false, "Error al subir la imagen (código {$file['error']})."];
    if ($file['size'] > ($maxSizeMb * 1024 * 1024)) return [false, "La imagen supera {$maxSizeMb}MB."];

    $fi = new finfo(FILEINFO_MIME_TYPE);
    $mime = $fi->file($file['tmp_name']);
    if (!in_array($mime, $allowedMime)) return [false, "Formato no permitido. Usa JPG, PNG, WEBP o GIF."];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)) . "_" . time();
    $targetRel = rtrim($destDir, '/') . '/' . $basename . '.' . $ext;
    ensure_upload_dir($destDir);

    if (!move_uploaded_file($file['tmp_name'], $targetRel)) {
        return [false, "No se pudo mover el archivo subido."];
    }
    return [true, $targetRel];
}

function delete_file_if_exists($path)
{
    if ($path && is_file($path)) {
        @unlink($path);
    }
}

///////////////////////
// ACCIONES
///////////////////////
$mensaje = '';
$tipo_msg = 'info';

$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$editing = false;
$edit = [
    'id' => '',
    'cliente_id' => '',
    'cliente' => '',
    'rotulo' => '',
    'codigo_caja' => '',
    'foto_caja' => '',
    'fecha_registro' => '',
    'direccion' => '',
    'olt' => '',
    'board' => '',
    'puerto' => '',
    'serie_modem' => '',
    'observacion' => ''
];

if ($action === 'edit' && $id > 0) {
    $stmt = $conexion->prepare("
        SELECT r.*, c.nombres AS cliente, c.id AS cliente_id
        FROM rotulos r
        JOIN clientes c ON r.cliente_id = c.id
        WHERE r.id = ?");
    if (!$stmt) {
        die("Error al preparar consulta (edit): " . $conexion->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $edit = $row;
        $editing = true;
    } else {
        $mensaje = "No se encontró el rótulo.";
        $tipo_msg = 'danger';
    }
    $stmt->close();
}

if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del rótulo
    $stmt = $conexion->prepare("
        SELECT r.foto_caja, r.cliente_id 
        FROM rotulos r 
        WHERE r.id = ?");
    if (!$stmt) {
        die("Error al preparar consulta (delete select): " . $conexion->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        $foto       = $row['foto_caja'];
        $cliente_id = $row['cliente_id'];

        // Borrar el rótulo
        $stmtDel = $conexion->prepare("DELETE FROM rotulos WHERE id = ?");
        if (!$stmtDel) {
            die("Error al preparar consulta (delete rotulos): " . $conexion->error);
        }
        $stmtDel->bind_param("i", $id);
        if ($stmtDel->execute()) {
            // Borrar foto si existe
            delete_file_if_exists($foto);

            // Verificar si el cliente tiene más rótulos
            $stmtCheck = $conexion->prepare("SELECT COUNT(*) AS total FROM rotulos WHERE cliente_id = ?");
            if (!$stmtCheck) {
                die("Error al preparar consulta (check rotulos): " . $conexion->error);
            }
            $stmtCheck->bind_param("i", $cliente_id);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $count = $resCheck->fetch_assoc()['total'];
            $stmtCheck->close();

            // Si no tiene más rótulos, borrar cliente
            if ($count == 0) {
                $stmtCli = $conexion->prepare("DELETE FROM clientes WHERE id = ?");
                if ($stmtCli) {
                    $stmtCli->bind_param("i", $cliente_id);
                    $stmtCli->execute();
                    $stmtCli->close();
                }
                $mensaje = "Rótulo eliminado y cliente asociado también eliminado.";
            } else {
                $mensaje = "Rótulo eliminado correctamente. El cliente aún tiene otros rótulos.";
            }

            $tipo_msg = 'success';
        } else {
            $mensaje = "No se pudo eliminar el rótulo.";
            $tipo_msg = 'danger';
        }
        $stmtDel->close();
    } else {
        $mensaje = "No se encontró el rótulo.";
        $tipo_msg = 'warning';
    }

    header("Location: rotulos.php?msg=" . urlencode($mensaje) . "&type=" . $tipo_msg);
    exit;
}


if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
    $rotulo         = trim($_POST['rotulo'] ?? '');
    $codigo_caja    = trim($_POST['codigo_caja'] ?? '');
    $direccion      = trim($_POST['direccion'] ?? '');
    $olt            = trim($_POST['olt'] ?? '');
    $board          = trim($_POST['board'] ?? '');
    $puerto         = trim($_POST['puerto'] ?? '');
    $serie_modem    = trim($_POST['serie_modem'] ?? '');
    $observacion    = trim($_POST['observacion'] ?? '');
    $fecha_registro = !empty($_POST['fecha_registro']) ? $_POST['fecha_registro'] : date("Y-m-d");

    if (!$cliente_nombre || !$rotulo || !$codigo_caja || !$direccion || !$olt || !$board || !$puerto || !$serie_modem || !$observacion) {
        $mensaje = "Completa todos los campos requeridos.";
        $tipo_msg = 'warning';
    } else {
        [$ok, $ruta_img_or_err] = validate_and_move_image($_FILES['foto_caja'] ?? [], $UPLOAD_DIR, $ALLOWED_MIME, $MAX_SIZE_MB);
        if (!$ok || !$ruta_img_or_err) {
            $mensaje = $ruta_img_or_err ?: "La imagen es obligatoria.";
            $tipo_msg = 'danger';
        } else {
            $stmtC = $conexion->prepare("INSERT INTO clientes (nombres, fecha_registro) VALUES (?, ?)");
            if (!$stmtC) {
                delete_file_if_exists($ruta_img_or_err);
                die("Error al preparar consulta (insert cliente): " . $conexion->error);
            }
            $stmtC->bind_param("ss", $cliente_nombre, $fecha_registro);
            if ($stmtC->execute()) {
                $cliente_id = $stmtC->insert_id;
                $stmtC->close();

                $stmtR = $conexion->prepare("
                    INSERT INTO rotulos
                    (cliente_id, rotulo, foto_caja, codigo_caja, direccion, olt, board, puerto, serie_modem, observacion, fecha_registro)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmtR) {
                    delete_file_if_exists($ruta_img_or_err);
                    die("Error al preparar consulta (insert rotulos): " . $conexion->error);
                }
                $stmtR->bind_param(
                    "issssssssss",
                    $cliente_id,
                    $rotulo,
                    $ruta_img_or_err,
                    $codigo_caja,
                    $direccion,
                    $olt,
                    $board,
                    $puerto,
                    $serie_modem,
                    $observacion,
                    $fecha_registro
                );

                if ($stmtR->execute()) {
                    $stmtR->close();
                    $mensaje = "Cliente y rótulo registrados correctamente.";
                    $tipo_msg = 'success';
                    header("Location: rotulos.php?msg=" . urlencode($mensaje) . "&type=" . $tipo_msg);
                    exit;
                } else {
                    $stmtR->close();
                    delete_file_if_exists($ruta_img_or_err);
                    $mensaje = "Error al registrar el rótulo.";
                    $tipo_msg = 'danger';
                }
            } else {
                $stmtC->close();
                delete_file_if_exists($ruta_img_or_err);
                $mensaje = "Error al registrar el cliente.";
                $tipo_msg = 'danger';
            }
        }
    }
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rotulo_id      = (int)($_POST['rotulo_id'] ?? 0);
    $cliente_id     = (int)($_POST['cliente_id'] ?? 0);
    $cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
    $rotulo         = trim($_POST['rotulo'] ?? '');
    $codigo_caja    = trim($_POST['codigo_caja'] ?? '');
    $direccion      = trim($_POST['direccion'] ?? '');
    $olt            = trim($_POST['olt'] ?? '');
    $board          = trim($_POST['board'] ?? '');
    $puerto         = trim($_POST['puerto'] ?? '');
    $serie_modem    = trim($_POST['serie_modem'] ?? '');
    $observacion    = trim($_POST['observacion'] ?? '');
    $fecha_registro = !empty($_POST['fecha_registro']) ? $_POST['fecha_registro'] : date("Y-m-d");

    if ($rotulo_id <= 0 || $cliente_id <= 0) {
        $mensaje = "Datos inválidos para la edición.";
        $tipo_msg = 'danger';
    } else {
        $stmtCur = $conexion->prepare("SELECT foto_caja FROM rotulos WHERE id = ?");
        if (!$stmtCur) {
            die("Error al preparar consulta (select foto): " . $conexion->error);
        }
        $stmtCur->bind_param("i", $rotulo_id);
        $stmtCur->execute();
        $resCur = $stmtCur->get_result();
        $foto_actual = ($r = $resCur->fetch_assoc()) ? $r['foto_caja'] : '';
        $stmtCur->close();

        $nueva_ruta = $foto_actual;
        if (!empty($_FILES['foto_caja']['name'])) {
            [$ok, $ruta_img_or_err] = validate_and_move_image($_FILES['foto_caja'], $UPLOAD_DIR, $ALLOWED_MIME, $MAX_SIZE_MB);
            if (!$ok) {
                $mensaje = $ruta_img_or_err;
                $tipo_msg = 'danger';
            } else {
                $nueva_ruta = $ruta_img_or_err;
            }
        }

        if ($tipo_msg !== 'danger') {
            $stmtUC = $conexion->prepare("UPDATE clientes SET nombres = ? WHERE id = ?");
            if (!$stmtUC) {
                if ($nueva_ruta !== $foto_actual) delete_file_if_exists($nueva_ruta);
                die("Error al preparar consulta (update cliente): " . $conexion->error);
            }
            $stmtUC->bind_param("si", $cliente_nombre, $cliente_id);
            $okC = $stmtUC->execute();
            $stmtUC->close();

            $stmtUR = $conexion->prepare("
                UPDATE rotulos SET
                    rotulo = ?, codigo_caja = ?, foto_caja = ?,
                    direccion = ?, olt = ?, board = ?, puerto = ?, serie_modem = ?, observacion = ?, fecha_registro = ?
                WHERE id = ?");
            if (!$stmtUR) {
                if ($nueva_ruta !== $foto_actual) delete_file_if_exists($nueva_ruta);
                die("Error al preparar consulta (update rotulos): " . $conexion->error);
            }
            $stmtUR->bind_param(
                "ssssssssssi",
                $rotulo,
                $codigo_caja,
                $nueva_ruta,
                $direccion,
                $olt,
                $board,
                $puerto,
                $serie_modem,
                $observacion,
                $fecha_registro,
                $rotulo_id
            );
            $okR = $stmtUR->execute();
            $stmtUR->close();

            if ($okC && $okR) {
                if ($nueva_ruta !== $foto_actual) {
                    delete_file_if_exists($foto_actual);
                }
                $mensaje = "Rótulo actualizado correctamente.";
                $tipo_msg = 'success';
                header("Location: rotulos.php?msg=" . urlencode($mensaje) . "&type=" . $tipo_msg);
                exit;
            } else {
                if ($nueva_ruta !== $foto_actual) {
                    delete_file_if_exists($nueva_ruta);
                }
                $mensaje = "No se pudo actualizar el rótulo.";
                $tipo_msg = 'danger';
            }
        }
    }
}

if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
    $tipo_msg = $_GET['type'] ?? 'info';
}

///////////////////////
// LISTADO + BUSCADOR
///////////////////////
$busca = trim($_GET['q'] ?? '');
$sql = "SELECT r.*, c.nombres AS cliente FROM rotulos r JOIN clientes c ON r.cliente_id = c.id ";
$params = [];
$types  = '';
if ($busca !== '') {
    $sql .= "WHERE (c.nombres LIKE CONCAT('%',?,'%') OR r.rotulo LIKE CONCAT('%',?,'%') OR r.codigo_caja LIKE CONCAT('%',?,'%')) ";
    $params = [$busca, $busca, $busca];
    $types  = 'sss';
}
$sql .= "ORDER BY r.fecha_registro DESC";

try {
    $stmtList = $conexion->prepare($sql);
    if (!$stmtList) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }

    if (!empty($types) && !empty($params)) {
        $stmtList->bind_param($types, ...$params);
    }

    $stmtList->execute();
    $lista = $stmtList->get_result();
    $stmtList->close();
} catch (Exception $e) {
    die("Error SQL: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Control de Rótulos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #c9a227;
            /* dorado suave */
            --brand-alt: #e8d79a;
            /* dorado claro */
            --brand-contrast: #111;
            --card-radius: 1.25rem;
        }

        body {
            background: linear-gradient(180deg, #f8f9fb 0%, #eef1f6 100%);
            font-family: "Poppins", system-ui, sans-serif;
            color: #222;
        }

        /* HEADER */
        .app-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .05);
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .logo-wrap img {
            height: 42px;
            width: auto;
            border-radius: .5rem;
            object-fit: contain;
        }

        .preview img {
            border: 2px solid var(--brand);
            border-radius: .75rem;
            transition: transform .2s ease;
        }

        .preview img:hover {
            transform: scale(1.05);
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: .5px;
            background: linear-gradient(90deg, var(--brand), var(--brand-alt));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            display: inline-block;
        }

        /* CARD */
        .card-pro {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 8px 30px rgba(0, 0, 0, .06);
            overflow: hidden;
            transition: transform .2s ease, box-shadow .3s ease;
        }

        .card-pro:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, .08);
        }

        .card-pro .card-header {
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand-alt) 100%);
            color: #000;
            border: none;
            font-weight: 600;
        }

        /* BOTONES */
        .btn-brand {
            background: var(--brand);
            border: none;
            color: var(--brand-contrast);
            box-shadow: 0 6px 14px rgba(201, 162, 39, .35);
            transition: all .2s ease;
        }

        .btn-brand:hover {
            filter: brightness(.95);
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(201, 162, 39, .45);
        }

        /* TABLA */
        .table thead th {
            background: #121212;
            color: #fff;
            border: 0;
        }

        .table tbody tr:hover {
            background: rgba(201, 162, 39, 0.08);
        }

        /* IMÁGENES */
        .thumb {
            width: 84px;
            height: 84px;
            object-fit: cover;
            border-radius: .75rem;
            border: 1px solid #e5e7eb;
            transition: transform .2s ease;
        }

        .thumb:hover {
            transform: scale(1.05);
        }

        /* ACCIONES */
        .actions .btn {
            margin-right: .25rem;
        }

        /* BADGES */
        .badge-soft {
            background: #fff;
            border: 1px solid #e5e7eb;
            color: #333;
            border-radius: 999px;
            padding: .35rem .65rem;
            font-size: .85rem;
            transition: all .2s ease;
        }

        .badge-soft:hover {
            background: var(--brand);
            color: #111;
            border-color: var(--brand);
        }

        /* SEARCH */
        .search-input {
            border-radius: 999px;
            padding-left: 2.5rem;
            background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="%23999" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg>') no-repeat 12px center;
            background-size: 18px;
            transition: box-shadow .2s ease;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(201, 162, 39, .25);
        }

        /* TEXTO */
        .form-text small {
            color: #6c757d;
        }
    </style>


</head>

<body>
    <header class="app-header py-3">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="logo-wrap">
                <img src="<?php echo h($LOGO_PATH); ?>" alt="Logo" onerror="this.style.display='none'">
                <div>
                    <div class="brand-title">Control de Rótulos</div>
                    <div class="text-muted small">Cliente · Rótulo · Caja · Foto · Fecha</div>
                </div>
            </div>
            <a href="panel_almacen.php" class="btn btn-outline-dark btn-sm">Volver al panel</a>
        </div>
    </header>

    <main class="container my-4">

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo h($tipo_msg); ?> shadow-sm">
                <?php echo h($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- FORM CARD -->
        <div class="card card-pro mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $editing ? 'Editar Rótulo' : 'Nuevo Rótulo'; ?></h5>
                <span class="badge bg-light text-dark">Fecha manual permitida</span>
            </div>
            <div class="card-body">
                <form class="row g-3" method="post" enctype="multipart/form-data" action="rotulos.php?action=<?php echo $editing ? 'update' : 'create'; ?>">
                    <?php if ($editing): ?>
                        <input type="hidden" name="rotulo_id" value="<?php echo h($edit['id']); ?>">
                        <input type="hidden" name="cliente_id" value="<?php echo h($edit['cliente_id']); ?>">
                    <?php endif; ?>

                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <input type="text" name="cliente_nombre" class="form-control" value="<?php echo h($edit['cliente']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rótulo</label>
                        <input type="text" name="rotulo" class="form-control" value="<?php echo h($edit['rotulo']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Código de Caja</label>
                        <input type="text" name="codigo_caja" class="form-control" value="<?php echo h($edit['codigo_caja']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?php echo h($edit['direccion'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">OLT</label>
                        <input type="text" name="olt" class="form-control" value="<?php echo h($edit['olt'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">BOARD</label>
                        <input type="text" name="board" class="form-control" value="<?php echo h($edit['board'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PUERTO</label>
                        <input type="text" name="puerto" class="form-control" value="<?php echo h($edit['puerto'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">SERIE DE MODEM</label>
                        <input type="text" name="serie_modem" class="form-control" value="<?php echo h($edit['serie_modem'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha de Registro</label>
                        <input type="date" name="fecha_registro" class="form-control" value="<?php echo h($edit['fecha_registro'] ? substr($edit['fecha_registro'], 0, 10) : ''); ?>">
                        <div class="form-text"><small>Si no eliges fecha, se usará la actual.</small></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"><?php echo $editing ? 'Nueva Foto (opcional)' : 'Foto de la Caja'; ?></label>
                        <input type="file" name="foto_caja" class="form-control" accept="image/*" <?php echo $editing ? '' : 'required'; ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Observación</label>
                        <select name="observacion" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="Recuperacion" <?php echo ($edit['observacion'] ?? '') === 'Recuperacion' ? 'selected' : ''; ?>>Recuperación</option>
                            <option value="Averia" <?php echo ($edit['observacion'] ?? '') === 'Averia' ? 'selected' : ''; ?>>Avería</option>
                            <option value="Sistema" <?php echo ($edit['observacion'] ?? '') === 'Sistema' ? 'selected' : ''; ?>>Sistema</option>
                            <option value="Traslado" <?php echo ($edit['observacion'] ?? '') === 'Traslado' ? 'selected' : ''; ?>>Traslado</option>
                        </select>
                    </div>


                    <?php if ($editing && $edit['foto_caja']): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?php echo h($edit['foto_caja']); ?>" class="img-thumbnail" style="width: 100px;" alt="Foto actual">
                                <span class="text-muted small">Foto actual</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-brand px-4" type="submit">
                            <?php echo $editing ? 'Guardar cambios' : 'Guardar rótulo'; ?>
                        </button>
                        <?php if ($editing): ?>
                            <a class="btn btn-outline-secondary" href="rotulos.php">Cancelar edición</a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" type="reset">Limpiar</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- SEARCH + LIST -->
        <div class="card card-pro">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0">Rótulos registrados</h5>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form class="d-flex" method="get" action="rotulos.php">
                        <input type="text" class="form-control me-2" name="q" placeholder="Buscar por cliente, rótulo o código…" value="<?php echo h($busca); ?>">
                        <button class="btn btn-dark me-2" type="submit">Buscar</button>
                        <?php if (!empty($busca)): ?>
                            <a href="rotulos.php" class="btn btn-outline-secondary">Limpiar</a>
                        <?php endif; ?>
                    </form>
                    <a href="historial_rotulos.php" class="btn btn-primary">📜 Ver historial</a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Rótulo</th>
                                <th>Dirección</th>
                                <th>OLT</th>
                                <th>BOARD</th>
                                <th>PUERTO</th>
                                <th>Serie Modem</th>
                                <th>Código Caja</th>
                                <th>Observación</th>
                                <th>Foto</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($lista->num_rows > 0): ?>
                                <?php while ($row = $lista->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo h($row['cliente']); ?></td>
                                        <td><?php echo h($row['rotulo']); ?></td>
                                        <td><?php echo h($row['direccion']); ?></td>
                                        <td><?php echo h($row['olt']); ?></td>
                                        <td><?php echo h($row['board']); ?></td>
                                        <td><?php echo h($row['puerto']); ?></td>
                                        <td><?php echo h($row['serie_modem']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo h($row['codigo_caja']); ?></span></td>
                                        <td><?php echo h($row['observacion']); ?></td>
                                        <td>
                                            <?php if (!empty($row['foto_caja'])): ?>
                                                <img src="<?php echo h($row['foto_caja']); ?>" class="img-thumbnail" style="width: 60px;" alt="foto">
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo h($row['fecha_registro']); ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-dark" href="rotulos.php?action=edit&id=<?php echo (int)$row['id']; ?>">Editar</a>
                                            <form method="post" action="rotulos.php?action=delete&id=<?php echo (int)$row['id']; ?>"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Eliminar este rótulo? Esta acción no se puede deshacer.');">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">No hay rótulos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputFile = document.querySelector('input[name="foto_caja"]');
            const previewContainer = document.createElement('div');
            previewContainer.classList.add('mt-2');
            inputFile.parentElement.appendChild(previewContainer);

            inputFile.addEventListener('change', function() {
                const file = this.files[0];
                previewContainer.innerHTML = ''; // limpia previa
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('img-thumbnail');
                        img.style.width = '120px';
                        img.style.marginTop = '8px';
                        previewContainer.innerHTML = '<small class="text-muted d-block">Vista previa:</small>';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>

</body>

</html>