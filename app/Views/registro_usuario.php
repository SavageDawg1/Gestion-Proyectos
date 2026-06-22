<?php
/**
 * Registro de usuarios.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$page_title = "Registrar Usuario - Almacén";

requireLogin();

if (!isset($_SESSION['rol_id']) || (int) $_SESSION['rol_id'] !== 1) {
    header("Location: dashboard.php");
    exit;
}

$isLoggedIn = isAuthenticated();
$currentPage = 'registro_usuario';
$user = getCurrentUser();
$page_css = [
    '/Software_Almacen/public/css/dashboard/dashboard.css',
    '/Software_Almacen/public/css/login/login.css'
];
?>
<?php require_once 'layouts/header.php'; ?>

    <div class="auth-box admin-register-box">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>REGISTRAR USUARIO</h1>
        </div>

        <div id="register-messages"></div>

        <form id="register-form" class="admin-register-form">
            <div class="form-group">
                <input type="text" id="nombre" name="nombre" placeholder="Nombre Completo" required>
            </div>

            <div class="form-group">
                <input type="text" id="rut_display" placeholder="R.U.T" required>
            </div>

            <div class="form-group">
                <input type="email" id="register-email" name="email" placeholder="Correo Electrónico" required>
            </div>

            <div class="form-group">
                <input type="password" id="register-password" name="password" placeholder="Contraseña" required>
            </div>

            <div class="form-group">
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirmar Contraseña" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar">REGISTRAR USUARIO</button>
        </form>
    </div>

    <script src="/Software_Almacen/public/js/script.js?v=2"></script>
    <script src="/Software_Almacen/public/js/login/login.js?v=2"></script>

<?php require_once 'layouts/footer.php'; ?>
