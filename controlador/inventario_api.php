<?php
// filepath: controlador/inventario_api.php
include '../modelo/conexion.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        listarInventario();
        break;
    case 'buscar':
        buscarProducto();
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

function listarInventario() {
    global $conn;
    $sql = "SELECT * FROM inventario WHERE estado = 1 ORDER BY tipo, material";
    $result = $conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function buscarProducto() {
    global $conn;
    $term = $_GET['term'] ?? '';
    $sql = "SELECT * FROM inventario WHERE estado = 1 AND (material LIKE ? OR descripcion LIKE ?) LIMIT 10";
    $stmt = $conn->prepare($sql);
    $search = "%$term%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}