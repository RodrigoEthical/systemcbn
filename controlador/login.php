<?php
session_start();
include '../modelo/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = md5($_POST['contrasena']);

    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = '$usuario' AND contrasena = '$contrasena'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['usuario'] = $user['nombre_usuario'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: ../vista/" . $user['rol'] . ".php");
        exit();
    } else {
        $_SESSION['error'] = "Credenciales incorrectas";
        header("Location: ../index.php");
        exit();
    }
}
?>