<?php
/**
 * Página Dashboard
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$page_title = "Dashboard - Almacén";

// Verificar autenticación
requireLogin();

$isLoggedIn = isAuthenticated();
$currentPage = 'dashboard';
$user = getCurrentUser();
$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';
?>
<?php require_once 'layouts/header.php'; ?>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="sidebar-logo">
            <h3>Menú</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Inicio</a></li>
                <li><a href="#productos">Productos</a></li>
                <li><a href="#categorias">Categorías</a></li>
                <li><a href="#reportes">Reportes</a></li>
                <li><a href="#configuracion">Configuración</a></li>
            </ul>
        </aside>
        
        <!-- Contenido principal -->
        <div class="main-content">
            <div class="welcome-section">
                <div class="welcome-header">
                    <div>
                        <h2>Bienvenido, <?php echo htmlspecialchars($user); ?>!</h2>
                        <p>Sistema de Gestión de Almacén</p>
                    </div>
                </div>
            </div>
            
            <!-- Cards de información -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Productos</h3>
                    <p class="stat-number">0</p>
                    <a href="#" class="btn btn-sm btn-primary">Ver</a>
                </div>
                
                <div class="stat-card">
                    <h3>Categorías</h3>
                    <p class="stat-number">0</p>
                    <a href="#" class="btn btn-sm btn-primary">Ver</a>
                </div>
                
                <div class="stat-card">
                    <h3>Stock Total</h3>
                    <p class="stat-number">0</p>
                    <a href="#" class="btn btn-sm btn-primary">Ver</a>
                </div>
                
                <div class="stat-card">
                    <h3>Transacciones</h3>
                    <p class="stat-number">0</p>
                    <a href="#" class="btn btn-sm btn-primary">Ver</a>
                </div>
            </div>
            
            <!-- Sección de acciones rápidas -->
            <div class="quick-actions">
                <h3>Acciones Rápidas</h3>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">+ Nuevo Producto</a>
                    <a href="#" class="btn btn-secondary">+ Nueva Categoría</a>
                    <a href="#" class="btn btn-info">Generar Reporte</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php require_once 'layouts/footer.php'; ?>
