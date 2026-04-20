<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'encargado') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Encargado - Cervecería Boliviana Nacional S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: white; }
        .navbar { background-color: #14468B !important; }
        .btn-primary { background-color: #DD2C1C; border-color: #DD2C1C; }
        .btn-primary:hover { background-color: #b3241a; border-color: #b3241a; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Cervecería Boliviana Nacional S.A.</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../controlador/logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1>Bienvenido Encargado del Inventario</h1>
        <p>Panel para gestionar inventario.</p>
        <div class="alert alert-info">Ruteo correcto para Encargado</div>
    </div>
</body>
</html>