<?php
/**
 * Registro de usuarios.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$page_title = "Registrar Usuario - Almacen";

requireLogin();

if (!isset($_SESSION['rol_id']) || (int) $_SESSION['rol_id'] !== 1) {
    header("Location: dashboard.php");
    exit;
}

$isLoggedIn = isAuthenticated();
$currentPage = 'registro_usuario';
$user = getCurrentUser();
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';
?>
<?php require_once 'layouts/header.php'; ?>

    <div class="dashboard-container">
        <aside class="sidebar">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="sidebar-logo">
            <h3>Menu Administrador</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="#productos">Productos</a></li>
                <li><a href="#categorias">Categorias</a></li>
                <li><a href="registro_usuario.php" class="active">Registrar Usuario</a></li>
                <li><a href="#">Ver Reportes</a></li>
                <li><a href="#configuracion">Configuracion</a></li>
            </ul>
        </aside>

        <div class="main-content">
            <div class="quick-actions">
                <h3>Registrar Usuario</h3>
                <div id="register-messages"></div>

                <form id="register-form" class="admin-register-form">
                    <div class="form-group">
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre Completo" required>
                    </div>

                    <div class="form-group">
                        <input type="text" id="rut_display" placeholder="R.U.T" required>
                    </div>

                    <div class="form-group">
                        <input type="email" id="register-email" name="email" placeholder="Correo Electronico" required>
                    </div>

                    <div class="form-group">
                        <input type="password" id="register-password" name="password" placeholder="Contrasena" required>
                    </div>

                    <div class="form-group">
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirmar Contrasena" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/Software_Almacen/public/js/script.js?v=2"></script>
    <script src="/Software_Almacen/public/js/login/login.js?v=2"></script>

<?php require_once 'layouts/footer.php'; ?>