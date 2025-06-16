<form method="POST" action="../controllers/AuthController.php">
    <input type="hidden" name="action" value="register">
    <input name="name" placeholder="Nombre" required>
    <input name="email" placeholder="Correo" required>
    <input name="password" placeholder="Contraseña" type="password" required>
    <button type="submit">Registrarse</button>
</form>
