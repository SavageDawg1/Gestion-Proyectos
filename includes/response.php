<?php
/**
 * Respuestas JSON para AJAX
 */

header('Content-Type: application/json');

// Función para respuesta exitosa
function successResponse($data = null, $message = "Operación exitosa") {
    return json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

// Función para respuesta de error
function errorResponse($message = "Error en la operación", $data = null) {
    http_response_code(400);
    return json_encode([
        'success' => false,
        'message' => $message,
        'data' => $data
    ]);
}

// Función para respuesta no autorizada
function unauthorizedResponse() {
    http_response_code(401);
    return json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
}
?>
