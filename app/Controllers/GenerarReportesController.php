<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../vendor/autoload.php';
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Models/Venta.php';

requireAdmin();

use Dompdf\Dompdf;
use Dompdf\Options;

function limpiarFechaReporte($fecha) {
    if (empty($fecha)) {
        return null;
    }

    $fecha = trim($fecha);
    $date = DateTime::createFromFormat('Y-m-d', $fecha);
    return $date && $date->format('Y-m-d') === $fecha ? $fecha : null;
}

$modoReporte = $_POST['modo_reporte'] ?? $_GET['modo_reporte'] ?? 'periodo';
$modoReporte = $modoReporte === 'todos' ? 'todos' : 'periodo';
$fechaInicio = limpiarFechaReporte($_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? null);
$fechaFin = limpiarFechaReporte($_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? null);

if ($modoReporte === 'periodo') {
    if (!$fechaInicio || !$fechaFin) {
        header("Location: ../Views/ver_reportes.php?status=error_fechas");
        exit;
    }

    if (strtotime($fechaInicio) > strtotime($fechaFin)) {
        header("Location: ../Views/ver_reportes.php?status=error_rango");
        exit;
    }
} else {
    $fechaInicio = null;
    $fechaFin = null;
}

$ventaModel = new Venta();
$ventas = $ventaModel->obtenerVentasPorPeriodo($fechaInicio, $fechaFin);
$detalleProductos = $ventaModel->obtenerDetalleProductosVendidosPorPeriodo($fechaInicio, $fechaFin);

$periodoTexto = $modoReporte === 'todos'
    ? 'Todos los registros disponibles'
    : 'Del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin));

$tituloDetalle = $modoReporte === 'todos'
    ? 'Detalle de productos vendidos'
    : 'Detalle de productos vendidos en el periodo seleccionado';

$ruta_css = '../../public/css/reportes/reporte_pdf.css';
$estilos = '';

if (file_exists($ruta_css)) {
    $estilos = file_get_contents($ruta_css);
} else {
    die("Error: No se encontro el archivo CSS en: " . $ruta_css);
}

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
        <p>' . htmlspecialchars($periodoTexto, ENT_QUOTES, 'UTF-8') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ingresos Reales</th>
                <th>Ventas Fiadas</th>
                <th>Total del Dia</th>
            </tr>
        </thead>
        <tbody>';

$gran_total_ingresos = 0;
$gran_total_fiado = 0;

if (empty($ventas)) {
    $html .= '<tr><td colspan="4">No hay movimientos para el periodo seleccionado.</td></tr>';
} else {
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
}

$html .= '
            <tr class="total-row">
                <td>TOTALES GLOBALES</td>
                <td>$' . number_format($gran_total_ingresos, 0, ',', '.') . '</td>
                <td>$' . number_format($gran_total_fiado, 0, ',', '.') . '</td>
                <td>$' . number_format($gran_total_ingresos + $gran_total_fiado, 0, ',', '.') . '</td>
            </tr>
        </tbody>
    </table>';

$html .= '
    <h2 class="section-title">' . htmlspecialchars($tituloDetalle, ENT_QUOTES, 'UTF-8') . '</h2>';

if (empty($detalleProductos)) {
    $html .= '<p class="empty-detail">No hay productos vendidos en el periodo seleccionado.</p>';
} else {
    $detalleAgrupado = [];
    foreach ($detalleProductos as $fila) {
        $dia = $fila['dia'];
        if (!isset($detalleAgrupado[$dia])) {
            $detalleAgrupado[$dia] = [];
        }
        $detalleAgrupado[$dia][] = $fila;
    }

    foreach ($detalleAgrupado as $dia => $items) {
        $html .= '
        <h3 class="day-title">' . date('d/m/Y', strtotime($dia)) . '</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad Vendida</th>
                    <th>Total Vendido</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($items as $item) {
            $esGranel = ($item['tipo_venta'] ?? 'unidad') === 'granel';
            $tipoTexto = $esGranel
                ? 'Granel (' . htmlspecialchars($item['unidad_granel'] ?? '1000g', ENT_QUOTES, 'UTF-8') . ')'
                : 'Unidad';
            $cantidadTexto = $esGranel
                ? number_format((float)$item['cantidad_total'], 0, ',', '.') . ' g'
                : number_format((float)$item['cantidad_total'], 0, ',', '.');

            $html .= '<tr>
                <td>' . htmlspecialchars($item['producto'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . $tipoTexto . '</td>
                <td>' . $cantidadTexto . '</td>
                <td>$' . number_format((float)$item['total_vendido'], 0, ',', '.') . '</td>
            </tr>';
        }

        $html .= '
            </tbody>
        </table>';
    }
}

$html .= '
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$output = $dompdf->output();
$directorio_guardado = "../../public/reportes/";

if (!file_exists($directorio_guardado)) {
    mkdir($directorio_guardado, 0777, true);
}

$sufijo = $modoReporte === 'todos'
    ? 'Todos'
    : $fechaInicio . '_a_' . $fechaFin;
$nombre_archivo = "Reporte_Ventas_" . $sufijo . "_" . date('Y-m-d_H-i-s') . ".pdf";
$ruta_completa = $directorio_guardado . $nombre_archivo;

if (file_put_contents($ruta_completa, $output)) {
    header("Location: ../Views/ver_reportes.php?status=success");
    exit;
}

die("Error critico: No se pudo escribir el archivo en el disco.");
?>
