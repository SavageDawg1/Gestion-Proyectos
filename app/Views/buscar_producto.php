<?php
require_once '../../config/database.php';
require_once '../Models/Producto.php';

header('Content-Type: application/json');

$termino = isset($_GET['q']) ? $_GET['q'] : '';

if (strlen($termino) < 2) {
    echo json_encode([]);
    exit;
}

$productoModel = new Producto();
$resultados = $productoModel->buscarPorTermino($termino);

echo json_encode($resultados);
?>