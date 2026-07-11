<?php
/**
 * Gestión de Sesiones
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserRoleIdFromDatabase() {
    if (!isAuthenticated()) {
        return null;
    }

    require_once __DIR__ . '/../config/database.php';

    if (!isset($conexion) || !$conexion instanceof mysqli) {
        return null;
    }

    $userId = (int) $_SESSION['user_id'];
    $stmt = $conexion->prepare("SELECT rol_id, activo FROM registro WHERE id = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();

    if (!$usuario || (int) ($usuario['activo'] ?? 0) !== 1) {
        return null;
    }

    $_SESSION['rol_id'] = (int) $usuario['rol_id'];
    return (int) $usuario['rol_id'];
}

function isAdmin() {
    if (isset($_SESSION['rol_id']) && (int) $_SESSION['rol_id'] === 1) {
        return true;
    }

    return getCurrentUserRoleIdFromDatabase() === 1;
}

// Función para obtener el usuario actual
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Función para establecer sesión de usuario
function setUserSession($user_id, $user_name, $user_email) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user'] = $user_name;
    $_SESSION['email'] = $user_email;
    $_SESSION['login_time'] = time();
}

// Función para cerrar sesión
function logoutUser() {
    session_destroy();
    // Redirigir a login usando host y ruta del proyecto para evitar path absolutos fijos
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ''; 
    $projectRoot = isset($_SERVER['SCRIPT_NAME']) ? dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))) : '';
    $loginUrl = $protocol . '://' . $host . $projectRoot . '/app/Views/login.php';
    header("Location: " . $loginUrl);
    exit;
}

// Redirigir si no está autenticado
function requireLogin() {
    if (!isAuthenticated()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Acceso denegado. Esta vista es exclusiva del administrador.';
        exit;
    }
}
?>
