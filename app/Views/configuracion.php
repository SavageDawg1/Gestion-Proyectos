<?php
/**
 * Configuracion del sistema.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$page_title = "Configuracion - Almacen";

requireLogin();

$isAdmin = isset($_SESSION['rol_id']) && (int) $_SESSION['rol_id'] === 1;
?>
<?php require_once 'layouts/header.php'; ?>

    <div class="modulo-header">
        <h2>Configuracion</h2>
    </div>

    <div class="settings-grid">
        <?php if ($isAdmin): ?>
            <a href="/Software_Almacen/app/Views/registro_usuario.php" class="settings-card">
                <span class="settings-card-title">Registrar Usuario</span>
                <span class="settings-card-copy">Crear usuarios y asignar rol de Administrador o Vendedor.</span>
            </a>

            <a href="/Software_Almacen/app/Views/editar_usuarios.php" class="settings-card">
                <span class="settings-card-title">Editar Usuarios</span>
                <span class="settings-card-copy">Modificar datos, roles, estado y contrasenas de acceso.</span>
            </a>
        <?php else: ?>
            <div class="settings-card settings-card-muted">
                <span class="settings-card-title">Sin opciones disponibles</span>
                <span class="settings-card-copy">Tu perfil no tiene permisos para administrar configuraciones.</span>
            </div>
        <?php endif; ?>
    </div>

<?php require_once 'layouts/footer.php'; ?>
