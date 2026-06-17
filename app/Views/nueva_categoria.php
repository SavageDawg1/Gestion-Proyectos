<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/CategoriaController.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$controller = new CategoriaController();
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->guardarCategoria($_POST);
}

$page_title = "Nueva Categoría - Sistema de Almacén";
$page_css = ['/Software_Almacen/public/css/categorias/categorias.css'];

require_once 'layouts/header.php';
?>

<div class="main-content" style="padding: 20px;">
    <div class="modulo-header">
        <h2>Registrar Nueva Categoría</h2>
        <a href="categorias.php" class="btn-nuevo" style="background-color: #7f8c8d;">Volver al Listado</a>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="nueva_categoria.php" method="POST">
            <div class="form-group">
                <label>Nombre de la Categoría *</label>
                <input type="text" name="nombre" required class="form-control" placeholder="Ej. Lácteos, Herramientas...">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="4" class="form-control" style="resize: vertical;" placeholder="Opcional: Detalle de los productos en esta categoría"></textarea>
            </div>

            <button type="submit" class="btn-nuevo" style="width: 100%; border: none; cursor: pointer;">Guardar Categoría</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>