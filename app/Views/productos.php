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

<div class="main-content">
    <div class="welcome-section">
        <h2>Inventario de Productos</h2>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta-pos <?php echo $mensaje['success'] ? 'alerta-exito' : 'alerta-error'; ?>">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="controles-tabla">
        <div class="buscador-container">
            <input type="text" id="buscadorProductos" class="buscador" placeholder="🔍 Buscar producto por código o nombre...">
        </div>
        <a href="nuevo_producto.php" class="btn-nuevo">+ Nuevo Producto</a>
    </div>

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
                    <td colspan="6" class="tabla-vacia">No hay productos registrados en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listaProductos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                        <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                        <td class="<?php echo ($producto['stock'] <= 5) ? 'stock-critico' : 'stock-normal'; ?>">
                            <?php echo $producto['stock']; ?>
                        </td>
                        <td>
                            <div class="acciones-wrapper">
                                <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-accion btn-editar">Editar</a>
                                <a href="productos.php?eliminar_id=<?php echo $producto['id']; ?>" 
                                   class="btn-accion btn-eliminar" 
                                   id="eliminar_prod_btn_<?php echo $producto['id']; ?>">
                                   Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Buscador Predictivo en tiempo real
    const buscador = document.getElementById('buscadorProductos');
    if (buscador) {
        buscador.addEventListener('keyup', function() {
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
    }

    // 2. Manejo limpio de confirmación de eliminación sin ensuciar el HTML (RNF-07)
    const botonesEliminar = document.querySelectorAll('.btn-eliminar');
    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este producto de forma permanente?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once 'layouts/footer.php'; ?>