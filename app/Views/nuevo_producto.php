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
    
    // Si se guardó correctamente, revisamos qué botón presionó
    if (isset($mensaje['success']) && $mensaje['success'] === true) {
        if (isset($_POST['accion']) && $_POST['accion'] === 'guardar_y_continuar') {
            // Recarga y permite seguir agregando
            header("Location: nuevo_producto.php?status=creado_continuar");
            exit;
        } else {
            // Regresa al listado
            header("Location: productos.php?status=creado");
            exit;
        }
    }
}

// Capturar el estatus de continuidad para mostrar la alerta
if (isset($_GET['status']) && $_GET['status'] === 'creado_continuar') {
    $mensaje = [
        'success' => true,
        'message' => 'Producto registrado exitosamente. Puedes agregar el siguiente.'
    ];
}

$categorias = $categoriaController->listarCategorias();
$categoriasJson = json_encode($categorias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$impuestoGlobal = $controller->obtenerImpuestoGlobal();
$page_title = "Nuevo Producto - Sistema de Almacen";
$page_css = [
    '/Software_Almacen/public/css/productos/productos.css',
    '/Software_Almacen/public/css/login/login.css'
];


$back_title = "Volver a Productos";
$back_url = "productos.php";

require_once 'layouts/header.php';
?>

<div class="product-form-page">
    
    <div class="auth-box product-auth-box">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>REGISTRAR PRODUCTO</h1>
        </div>

        <?php if ($mensaje): ?>
            <?php $alertClass = $mensaje['success'] ? 'alert-success' : (($mensaje['type'] ?? '') === 'warning' ? 'alert-warning' : 'alert-danger'); ?>
            <div class="alert <?php echo $alertClass; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form id="nuevo-producto-form" action="nuevo_producto.php" method="POST" class="product-auth-form" data-product-form>
            <div class="form-group">
                <label for="codigo">Código de Barra / SKU *</label>
                <div class="barcode-field">
                    <input type="text" id="codigo" name="codigo" placeholder="Código de Barra / SKU *" required data-barcode-input autocomplete="off">
                    <button type="button" class="btn-barcode-scan" data-barcode-scan>Scaner</button>
                </div>
                <small>El código debe ser único para evitar productos duplicados.</small>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre del Producto *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre del Producto *" required>
                <small class="field-status" data-product-name-status></small>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción"></textarea>
            </div>

            <div class="auth-form-row">
                <div class="form-group">
                    <label for="costo">Costo Base ($) *</label>
                    <input type="number" id="costo" name="costo" min="1" step="0.01" placeholder="Costo Base ($) *" required>
                </div>
                <div class="form-group">
                    <label for="porcentaje_ganancia">Ganancia (%) *</label>
                    <input type="number" id="porcentaje_ganancia" name="porcentaje_ganancia" min="0" max="99.99" step="0.01" value="30" required>
                </div>
                <div class="form-group">
                    <label for="impuesto_ref">Impuesto Global (%)</label>
                    <input type="number" id="impuesto_ref" value="<?php echo htmlspecialchars(number_format($impuestoGlobal, 2, '.', '')); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="precio">Precio de Venta ($)</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" placeholder="Precio de Venta ($)" readonly>
                </div>
            </div>

            <div class="auth-form-row">
                <div class="form-group">
                    <label for="tipo_venta">Tipo de Venta *</label>
                    <select id="tipo_venta" name="tipo_venta" required>
                        <option value="unidad">Por unidad</option>
                        <option value="granel">A granel</option>
                    </select>
                </div>
                <div class="form-group" id="unidad_granel_group" style="display:none;">
                    <label for="unidad_granel">Precio base de granel *</label>
                    <select id="unidad_granel" name="unidad_granel">
                        <option value="250g">Cuarto kilo (250 g)</option>
                        <option value="500g">Medio kilo (500 g)</option>
                        <option value="1000g" selected>Un kilo (1000 g)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock" id="label_stock">Stock Inicial *</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" value="0" placeholder="Stock Inicial" required>
                </div>
                <div class="form-group">
                    <label for="stock_minimo">Stock Mínimo *</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" value="5" placeholder="Stock Mínimo" required>
                </div>
            </div>

            <div class="form-group">
                <label for="categoria_nombre">Categoría</label>
                <input type="hidden" id="categoria_id" name="categoria_id">
                <input type="text" id="categoria_nombre" name="categoria_nombre" list="categorias_disponibles" placeholder="Escribe o selecciona una categoría" autocomplete="off">
                <datalist id="categorias_disponibles">
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['nombre']); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <small class="field-status" data-category-status></small>
                <div class="category-suggestions" data-category-suggestions></div>
            </div>

            <div class="form-group">
                <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" aria-label="Fecha de vencimiento" min="<?php echo date('Y-m-d'); ?>">
                <small>(Dejar en blanco si el producto no expira)</small>
            </div>

            <button type="submit" name="accion" value="guardar_y_continuar" class="btn btn-secondary btn-block btn-ingresar form-secondary-action">GUARDAR Y AGREGAR OTRO</button>
            
            <button type="submit" name="accion" value="guardar" class="btn btn-primary btn-block btn-ingresar">GUARDAR Y VOLVER AL LISTADO</button>
        </form>

    </div>
</div>

<script>
    window.productFormCategories = <?php echo $categoriasJson ?: '[]'; ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('nuevo-producto-form');
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
            stockLabel.textContent = esGranel ? 'Stock Inicial (gramos) *' : 'Stock Inicial *';
        }

        costoInput.addEventListener('input', calcularPrecio);
        gananciaInput.addEventListener('input', calcularPrecio);
        tipoVentaInput.addEventListener('change', actualizarTipoVenta);
        calcularPrecio();
        actualizarTipoVenta();
        
        if (form) {
            form.addEventListener('invalid', function(e) {
                e.preventDefault();
                
                const primerCampoInvalido = form.querySelector(':invalid');
                
                if (primerCampoInvalido) {
                    primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    primerCampoInvalido.focus({ preventScroll: true });
                    
                    primerCampoInvalido.style.border = '2px solid red';
                    setTimeout(() => {
                        primerCampoInvalido.style.border = '';
                    }, 2000);
                }
            }, true); 
        }
    });
</script>

<script src="/Software_Almacen/public/js/productos/barcodeScanner.js?v=20260623-product-stock-min"></script>
<script src="/Software_Almacen/public/js/productos/productFormEnhancements.js?v=20260709-product-name-category"></script>
<?php require_once 'layouts/footer.php'; ?>
