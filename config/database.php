<?php
/**
 * Configuración de Base de Datos
 * Conexión a MySQL usando XAMPP
 */

// Credenciales de conexión
date_default_timezone_set('America/Santiago');

define('DB_HOST', 'localhost');
define('DB_USER', 'carlo862_gaspar');
define('DB_PASS', '123456@#legado');
define('DB_NAME', 'carlo862_legado');

try {
    // Crear conexión con mysqli
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Configurar charset
    $conexion->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
