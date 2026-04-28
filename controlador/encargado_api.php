<?php
// filepath: controlador/encargado_api.php
session_start();
include '../modelo/conexion.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'solicitudes_pendientes':
        solicitudesPendientes();
        break;
    case 'solicitudes_aprobadas':
        solicitudesAprobadas();
        break;
    case 'responder_solicitud':
        responderSolicitud();
        break;
    case 'registrar_movimiento':
        registrarMovimiento();
        break;
    case 'movimientos':
        listarMovimientos();
        break;
    case 'entregar':
        entregarSolicitud();
        break;
    case 'generar_recibo':
        generarRecibo();
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

function solicitudesPendientes() {
    global $conn;
    $sql = "SELECT s.id, s.observaciones, s.fecha_solicitud, u.nombre_usuario as usuario,
                   i.id as inventario_id, i.material, i.descripcion, ds.cantidad_solicitada
            FROM solicitudes s
            JOIN detalle_solicitud ds ON s.id = ds.solicitud_id
            JOIN inventario i ON ds.inventario_id = i.id
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.estado = 'pendiente'
            ORDER BY s.fecha_solicitud ASC";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function responderSolicitud() {
    global $conn;
    
    $solicitud_id = $_POST['solicitud_id'] ?? 0;
    $producto_id = $_POST['producto_id'] ?? 0;
    $cantidad = $_POST['cantidad'] ?? 0;
    $cantidad_aprobada = $_POST['cantidad_aprobada'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Determinar acción desde el botón submit
    $accion = $_POST['accion'] ?? '';
    
    // Debug
    error_log("accion recibida: " . $accion);
    
    $estado = ($accion === 'aprobar') ? 'aprobada' : 'rechazada';
    
    // Actualizar solicitud
    $stmt = $conn->prepare("UPDATE solicitudes SET estado = ?, observaciones = CONCAT(IFNULL(observaciones, ''), '\n', ?), fecha_respuesta = NOW() WHERE id = ?");
    $obs = $observaciones ? "[$accion] $observaciones" : "Solicitud $accion";
    $stmt->bind_param("ssi", $estado, $obs, $solicitud_id);
    $stmt->execute();
    
    // Actualizar cantidad aprobada
    $stmt = $conn->prepare("UPDATE detalle_solicitud SET cantidad_aprobada = ? WHERE solicitud_id = ? AND inventario_id = ?");
    $stmt->bind_param("iii", $cantidad_aprobada, $solicitud_id, $producto_id);
    $stmt->execute();
    
    // Si se aprueba, reducir stock
    if ($estado === 'aprobada') {
        $stmt = $conn->prepare("UPDATE inventario SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $cantidad_aprobada, $producto_id);
        $stmt->execute();
        
        // Registrar movimiento de salida
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $stmt = $conn->prepare("INSERT INTO movimientos (inventario_id, tipo, cantidad, referencia, usuario_id) VALUES (?, 'salida', ?, ?, ?)");
        $ref = "Solicitud #$solicitud_id";
        $stmt->bind_param("iisi", $producto_id, $cantidad_aprobada, $ref, $usuario_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'mensaje' => "Solicitud $accion correctamente"]);
}

function registrarMovimiento() {
    global $conn;
    
    $producto_id = $_POST['producto_id'] ?? 0;
    $cantidad = $_POST['cantidad'] ?? 0;
    $referencia = $_POST['referencia'] ?? '';
    $tipo = $_POST['tipo'] ?? 'entrada';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    if (!$producto_id || !$cantidad) {
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
        return;
    }
    
    // Verificar stock para salidas
    if ($tipo === 'salida') {
        $stmt = $conn->prepare("SELECT stock FROM inventario WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['stock'] < $cantidad) {
            echo json_encode(['success' => false, 'mensaje' => 'Stock insuficiente']);
            return;
        }
    }
    
    // Actualizar stock
    $signo = ($tipo === 'entrada') ? '+' : '-';
    $stmt = $conn->prepare("UPDATE inventario SET stock = stock $signo ? WHERE id = ?");
    $stmt->bind_param("ii", $cantidad, $producto_id);
    $stmt->execute();
    
    // Registrar movimiento
    $stmt = $conn->prepare("INSERT INTO movimientos (inventario_id, tipo, cantidad, referencia, usuario_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isisi", $producto_id, $tipo, $cantidad, $referencia, $usuario_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => ucfirst($tipo) . ' registrada correctamente']);
}

function listarMovimientos() {
    global $conn;
    $sql = "SELECT m.fecha, m.tipo, m.cantidad, m.referencia, i.material, u.nombre_usuario as usuario
            FROM movimientos m
            JOIN inventario i ON m.inventario_id = i.id
            JOIN usuarios u ON m.usuario_id = u.id
            ORDER BY m.fecha DESC
            LIMIT 50";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function entregarSolicitud() {
    global $conn;
    
    $solicitud_id = $_POST['solicitud_id'] ?? 0;
    $usuario_entrega_id = $_SESSION['usuario_id'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Obtener detalles de la solicitud
    $stmt = $conn->prepare("SELECT ds.inventario_id, ds.cantidad_aprobada FROM detalle_solicitud ds WHERE ds.solicitud_id = ?");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $detalle = $result->fetch_assoc();
    $inventario_id = $detalle['inventario_id'];
    $cantidad = $detalle['cantidad_aprobada'];
    
    // Actualizar estado
    $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'entregada' WHERE id = ?");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    
    // Obtener usuario que recibe
    $stmt = $conn->prepare("SELECT usuario_id FROM solicitudes WHERE id = ?");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $usuario_recibe_id = $row['usuario_id'];
    
    // Crear registro de entrega
    $stmt = $conn->prepare("INSERT INTO entregas (solicitud_id, usuario_entrega_id, usuario_recibe_id, observaciones) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $solicitud_id, $usuario_entrega_id, $usuario_recibe_id, $observaciones);
    $stmt->execute();
    
    // Registrar movimiento de salida por entrega
    $stmt = $conn->prepare("INSERT INTO movimientos (inventario_id, tipo, cantidad, referencia, usuario_id) VALUES (?, 'salida', ?, ?, ?)");
    $ref = "Entrega Solicitud #$solicitud_id";
    $stmt->bind_param("iisi", $inventario_id, $cantidad, $ref, $usuario_entrega_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'mensaje' => 'Entrega registrada correctamente']);
}

function solicitudesAprobadas() {
    global $conn;
    $sql = "SELECT s.id, s.fecha_respuesta, u.nombre_usuario as usuario,
                   i.material, i.descripcion, ds.cantidad_aprobada
            FROM solicitudes s
            JOIN detalle_solicitud ds ON s.id = ds.solicitud_id
            JOIN inventario i ON ds.inventario_id = i.id
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.estado = 'aprobada'
            ORDER BY s.fecha_respuesta DESC";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function generarRecibo() {
    global $conn;
    
    $solicitud_id = $_GET['solicitud_id'] ?? 0;
    
    $sql = "SELECT s.id, s.fecha_solicitud, s.fecha_respuesta, s.observaciones,
                   u.nombre_usuario as usuario_solicita, u2.nombre_usuario as usuario_entrega,
                   i.material, i.descripcion, i.unidad, ds.cantidad_aprobada
            FROM solicitudes s
            JOIN detalle_solicitud ds ON s.id = ds.solicitud_id
            JOIN inventario i ON ds.inventario_id = i.id
            JOIN usuarios u ON s.usuario_id = u.id
            LEFT JOIN usuarios u2 ON u2.id = (SELECT usuario_entrega_id FROM entregas WHERE solicitud_id = s.id)
            WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Solicitud no encontrada']);
        return;
    }
    
    $data = $result->fetch_assoc();
    
    // Generar PDF con TCPDF
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Crear PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('CBN Sistema');
    $pdf->SetAuthor('CBN');
    $pdf->SetTitle('Recibo de Entrega - ' . $solicitud_id);
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetAutoPageBreak(true, 20);
    
    // Agregar página
    $pdf->AddPage();
    
    // Logo y encabezado
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(20, 70, 139); // CBN Azul
    $pdf->Cell(0, 10, 'CERVEZERA BOLIVIANA NACIONAL S.A.', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Sistema de Gestión de Inventario', 0, 1, 'C');
    
    $pdf->SetDrawColor(20, 70, 139);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(20, 45, 190, 45);
    
    $pdf->Ln(10);
    
    // Título del recibo
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'RECIBO DE ENTREGA', 0, 1, 'C');
    
    $pdf->Ln(5);
    
    // Información de la solicitud
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(50, 8, 'N° Solicitud:', 0, 0, 'L', true);
    $pdf->Cell(60, 8, $data['id'], 0, 1, 'L');
    $pdf->Cell(50, 8, 'Fecha de Solicitud:', 0, 0, 'L', true);
    $pdf->Cell(60, 8, date('d/m/Y H:i', strtotime($data['fecha_solicitud'])), 0, 1, 'L');
    $pdf->Cell(50, 8, 'Fecha de Aprobación:', 0, 0, 'L', true);
    $pdf->Cell(60, 8, date('d/m/Y H:i', strtotime($data['fecha_respuesta'])), 0, 1, 'L');
    
    $pdf->Ln(5);
    
    // Tabla de productos
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(20, 70, 139);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(50, 8, 'Producto', 1, 0, 'C', true);
    $pdf->Cell(80, 8, 'Descripción', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
    $pdf->Cell(15, 8, 'Unidad', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(50, 8, $data['material'], 1, 0, 'C');
    $pdf->Cell(80, 8, substr($data['descripcion'], 0, 40), 1, 0, 'L');
    $pdf->Cell(25, 8, $data['cantidad_aprobada'], 1, 0, 'C');
    $pdf->Cell(15, 8, $data['unidad'], 1, 1, 'C');
    
    $pdf->Ln(5);
    
    // Información de usuarios
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(50, 8, 'Solicitante:', 0, 0, 'L', true);
    $pdf->Cell(60, 8, $data['usuario_solicita'], 0, 1, 'L');
    $pdf->Cell(50, 8, 'Entregado por:', 0, 0, 'L', true);
    $pdf->Cell(60, 8, $data['usuario_entrega'] ?? $_SESSION['nombre_usuario'], 0, 1, 'L');
    
    $pdf->Ln(15);
    
    // Firmas
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    
    // Firma entrega
    $pdf->Line(30, 200, 90, 200);
    $pdf->Cell(60, 5, 'Firma del Entregó', 0, 0, 'C');
    $pdf->Cell(60, 5, '', 0, 0, 'C');
    // Firma recibe
    $pdf->Line(120, 200, 180, 200);
    $pdf->Cell(60, 5, 'Firma del Recibió', 0, 1, 'C');
    
    $pdf->Cell(60, 5, 'Nombre: ___________________', 0, 0, 'C');
    $pdf->Cell(60, 5, '', 0, 0, 'C');
    $pdf->Cell(60, 5, 'Nombre: ' . $data['usuario_solicita'], 0, 1, 'C');
    
    // Footer
    $pdf->SetY(270);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, 0, 'C');
    
    // Salida del PDF
    $pdf->Output('Recibo_Entrega_' . $solicitud_id . '.pdf', 'I');
}