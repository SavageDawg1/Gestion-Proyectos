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
$mover_layout = 'desplazar-bloque-completo';
// --- NUEVO: Detectar si se hizo clic en "Eliminar" ---
if (isset($_GET['eliminar_id'])) {
    $mensaje = $controller->eliminarProducto($_GET['eliminar_id']);
}

// Obtenemos la lista DESPUÉS de una posible eliminación para que la tabla se actualice sola
$listaProductos = $controller->listarProductos();

$page_title = "Productos - Sistema de Almacén";
$page_css = ['/Software_Almacen/public/css/productos/productos.css'];


require_once 'layouts/header.php';
?>


    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <input type="text" id="buscadorProductos" class="buscador" placeholder="Buscar por código o nombre...">

    <div class="table-card table-responsive">
    <table class="tabla-productos" id="tablaProductos">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listaProductos)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No hay productos registrados en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listaProductos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                        <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                        <td class="<?php echo ($producto['stock'] <= 5) ? 'stock-bajo' : ''; ?>">
                            <?php echo $producto['stock']; ?>
                        </td>
                        <td>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-accion btn-editar">Editar</a>
                            
                            <a href="productos.php?eliminar_id=<?php echo $producto['id']; ?>" 
                               class="btn-accion btn-eliminar" 
                               onclick="return confirm('¿Estás seguro de que deseas eliminar este producto de forma permanente?');">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
    // Tu script del buscador sigue igual...
    document.getElementById('buscadorProductos').addEventListener('keyup', function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll('#tablaProductos tbody tr');

        filas.forEach(function(fila) {
            if (fila.cells.length === 1) return; 

            let codigo = fila.cells[0].textContent.toLowerCase();
            let nombre = fila.cells[1].textContent.toLowerCase();
            
            if (codigo.includes(filtro) || nombre.includes(filtro)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
</script>

<?php require_once 'layouts/footer.php'; ?>
