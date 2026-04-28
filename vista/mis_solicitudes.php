<?php
// filepath: vista/mis_solicitudes.php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'usuario') {
    header("Location: ../index.php");
    exit();
}
$nombre_usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis Solicitudes - CBN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --cbn-azul: #14468B; --cbn-rojo: #DD2C1C; }
        .navbar { background-color: var(--cbn-azul) !important; }
        .btn-cbn { background-color: var(--cbn-rojo); border-color: var(--cbn-rojo); color: white; }
        .btn-cbn:hover { background-color: #b3241a; border-color: #b3241a; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <img src="../imagenes/logocbn/logo1.jpeg" height="40" alt="CBN">
            <span class="navbar-text text-white ms-2">Sistema de Inventario</span>
            <div class="d-flex align-items-center ms-auto">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo $nombre_usuario; ?></span>
                <a href="../controlador/logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="usuario.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam"></i> Solicitar Productos</a>
                    <a href="mis_solicitudes.php" class="list-group-item list-group-item-action active"><i class="bi bi-list-check"></i> Mis Solicitudes</a>
                </div>
            </div>
            <div class="col-md-9">
                <h4><i class="bi bi-list-check"></i> Mis Solicitudes</h4>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaMisSolicitudes">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function getEstadoBadge(estado) {
        const badges = {
            'pendiente': '<span class="badge bg-warning">Pendiente</span>',
            'aprobada': '<span class="badge bg-success">Aprobada</span>',
            'rechazada': '<span class="badge bg-danger">Rechazada</span>',
            'entregada': '<span class="badge bg-info">Entregada</span>'
        };
        return badges[estado] || estado;
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetch('../controlador/solicitud_api.php?action=mis_solicitudes')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#tablaMisSolicitudes tbody');
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No tienes solicitudes</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map(s => `
                    <tr>
                        <td>#${s.id}</td>
                        <td>${new Date(s.fecha_solicitud).toLocaleString('es-BO')}</td>
                        <td>${s.material}<br><small class="text-muted">${s.descripcion}</small></td>
                        <td>S: ${s.cantidad_solicitada}${s.cantidad_aprobada ? '<br>A: ' + s.cantidad_aprobada : ''}</td>
                        <td>${getEstadoBadge(s.estado)}</td>
                        <td>${s.observaciones || '-'}</td>
                    </tr>
                `).join('');
            })
            .catch(err => console.error('Error:', err));
    });
    </script>
</body>
</html>