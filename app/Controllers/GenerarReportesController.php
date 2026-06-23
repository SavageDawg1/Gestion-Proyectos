<?php
// Reporte de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../vendor/autoload.php';
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Models/Venta.php';

requireLogin();

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Obtener los datos
$ventaModel = new Venta();
$ventas = $ventaModel->obtenerVentasUltimos7Dias();

// 2. Leer el CSS externo (Lógica separada del diseño)
// IMPORTANTE: Ajusta esta ruta si la programadora frontend cambió la estructura de carpetas
$ruta_css = '../../public/css/reportes/reporte_pdf.css';
$estilos = '';

if (file_exists($ruta_css)) {
    $estilos = file_get_contents($ruta_css);
} else {
    die("Error: No se encontró el archivo CSS en: " . $ruta_css);
}

// 3. Estructura HTML pura usando la variable de estilos
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>' . $estilos . '</style>
</head>
<body>
    <div class="fecha">Generado el: ' . date('d/m/Y H:i') . '</div>
    <div class="header">
        <h1>REPORTE DE VENTAS - EL LEGADO</h1>
        <p>Resumen de movimientos de los últimos 7 días</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ingresos Reales</th>
                <th>Ventas Fiadas</th>
                <th>Total del Día</th>
            </tr>
        </thead>
        <tbody>';

$gran_total_ingresos = 0;
$gran_total_fiado = 0;

foreach ($ventas as $v) {
    $ingresos = floatval($v['total_ingresos']);
    $fiado = floatval($v['total_fiado']);
    $total_dia = $ingresos + $fiado;
    
    $gran_total_ingresos += $ingresos;
    $gran_total_fiado += $fiado;

    $html .= '<tr>
                <td>' . date('d/m/Y', strtotime($v['dia'])) . '</td>
                <td>$' . number_format($ingresos, 0, ',', '.') . '</td>
                <td>$' . number_format($fiado, 0, ',', '.') . '</td>
                <td>$' . number_format($total_dia, 0, ',', '.') . '</td>
              </tr>';
}

$html .= '
            <tr class="total-row">
                <td>TOTALES GLOBALES</td>
                <td>$' . number_format($gran_total_ingresos, 0, ',', '.') . '</td>
                <td>$' . number_format($gran_total_fiado, 0, ',', '.') . '</td>
                <td>$' . number_format($gran_total_ingresos + $gran_total_fiado, 0, ',', '.') . '</td>
            </tr>
        </tbody>
    </table>
</body>
</html>';

// 4. Generar el PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 5. Guardar el PDF en Windows
$output = $dompdf->output();
$directorio_guardado = "../../public/reportes/";

// En Windows no necesitamos pelear con permisos estrictos, pero nos aseguramos de que la carpeta exista
if (!file_exists($directorio_guardado)) {
    mkdir($directorio_guardado, 0777, true);
}

$nombre_archivo = "Reporte_Ventas_" . date('Y-m-d_H-i-s') . ".pdf";
$ruta_completa = $directorio_guardado . $nombre_archivo;

if (file_put_contents($ruta_completa, $output)) {
    header("Location: ../Views/dashboard.php?status=reporte_listo");
    exit;
} else {
    die("Error crítico: No se pudo escribir el archivo en el disco.");
}
?>