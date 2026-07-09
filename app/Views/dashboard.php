<?php
/**
 * Página Dashboard.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../Controllers/ProductoController.php';
require_once '../Controllers/CategoriaController.php';
require_once '../Models/Venta.php';
require_once '../Models/Producto.php'; 

$page_title = "Dashboard - Almacén";

requireLogin();

$isLoggedIn = isAuthenticated();
$isAdmin = isset($_SESSION['rol_id']) && (int) $_SESSION['rol_id'] === 1;
$currentPage = 'dashboard';
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';

$productoController = new ProductoController();
$totalProductos = $productoController->contarProductos();
$totalStock = $productoController->obtenerStockTotal();

$categoriaController = new CategoriaController();
$totalCategorias = $categoriaController->contarCategorias();

$ventaModel = new Venta();
$ventasSemanales = $isAdmin ? $ventaModel->obtenerVentasUltimos7Dias() : [];

$productoModel = new Producto();
$alertasStock = $productoModel->obtenerStockCritico();
$alertasVencimiento = $productoModel->obtenerProximosVencimientos(30); 

$labelsGrafico = [];
$datosGrafico = [];

if (empty($ventasSemanales)) {
    for ($i = 6; $i >= 0; $i--) {
        $labelsGrafico[] = date('d/m', strtotime("-$i days"));
        $datosGrafico[] = 0;
    }
} else {
    foreach ($ventasSemanales as $v) {
        $labelsGrafico[] = date('d/m', strtotime($v['dia']));
        $datosGrafico[] = floatval($v['total_ingresos']) + floatval($v['total_fiado']);
    }
}
?>
<?php require_once 'layouts/header.php'; ?>
    <?php if ($isAdmin): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <div class="toast-container">
        <?php if (!empty($alertasStock)): ?>
            <div class="toast-alert toast-warning">
                <button class="toast-close" onclick="this.parentElement.remove()" title="Cerrar">&times;</button>
                <h4>⚠️ Stock Crítico</h4>
                <div class="toast-content">
                    <ul>
                        <?php foreach ($alertasStock as $item): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($item['nombre']); ?></strong> 
                                - Stock: <span class="stock-critico-num"><?php echo $item['stock']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($alertasVencimiento)): ?>
            <div class="toast-alert toast-danger">
                <button class="toast-close" onclick="this.parentElement.remove()" title="Cerrar">&times;</button>
                <h4>🚨 Vencimientos</h4>
                <div class="toast-content">
                    <ul>
                        <?php foreach ($alertasVencimiento as $item): ?>
                            <?php 
                                $fechaVencimiento = strtotime($item['fecha_vencimiento']);
                                $esVencido = $fechaVencimiento < time();
                            ?>
                            <li>
                                <strong><?php echo htmlspecialchars($item['nombre']); ?></strong> 
                                - <?php echo $esVencido ? '<span class="vencido-texto">¡VENCIDO!</span>' : 'Vence:'; ?> 
                                <strong><?php echo date('d/m/Y', $fechaVencimiento); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'reporte_listo'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.appAlert) {
                window.appAlert('Reporte generado y guardado exitosamente en tu historial.', 'success');
            }
        });
        window.history.replaceState(null, null, window.location.pathname);
    </script>
    <?php endif; ?>

    <div class="quick-actions">
        <h3>Acciones Rápidas</h3>
        <div class="action-buttons">
            <a href="nuevo_producto.php" class="btn btn-primary">Nuevo Producto</a>
            <a href="nueva_categoria.php" class="btn btn-secondary">Nueva Categoria</a>
            <a href="ventas.php" class="btn btn-success">Realizar Venta</a>
            <a href="ver_reportes.php?generar=1" class="btn btn-info">Generar Reporte</a>
            <a href="lista_compras.php" class="btn btn-warning btn-compras">Sugerencia de pedidos</a>
        </div>
    </div>

    <div class="stats-grid">
        <a href="productos.php" class="stat-card">
            <h3>Productos</h3>
            <p class="stat-number"><?php echo $totalProductos; ?></p>
        </a>

        <a href="categorias.php" class="stat-card">
            <h3>Categorias</h3>
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

    <?php if ($isAdmin): ?>
        <div class="chart-container dashboard-chart" id="resumen-general">
            <h3>Movimiento de Ventas (Últimos 7 días)</h3>
            <canvas id="graficoVentas" height="80"></canvas>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <script>
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
    <?php endif; ?>

<?php require_once 'layouts/footer.php'; ?>
