<?php
require_once '../../includes/session.php';

requireAdmin();

$archivo = basename($_GET['archivo'] ?? '');
$modo = ($_GET['modo'] ?? 'ver') === 'descargar' ? 'descargar' : 'ver';

if ($archivo === '' || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/', $archivo)) {
    http_response_code(400);
    exit('Solicitud invalida.');
}

$ruta = realpath(__DIR__ . '/../../public/reportes/' . $archivo);
$directorio = realpath(__DIR__ . '/../../public/reportes');

if (!$ruta || !$directorio || strpos($ruta, $directorio . DIRECTORY_SEPARATOR) !== 0 || !is_file($ruta)) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . ($modo === 'descargar' ? 'attachment' : 'inline') . '; filename="' . $archivo . '"');
header('X-Content-Type-Options: nosniff');

readfile($ruta);
exit;
?>
