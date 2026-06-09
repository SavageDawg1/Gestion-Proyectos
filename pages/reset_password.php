<?php
require_once '../config/database.php';

$mensaje = '';
$token_valido = false;
$usuario_id = null;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT id FROM registro WHERE reset_token = ? AND token_expiracion > NOW() LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $token_valido = true;
        $usuario = $resultado->fetch_assoc();
        $usuario_id = $usuario['id'];
    } else {
        $mensaje = '<div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 15px;">El enlace de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo.</div>';
    }
    $stmt->close();
} else {
    $mensaje = '<div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 15px;">No se proporcionó ningún token de seguridad.</div>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nueva_contrasena']) && $token_valido) {
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if (strlen($nueva_contrasena) < 6) {
        $mensaje = '<div class="alert alert-warning" style="color: #856404; text-align: center; margin-bottom: 15px;">La contraseña debe tener al menos 6 caracteres.</div>';
    } elseif ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = '<div class="alert alert-warning" style="color: #856404; text-align: center; margin-bottom: 15px;">Las contraseñas no coinciden.</div>';
    } else {
        // Encriptar la nueva contraseña con el mismo estándar de tu sistema
        $password_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

        // Actualizar la contraseña y LIMPIAR el token para que el link quede inutilizable
        $update = "UPDATE registro SET contrasena = ?, reset_token = NULL, token_expiracion = NULL WHERE id = ?";
        $stmtUpdate = $conexion->prepare($update);
        $stmtUpdate->bind_param("si", $password_hash, $usuario_id);

        if ($stmtUpdate->execute()) {
            $mensaje = '<div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 15px;">¡Contraseña actualizada con éxito! Redirigiendo...</div>';
            $token_valido = false; // Ocultamos el formulario
            
            echo "<script>setTimeout(function() { window.location.href = '../index.php'; }, 3000);</script>";
        } else {
            $mensaje = '<div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 15px;">Hubo un error al actualizar la contraseña.</div>';
        }
        $stmtUpdate->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema Almacén</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/login/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="../assets/images/logo_el_legado.png" alt="Logo" class="logo" onerror="this.style.display='none'">
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
                    <a href="../index.php" style="text-decoration: underline; color: #007bff; font-weight: bold;">Volver a Iniciar Sesión</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>