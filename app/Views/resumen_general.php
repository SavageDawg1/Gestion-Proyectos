<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/ProductoController.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$productoController = new ProductoController();

$stockCritico = $productoController->listarStockCritico();
$vencimientos = $productoController->listarProximosVencimientos();

$page_title = "Resumen General - Sistema de Almacen";
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';

require_once 'layouts/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="view-stack">
    <div class="modulo-header">
        <div class="welcome-text">
            <h2>Resumen Analitico</h2>
        </div>
    </div>

    <div class="chart-container">
        <h3 style="margin-bottom: 15px; color: #343a40; font-weight: 800; text-transform: uppercase;">Movimiento de Ventas (Ultimos 7 dias)</h3>
        <canvas id="graficoVentas" height="80"></canvas>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-panel">
            <h3 class="text-danger">Stock Critico segun minimo</h3>
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
                        <tr><td colspan="4">No hay productos con stock critico.</td></tr>
                    <?php else: ?>
                        <?php foreach($stockCritico as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td class="text-danger"><?php echo $item['stock']; ?></td>
                                <td><?php echo $item['stock_minimo']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-panel">
            <h3 class="text-warning">Proximos a Vencer (30 dias)</h3>
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
                        <tr><td colspan="3">No hay productos proximos a vencer.</td></tr>
                    <?php else: ?>
                        <?php foreach($vencimientos as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td class="text-warning"><?php echo date("d-m-Y", strtotime($item['fecha_vencimiento'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    const graficoVentas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'],
            datasets: [{
                label: 'Ingresos ($)',
                data: [12000, 19000, 15000, 22000, 18000, 35000, 42000],
                backgroundColor: 'rgba(213, 91, 34, 0.7)',
                borderColor: 'rgba(213, 91, 34, 1)',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>

<?php require_once 'layouts/footer.php'; ?>
