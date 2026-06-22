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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id_producto = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->modificarProducto($id_producto, $_POST);
}

$producto = $controller->obtenerProducto($id_producto);
if (!$producto) {
    header("Location: productos.php");
    exit;
}

$categorias = $categoriaController->listarCategorias();
$page_title = "Editar Producto - Sistema de Almacén";
$page_css = '/Software_Almacen/public/css/productos/productos.css';

require_once 'layouts/header.php';
?>

<div class="view-stack">
    <div class="modulo-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?></h2>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Código de Barra / SKU *</label>
                <input type="text" name="codigo" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?php echo htmlspecialchars($producto['codigo']); ?>">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nombre del Producto *</label>
                <input type="text" name="nombre" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Descripción</label>
                <textarea name="descripcion" rows="3" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Precio ($) *</label>
                    <input type="number" name="precio" min="0" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?php echo intval($producto['precio']); ?>">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Stock Actual</label>
                    <input type="number" name="stock" min="0" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?php echo intval($producto['stock']); ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Categoría</label>
                <select name="categoria_id" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">Sin Categoría</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($producto['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Fecha de Vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?php echo !empty($producto['fecha_vencimiento']) ? htmlspecialchars($producto['fecha_vencimiento']) : ''; ?>">
                <small style="color: #666; font-size: 12px;">(Dejar en blanco si el producto no expira)</small>
            </div>

            <button type="submit" class="btn-nuevo" style="width: 100%; border: none; cursor: pointer; padding: 12px; background: #d55b22; color: white; border-radius: 6px; font-weight: bold; font-size: 16px;">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
