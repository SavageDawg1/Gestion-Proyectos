<?php
/**
 * Pagina de Login
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';

$page_title = "Iniciar Sesion - El Legado";
$asset_version = "20260709-login-refresh";

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
    <link rel="stylesheet" href="../../public/css/style.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="../../public/css/login/login.css?v=<?php echo $asset_version; ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="auth-box" id="login-box">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
                <h1>INICIAR SESIÓN</h1>
            </div>

            <div id="login-messages"></div>

            <form id="login-form">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="Correo Electrónico" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-ingresar">INGRESAR</button>
            </form>

            <div class="auth-links">
                <p><a href="#" onclick="toggleRecover()" class="link-forgot">¿Olvidó su contraseña?</a></p>
            </div>
        </div>

        <div class="auth-box" id="recover-box" style="display: none;">
            <div class="auth-header">
                <img src="../../public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
                <h1>RECUPERAR CONTRASEÑA</h1>
            </div>

            <div id="recover-messages"></div>

            <p class="recover-subtitle">Ingresa tu email y recibirás instrucciones para recuperar tu contraseña</p>

            <form id="recover-form">
                <div class="form-group">
                    <label for="recover-email">Correo Electrónico</label>
                    <input type="email" id="recover-email" name="email" placeholder="Tu Email" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-ingresar">ENVIAR INSTRUCCIONES</button>
            </form>

            <div class="auth-links">
                <p><a href="#" onclick="toggleRecover()" class="link-register">Volver</a></p>
            </div>
        </div>
    </div>

    <script src="../../public/js/script.js?v=<?php echo $asset_version; ?>"></script>
    <script src="../../public/js/login/login.js?v=<?php echo $asset_version; ?>"></script>
</body>
</html>
