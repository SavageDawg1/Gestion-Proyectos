<?php
/**
 * Página Dashboard.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../Controllers/ProductoController.php';
require_once '../Controllers/CategoriaController.php';
require_once '../Models/Venta.php'; // Importamos el modelo de ventas para el gráfico

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

// Obtener datos reales de la base de datos para el gráfico
$ventaModel = new Venta();
$ventasSemanales = $ventaModel->obtenerVentasUltimos7Dias();

$labelsGrafico = [];
$datosGrafico = [];

if (empty($ventasSemanales)) {
    // Si no hay registros, estructura los últimos 7 días en cero para mantener el diseño gráfico
    for ($i = 6; $i >= 0; $i--) {
        $labelsGrafico[] = date('d/m', strtotime("-$i days"));
        $datosGrafico[] = 0;
    }
} else {
    // Extrae los días mapeados y calcula el total combinado (ingresos directos + fiados)
    foreach ($ventasSemanales as $v) {
        $labelsGrafico[] = date('d/m', strtotime($v['dia']));
        $datosGrafico[] = floatval($v['total_ingresos']) + floatval($v['total_fiado']);
    }
}
?>
<?php require_once 'layouts/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'reporte_listo'): ?>
    <script>
        // Una pequeña alerta para confirmar que se generó por detrás
        alert('¡Reporte generado y guardado exitosamente en tu historial!');
        
        // Limpiamos la URL para que no vuelva a salir la alerta si recargas la página
        window.history.replaceState(null, null, window.location.pathname);
    </script>
    <?php endif; ?>

    <div class="quick-actions">
        <h3>Acciones Rápidas</h3>
        <div class="action-buttons">
            <a href="nuevo_producto.php" class="btn btn-primary">+ Nuevo Producto</a>
            <a href="nueva_categoria.php" class="btn btn-secondary">+ Nueva Categoría</a>
            <a href="ventas.php" class="btn btn-success">Realizar Venta</a>
            <a href="../Controllers/GenerarReportesController.php" class="btn btn-info">Generar Reporte</a>
            <a href="ver_reportes.php" class="btn btn-secondary">Ver Reportes</a>
            <a href="cobrar_fiado.php" class="btn btn-cobro">Cobrar Fiado</a>
        </div>
    </div>

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

    <script>
        // Inyección de arreglos procesados en PHP a las estructuras nativas de JavaScript
        const labelsDinamicos = <?php echo json_encode($labelsGrafico); ?>;
        const datosDinamicos = <?php echo json_encode($datosGrafico); ?>;

        const ctx = document.getElementById('graficoVentas').getContext('2d');
        const graficoVentas = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labelsDinamicos,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: datosDinamicos,
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