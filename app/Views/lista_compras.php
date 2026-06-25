<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Models/Producto.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$productoModel = new Producto();
$productosAComprar = $productoModel->obtenerStockCritico(); 

$page_title = "Lista de Compras - Sistema de Almacen";
$page_css = ['/Software_Almacen/public/css/productos/productos.css']; 

require_once 'layouts/header.php';
?>

    <div class="modulo-header compras-header">
        <div class="compras-titulo">
            <h2>Listado de Reposición</h2>
        </div>
        <button onclick="window.print()" class="btn btn-info btn-imprimir">Imprimir Listado</button>
    </div>

    <div class="table-card table-responsive">
        <table class="tabla-productos" id="tablaCompras">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre del Producto</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th class="columna-sugerida-header">Cantidad a Comprar (Sugerida)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productosAComprar)): ?>
                    <tr>
                        <td colspan="5" class="mensaje-vacio-compras">
                            No hay productos que necesiten reposición en este momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productosAComprar as $producto): ?>
                        <?php 
                            $faltante = $producto['stock_minimo'] - $producto['stock'];
                            $cantidadSugerida = $faltante > 0 ? $faltante : 0;
                        ?>
                        <tr>
                            <td data-label="Código"><?php echo htmlspecialchars($producto['codigo']); ?></td>
                            <td data-label="Nombre"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td data-label="Stock Actual" class="stock-actual-critico">
                                <?php echo $producto['stock']; ?>
                            </td>
                            <td data-label="Stock Mínimo"><?php echo $producto['stock_minimo']; ?></td>
                            <td data-label="Sugerida" class="cantidad-sugerida">
                                +<?php echo $cantidadSugerida; ?> unidades
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php require_once 'layouts/footer.php'; ?>