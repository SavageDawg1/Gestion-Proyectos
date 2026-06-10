<?php
/**
 * Página Principal / Punto de Entrada
 * Redirige al login o dashboard dependiendo del estado de sesión
 */

require_once 'config/database.php';
require_once 'includes/session.php';

// Si está autenticado, ir al dashboard
if (isAuthenticated()) {
    header("Location: app/Views/dashboard.php");
    exit;
} else {
    // Si no está autenticado, ir al login
    header("Location: app/Views/login.php");
    exit;
}
?>
