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

    if (isset($mensaje['success']) && $mensaje['success'] === true) {
        // Evaluamos qué botón presionó el usuario
        if (isset($_POST['accion']) && $_POST['accion'] === 'guardar_y_continuar') {
            // Recarga la misma página con un mensaje de éxito para seguir agregando
            header("Location: nueva_categoria.php?status=creado_continuar");
            exit;
        } else {
            // Si apretó el botón normal, lo devuelve al listado
            header("Location: categorias.php?status=creado");
            exit;
        }
    }
}

// Capturamos la redirección de "Guardar y Continuar" para mostrar la alerta
if (isset($_GET['status']) && $_GET['status'] === 'creado_continuar') {
    $mensaje = [
        'success' => true,
        'message' => 'Categoría registrada exitosamente. Puedes agregar la siguiente.'
    ];
}

$page_title = "Nueva Categoria - Sistema de Almacen";
$page_css = [
    '/Software_Almacen/public/css/categorias/categorias.css',
    '/Software_Almacen/public/css/login/login.css'
];

$back_title = "Volver a Categorías";
$back_url = "categorias.php";
require_once 'layouts/header.php';
?>

<div class="product-form-page">
    
    <div class="auth-box product-auth-box">
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
                <input type="text" id="nombre" name="nombre" placeholder="Nombre de la Categoria *" required maxlength="80">
                <small>Usa un nombre corto y facil de reconocer en ventas e inventario.</small>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Descripcion" maxlength="255"></textarea>
            </div>

            <button type="submit" name="accion" value="guardar_y_continuar" class="btn btn-secondary btn-block btn-ingresar form-secondary-action">
                GUARDAR Y AGREGAR OTRA
            </button>

            <button type="submit" name="accion" value="guardar" class="btn btn-primary btn-block btn-ingresar">
                GUARDAR Y VOLVER AL LISTADO
            </button>
        </form>

    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
