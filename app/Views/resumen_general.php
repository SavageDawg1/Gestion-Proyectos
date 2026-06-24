<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/ProductoController.php';
require_once '../Models/Venta.php'; // Agregamos el modelo de Ventas para el gráfico

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

// 1. Obtener datos de Productos (Stock y Vencimientos)
$productoController = new ProductoController();
$stockCritico = $productoController->listarStockCritico();
$vencimientos = $productoController->listarProximosVencimientos();

// 2. Obtener datos reales de Ventas para el gráfico
$ventaModel = new Venta();
$ventasSemanales = $ventaModel->obtenerVentasUltimos7Dias();

// Preparar los arreglos para inyectarlos en Chart.js
$labelsGrafico = [];
$datosGrafico = [];

if (empty($ventasSemanales)) {
    // Si no hay ventas, rellenamos con los últimos 7 días en cero para que el gráfico no quede vacío
    for ($i = 6; $i >= 0; $i--) {
        $labelsGrafico[] = date('d/m', strtotime("-$i days"));
        $datosGrafico[] = 0;
    }
} else {
    // Si hay ventas, extraemos las fechas y sumamos los totales diarios
    foreach ($ventasSemanales as $v) {
        $labelsGrafico[] = date('d/m', strtotime($v['dia']));
        // Sumamos los ingresos reales y los fiados para el total del día
        $datosGrafico[] = floatval($v['total_ingresos']) + floatval($v['total_fiado']);
    }
}

$page_title = "Resumen General - Sistema de Almacen";
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';

require_once 'layouts/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="view-stack">
    <div class="modulo-header">
        <div class="welcome-text">
            <h2>Resumen Analítico</h2>
        </div>
    </div>

    <div class="chart-container">
        <h3 style="margin-bottom: 15px; color: #343a40; font-weight: 800; text-transform: uppercase;">Movimiento de Ventas (Últimos 7 días)</h3>
        <canvas id="graficoVentas" height="80"></canvas>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-panel">
            <h3 class="text-danger">Stock Crítico según mínimo</h3>
            <table class="tabla-mini">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Min.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($stockCritico)): ?>
                        <tr><td colspan="4">No hay productos con stock crítico.</td></tr>
                    <?php else: ?>
                        <?php foreach($stockCritico as $item): ?>
                            <tr>
                                <td data-label="Cod."><?php echo htmlspecialchars($item['codigo']); ?></td>
                                <td data-label="Producto"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td data-label="Stock" class="text-danger" style="font-weight: bold;"><?php echo $item['stock']; ?></td>
                                <td data-label="Min."><?php echo htmlspecialchars($item['stock_minimo'] ?? 5); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-panel">
            <h3 class="text-warning">Próximos a Vencer (30 días)</h3>
            <table class="tabla-mini">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Producto</th>
                        <th>Fecha Venc.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($vencimientos)): ?>
                        <tr><td colspan="3">No hay productos próximos a vencer.</td></tr>
                    <?php else: ?>
                        <?php foreach($vencimientos as $item): ?>
                            <tr>
                                <td data-label="Cod."><?php echo htmlspecialchars($item['codigo']); ?></td>
                                <td data-label="Producto"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td data-label="Fecha Venc." class="text-warning" style="font-weight: bold;"><?php echo date("d-m-Y", strtotime($item['fecha_vencimiento'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Inyectamos las variables de PHP directamente al JavaScript usando JSON
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
