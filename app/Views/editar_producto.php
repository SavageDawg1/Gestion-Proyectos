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
$impuestoGlobal = $controller->obtenerImpuestoGlobal();
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

        <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST" class="product-auth-form" data-dirty-guard>
            <div class="form-group">
                <label for="codigo">Codigo de Barra / SKU *</label>
                <div class="barcode-field">
                    <input type="text" id="codigo" name="codigo" placeholder="Codigo de Barra / SKU *" required value="<?php echo htmlspecialchars($producto['codigo']); ?>" data-barcode-input autocomplete="off">
                    <button type="button" class="btn-barcode-scan" data-barcode-scan>Scaner</button>
                </div>
                <small>Usa el mismo codigo si solo estas corrigiendo datos del producto.</small>
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
                    <label for="costo">Costo Base ($) *</label>
                    <input type="number" id="costo" name="costo" min="1" step="0.01" placeholder="Costo Base ($) *" required value="<?php echo htmlspecialchars(number_format((float)($producto['costo'] ?? $producto['precio']), 2, '.', '')); ?>">
                </div>
                <div class="form-group">
                    <label for="porcentaje_ganancia">Ganancia (%) *</label>
                    <input type="number" id="porcentaje_ganancia" name="porcentaje_ganancia" min="0" max="99.99" step="0.01" required value="<?php echo htmlspecialchars(number_format((float)($producto['porcentaje_ganancia'] ?? 30), 2, '.', '')); ?>">
                </div>
                <div class="form-group">
                    <label for="impuesto_ref">Impuesto Global (%)</label>
                    <input type="number" id="impuesto_ref" value="<?php echo htmlspecialchars(number_format($impuestoGlobal, 2, '.', '')); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="precio">Precio de Venta ($)</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" placeholder="Precio de Venta ($)" readonly value="<?php echo htmlspecialchars(number_format((float)$producto['precio'], 2, '.', '')); ?>">
                </div>
            </div>

            <div class="auth-form-row">
                <div class="form-group">
                    <label for="tipo_venta">Tipo de Venta *</label>
                    <?php $tipoVentaActual = ($producto['tipo_venta'] ?? 'unidad') === 'granel' ? 'granel' : 'unidad'; ?>
                    <select id="tipo_venta" name="tipo_venta" required>
                        <option value="unidad" <?php echo $tipoVentaActual === 'unidad' ? 'selected' : ''; ?>>Por unidad</option>
                        <option value="granel" <?php echo $tipoVentaActual === 'granel' ? 'selected' : ''; ?>>A granel</option>
                    </select>
                </div>
                <div class="form-group" id="unidad_granel_group" style="display:none;">
                    <label for="unidad_granel">Precio base de granel *</label>
                    <?php $unidadGranelActual = $producto['unidad_granel'] ?? '1000g'; ?>
                    <select id="unidad_granel" name="unidad_granel">
                        <option value="250g" <?php echo $unidadGranelActual === '250g' ? 'selected' : ''; ?>>Cuarto kilo (250 g)</option>
                        <option value="500g" <?php echo $unidadGranelActual === '500g' ? 'selected' : ''; ?>>Medio kilo (500 g)</option>
                        <option value="1000g" <?php echo $unidadGranelActual === '1000g' ? 'selected' : ''; ?>>Un kilo (1000 g)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock" id="label_stock">Stock Actual *</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" placeholder="Stock Actual" required value="<?php echo intval($producto['stock']); ?>">
                </div>
                <div class="form-group">
                    <label for="stock_minimo">Stock Minimo *</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" placeholder="Stock Minimo" required value="<?php echo intval($producto['stock_minimo'] ?? 5); ?>">
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

            <button type="submit" class="btn btn-primary btn-block btn-ingresar" data-dirty-submit data-confirm-message="Se guardar&aacute;n los cambios de este producto. &iquest;Confirmas la edici&oacute;n?" disabled>GUARDAR CAMBIOS</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const costoInput = document.getElementById('costo');
        const gananciaInput = document.getElementById('porcentaje_ganancia');
        const precioInput = document.getElementById('precio');
        const impuestoInput = document.getElementById('impuesto_ref');
        const tipoVentaInput = document.getElementById('tipo_venta');
        const unidadGranelGroup = document.getElementById('unidad_granel_group');
        const unidadGranelInput = document.getElementById('unidad_granel');
        const stockLabel = document.getElementById('label_stock');

        function calcularPrecio() {
            const costo = parseFloat(costoInput.value || '0');
            const ganancia = parseFloat(gananciaInput.value || '0');
            const impuesto = parseFloat(impuestoInput.value || '0');

            if (costo <= 0 || ganancia < 0 || ganancia >= 100) {
                precioInput.value = '';
                return;
            }

            const gananciaDecimal = ganancia / 100;
            const impuestoDecimal = impuesto / 100;
            const precioSinImpuesto = costo / (1 - gananciaDecimal);
            const total = precioSinImpuesto * (1 + impuestoDecimal);
            precioInput.value = total.toFixed(2);
        }

        function actualizarTipoVenta() {
            const esGranel = tipoVentaInput.value === 'granel';
            unidadGranelGroup.style.display = esGranel ? '' : 'none';
            unidadGranelInput.required = esGranel;
            stockLabel.textContent = esGranel ? 'Stock Actual (gramos) *' : 'Stock Actual *';
        }

        costoInput.addEventListener('input', calcularPrecio);
        gananciaInput.addEventListener('input', calcularPrecio);
        tipoVentaInput.addEventListener('change', actualizarTipoVenta);

        calcularPrecio();
        actualizarTipoVenta();
    });
</script>

<script src="/Software_Almacen/public/js/productos/barcodeScanner.js?v=20260623-product-stock-min"></script>
<?php require_once 'layouts/footer.php'; ?>
