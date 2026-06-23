<?php
/**
 * Header/Navbar - Se incluye en todas las páginas.
 */
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$showLogoutButton = $currentScript === 'dashboard.php';
$showSidebar = !isset($hide_sidebar) || !$hide_sidebar;
$showBackButton = $showSidebar && $currentScript !== 'dashboard.php';
$asset_version = '20260623-form-labels';
$rolId = isset($_SESSION['rol_id']) ? (int) $_SESSION['rol_id'] : null;
$menuTitle = $rolId === 1 ? 'Menú Administrador' : 'Menú Vendedor';
$fullUserName = isset($_SESSION['user']) ? trim((string) $_SESSION['user']) : '';
$firstUserName = $fullUserName !== '' ? preg_split('/\s+/', $fullUserName)[0] : '';
$headerGreeting = $firstUserName !== '' ? '¡Bienvenido/a ' . $firstUserName . '!' : 'Sistema de Almacén';

$productPages = ['productos.php', 'nuevo_producto.php', 'editar_producto.php'];
$categoryPages = ['categorias.php', 'nueva_categoria.php', 'editar_categoria.php'];
$configPages = ['configuracion.php', 'registro_usuario.php', 'editar_usuarios.php'];
$backFallbackUrl = '/Software_Almacen/app/Views/dashboard.php';
if (in_array($currentScript, ['nuevo_producto.php', 'editar_producto.php'], true)) {
    $backFallbackUrl = '/Software_Almacen/app/Views/productos.php';
} elseif (in_array($currentScript, ['nueva_categoria.php', 'editar_categoria.php'], true)) {
    $backFallbackUrl = '/Software_Almacen/app/Views/categorias.php';
} elseif (in_array($currentScript, ['registro_usuario.php', 'editar_usuarios.php'], true)) {
    $backFallbackUrl = '/Software_Almacen/app/Views/configuracion.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Almacén'; ?></title>
    <link rel="stylesheet" href="/Software_Almacen/public/css/style.css?v=<?php echo $asset_version; ?>">
    <?php if (isset($page_css)): ?>
        <?php foreach ((array) $page_css as $css_file): ?>
            <link rel="stylesheet" href="<?php echo $css_file; ?>?v=<?php echo $asset_version; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>
                    <a href="/Software_Almacen/app/Views/dashboard.php" style="text-decoration: none; color: inherit;">
                        <?php echo htmlspecialchars($headerGreeting, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h1>
            </div>

            <?php if ($showLogoutButton || $showBackButton): ?>
                <ul class="navbar-menu">
                    <?php if ($showLogoutButton): ?>
                        <li>
                            <a href="/Software_Almacen/app/Controllers/authController.php?action=logout" class="navbar-action">
                                Cerrar sesión
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?php echo $backFallbackUrl; ?>" class="navbar-action"
                               onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                                Volver
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">
        <?php if ($showSidebar): ?>
            <div class="dashboard-container app-shell <?php echo isset($mover_layout) ? $mover_layout : ''; ?>">
                <aside class="sidebar">
                    <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="sidebar-logo">
                    <h3><?php echo $menuTitle; ?></h3>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php" class="<?php echo $currentScript === 'dashboard.php' ? 'active' : ''; ?>">Inicio</a></li>
                        <li><a href="productos.php" class="<?php echo in_array($currentScript, $productPages, true) ? 'active' : ''; ?>">Productos</a></li>
                        <li><a href="categorias.php" class="<?php echo in_array($currentScript, $categoryPages, true) ? 'active' : ''; ?>">Categorías</a></li>
                        <?php if ($rolId === 1): ?>
                            <li><a href="resumen_general.php" class="<?php echo $currentScript === 'resumen_general.php' ? 'active' : ''; ?>">Ver Reportes</a></li>
                        <?php endif; ?>
                        <li><a href="configuracion.php" class="<?php echo in_array($currentScript, $configPages, true) ? 'active' : ''; ?>">Configuración</a></li>
                    </ul>
                </aside>

                <div class="main-content">
        <?php endif; ?>
