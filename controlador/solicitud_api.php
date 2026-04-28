<?php
// filepath: controlador/solicitud_api.php
session_start();
include '../modelo/conexion.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'crear':
        crearSolicitud();
        break;
    case 'listar':
        listarSolicitudes();
        break;
    case 'mis_solicitudes':
        misSolicitudes();
        break;
    case 'detalle':
        detalleSolicitud();
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

function crearSolicitud() {
    global $conn;
    
    $producto_id = $_POST['producto_id'] ?? 0;
    $cantidad = intval($_POST['cantidad'] ?? 0);
    $observaciones = $_POST['observaciones'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    // Debug
    error_log("producto_id: $producto_id, cantidad: $cantidad, usuario_id: $usuario_id");
    
    if (!$producto_id || $cantidad <= 0 || !$usuario_id) {
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos', 'debug' => ['producto_id' => $producto_id, 'cantidad' => $cantidad, 'usuario_id' => $usuario_id]]);
        return;
    }
    
    // Verificar stock disponible
    $stmt = $conn->prepare("SELECT material, stock FROM inventario WHERE id = ? AND estado = 1");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado']);
        return;
    }
    
    $producto = $result->fetch_assoc();
    if ($producto['stock'] < $cantidad) {
        echo json_encode(['success' => false, 'mensaje' => 'Stock insuficiente. Stock actual: ' . $producto['stock']]);
        return;
    }
    
    // Crear solicitud
    $stmt = $conn->prepare("INSERT INTO solicitudes (usuario_id, observaciones) VALUES (?, ?)");
    $stmt->bind_param("is", $usuario_id, $observaciones);
    $stmt->execute();
    $solicitud_id = $stmt->insert_id;
    
    // Crear detalle
    $stmt = $conn->prepare("INSERT INTO detalle_solicitud (solicitud_id, inventario_id, cantidad_solicitada) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $solicitud_id, $producto_id, $cantidad);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => 'Solicitud creada correctamente', 'solicitud_id' => $solicitud_id]);
}

function misSolicitudes() {
    global $conn;
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    $sql = "SELECT s.id, s.estado, s.observaciones, s.fecha_solicitud, 
                   i.material, i.descripcion, ds.cantidad_solicitada, ds.cantidad_aprobada
            FROM solicitudes s
            JOIN detalle_solicitud ds ON s.id = ds.solicitud_id
            JOIN inventario i ON ds.inventario_id = i.id
            WHERE s.usuario_id = ?
            ORDER BY s.fecha_solicitud DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function listarSolicitudes() {
    global $conn;
    $estado = $_GET['estado'] ?? '';
    
    $sql = "SELECT s.id, s.estado, s.observaciones, s.fecha_solicitud, s.fecha_respuesta,
                   u.nombre_usuario, i.material, i.descripcion, ds.cantidad_solicitada, ds.cantidad_aprobada
            FROM solicitudes s
            JOIN detalle_solicitud ds ON s.id = ds.solicitud_id
            JOIN inventario i ON ds.inventario_id = i.id
            JOIN usuarios u ON s.usuario_id = u.id";
    
    if ($estado) {
        $sql .= " WHERE s.estado = '$estado'";
    }
    
    $sql .= " ORDER BY s.fecha_solicitud DESC";
    $result = $conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function detalleSolicitud() {
    global $conn;
    $id = $_GET['id'] ?? 0;
    
    $sql = "SELECT s.*, u.nombre_usuario as usuario_nombre
            FROM solicitudes s
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_assoc());
}