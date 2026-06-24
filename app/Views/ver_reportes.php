<?php
// Reporte de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Validar que sea administrador
requireLogin();

$isLoggedIn = isAuthenticated();
$currentPage = 'reportes';
$page_title = "Historial de Reportes - El Legado";
$page_css = '/Software_Almacen/public/css/reportes/ver_reportes.css';

require_once 'layouts/header.php';

// Escanear la carpeta de reportes
$directorio = '../../public/reportes/';

// Crear el directorio si no existe en Windows
if (!file_exists($directorio)) {
    mkdir($directorio, 0777, true);
}

$archivos = glob($directorio . '*.pdf');

if ($archivos) {
    usort($archivos, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}
?>

<div class="view-stack reportes-view">
    <div class="welcome-header reportes-header">
        <div class="welcome-text">
            <h2>Historial de Reportes</h2>
            <p>Aqu&iacute; puedes consultar todos los reportes generados anteriormente.</p>
        </div>
        <a href="../Controllers/GenerarReportesController.php" class="btn btn-info reportes-generate-btn">+ Generar Nuevo Reporte</a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="reportes-alert reportes-alert-success">
            &iexcl;Reporte generado y guardado exitosamente!
        </div>
    <?php endif; ?>

    <div class="pos-panel reportes-panel">
        <table class="cart-table table-reportes">
            <thead>
                <tr>
                    <th>Nombre del Documento</th>
                    <th>Fecha de Creaci&oacute;n</th>
                    <th>Tama&ntilde;o</th>
                    <th class="reportes-actions-heading">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($archivos)): ?>
                    <tr>
                        <td colspan="4" class="reportes-empty">
                            No hay reportes generados todav&iacute;a.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($archivos as $archivo):
                        $nombre = basename($archivo);
                        $fecha = date('d/m/Y H:i', filemtime($archivo));
                        $tamano = round(filesize($archivo) / 1024) . ' KB';
                        $ruta_publica = '/Software_Almacen/public/reportes/' . $nombre;
                    ?>
                        <tr class="reportes-row">
                            <td class="reportes-file-name">PDF <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($tamano, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="reportes-actions">
                                <a href="<?php echo htmlspecialchars($ruta_publica, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="reportes-action reportes-action-view">Ver PDF</a>
                                <a href="<?php echo htmlspecialchars($ruta_publica, ENT_QUOTES, 'UTF-8'); ?>" download class="reportes-action reportes-action-download">Descargar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
