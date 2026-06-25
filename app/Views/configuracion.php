<?php
/**
 * Configuracion del sistema.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../Models/Configuracion.php';

$page_title = "Configuracion - Almacen";

requireLogin();

$isAdmin = isset($_SESSION['rol_id']) && (int) $_SESSION['rol_id'] === 1;
$mensaje = null;
$configuracionModel = new Configuracion();

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_impuesto'])) {
    $impuesto = isset($_POST['impuesto_porcentaje']) ? floatval($_POST['impuesto_porcentaje']) : -1;
    if ($impuesto < 0 || $impuesto > 100) {
        $mensaje = ['success' => false, 'text' => 'El impuesto debe estar entre 0 y 100.'];
    } else {
        $ok = $configuracionModel->actualizarImpuestoPorcentaje($impuesto);
        $mensaje = $ok
            ? ['success' => true, 'text' => 'Impuesto global actualizado correctamente.']
            : ['success' => false, 'text' => 'No se pudo actualizar el impuesto global.'];
    }
}

$impuestoGlobal = $configuracionModel->obtenerImpuestoPorcentaje();
?>
<?php require_once 'layouts/header.php'; ?>

    <div class="modulo-header">
        <h2>Configuracion</h2>
    </div>

    <?php if ($mensaje): ?>
        <div class="page-alert <?php echo $mensaje['success'] ? 'page-alert-success' : 'page-alert-danger'; ?>" data-page-alert>
            <?php echo htmlspecialchars($mensaje['text']); ?>
        </div>
    <?php endif; ?>

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

            <div class="settings-card">
                <span class="settings-card-title">Impuesto Global</span>
                <span class="settings-card-copy">Este porcentaje se aplica automaticamente al calcular el precio de venta de todos los productos.</span>
                <form method="POST" action="configuracion.php" style="margin-top: 12px;">
                    <label for="impuesto_porcentaje">Impuesto (%)</label>
                    <input
                        type="number"
                        id="impuesto_porcentaje"
                        name="impuesto_porcentaje"
                        min="0"
                        max="100"
                        step="0.01"
                        value="<?php echo htmlspecialchars(number_format($impuestoGlobal, 2, '.', '')); ?>"
                        required
                    >
                    <button type="submit" name="actualizar_impuesto" class="btn btn-primary" style="margin-top: 8px;">Guardar Impuesto</button>
                </form>
            </div>
        <?php else: ?>
            <div class="settings-card settings-card-muted">
                <span class="settings-card-title">Sin opciones disponibles</span>
                <span class="settings-card-copy">Tu perfil no tiene permisos para administrar configuraciones.</span>
            </div>
        <?php endif; ?>
    </div>

<?php require_once 'layouts/footer.php'; ?>
