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
$page_title = "Nuevo Producto - Sistema de Almacen";
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
            <h1>REGISTRAR PRODUCTO</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $mensaje['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form action="nuevo_producto.php" method="POST" class="product-auth-form">
            <div class="form-group">
                <input type="text" name="codigo" placeholder="Codigo de Barra / SKU *" required>
            </div>

            <div class="form-group">
                <input type="text" name="nombre" placeholder="Nombre del Producto *" required>
            </div>

            <div class="form-group">
                <textarea name="descripcion" rows="3" placeholder="Descripcion"></textarea>
            </div>

            <div class="auth-form-row">
                <div class="form-group">
                    <input type="number" name="precio" min="0" placeholder="Precio ($) *" required>
                </div>
                <div class="form-group">
                    <input type="number" name="stock" min="0" value="0" placeholder="Stock Inicial" required>
                </div>
            </div>

            <div class="form-group">
                <select name="categoria_id">
                    <option value="">Sin Categoria</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <input type="date" name="fecha_vencimiento" aria-label="Fecha de vencimiento">
                <small>(Dejar en blanco si el producto no expira)</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar">GUARDAR PRODUCTO</button>
        </form>

        <div class="auth-links">
            <p><a href="productos.php" class="link-register">Volver al listado</a></p>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
