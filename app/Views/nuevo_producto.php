<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/ProductoController.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$controller = new ProductoController();
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->guardarProducto($_POST);
}

$page_title = "Nuevo Producto - Sistema de Almacén";
$page_css = ['/Software_Almacen/public/css/productos/productos.css'];

require_once 'layouts/header.php';
?>

<div class="main-content" style="padding: 20px;">
    <div class="modulo-header">
        <h2>Registrar Nuevo Producto</h2>
        <a href="productos.php" class="btn-nuevo" style="background-color: #7f8c8d;">Volver al Listado</a>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="nuevo_producto.php" method="POST">
            <div class="form-group">
                <label>Código de Barra / SKU *</label>
                <input type="text" name="codigo" required class="form-control">
            </div>

            <div class="form-group">
                <label>Nombre del Producto *</label>
                <input type="text" name="nombre" required class="form-control">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" class="form-control" style="resize: vertical;"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio ($) *</label>
                    <input type="number" name="precio" min="0" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Stock Inicial</label>
                    <input type="number" name="stock" min="0" value="0" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id" class="form-control">
                    <option value="1">General</option>
                </select>
            </div>

            <button type="submit" class="btn-nuevo" style="width: 100%; border: none; cursor: pointer;">Guardar Producto</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>