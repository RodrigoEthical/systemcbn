<?php
// filepath: vista/header.php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CBN - Sistema de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --cbn-azul: #14468B;
            --cbn-rojo: #DD2C1C;
        }
        .navbar { background-color: var(--cbn-azul) !important; }
        .btn-cbn { background-color: var(--cbn-rojo); border-color: var(--cbn-rojo); color: white; }
        .btn-cbn:hover { background-color: #b3241a; border-color: #b3241a; color: white; }
        .sidebar { min-height: 100vh; background: #f8f9fa; }
        .sidebar a { text-decoration: none; color: #333; padding: 10px 15px; display: block; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: var(--cbn-azul); color: white; }
        .card-header { background: var(--cbn-azul); color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../imagenes/logocbn/logo1.jpeg" height="40" alt="CBN">
            </a>
            <span class="navbar-text text-white">Sistema de Inventario</span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo $usuario; ?> (<?php echo ucfirst($rol); ?>)</span>
                <a href="../controlador/logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">