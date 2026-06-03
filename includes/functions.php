<?php
/**
 * Funciones Generales del Proyecto
 */

// Función para sanitizar entrada
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Función para escapar datos en base de datos
function escapeDB($conexion, $input) {
    return $conexion->real_escape_string($input);
}

// Función para log de errores
function logError($message, $file = "error.log") {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    error_log($log_message, 3, $file);
}

// Función para mostrar mensajes de error
function showError($message) {
    echo "<div class='alert alert-danger'>$message</div>";
}

// Función para mostrar mensajes de éxito
function showSuccess($message) {
    echo "<div class='alert alert-success'>$message</div>";
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit;
}
?>
