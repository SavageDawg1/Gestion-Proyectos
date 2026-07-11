<?php
/**
 * Header/Navbar - Se incluye en todas las páginas.
 */
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$showLogoutButton = $currentScript === 'dashboard.php';
$showSidebar = !isset($hide_sidebar) || !$hide_sidebar;
$showBackButton = $showSidebar && $currentScript !== 'dashboard.php';
$asset_version = '20260709-navbar-notifications';
$rolId = isset($_SESSION['rol_id']) ? (int) $_SESSION['rol_id'] : null;
$menuTitle = $rolId === 1 ? 'Menú Administrador' : 'Menú Vendedor';
$fullUserName = isset($_SESSION['user']) ? trim((string) $_SESSION['user']) : '';
$firstUserName = $fullUserName !== '' ? preg_split('/\s+/', $fullUserName)[0] : '';
$headerGreeting = $firstUserName !== '' ? '¡Bienvenido/a ' . $firstUserName . '!' : 'Sistema de Almacén';

$productPages = ['productos.php', 'nuevo_producto.php', 'editar_producto.php'];
$categoryPages = ['categorias.php', 'nueva_categoria.php', 'editar_categoria.php'];
$clientPages = ['clientes.php'];
$configPages = ['configuracion.php', 'registro_usuario.php', 'editar_usuarios.php'];
$backFallbackUrl = '/Software_Almacen/app/Views/dashboard.php';
if (in_array($currentScript, ['nuevo_producto.php', 'editar_producto.php'], true)) {
    $backFallbackUrl = '/Software_Almacen/app/Views/productos.php';
} elseif (in_array($currentScript, ['nueva_categoria.php', 'editar_categoria.php'], true)) {
    $backFallbackUrl = '/Software_Almacen/app/Views/categorias.php';
} elseif (in_array($currentScript, ['registro_usuario.php', 'editar_usuarios.php'], true)) {
    $backFallbackUrl = '/Software_Almacen/app/Views/configuracion.php';
}

$stockNotifications = [];
$expirationNotifications = [];
$notificationCount = 0;
$autoOpenNotifications = false;

if (function_exists('isAuthenticated') && isAuthenticated()) {
    require_once __DIR__ . '/../../Models/Producto.php';

    $notificationProductModel = new Producto();
    $stockNotifications = $notificationProductModel->obtenerStockCritico();
    $expirationNotifications = $notificationProductModel->obtenerProximosVencimientos(30);
    $notificationCount = count($stockNotifications) + count($expirationNotifications);
    $autoOpenNotifications = !empty($_SESSION['mostrar_notificaciones_inicio']) && $notificationCount > 0;
    $_SESSION['mostrar_notificaciones_inicio'] = false;
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

            <?php if ($showBackButton || true): ?>
                <ul class="navbar-menu">
                    <li class="navbar-notifications <?php echo $autoOpenNotifications ? 'is-open' : ''; ?>" data-notifications>
                        <button type="button" class="navbar-bell" data-notifications-toggle aria-label="Ver notificaciones" aria-expanded="<?php echo $autoOpenNotifications ? 'true' : 'false'; ?>">
                            <span class="navbar-bell-icon" aria-hidden="true">&#128276;</span>
                            <?php if ($notificationCount > 0): ?>
                                <span class="navbar-bell-badge"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </button>

                        <div class="notification-panel" data-notifications-panel>
                            <div class="notification-panel-header">
                                <h2>Notificaciones</h2>
                                <button type="button" class="notification-panel-close" data-notifications-close aria-label="Cerrar">&times;</button>
                            </div>

                            <?php if ($notificationCount === 0): ?>
                                <p class="notification-empty">No hay alertas de stock ni vencimientos.</p>
                            <?php else: ?>
                                <?php if (!empty($stockNotifications)): ?>
                                    <section class="notification-section">
                                        <h3>Stock critico</h3>
                                        <ul class="notification-list">
                                            <?php foreach ($stockNotifications as $item): ?>
                                                <li class="notification-item notification-item-warning">
                                                    <strong><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <span>Stock: <?php echo htmlspecialchars((string) $item['stock'], ENT_QUOTES, 'UTF-8'); ?> / Min: <?php echo htmlspecialchars((string) ($item['stock_minimo'] ?? 5), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </section>
                                <?php endif; ?>

                                <?php if (!empty($expirationNotifications)): ?>
                                    <section class="notification-section">
                                        <h3>Vencimientos</h3>
                                        <ul class="notification-list">
                                            <?php foreach ($expirationNotifications as $item): ?>
                                                <?php $expirationDate = strtotime($item['fecha_vencimiento']); ?>
                                                <li class="notification-item notification-item-danger">
                                                    <strong><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <span>Vence: <?php echo date('d/m/Y', $expirationDate); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </section>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php if ($showBackButton): ?>
                        <li>
                            <a href="<?php echo $back_url ?? 'dashboard.php'; ?>" class="navbar-action">
                                <?php echo $back_title ?? 'Volver'; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="/Software_Almacen/app/Controllers/authController.php?action=logout" class="navbar-action">
                            Cerrar sesión
                        </a>
                    </li>
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
                        <li><a href="clientes.php" class="<?php echo in_array($currentScript, $clientPages, true) ? 'active' : ''; ?>">Clientes</a></li>
                        <?php if ($rolId === 1): ?>
                            <li><a href="resumen_general.php" class="<?php echo $currentScript === 'resumen_general.php' ? 'active' : ''; ?>">Ver Resumen</a></li>
                            <li><a href="ver_reportes.php" class="<?php echo $currentScript === 'ver_reportes.php' ? 'active' : ''; ?>">Ver Reportes</a></li>
                        <?php endif; ?>
                        <li><a href="configuracion.php" class="<?php echo in_array($currentScript, $configPages, true) ? 'active' : ''; ?>">Configuración</a></li>
                    </ul>
                </aside>

                <div class="main-content">
        <?php endif; ?>
