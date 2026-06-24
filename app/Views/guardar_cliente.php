<?php
require_once '../../config/database.php';
require_once '../../includes/validation.php';
require_once '../Controllers/ClienteController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $rut = trim($_POST['rut'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre === '' || $rut === '') {
        echo json_encode(['success' => false, 'message' => 'Nombre y RUT son obligatorios.']);
        exit;
    }

    if (!isValidRut($rut)) {
        echo json_encode(['success' => false, 'message' => 'RUT inválido. Use el formato xx.xxx.xxx-x o x.xxx.xxx-x, y el dígito verificador debe ser 0-9 o K.']);
        exit;
    }

    if ($telefono !== '' && !isValidChilePhone($telefono)) {
        echo json_encode(['success' => false, 'message' => 'Teléfono inválido. Debe comenzar con +56 y tener 9 dígitos.']);
        exit;
    }

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