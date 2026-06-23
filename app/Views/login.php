<?php
/**
 * Pagina de Login
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';

$page_title = "Iniciar Sesion - El Legado";

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
    <link rel="stylesheet" href="../../public/css/style.css?v=20260623-form-labels">
    <link rel="stylesheet" href="../../public/css/login/login.css?v=20260623-form-labels">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="auth-box" id="login-box">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
                <h1>INICIAR SESION</h1>
            </div>

            <div id="login-messages"></div>

            <form id="login-form">
                <div class="form-group">
                    <label for="email">Correo Electronico</label>
                    <input type="email" id="email" name="email" placeholder="Correo Electronico" required>
                </div>

                <div class="form-group">
                    <label for="password">Contrasena</label>
                    <input type="password" id="password" name="password" placeholder="Contrasena" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-ingresar">INGRESAR</button>
            </form>

            <div class="auth-links">
                <p><a href="#" onclick="toggleRecover()" class="link-forgot">Olvido su contrasena?</a></p>
            </div>
        </div>

        <div class="auth-box" id="recover-box" style="display: none;">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
                <h1>RECUPERAR CONTRASENA</h1>
            </div>

            <div id="recover-messages"></div>

            <p class="recover-subtitle">Ingresa tu email y recibiras instrucciones para recuperar tu contrasena</p>

            <form id="recover-form">
                <div class="form-group">
                    <label for="recover-email">Correo Electronico</label>
                    <input type="email" id="recover-email" name="email" placeholder="Tu Email" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-ingresar">ENVIAR INSTRUCCIONES</button>
            </form>

            <div class="auth-links">
                <p><a href="#" onclick="toggleRecover()" class="link-register">Volver</a></p>
            </div>
        </div>
    </div>

    <script src="../../public/js/script.js?v=20260623-form-labels"></script>
    <script src="../../public/js/login/login.js?v=20260623-form-labels"></script>
</body>
</html>
