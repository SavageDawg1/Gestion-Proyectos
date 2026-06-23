<?php
require_once '../../config/database.php';
require_once '../Controllers/ClienteController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ClienteController();
    $id_nuevo_cliente = $controller->registrarClienteRapido($_POST);

    if ($id_nuevo_cliente) {
        echo json_encode(['success' => true, 'id' => $id_nuevo_cliente, 'nombre' => htmlspecialchars($_POST['nombre'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar. Revisa que el RUT no esté duplicado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>