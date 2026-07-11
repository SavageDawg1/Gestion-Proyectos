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
$categoriaActualNombre = '';
foreach ($categorias as $cat) {
    if ((int) ($producto['categoria_id'] ?? 0) === (int) $cat['id']) {
        $categoriaActualNombre = $cat['nombre'];
        break;
    }
}
$categoriasJson = json_encode($categorias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
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
            <?php $alertClass = $mensaje['success'] ? 'alert-success' : (($mensaje['type'] ?? '') === 'warning' ? 'alert-warning' : 'alert-danger'); ?>
            <div class="alert <?php echo $alertClass; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST" class="product-auth-form" data-dirty-guard data-product-form data-product-id="<?php echo (int) $id_producto; ?>">
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
                <input type="text" id="nombre" name="nombre" placeholder="Nombre del Producto *" required value="<?php echo htmlspecialchars($producto['nombre']); ?>" data-original-name="<?php echo htmlspecialchars($producto['nombre']); ?>">
                <small class="field-status" data-product-name-status></small>
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
                <label for="categoria_nombre">Categoria</label>
                <input type="hidden" id="categoria_id" name="categoria_id" value="<?php echo (int) ($producto['categoria_id'] ?? 0); ?>">
                <div class="category-combobox" data-category-combobox>
                    <input type="text" id="categoria_nombre" name="categoria_nombre" placeholder="Escribe o selecciona una categoria" autocomplete="off" value="<?php echo htmlspecialchars($categoriaActualNombre); ?>" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="categoria_sugerencias">
                    <button type="button" class="category-combobox-toggle" data-category-toggle aria-label="Mostrar categorias">&#9662;</button>
                    <div class="category-suggestions" id="categoria_sugerencias" data-category-suggestions role="listbox"></div>
                </div>
                <small class="field-status" data-category-status></small>
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
    window.productFormCategories = <?php echo $categoriasJson ?: '[]'; ?>;

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
<script src="/Software_Almacen/public/js/productos/productFormEnhancements.js?v=20260709-product-category-combobox"></script>
<?php require_once 'layouts/footer.php'; ?>
