<?php
// filepath: controlador/admin_api.php
session_start();
include '../modelo/conexion.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'listar_usuarios':
        listarUsuarios();
        break;
    case 'crear_usuario':
        crearUsuario();
        break;
    case 'toggle_usuario':
        toggleUsuario();
        break;
    case 'crear_producto':
        crearProducto();
        break;
    case 'eliminar_producto':
        eliminarProducto();
        break;
    case 'reportes':
        getReportes();
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

function listarUsuarios() {
    global $conn;
    $sql = "SELECT id, nombre_usuario, rol, estado, created_at FROM usuarios ORDER BY id DESC";
    $result = $conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function crearUsuario() {
    global $conn;
    
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';
    
    if (!$nombre_usuario || !$contrasena) {
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
        return;
    }
    
    // Verificar si existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $nombre_usuario);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'mensaje' => 'El usuario ya existe']);
        return;
    }
    
    // Crear usuario
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre_usuario, $hash, $rol);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => 'Usuario creado correctamente']);
}

function toggleUsuario() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    $estado = $_POST['estado'] ?? 0;
    
    $stmt = $conn->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
    $stmt->bind_param("ii", $estado, $id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => $estado ? 'Usuario activado' : 'Usuario desactivado']);
}

function crearProducto() {
    global $conn;
    
    $material = trim($_POST['material'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $valor_unitario = $_POST['valor_unitario'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $unidad = $_POST['unidad'] ?? 'UN';
    $tipo = $_POST['tipo'] ?? 'repuesto';
    $moneda = $_POST['moneda'] ?? 'Bs';
    
    if (!$material || !$descripcion) {
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
        return;
    }
    
    // Verificar si existe
    $stmt = $conn->prepare("SELECT id FROM inventario WHERE material = ?");
    $stmt->bind_param("s", $material);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'mensaje' => 'El código de material ya existe']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO inventario (material, descripcion, valor_unitario, stock, unidad, tipo, moneda) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisss", $material, $descripcion, $valor_unitario, $stock, $unidad, $tipo, $moneda);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => 'Producto creado correctamente']);
}

function eliminarProducto() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    
    // Eliminación lógica
    $stmt = $conn->prepare("UPDATE inventario SET estado = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado']);
}

function getReportes() {
    global $conn;
    
    // Total productos
    $result = $conn->query("SELECT COUNT(*) as total FROM inventario WHERE estado = 1");
    $total_productos = $result->fetch_assoc()['total'];
    
    // Total usuarios
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 1");
    $total_usuarios = $result->fetch_assoc()['total'];
    
    // Solicitudes pendientes
    $result = $conn->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'");
    $solicitudes_pendientes = $result->fetch_assoc()['total'];
    
    echo json_encode([
        'total_productos' => $total_productos,
        'total_usuarios' => $total_usuarios,
        'solicitudes_pendientes' => $solicitudes_pendientes
    ]);
}