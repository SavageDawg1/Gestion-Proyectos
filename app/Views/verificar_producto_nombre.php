<?php
require_once '../../includes/session.php';
require_once '../Models/Producto.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'exists' => false,
        'message' => 'Sesion no autorizada'
    ]);
    exit;
}

$nombre = trim($_GET['nombre'] ?? '');
$excluirId = isset($_GET['excluir_id']) && $_GET['excluir_id'] !== '' ? intval($_GET['excluir_id']) : null;

if ($nombre === '') {
    echo json_encode([
        'exists' => false,
        'message' => ''
    ]);
    exit;
}

$productoModel = new Producto();
$producto = $productoModel->obtenerPorNombre($nombre, null, $excluirId);

echo json_encode([
    'exists' => $producto !== null,
    'message' => $producto ? 'Ya existe un producto con este nombre.' : '',
    'product' => $producto ? [
        'id' => (int) $producto['id'],
        'codigo' => $producto['codigo'],
        'nombre' => $producto['nombre']
    ] : null
], JSON_UNESCAPED_UNICODE);
?>
