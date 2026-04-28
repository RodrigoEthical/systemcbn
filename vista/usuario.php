<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'usuario') {
    header("Location: ../index.php");
    exit();
}
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Usuario - CBN</title>
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
                    <a href="usuario.php" class="list-group-item list-group-item-action active"><i class="bi bi-box-seam"></i> Solicitar Productos</a>
                    <a href="mis_solicitudes.php" class="list-group-item list-group-item-action"><i class="bi bi-list-check"></i> Mis Solicitudes</a>
                </div>
            </div>
            <div class="col-md-9">
                <h4><i class="bi bi-box-seam"></i> Catálogo de Productos</h4>
                
                <!-- Tabs Repuestos/Insumos -->
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#repuestos">Repuestos</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#insumos">Insumos</button></li>
                </ul>
                
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="repuestos">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tablaRepuestos">
                                <thead class="table-dark"><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Unidad</th><th>Acción</th></tr></thead>
                                <tbody><tr><td colspan="5" class="text-center">Cargando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="insumos">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tablaInsumos">
                                <thead class="table-dark"><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Unidad</th><th>Acción</th></tr></thead>
                                <tbody><tr><td colspan="5" class="text-center">Cargando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Solicitud -->
    <div class="modal fade" id="modalSolicitar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam"></i> Solicitar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSolicitar">
                    <div class="modal-body">
                        <input type="hidden" id="productoId" name="producto_id">
                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <input type="text" class="form-control" id="productoNombre" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-cbn"><i class="bi bi-send"></i> Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let inventario = { repuestos: [], insumos: [] };

    // Cargar inventario al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        cargarInventario();
    });

    function cargarInventario() {
        fetch('../controlador/inventario_api.php?action=listar')
            .then(res => res.json())
            .then(data => {
                inventario.repuestos = data.filter(p => p.tipo === 'repuesto');
                inventario.insumos = data.filter(p => p.tipo === 'insumo');
                renderTablas();
            })
            .catch(err => console.error('Error:', err));
    }

    function renderTablas() {
        const tbodyRep = document.querySelector('#tablaRepuestos tbody');
        const tbodyIns = document.querySelector('#tablaInsumos tbody');
        
        tbodyRep.innerHTML = inventario.repuestos.length ? inventario.repuestos.map(p => `
            <tr>
                <td>${p.material}</td>
                <td>${p.descripcion}</td>
                <td><span class="badge ${p.stock > 0 ? 'bg-success' : 'bg-danger'}">${p.stock}</span></td>
                <td>${p.unidad}</td>
                <td><button class="btn btn-sm btn-cbn" onclick="solicitar(${p.id}, '${p.material.replace(/'/g, "\\'")}', '${p.descripcion.replace(/'/g, "\\'")}')"><i class="bi bi-cart-plus"></i> Solicitar</button></td>
            </tr>
        `).join('') : '<tr><td colspan="5" class="text-center text-muted">No hay repuestos disponibles</td></tr>';
        
        tbodyIns.innerHTML = inventario.insumos.length ? inventario.insumos.map(p => `
            <tr>
                <td>${p.material}</td>
                <td>${p.descripcion}</td>
                <td><span class="badge ${p.stock > 0 ? 'bg-success' : 'bg-danger'}">${p.stock}</span></td>
                <td>${p.unidad}</td>
                <td><button class="btn btn-sm btn-cbn" onclick="solicitar(${p.id}, '${p.material.replace(/'/g, "\\'")}', '${p.descripcion.replace(/'/g, "\\'")}')"><i class="bi bi-cart-plus"></i> Solicitar</button></td>
            </tr>
        `).join('') : '<tr><td colspan="5" class="text-center text-muted">No hay insumos disponibles</td></tr>';
    }

    function solicitar(id, material, descripcion) {
        document.getElementById('productoId').value = id;
        document.getElementById('productoNombre').value = material + ' - ' + descripcion;
        document.getElementById('cantidad').value = '';
        document.querySelector('#formSolicitar textarea').value = '';
        // Debug: ver qué se envía
        console.log('Producto ID:', id);
        new bootstrap.Modal(document.getElementById('modalSolicitar')).show();
    }

    document.getElementById('formSolicitar').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'crear');
        
        // Debug: mostrar datos enviados
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        
        fetch('../controlador/solicitud_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalSolicitar')).hide();
                    window.location.href = 'mis_solicitudes.php';
                }
            })
            .catch(err => alert('Error: ' + err));
    });
    </script>
</body>
</html>