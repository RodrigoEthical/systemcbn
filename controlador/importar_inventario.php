<?php
// filepath: controlador/importar_inventario.php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../modelo/conexion.php';

header('Content-Type: application/json');

// Requiere PhpSpreadsheet
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo'];
    $tipo = $_POST['tipo'] ?? 'repuesto'; // repuesto o insumo
    
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        
        try {
            if (in_array($ext, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($archivo['tmp_name']);
                $hoja = $spreadsheet->getActiveSheet();
                $filas = $hoja->toArray();
                
                $insertados = 0;
                $errores = [];
                
                // Saltar encabezado (fila 0)
                for ($i = 1; $i < count($filas); $i++) {
                    $fila = $filas[$i];
                    
                    // Formato: Material | Descripcion | Val.stk.valor | Moneda (BOB) | Stock sap | Unidad
                    $material = trim($fila[0] ?? '');
                    $descripcion = trim($fila[1] ?? '');
                    $valor = floatval($fila[2] ?? 0);
                    $monedaRaw = trim($fila[3] ?? 'BOB'); // Columna sin nombre: BOB = Bolivianos
                    $stock = intval($fila[4] ?? 0);
                    $unidad = trim($fila[5] ?? 'UN');
                    
                    // Convertir BOB a Bs
                    $moneda = (stripos($monedaRaw, 'BOB') !== false) ? 'Bs' : '$';
                    
                    if ($material && $descripcion) {
                        // Verificar si existe
                        $stmt = $conn->prepare("SELECT id FROM inventario WHERE material = ?");
                        $stmt->bind_param("s", $material);
                        $stmt->execute();
                        $existe = $stmt->get_result()->num_rows > 0;
                        
                        if ($existe) {
                            // Actualizar
                            $stmt = $conn->prepare("UPDATE inventario SET descripcion = ?, valor_unitario = ?, stock = ?, unidad = ?, tipo = ?, moneda = ? WHERE material = ?");
                            $stmt->bind_param("sdissss", $descripcion, $valor, $stock, $unidad, $tipo, $moneda, $material);
                        } else {
                            // Insertar
                            $stmt = $conn->prepare("INSERT INTO inventario (material, descripcion, valor_unitario, stock, unidad, tipo, moneda) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ssdisss", $material, $descripcion, $valor, $stock, $unidad, $tipo, $moneda);
                        }
                        $stmt->execute();
                        $insertados++;
                    }
                }
                
                echo json_encode(['success' => true, 'mensaje' => "Se importaron $insertados productos ($tipo)"]);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Formato no válido. Use XLS o XLSX']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al leer archivo: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al subir archivo']);
    }
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Solicitud inválida']);
}