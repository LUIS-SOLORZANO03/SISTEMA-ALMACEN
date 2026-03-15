<?php
require_once "conexion.php";
session_start();
header("Content-Type: application/json");

// 🔐 Seguridad
if (!isset($_SESSION['tecnico'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$tecnico_id = (int)($_POST['tecnico_id'] ?? 0);
if ($tecnico_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Técnico inválido']);
    exit;
}

// 📦 Datos recibidos
$clientes = $_POST['cliente'] ?? [];
$cuads    = $_POST['cuadrilla'] ?? [];
$turnos   = $_POST['turno'] ?? [];
$cats     = $_POST['categoria'] ?? [];
$mats     = $_POST['material'] ?? [];
$cants    = $_POST['cantidad'] ?? [];
$obs      = $_POST['observacion'] ?? [];

// 🆔 Obtener DNI del técnico
$dniStmt = $conexion->prepare("SELECT dni FROM tecnicos WHERE id_tecnico = ?");
$dniStmt->bind_param("i", $tecnico_id);
$dniStmt->execute();
$resDni = $dniStmt->get_result()->fetch_assoc();
if (!$resDni) {
    echo json_encode(['status' => 'error', 'message' => 'Técnico no encontrado']);
    exit;
}
$dni = $resDni['dni'];

// 🔄 Transacción
$conexion->begin_transaction();
try {
    // Insertar registro técnico
    $stmt = $conexion->prepare("
        INSERT INTO tecnicos_registros
        (cliente, id_categoria, dni, turno, cuadrilla, producto, cantidad, observacion, fecha_registro, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'activo')
    ");

    // Insertar salida de producto
    $salida = $conexion->prepare("
        INSERT INTO salidas_productos
        (id_producto, cantidad, fecha, motivo, personal_id)
        VALUES (?, ?, CURDATE(), 'Campo', ?)
    ");

    for ($i = 0; $i < count($clientes); $i++) {
        $cliente = trim($clientes[$i] ?? '');
        if (!$cliente) continue;

        $cuad   = (int)($cuads[$i] ?? 0);
        $turno  = $turnos[$i] ?? 'Mañana';
        $cat    = $cats[$i] ?? '';
        $mat    = $mats[$i] ?? '';
        $cant   = (int)($cants[$i] ?? 0);
        $ob     = $obs[$i] ?? '';

        // ✅ Buscar id_categoria
        $idcat = null;
        if ($cat) {
            $c = $conexion->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ? LIMIT 1");
            $c->bind_param("i", $cat);
            $c->execute();
            if ($r = $c->get_result()->fetch_assoc()) {
                $idcat = $r['id_categoria'];
            }
            $c->close();
        }

        // ✅ Producto: viene como "id__nombre"
        $prod_id = null;
        $prod_name = $mat;
        if (strpos($mat, "__") !== false) {
            list($id, $n) = explode("__", $mat, 2);
            $prod_id = (int)$id;
            $prod_name = $n;
        }

        // Guardar en tecnicos_registros
        $stmt->bind_param(
            "sisssiss",
            $cliente,
            $idcat,
            $dni,
            $turno,
            $cuad,
            $prod_name,
            $cant,
            $ob
        );
        $stmt->execute();

        // Guardar salida de producto si aplica
        if ($prod_id && $cant > 0) {
            $salida->bind_param("iii", $prod_id, $cant, $tecnico_id);
            $salida->execute();
        }
    }

    $conexion->commit();
    echo json_encode(['status' => 'success', 'message' => '✅ Registros guardados correctamente']);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['status' => 'error', 'message' => '❌ Error interno: ' . $e->getMessage()]);
}
