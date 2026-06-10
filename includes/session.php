<?php
/**
 * Gestión de Sesiones
 */

session_start();

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
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
    header("Location: /Software_Almacen/app/Views/login.php");
    exit;
}

// Redirigir si no está autenticado
function requireLogin() {
    if (!isAuthenticated()) {
        header("Location: login.php");
        exit;
    }
}
?>
