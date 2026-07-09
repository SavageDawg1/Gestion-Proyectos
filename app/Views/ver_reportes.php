<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$isLoggedIn = isAuthenticated();
$currentPage = 'reportes';
$page_title = "Historial de Reportes - El Legado";
$page_css = '/Software_Almacen/public/css/reportes/ver_reportes.css';

$directorio = '../../public/reportes/';

if (!file_exists($directorio)) {
    mkdir($directorio, 0777, true);
}

$archivos = glob($directorio . '*.pdf') ?: [];

if ($archivos) {
    usort($archivos, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}

$reportesPorPagina = 10;
$totalReportes = count($archivos);
$totalPaginas = max(1, (int) ceil($totalReportes / $reportesPorPagina));
$paginaActual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$paginaActual = min($paginaActual, $totalPaginas);
$inicioPagina = ($paginaActual - 1) * $reportesPorPagina;
$archivosPagina = array_slice($archivos, $inicioPagina, $reportesPorPagina);
$desdeReporte = $totalReportes > 0 ? $inicioPagina + 1 : 0;
$hastaReporte = min($inicioPagina + $reportesPorPagina, $totalReportes);
$abrirModalReporte = isset($_GET['generar']) && $_GET['generar'] === '1';

require_once 'layouts/header.php';
?>

<div class="view-stack reportes-view">
    <div class="welcome-header reportes-header">
        <div class="welcome-text">
            <h2>Historial de Reportes</h2>
            <p>Aqu&iacute; puedes consultar todos los reportes generados anteriormente.</p>
        </div>
        <button type="button" class="btn btn-info reportes-generate-btn" id="abrir_reporte_modal">+ Generar Nuevo Reporte</button>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="reportes-alert reportes-alert-success" data-page-alert>
            &iexcl;Reporte generado y guardado exitosamente!
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error_fechas'): ?>
        <div class="reportes-alert reportes-alert-error" data-page-alert>
            Selecciona fecha de inicio y fecha de fin para generar por periodo.
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error_rango'): ?>
        <div class="reportes-alert reportes-alert-error" data-page-alert>
            La fecha de inicio no puede ser posterior a la fecha de fin.
        </div>
    <?php endif; ?>

    <div class="reportes-summary">
        <span><?php echo $totalReportes; ?> reportes guardados</span>
        <?php if ($totalReportes > 0): ?>
            <span>Mostrando <?php echo $desdeReporte; ?>-<?php echo $hastaReporte; ?> de <?php echo $totalReportes; ?></span>
        <?php endif; ?>
    </div>

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
                <?php if (empty($archivosPagina)): ?>
                    <tr>
                        <td colspan="4" class="reportes-empty">
                            No hay reportes generados todav&iacute;a.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($archivosPagina as $archivo):
                        $nombre = basename($archivo);
                        $fecha = date('d/m/Y H:i', filemtime($archivo));
                        $tamano = round(filesize($archivo) / 1024) . ' KB';
                        $ruta_publica = '/Software_Almacen/public/reportes/' . rawurlencode($nombre);
                    ?>
                        <tr class="reportes-row">
                            <td data-label="Documento" class="reportes-file-name">PDF <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Fecha"><?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Tamano"><?php echo htmlspecialchars($tamano, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Acciones" class="reportes-actions">
                                <a href="<?php echo htmlspecialchars($ruta_publica, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="reportes-action reportes-action-view">Ver PDF</a>
                                <a href="<?php echo htmlspecialchars($ruta_publica, ENT_QUOTES, 'UTF-8'); ?>" download class="reportes-action reportes-action-download">Descargar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPaginas > 1): ?>
        <nav class="reportes-pagination" aria-label="Paginacion de reportes">
            <?php if ($paginaActual > 1): ?>
                <a href="ver_reportes.php?pagina=<?php echo $paginaActual - 1; ?>" class="reportes-page-link">Anterior</a>
            <?php else: ?>
                <span class="reportes-page-link is-disabled">Anterior</span>
            <?php endif; ?>

            <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++): ?>
                <?php if ($pagina === $paginaActual): ?>
                    <span class="reportes-page-link is-active"><?php echo $pagina; ?></span>
                <?php else: ?>
                    <a href="ver_reportes.php?pagina=<?php echo $pagina; ?>" class="reportes-page-link"><?php echo $pagina; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <a href="ver_reportes.php?pagina=<?php echo $paginaActual + 1; ?>" class="reportes-page-link">Siguiente</a>
            <?php else: ?>
                <span class="reportes-page-link is-disabled">Siguiente</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>

<div class="reportes-modal" id="reporte_modal" aria-hidden="true">
    <div class="reportes-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="reporte_modal_title">
        <div class="reportes-modal-header">
            <h3 id="reporte_modal_title">Generar reporte</h3>
            <button type="button" class="reportes-modal-close" id="cerrar_reporte_modal" aria-label="Cerrar">&times;</button>
        </div>

        <form action="../Controllers/GenerarReportesController.php" method="POST" class="reportes-form" id="form_generar_reporte">
            <div class="reportes-options" role="radiogroup" aria-label="Alcance del reporte">
                <label class="reportes-option">
                    <input type="radio" name="modo_reporte" value="todos">
                    <span>Todo</span>
                </label>
                <label class="reportes-option">
                    <input type="radio" name="modo_reporte" value="periodo" checked>
                    <span>Periodo</span>
                </label>
            </div>

            <div class="reportes-date-grid" id="reportes_date_grid">
                <label>
                    <span>Fecha inicio</span>
                    <input type="date" name="fecha_inicio" id="fecha_inicio_reporte" value="<?php echo date('Y-m-01'); ?>">
                </label>
                <label>
                    <span>Fecha fin</span>
                    <input type="date" name="fecha_fin" id="fecha_fin_reporte" value="<?php echo date('Y-m-d'); ?>">
                </label>
            </div>

            <div class="reportes-modal-actions">
                <button type="button" class="btn-accion reportes-cancel-btn" id="cancelar_reporte_modal">Cancelar</button>
                <button type="submit" class="btn-accion reportes-submit-btn">Generar reporte</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('reporte_modal');
    const openButton = document.getElementById('abrir_reporte_modal');
    const closeButton = document.getElementById('cerrar_reporte_modal');
    const cancelButton = document.getElementById('cancelar_reporte_modal');
    const form = document.getElementById('form_generar_reporte');
    const dateGrid = document.getElementById('reportes_date_grid');
    const startInput = document.getElementById('fecha_inicio_reporte');
    const endInput = document.getElementById('fecha_fin_reporte');

    function openModal() {
        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
    }

    function selectedMode() {
        const checked = form.querySelector('input[name="modo_reporte"]:checked');
        return checked ? checked.value : 'periodo';
    }

    function updateDateFields() {
        const useDates = selectedMode() === 'periodo';
        dateGrid.classList.toggle('is-disabled', !useDates);
        startInput.required = useDates;
        endInput.required = useDates;
        startInput.disabled = !useDates;
        endInput.disabled = !useDates;
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function(event) {
        if (event.target === modal) closeModal();
    });

    form.querySelectorAll('input[name="modo_reporte"]').forEach(function(radio) {
        radio.addEventListener('change', updateDateFields);
    });

    form.addEventListener('submit', function(event) {
        if (selectedMode() !== 'periodo') return;

        if (startInput.value && endInput.value && startInput.value > endInput.value) {
            event.preventDefault();
            if (window.appAlert) {
                window.appAlert('La fecha de inicio no puede ser posterior a la fecha de fin.', 'error');
            }
        }
    });

    updateDateFields();

    <?php if ($abrirModalReporte): ?>
    openModal();
    <?php endif; ?>
})();
</script>

<?php require_once 'layouts/footer.php'; ?>
