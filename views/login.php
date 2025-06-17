<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height:100vh">
    <div class="container" style="max-width: 400px">
        <div class="card p-4 shadow">
            <h4 class="mb-4 text-center">Iniciar sesión</h4>
            <form method="POST" action="../controllers/AuthController.php">
                <input type="hidden" name="action" value="login">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" placeholder="ejemplo@dominio.com" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" name="password" type="password" placeholder="********" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary w-100">Ingresar</button>

                <div class="text-center mt-3">
                    <a href="register.php">¿Aún no tienes cuenta? Regístrate aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>