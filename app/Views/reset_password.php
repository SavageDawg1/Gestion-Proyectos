<?php
require_once '../../config/database.php';
require_once '../Models/Usuario.php';

$mensaje = '';
$token_valido = false;
$usuario_id = null;
$usuarioModel = new Usuario($conexion);

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $usuario = $usuarioModel->buscarPorToken($token);

    if ($usuario) {
        $token_valido = true;
        $usuario_id = $usuario['id'];
    } else {
        $mensaje = '<div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 15px;">El enlace es inválido o ha expirado.</div>';
    }
} else {
    $mensaje = '<div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 15px;">No se proporcionó ningún token.</div>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nueva_contrasena']) && $token_valido) {
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if (strlen($nueva_contrasena) < 6) {
        $mensaje = '<div class="alert alert-warning" style="color: #856404; text-align: center; margin-bottom: 15px;">Mínimo 6 caracteres.</div>';
    } elseif ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = '<div class="alert alert-warning" style="color: #856404; text-align: center; margin-bottom: 15px;">Las contraseñas no coinciden.</div>';
    } else {
        $password_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

        if ($usuarioModel->actualizarContrasenaYLimpiarToken($usuario_id, $password_hash)) {
            $mensaje = '<div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 15px;">¡Contraseña actualizada! Redirigiendo...</div>';
            $token_valido = false; 
            echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 3000);</script>";
        } else {
            $mensaje = '<div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 15px;">Error al actualizar.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - El Legado</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/login/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="Logo" class="logo" onerror="this.style.display='none'">
                <h1>NUEVA CONTRASEÑA</h1>
            </div>

            <div id="messages"><?php echo $mensaje; ?></div>

            <?php if ($token_valido): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="password" name="nueva_contrasena" placeholder="Nueva Contraseña" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-ingresar" style="margin-top: 20px;">GUARDAR CONTRASEÑA</button>
            </form>
            <?php else: ?>
                <div class="auth-links" style="margin-top: 20px; text-align: center;">
                    <a href="login.php" style="text-decoration: underline; color: #007bff; font-weight: bold;">Volver</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
