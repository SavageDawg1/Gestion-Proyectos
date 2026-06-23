<?php
// Reporte de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php'; // Agregado por el nuevo estándar frontend

// Validar que sea administrador
requireLogin();

// Las "credenciales" que exige el nuevo diseño de tu programadora
$isLoggedIn = isAuthenticated();
$currentPage = 'reportes'; // Esto evitará que te redirija
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

<div class="main-content">
    <div class="welcome-header reportes-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="welcome-text">
            <h2>Historial de Reportes</h2>
            <p>Aquí puedes consultar todos los reportes generados anteriormente.</p>
        </div>
        <a href="../Controllers/GenerarReportesController.php" class="btn btn-info" style="padding: 10px 20px; font-weight: bold; border-radius: 5px;">+ Generar Nuevo Reporte</a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #d4edda; color: #155724; font-weight: bold; border: 1px solid #c3e6cb;">
            ¡Reporte generado y guardado exitosamente!
        </div>
    <?php endif; ?>

    <div class="pos-panel" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <table class="cart-table table-reportes" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #ddd; background-color: #f8f9fa;">Nombre del Documento</th>
                    <th style="padding: 12px; border-bottom: 2px solid #ddd; background-color: #f8f9fa;">Fecha de Creación</th>
                    <th style="padding: 12px; border-bottom: 2px solid #ddd; background-color: #f8f9fa;">Tamaño</th>
                    <th style="padding: 12px; border-bottom: 2px solid #ddd; background-color: #f8f9fa; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($archivos)): ?>
                    <tr>
                        <td colspan="4" style="padding: 30px; text-align: center; color: #666; font-style: italic;">
                            No hay reportes generados todavía.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($archivos as $archivo): 
                        $nombre = basename($archivo);
                        $fecha = date('d/m/Y H:i', filemtime($archivo));
                        $tamano = round(filesize($archivo) / 1024) . ' KB';
                        $ruta_publica = '/Software_Almacen/public/reportes/' . $nombre;
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 12px; font-weight: bold; color: #333;">📄 <?php echo $nombre; ?></td>
                            <td style="padding: 15px 12px; color: #666;"><?php echo $fecha; ?></td>
                            <td style="padding: 15px 12px; color: #666;"><?php echo $tamano; ?></td>
                            <td style="padding: 15px 12px; text-align: center;">
                                <a href="<?php echo $ruta_publica; ?>" target="_blank" style="display: inline-block; background-color: #fff; color: #d55b22; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; border: 1px solid #d55b22; margin-right: 5px;">Ver PDF</a>
                                <a href="<?php echo $ruta_publica; ?>" download style="display: inline-block; background-color: #333; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold;">Descargar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>