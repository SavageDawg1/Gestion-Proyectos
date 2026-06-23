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

if (isset($_GET['eliminar_id'])) {
    $mensaje = $controller->eliminarProducto($_GET['eliminar_id']);
}

$listaProductos = $controller->listarProductos();

$page_title = "Productos - Sistema de Almacen";
$page_css = ['/Software_Almacen/public/css/productos/productos.css'];

require_once 'layouts/header.php';
?>

    <div class="modulo-header">
        <h2>Productos</h2>
        <a href="/Software_Almacen/app/Views/nuevo_producto.php" class="btn-nuevo">+ Agregar Producto</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="page-alert <?php echo $mensaje['success'] ? 'page-alert-success' : 'page-alert-danger'; ?>">
            <?php echo htmlspecialchars($mensaje['message']); ?>
        </div>
    <?php endif; ?>

    <input type="text" id="buscadorProductos" class="buscador" placeholder="Buscar por codigo o nombre...">

    <div class="table-card table-responsive">
        <table class="tabla-productos" id="tablaProductos">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Categoria</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Min.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listaProductos)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No hay productos registrados en el sistema.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listaProductos as $producto): ?>
                        <?php $stockMinimo = intval($producto['stock_minimo'] ?? 5); ?>
                        <?php $stockBajo = intval($producto['stock']) <= $stockMinimo; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoria'); ?></td>
                            <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                            <td class="<?php echo $stockBajo ? 'stock-bajo' : ''; ?>"<?php echo $stockBajo ? ' style="color: #dc3545; font-weight: 800;"' : ''; ?>>
                                <?php echo $producto['stock']; ?>
                            </td>
                            <td><?php echo $stockMinimo; ?></td>
                            <td>
                                <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-accion btn-editar">Editar</a>
                                <a href="productos.php?eliminar_id=<?php echo $producto['id']; ?>"
                                   class="btn-accion btn-eliminar"
                                   onclick="return confirm('Seguro que deseas eliminar este producto de forma permanente?');">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<script>
    document.getElementById('buscadorProductos').addEventListener('keyup', function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll('#tablaProductos tbody tr');

        filas.forEach(function(fila) {
            if (fila.cells.length === 1) return;

            let codigo = fila.cells[0].textContent.toLowerCase();
            let nombre = fila.cells[1].textContent.toLowerCase();
            fila.style.display = codigo.includes(filtro) || nombre.includes(filtro) ? '' : 'none';
        });
    });
</script>

<?php require_once 'layouts/footer.php'; ?>
