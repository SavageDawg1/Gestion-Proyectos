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

// Validar que el ID venga en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id_producto = $_GET['id'];

// Procesar el formulario si se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->modificarProducto($id_producto, $_POST);
}

// Obtener los datos actuales del producto para rellenar los inputs
$producto = $controller->obtenerProducto($id_producto);

// Si el producto no existe en la BD, redirigir
if (!$producto) {
    header("Location: productos.php");
    exit;
}

$page_title = "Editar Producto - Sistema de Almacén";
$page_css = ['/Software_Almacen/public/css/productos/productos.css'];

require_once 'layouts/header.php';
?>

<div class="main-content" style="padding: 20px;">
    <div class="modulo-header">
        <h2>Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?></h2>
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
        <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST">
            <div class="form-group">
                <label>Código de Barra / SKU *</label>
                <input type="text" name="codigo" required class="form-control" 
                       value="<?php echo htmlspecialchars($producto['codigo']); ?>">
            </div>

            <div class="form-group">
                <label>Nombre del Producto *</label>
                <input type="text" name="nombre" required class="form-control" 
                       value="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" class="form-control" style="resize: vertical;"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio ($) *</label>
                    <input type="number" name="precio" min="0" required class="form-control" 
                           value="<?php echo intval($producto['precio']); ?>">
                </div>
                <div class="form-group">
                    <label>Stock Actual</label>
                    <input type="number" name="stock" min="0" required class="form-control" 
                           value="<?php echo intval($producto['stock']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id" class="form-control">
                    <option value="1" <?php echo ($producto['categoria_id'] == 1) ? 'selected' : ''; ?>>General</option>
                </select>
            </div>

            <button type="submit" class="btn-nuevo" style="width: 100%; border: none; cursor: pointer;">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>