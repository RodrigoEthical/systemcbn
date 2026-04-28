<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'encargado') {
    header("Location: ../index.php");
    exit();
}
$nombre_usuario = $_SESSION['usuario'];
$usuario_id = $_SESSION['usuario_id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Encargado - CBN</title>
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
                    <a href="#" class="list-group-item list-group-item-action active" data-view="solicitudes"><i class="bi bi-clipboard-check"></i> Solicitudes</a>
                    <a href="#" class="list-group-item list-group-item-action" data-view="inventario"><i class="bi bi-box-seam"></i> Inventario</a>
                    <a href="#" class="list-group-item list-group-item-action" data-view="movimientos"><i class="bi bi-arrow-left-right"></i> Movimientos</a>
                    <a href="#" class="list-group-item list-group-item-action" data-view="entradas"><i class="bi bi-plus-circle"></i> Registrar Entrada</a>
                    <a href="#" class="list-group-item list-group-item-action" data-view="salidas"><i class="bi bi-dash-circle"></i> Registrar Salida</a>
                </div>
            </div>
            <div class="col-md-9">
                <!-- Vista: Solicitudes -->
                <div id="view-solicitudes">
                    <h4><i class="bi bi-clipboard-check"></i> Solicitudes Pendientes</h4>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="buscarSolicitud" placeholder="Buscar por código..." onkeyup="filtrarSolicitudes()">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tablaSolicitudes">
                                    <thead class="table-dark"><tr><th>ID</th><th>Usuario</th><th>Producto</th><th>Cantidad</th><th>Fecha</th><th>Obs.</th><th>Acción</th></tr></thead>
                                    <tbody><tr><td colspan="7" class="text-center">Cargando...</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Solicitudes Aprobadas para entrega -->
                    <h4 class="mt-4"><i class="bi bi-check2-circle"></i> Aprobadas - Listas para Entrega</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tablaAprobadas">
                                    <thead class="table-dark"><tr><th>ID</th><th>Usuario</th><th>Producto</th><th>Cantidad Aprobada</th><th>Fecha</th><th>Entregar</th></tr></thead>
                                    <tbody><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista: Inventario -->
                <div id="view-inventario" style="display:none;">
                    <h4><i class="bi bi-box-seam"></i> Inventario</h4>
                    <div class="card mb-3">
                        <div class="card-body">
                            <input type="text" class="form-control" id="buscarInventario" placeholder="Buscar por código..." onkeyup="buscarEnInventario()">
                        </div>
                    </div>
                    <ul class="nav nav-tabs" id="invTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inv-repuestos">Repuestos</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inv-insumos">Insumos</button></li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="inv-repuestos">
                            <table class="table table-hover" id="tablaInvRepuestos"><thead class="table-dark"><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Unidad</th><th>Valor</th></tr></thead><tbody></tbody></table>
                        </div>
                        <div class="tab-pane fade" id="inv-insumos">
                            <table class="table table-hover" id="tablaInvInsumos"><thead class="table-dark"><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Unidad</th><th>Valor</th></tr></thead><tbody></tbody></table>
                        </div>
                    </div>
                </div>

                <!-- Vista: Movimientos -->
                <div id="view-movimientos" style="display:none;">
                    <h4><i class="bi bi-arrow-left-right"></i> Historial de Movimientos</h4>
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-hover" id="tablaMovimientos"><thead class="table-dark"><tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cantidad</th><th>Referencia</th><th>Usuario</th></tr></thead><tbody></tbody></table>
                        </div>
                    </div>
                </div>

                <!-- Vista: Entrada -->
                <div id="view-entradas" style="display:none;">
                    <h4><i class="bi bi-plus-circle"></i> Registrar Entrada</h4>
                    <div class="card">
                        <div class="card-body">
                            <form id="formEntrada">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Producto</label>
                                        <select class="form-select" name="producto_id" required>
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" class="form-control" name="cantidad" min="1" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Referencia</label>
                                        <input type="text" class="form-control" name="referencia" placeholder="N° factura, orden...">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-cbn"><i class="bi bi-save"></i> Registrar Entrada</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Vista: Salida -->
                <div id="view-salidas" style="display:none;">
                    <h4><i class="bi bi-dash-circle"></i> Registrar Salida</h4>
                    <div class="card">
                        <div class="card-body">
                            <form id="formSalida">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Producto</label>
                                        <select class="form-select" name="producto_id" required>
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" class="form-control" name="cantidad" min="1" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Referencia</label>
                                        <input type="text" class="form-control" name="referencia" placeholder="N° solicitud, orden...">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-cbn"><i class="bi bi-save"></i> Registrar Salida</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aprobar/Rechazar -->
    <div class="modal fade" id="modalRespuesta" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Responder Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRespuesta">
                    <div class="modal-body">
                        <input type="hidden" id="solId" name="solicitud_id">
                        <input type="hidden" id="solProductoId" name="producto_id">
                        <input type="hidden" id="solCantidad" name="cantidad">
                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <input type="text" class="form-control" id="solProducto" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad Solicitada</label>
                            <input type="text" class="form-control" id="solCantidadTxt" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad a Aprobar</label>
                            <input type="number" class="form-control" id="cantidadAprobar" name="cantidad_aprobada" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="enviarRespuesta('rechazar')"><i class="bi bi-x-circle"></i> Rechazar</button>
                        <button type="button" class="btn btn-cbn" onclick="enviarRespuesta('aprobar')"><i class="bi bi-check-circle"></i> Aprobar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Navegación entre vistas
    document.querySelectorAll('[data-view]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('[data-view]').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('[id^="view-"]').forEach(v => v.style.display = 'none');
            const viewId = 'view-' + this.dataset.view;
            document.getElementById(viewId).style.display = 'block';
            
            // Cargar datos según la vista
            const view = this.dataset.view;
            if (view === 'movimientos') {
                cargarMovimientos();
            } else if (view === 'inventario') {
                cargarInventario();
            }
        });
    });

    // Cargar solicitudes pendientes
    let todasSolicitudes = [];
    function cargarSolicitudes() {
        fetch('../controlador/encargado_api.php?action=solicitudes_pendientes')
            .then(res => res.json())
            .then(data => {
                todasSolicitudes = data;
                filtrarSolicitudes();
                cargarAprobadas();
            });
    }

    function filtrarSolicitudes() {
        const term = document.getElementById('buscarSolicitud').value.toLowerCase();
        const filtered = todasSolicitudes.filter(s => 
            s.material.toLowerCase().includes(term) || 
            s.usuario.toLowerCase().includes(term)
        );
        const tbody = document.querySelector('#tablaSolicitudes tbody');
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay solicitudes pendientes</td></tr>';
            return;
        }
        tbody.innerHTML = filtered.map(s => `
            <tr>
                <td>#${s.id}</td>
                <td>${s.usuario}</td>
                <td>${s.material}<br><small class="text-muted">${s.descripcion}</small></td>
                <td>${s.cantidad_solicitada}</td>
                <td>${new Date(s.fecha_solicitud).toLocaleDateString('es-BO')}</td>
                <td>${s.observaciones || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="responder(${s.id}, ${s.inventario_id}, '${s.material}', ${s.cantidad_solicitada}, 'aprobar')"><i class="bi bi-check"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="responder(${s.id}, ${s.inventario_id}, '${s.material}', ${s.cantidad_solicitada}, 'rechazar')"><i class="bi bi-x"></i></button>
                </td>
            </tr>
        `).join('');
    }

    // Cargar solicitudes aprobadas para entrega
    function cargarAprobadas() {
        fetch('../controlador/encargado_api.php?action=solicitudes_aprobadas')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#tablaAprobadas tbody');
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay solicitudes aprobadas</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map(s => `
                    <tr>
                        <td>#${s.id}</td>
                        <td>${s.usuario}</td>
                        <td>${s.material}<br><small class="text-muted">${s.descripcion}</small></td>
                        <td>${s.cantidad_aprobada}</td>
                        <td>${new Date(s.fecha_respuesta).toLocaleDateString('es-BO')}</td>
                        <td>
                            <button class="btn btn-sm btn-cbn" onclick="entregar(${s.id}, '${s.usuario}', '${s.material}', ${s.cantidad_aprobada})">
                                <i class="bi bi-box-arrow-up"></i> Entregar
                            </button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    // Cargar inventario con búsqueda
    let todoInventario = [];
    function cargarInventario() {
        fetch('../controlador/inventario_api.php?action=listar')
            .then(res => res.json())
            .then(data => {
                todoInventario = data;
                buscarEnInventario();
            });
    }

    function buscarEnInventario() {
        const term = document.getElementById('buscarInventario').value.toLowerCase();
        const repuestos = todoInventario.filter(p => p.tipo === 'repuesto' && p.material.toLowerCase().includes(term));
        const insumos = todoInventario.filter(p => p.tipo === 'insumo' && p.material.toLowerCase().includes(term));
        
        document.querySelector('#tablaInvRepuestos tbody').innerHTML = repuestos.map(p => `
            <tr><td>${p.material}</td><td>${p.descripcion}</td><td>${p.stock}</td><td>${p.unidad}</td><td>${p.moneda} ${p.valor_unitario}</td></tr>
        `).join('');
        
        document.querySelector('#tablaInvInsumos tbody').innerHTML = insumos.map(p => `
            <tr><td>${p.material}</td><td>${p.descripcion}</td><td>${p.stock}</td><td>${p.unidad}</td><td>${p.moneda} ${p.valor_unitario}</td></tr>
        `).join('');
        
        // Llenar selects
        const options = todoInventario.map(p => `<option value="${p.id}">${p.material} - ${p.descripcion}</option>`).join('');
        document.querySelectorAll('select[name="producto_id"]').forEach(s => s.innerHTML = '<option value="">Seleccionar...</option>' + options);
    }

    // Cargar movimientos
    function cargarMovimientos() {
        fetch('../controlador/encargado_api.php?action=movimientos')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#tablaMovimientos tbody');
                tbody.innerHTML = data.length ? data.map(m => `
                    <tr>
                        <td>${new Date(m.fecha).toLocaleString('es-BO')}</td>
                        <td><span class="badge ${m.tipo === 'entrada' ? 'bg-success' : 'bg-warning'}">${m.tipo.toUpperCase()}</span></td>
                        <td>${m.material}</td>
                        <td>${m.cantidad}</td>
                        <td>${m.referencia || '-'}</td>
                        <td>${m.usuario}</td>
                    </tr>
                `).join('') : '<tr><td colspan="6" class="text-center">No hay movimientos</td></tr>';
            });
    }

    // Responder solicitud
    function responder(solId, prodId, material, cantidad, accion) {
        document.getElementById('solId').value = solId;
        document.getElementById('solProductoId').value = prodId;
        document.getElementById('solCantidad').value = cantidad;
        document.getElementById('solProducto').value = material;
        document.getElementById('solCantidadTxt').value = cantidad;
        document.getElementById('cantidadAprobar').value = cantidad;
        document.getElementById('cantidadAprobar').max = cantidad;
        document.getElementById('formRespuesta').dataset.accion = accion;
        new bootstrap.Modal(document.getElementById('modalRespuesta')).show();
    }

    // Enviar respuesta con acción correcta
    function enviarRespuesta(accion) {
        const form = document.getElementById('formRespuesta');
        const formData = new FormData(form);
        formData.append('action', 'responder_solicitud');
        formData.append('accion', accion); // Enviar acción explícitamente
        
        fetch('../controlador/encargado_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalRespuesta')).hide();
                    cargarSolicitudes();
                    cargarInventario();
                }
            });
    }

    // Formularios de entrada y salida
    document.getElementById('formEntrada').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'registrar_movimiento');
        formData.append('tipo', 'entrada');
        
        fetch('../controlador/encargado_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) this.reset();
            });
    });

    document.getElementById('formSalida').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'registrar_movimiento');
        formData.append('tipo', 'salida');
        
        fetch('../controlador/encargado_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) this.reset();
            });
    });

    // Inicializar
    cargarSolicitudes();
    cargarInventario();

    // Función entregar y generar recibo
    function entregar(solId, usuario, material, cantidad) {
        if (!confirm('¿Confirmar entrega a ' + usuario + '?')) return;
        
        const formData = new FormData();
        formData.append('action', 'entregar');
        formData.append('solicitud_id', solId);
        
        fetch('../controlador/encargado_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) {
                    // Abrir recibo en nueva ventana
                    window.open('../controlador/encargado_api.php?action=generar_recibo&solicitud_id=' + solId, '_blank');
                    cargarAprobadas();
                }
            });
    }
    </script>
</body>
</html>