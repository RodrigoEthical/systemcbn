<?php
session_start();
include '../modelo/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];

    // Prepared statement para prevenir SQL injection
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ? AND estado = 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar contraseña con password_verify
        if (password_verify($contrasena, $user['contrasena'])) {
            $_SESSION['usuario'] = $user['nombre_usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['usuario_id'] = $user['id'];
            
            header("Location: ../vista/" . $user['rol'] . ".php");
            exit();
        } else {
            $_SESSION['error'] = "Credenciales incorrectas";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Credenciales incorrectas o usuario inactivo";
        header("Location: ../index.php");
        exit();
    }
    
    $stmt->close();
}
?>