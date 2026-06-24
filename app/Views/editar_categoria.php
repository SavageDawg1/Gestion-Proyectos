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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}

$id_categoria = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Intentamos modificar la categoría
    $mensaje = $controller->modificarCategoria($id_categoria, $_POST);
    
    // Si la respuesta fue exitosa, redirigimos al listado
    if (isset($mensaje['success']) && $mensaje['success'] === true) {
        header("Location: categorias.php?status=editado");
        exit;
    }
    // Si hubo error, el código sigue de largo y mostrará el div alert-danger abajo
}

$categoria = $controller->obtenerCategoria($id_categoria);
if (!$categoria) {
    header("Location: categorias.php");
    exit;
}

$page_title = "Editar Categoria - Sistema de Almacen";
$page_css = [
    '/Software_Almacen/public/css/categorias/categorias.css',
    '/Software_Almacen/public/css/login/login.css'
];

require_once 'layouts/header.php';
?>

<div class="product-form-page">
    <div class="auth-box product-auth-box">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>EDITAR CATEGORIA</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $mensaje['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensaje['message']); ?>
            </div>
        <?php endif; ?>

        <form action="editar_categoria.php?id=<?php echo $id_categoria; ?>" method="POST" data-dirty-guard>
            <div class="form-group">
                <label for="nombre">Nombre de la Categoria *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre de la Categoria *" required maxlength="80" value="<?php echo htmlspecialchars($categoria['nombre']); ?>">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Descripcion" maxlength="255"><?php echo htmlspecialchars($categoria['descripcion']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar" data-dirty-submit data-confirm-message="Se guardar&aacute;n los cambios de esta categor&iacute;a. &iquest;Confirmas la edici&oacute;n?" disabled>GUARDAR CAMBIOS</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
