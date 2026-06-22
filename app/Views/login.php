<?php
/**
 * Página de Login
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';

$page_title = "Iniciar Sesión - El Legado";

// Si ya está logueado, redirigir al dashboard
if (isAuthenticated()) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../public/css/style.css?v=20260603-responsive-scale-3">
    <link rel="stylesheet" href="../../public/css/login/login.css?v=20260603-responsive-scale-3">
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Formulario de Login -->
        <div class="auth-box" id="login-box">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
                <h1>INICIAR SESIÓN</h1>
            </div>
            
            <div id="login-messages"></div>
            
            <form id="login-form">
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Correo Electrónico" required>
                </div>
                
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-ingresar">INGRESAR</button>
            </form>
            
            <div class="auth-links">
                <p><a href="#" onclick="toggleRecover()" class="link-forgot">¿Olvido su contraseña?</a></p>
            </div>
        </div>
        <!-- Formulario de Recuperar Contraseña (oculto) -->
        <div class="auth-box" id="recover-box" style="display: none;">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
                <h1>RECUPERAR CONTRASEÑA</h1>
            </div>
            
            <div id="recover-messages"></div>
            
            <p class="recover-subtitle">Ingresa tu email y recibirás instrucciones para recuperar tu contraseña</p>
            
            <form id="recover-form">
                <div class="form-group">
                    <input type="email" id="recover-email" name="email" placeholder="Tu Email" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-ingresar">ENVIAR INSTRUCCIONES</button>
            </form>
            
            <div class="auth-links">
                <p><a href="#" onclick="toggleRecover()" class="link-register">Volver</a></p>
            </div>
        </div>
    </div>
    
    <script src="../../public/js/script.js?v=2"></script>
    <script src="../../public/js/login/login.js?v=2"></script>
</body>
</html>
