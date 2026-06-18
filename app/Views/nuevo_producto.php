<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/ProductoController.php';
require_once '../Controllers/CategoriaController.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$controller = new ProductoController();
$categoriaController = new CategoriaController();
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->guardarProducto($_POST);
}

$categorias = $categoriaController->listarCategorias();
$page_title = "Nuevo Producto - Sistema de Almacén";
$page_css = '/Software_Almacen/public/css/productos/productos.css';

require_once 'layouts/header.php';
?>

<div class="main-content" style="padding: 20px;">
    <div class="modulo-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Registrar Nuevo Producto</h2>
        <a href="productos.php" class="btn-nuevo" style="background-color: #7f8c8d; text-decoration: none; color: white; padding: 10px 15px; border-radius: 6px; font-weight: bold;">Volver al Listado</a>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form action="nuevo_producto.php" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Código de Barra / SKU *</label>
                <input type="text" name="codigo" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nombre del Producto *</label>
                <input type="text" name="nombre" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Descripción</label>
                <textarea name="descripcion" rows="3" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Precio ($) *</label>
                    <input type="number" name="precio" min="0" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Stock Inicial</label>
                    <input type="number" name="stock" min="0" value="0" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Categoría</label>
                <select name="categoria_id" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">Sin Categoría</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Fecha de Vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <small style="color: #666; font-size: 12px;">(Dejar en blanco si el producto no expira)</small>
            </div>

            <button type="submit" class="btn-nuevo" style="width: 100%; border: none; cursor: pointer; padding: 12px; background: #d55b22; color: white; border-radius: 6px; font-weight: bold; font-size: 16px;">Guardar Producto</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>