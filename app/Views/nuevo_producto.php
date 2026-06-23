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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->guardarProducto($_POST);
}

$categorias = $categoriaController->listarCategorias();
$page_title = "Nuevo Producto - Sistema de Almacen";
$page_css = [
    '/Software_Almacen/public/css/productos/productos.css',
    '/Software_Almacen/public/css/login/login.css'
];

require_once 'layouts/header.php';
?>

<div class="product-form-page" style="display: flex; justify-content: center; align-items: center; min-height: 75vh; width: 100%;">
    
    <div class="auth-box product-auth-box" style="margin: 0 auto; float: none;">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>REGISTRAR PRODUCTO</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $mensaje['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form action="nuevo_producto.php" method="POST" class="product-auth-form">
            <div class="form-group">
                <label for="codigo">Codigo de Barra / SKU *</label>
                <div class="barcode-field">
                    <input type="text" id="codigo" name="codigo" placeholder="Codigo de Barra / SKU *" required data-barcode-input autocomplete="off">
                    <button type="button" class="btn-barcode-scan" data-barcode-scan>Scaner</button>
                </div>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre del Producto *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre del Producto *" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripcion"></textarea>
            </div>

            <div class="auth-form-row">
                <div class="form-group">
                    <label for="precio">Precio ($) *</label>
                    <input type="number" id="precio" name="precio" min="0" placeholder="Precio ($) *" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock Inicial *</label>
                    <input type="number" id="stock" name="stock" min="0" value="0" placeholder="Stock Inicial" required>
                </div>
                <div class="form-group">
                    <label for="stock_minimo">Stock Minimo *</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="5" placeholder="Stock Minimo" required>
                </div>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id">
                    <option value="">Sin Categoria</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" aria-label="Fecha de vencimiento">
                <small>(Dejar en blanco si el producto no expira)</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar">GUARDAR PRODUCTO</button>
        </form>

    </div>
</div>
<script src="/Software_Almacen/public/js/productos/barcodeScanner.js?v=20260623-product-stock-min"></script>
<?php require_once 'layouts/footer.php'; ?>
