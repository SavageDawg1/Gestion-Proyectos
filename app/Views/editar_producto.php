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
$mover_layout = 'desplazar-bloque-completo';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id_producto = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->modificarProducto($id_producto, $_POST);
    
    // Si la edición fue exitosa, redirige al listado
    if (isset($mensaje['success']) && $mensaje['success'] === true) {
        header("Location: productos.php?status=editado");
        exit;
    }
}

$producto = $controller->obtenerProducto($id_producto);
if (!$producto) {
    header("Location: productos.php");
    exit;
}

$categorias = $categoriaController->listarCategorias();
$page_title = "Editar Producto - Sistema de Almacen";
$page_css = [
    '/Software_Almacen/public/css/productos/productos.css',
    '/Software_Almacen/public/css/login/login.css'
];

require_once 'layouts/header.php';
?>

<div class="product-form-page">
    <div class="auth-box product-auth-box">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>EDITAR PRODUCTO</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $mensaje['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST" class="product-auth-form">
            <div class="form-group">
                <label for="codigo">Codigo de Barra / SKU *</label>
                <div class="barcode-field">
                    <input type="text" id="codigo" name="codigo" placeholder="Codigo de Barra / SKU *" required value="<?php echo htmlspecialchars($producto['codigo']); ?>" data-barcode-input autocomplete="off">
                    <button type="button" class="btn-barcode-scan" data-barcode-scan>Scaner</button>
                </div>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre del Producto *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre del Producto *" required value="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div class="auth-form-row">
                <div class="form-group">
                    <label for="precio">Precio ($) *</label>
                    <input type="number" id="precio" name="precio" min="0" placeholder="Precio ($) *" required value="<?php echo intval($producto['precio']); ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Stock Actual *</label>
                    <input type="number" id="stock" name="stock" min="0" placeholder="Stock Actual" required value="<?php echo intval($producto['stock']); ?>">
                </div>
                <div class="form-group">
                    <label for="stock_minimo">Stock Minimo *</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" min="0" placeholder="Stock Minimo" required value="<?php echo intval($producto['stock_minimo'] ?? 5); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id">
                    <option value="">Sin Categoria</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($producto['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" aria-label="Fecha de vencimiento" value="<?php echo !empty($producto['fecha_vencimiento']) ? htmlspecialchars($producto['fecha_vencimiento']) : ''; ?>">
                <small>(Dejar en blanco si el producto no expira)</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar">GUARDAR CAMBIOS</button>
        </form>
    </div>
</div>

<script src="/Software_Almacen/public/js/productos/barcodeScanner.js?v=20260623-product-stock-min"></script>
<?php require_once 'layouts/footer.php'; ?>