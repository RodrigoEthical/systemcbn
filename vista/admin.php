<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
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
    <title>Panel Admin - CBN</title>
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
                    <a href="#" class="list-group-item list-group-item-action active" data-view="usuarios"><i class="bi bi-people"></i> Gestionar Usuarios</a>
                    <a href="#" class="list-group-item list-group-item-action" data-view="inventario"><i class="bi bi-box-seam"></i> Inventario</a>
                    <a href="#" class="list-group-item list-group-item-action" data-view="reportes"><i class="bi bi-graph-up"></i> Reportes</a>
                </div>
            </div>
            <div class="col-md-9">
                <!-- Vista: Usuarios -->
                <div id="view-usuarios">
                    <h4><i class="bi bi-people"></i> Gestión de Usuarios</h4>
                    <div class="card">
                        <div class="card-body">
                            <button class="btn btn-cbn mb-3" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                                <i class="bi bi-person-plus"></i> Nuevo Usuario
                            </button>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tablaUsuarios">
                                    <thead class="table-dark"><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Creado</th><th>Acciones</th></tr></thead>
                                    <tbody><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista: Inventario -->
                <div id="view-inventario" style="display:none;">
                    <h4><i class="bi bi-box-seam"></i> Inventario</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="buscarInventario" placeholder="Buscar por código..." onkeyup="buscarEnInventario()">
                                </div>
                                <div class="col-md-8">
                                    <button class="btn btn-cbn mb-1" data-bs-toggle="modal" data-bs-target="#modalProducto">
                                        <i class="bi bi-plus-circle"></i> Nuevo Producto
                                    </button>
                                    <button class="btn btn-success mb-1" data-bs-toggle="modal" data-bs-target="#modalImportar">
                                        <i class="bi bi-file-earmark-excel"></i> Importar Excel
                                    </button>
                                </div>
                            </div>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#admin-repuestos">Repuestos</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin-insumos">Insumos</button></li>
                            </ul>
                            <div class="tab-content mt-3">
                                <div class="tab-pane fade show active" id="admin-repuestos">
                                    <table class="table table-hover" id="tablaInvRep"><thead class="table-dark"><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Unidad</th><th>Valor</th><th>Moneda</th><th>Acciones</th></tr></thead><tbody></tbody></table>
                                </div>
                                <div class="tab-pane fade" id="admin-insumos">
                                    <table class="table table-hover" id="tablaInvIns"><thead class="table-dark"><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Unidad</th><th>Valor</th><th>Moneda</th><th>Acciones</th></tr></thead><tbody></tbody></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista: Reportes -->
                <div id="view-reportes" style="display:none;">
                    <h4><i class="bi bi-graph-up"></i> Reportes</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Total Productos</h5>
                                    <h2 id="totalProductos">-</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Total Usuarios</h5>
                                    <h2 id="totalUsuarios">-</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Solicitudes Pendientes</h5>
                                    <h2 id="solicitudesPendientes">-</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formUsuario">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="nombre_usuario" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol" required>
                                <option value="usuario">Usuario</option>
                                <option value="encargado">Encargado</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-cbn"><i class="bi bi-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProducto">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Código Material</label>
                            <input type="text" class="form-control" name="material" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valor Unitario</label>
                                <input type="number" class="form-control" name="valor_unitario" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock" value="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unidad</label>
                                <select class="form-select" name="unidad" required>
                                    <option value="UN">UN (Unidad)</option>
                                    <option value="KG">KG (Kilogramo)</option>
                                    <option value="L">L (Litro)</option>
                                    <option value="PC">PC (Pieza)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="repuesto">Repuesto</option>
                                    <option value="insumo">Insumo</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Moneda</label>
                            <select class="form-select" name="moneda" required>
                                <option value="Bs">Bs (Bolivianos)</option>
                                <option value="$">$ (Dólares)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-cbn"><i class="bi bi-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Importar Excel -->
    <div class="modal fade" id="modalImportar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel"></i> Importar desde Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formImportar" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> El archivo debe tener las columnas en este orden:<br>
                            <strong>Material | Descripción | Valor | Stock | Unidad</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Producto</label>
                            <select class="form-select" name="tipo" required>
                                <option value="repuesto">Repuestos</option>
                                <option value="insumo">Insumos</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Archivo Excel</label>
                            <input type="file" class="form-control" name="archivo" accept=".xlsx,.xls" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-upload"></i> Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Navegación
    document.querySelectorAll('[data-view]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('[data-view]').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('[id^="view-"]').forEach(v => v.style.display = 'none');
            document.getElementById('view-' + this.dataset.view).style.display = 'block';
            if (this.dataset.view === 'reportes') cargarReportes();
        });
    });

    // Cargar usuarios
    function cargarUsuarios() {
        fetch('../controlador/admin_api.php?action=listar_usuarios')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#tablaUsuarios tbody');
                tbody.innerHTML = data.map(u => `
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.nombre_usuario}</td>
                        <td><span class="badge bg-primary">${u.rol}</span></td>
                        <td>${u.estado ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
                        <td>${new Date(u.created_at).toLocaleDateString('es-BO')}</td>
                        <td>
                            <button class="btn btn-sm btn-${u.estado ? 'warning' : 'success'}" onclick="toggleEstado(${u.id}, ${u.estado})">
                                <i class="bi bi-${u.estado ? 'trash' : 'arrow-repeat'}"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    // Cargar inventario
    function cargarInventario() {
        fetch('../controlador/inventario_api.php?action=listar')
            .then(res => res.json())
            .then(data => {
                const rep = data.filter(p => p.tipo === 'repuesto');
                const ins = data.filter(p => p.tipo === 'insumo');
                
                document.querySelector('#tablaInvRep tbody').innerHTML = rep.map(p => `
                    <tr><td>${p.material}</td><td>${p.descripcion}</td><td>${p.stock}</td><td>${p.unidad}</td><td>${p.valor_unitario}</td><td>${p.moneda}</td><td>
                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${p.id})"><i class="bi bi-trash"></i></button>
                    </td></tr>
                `).join('');
                
                document.querySelector('#tablaInvIns tbody').innerHTML = ins.map(p => `
                    <tr><td>${p.material}</td><td>${p.descripcion}</td><td>${p.stock}</td><td>${p.unidad}</td><td>${p.valor_unitario}</td><td>${p.moneda}</td><td>
                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${p.id})"><i class="bi bi-trash"></i></button>
                    </td></tr>
                `).join('');
            });
    }

    // Cargar reportes
    function cargarReportes() {
        fetch('../controlador/admin_api.php?action=reportes')
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalProductos').textContent = data.total_productos;
                document.getElementById('totalUsuarios').textContent = data.total_usuarios;
                document.getElementById('solicitudesPendientes').textContent = data.solicitudes_pendientes;
            });
    }

    // Toggle estado usuario
    function toggleEstado(id, estado) {
        if (!confirm(estado ? '¿Desactivar usuario?' : '¿Activar usuario?')) return;
        const formData = new FormData();
        formData.append('action', 'toggle_usuario');
        formData.append('id', id);
        formData.append('estado', estado ? 0 : 1);
        
        fetch('../controlador/admin_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                cargarUsuarios();
            });
    }

    // Eliminar producto
    function eliminarProducto(id) {
        if (!confirm('¿Eliminar producto?')) return;
        const formData = new FormData();
        formData.append('action', 'eliminar_producto');
        formData.append('id', id);
        
        fetch('../controlador/admin_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                cargarInventario();
            });
    }

    // Formularios
    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'crear_usuario');
        
        fetch('../controlador/admin_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
                    this.reset();
                    cargarUsuarios();
                }
            });
    });

    document.getElementById('formProducto').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'crear_producto');
        
        fetch('../controlador/admin_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalProducto')).hide();
                    this.reset();
                    cargarInventario();
                }
            });
    });

    // Inicializar
    cargarUsuarios();

    // Formulario Importar
    document.getElementById('formImportar').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../controlador/importar_inventario.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalImportar')).hide();
                    this.reset();
                    cargarInventario();
                }
            });
    });
    </script>
</body>
</html>