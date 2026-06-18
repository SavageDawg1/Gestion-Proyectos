<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/ProductoController.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$productoController = new ProductoController();

// Obtener datos para las tablas operativas
$stockCritico = $productoController->listarStockCritico();
$vencimientos = $productoController->listarProximosVencimientos();

$page_title = "Resumen General - Sistema de Almacén";

// AQUÍ ESTÁ LA LÍNEA PARA CARGAR TU CSS:
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';

require_once 'layouts/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-content" style="padding: 20px;">
    
    <div class="welcome-header" style="margin-bottom: 2rem;">
        <div class="welcome-text">
            <h2>Resumen Analítico</h2>
        </div>
        <a href="dashboard.php" class="btn-volver">Volver al Inicio</a>
    </div>

    <div class="chart-container">
        <h3 style="margin-bottom: 15px; color: #343a40; font-weight: 800; text-transform: uppercase;">Movimiento de Ventas (Últimos 7 días)</h3>
        <canvas id="graficoVentas" height="80"></canvas>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-panel">
            <h3 class="text-danger">⚠️ Stock Crítico (<= 5)</h3>
            <table class="tabla-mini">
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Producto</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($stockCritico)): ?>
                        <tr><td colspan="3">No hay productos con stock crítico.</td></tr>
                    <?php else: ?>
                        <?php foreach($stockCritico as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td class="text-danger"><?php echo $item['stock']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-panel">
            <h3 class="text-warning">⏳ Próximos a Vencer (30 días)</h3>
            <table class="tabla-mini">
                <thead>
                    <tr>
                        <th>Cód.</th>
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
    // Configuración de Chart.js con los colores de tu marca (Naranja #d55b22)
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    const graficoVentas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            datasets: [{
                label: 'Ingresos ($)',
                data: [12000, 19000, 15000, 22000, 18000, 35000, 42000],
                backgroundColor: 'rgba(213, 91, 34, 0.7)', // Naranja transparente
                borderColor: 'rgba(213, 91, 34, 1)',     // Naranja sólido
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>

<?php require_once 'layouts/footer.php'; ?>