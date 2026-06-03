<?php
/**
 * Header/Navbar - Se incluye en todas las páginas
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Almacén'; ?></title>
    <?php $asset_version = '20260603-responsive-scale-3'; ?>
    <link rel="stylesheet" href="/Software_Almacen/public/css/style.css?v=<?php echo $asset_version; ?>">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $page_css; ?>?v=<?php echo $asset_version; ?>">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>Sistema de Almacén</h1>
            </div>
            <ul class="navbar-menu">
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <?php if (!(isset($currentPage) && $currentPage === 'dashboard')): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="/Software_Almacen/api/auth.php?action=logout">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="/Software_Almacen/pages/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="container">
