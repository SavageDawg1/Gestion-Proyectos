<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Controllers/CategoriaController.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

$controller = new CategoriaController();
$mensaje = null;
$mover_layout = 'desplazar-bloque-completo';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->guardarCategoria($_POST);
}

$page_title = "Nueva Categoria - Sistema de Almacen";
$page_css = [
    '/Software_Almacen/public/css/categorias/categorias.css',
    '/Software_Almacen/public/css/login/login.css'
];

require_once 'layouts/header.php';
?>

<div class="product-form-page" style="display: flex; justify-content: center; align-items: center; min-height: 75vh; width: 100%;">
    
    <div class="auth-box product-auth-box" style="margin: 0 auto; float: none;">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>REGISTRAR CATEGORIA</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $mensaje['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form action="nueva_categoria.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre de la Categoria *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre de la Categoria *" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Descripcion"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar">GUARDAR CATEGORIA</button>
        </form>

    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
