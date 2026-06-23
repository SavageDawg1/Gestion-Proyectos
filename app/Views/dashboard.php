<?php
/**
 * Página Dashboard.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../Controllers/ProductoController.php';
require_once '../Controllers/CategoriaController.php';

$page_title = "Dashboard - Almacén";

requireLogin();

$isLoggedIn = isAuthenticated();
$currentPage = 'dashboard';
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';

$productoController = new ProductoController();
$totalProductos = $productoController->contarProductos();
$totalStock = $productoController->obtenerStockTotal();

$categoriaController = new CategoriaController();
$totalCategorias = $categoriaController->contarCategorias();
?>
<?php require_once 'layouts/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="stats-grid">
        <a href="productos.php" class="stat-card">
            <h3>Productos</h3>
            <p class="stat-number"><?php echo $totalProductos; ?></p>
        </a>

        <a href="categorias.php" class="stat-card">
            <h3>Categorías</h3>
            <p class="stat-number"><?php echo $totalCategorias; ?></p>
        </a>

        <a href="#stock" class="stat-card">
            <h3>Stock Total</h3>
            <p class="stat-number"><?php echo $totalStock; ?></p>
        </a>

        <a href="#transacciones" class="stat-card">
            <h3>Transacciones</h3>
            <p class="stat-number">0</p>
        </a>
    </div>

    <div class="chart-container dashboard-chart" id="resumen-general">
        <h3>Movimiento de Ventas (Últimos 7 días)</h3>
        <canvas id="graficoVentas" height="80"></canvas>
    </div>

    <div class="quick-actions">
        <h3>Acciones Rápidas</h3>
        <div class="action-buttons">
            <a href="nuevo_producto.php" class="btn btn-primary">+ Nuevo Producto</a>
            <a href="nueva_categoria.php" class="btn btn-secondary">+ Nueva Categoría</a>
            <a href="ventas.php" class="btn btn-success">Realizar Venta</a>
            <a href="#" class="btn btn-info">Generar Reporte</a>
            <a href="cobrar_fiado.php" class="btn btn-cobro">Cobrar Fiado</a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('graficoVentas').getContext('2d');
        const graficoVentas = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                datasets: [{
                    label: 'Ingresos ($)',
                    data: [12000, 19000, 15000, 22000, 18000, 35000, 42000],
                    backgroundColor: 'rgba(213, 91, 34, 0.7)',
                    borderColor: 'rgba(213, 91, 34, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

<?php require_once 'layouts/footer.php'; ?>
