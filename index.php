<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Cervecería Boliviana Nacional S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: white; }
        .navbar { background-color: #14468B !important; }
        .btn-primary { background-color: #DD2C1C; border-color: #DD2C1C; }
        .btn-primary:hover { background-color: #b3241a; border-color: #b3241a; }
        .form-control { border: 2px solid #14468B; border-radius: 5px; }
        .form-control:focus { border-color: #DD2C1C; box-shadow: 0 0 5px rgba(221, 44, 28, 0.5); }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-10 col-md-6 col-lg-4 text-center">
                <img src="imagenes/logocbn/logo1.jpeg" class="img-fluid mb-3" style="max-width: 200px;" alt="Logo">
                <h2>Cervecería Boliviana Nacional S.A.</h2>
                <?php if (isset($_SESSION['error'])) { echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>'; unset($_SESSION['error']); } ?>
                <form action="controlador/login.php" method="post">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>