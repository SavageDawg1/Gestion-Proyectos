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

// Validar que el ID venga en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}

$id_categoria = $_GET['id'];

// Procesar el formulario si se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $controller->modificarCategoria($id_categoria, $_POST);
}

// Obtener los datos actuales de la categoría
$categoria = $controller->obtenerCategoria($id_categoria);

// Si alguien pone un ID falso en la URL, lo devolvemos a la lista
if (!$categoria) {
    header("Location: categorias.php");
    exit;
}

$page_title = "Editar Categoría - Sistema de Almacén";
$page_css = ['/Software_Almacen/public/css/categorias/categorias.css'];

require_once 'layouts/header.php';
?>

<div class="main-content" style="padding: 20px;">
    <div class="modulo-header">
        <h2>Editar Categoría: <?php echo htmlspecialchars($categoria['nombre']); ?></h2>
        <a href="categorias.php" class="btn-nuevo" style="background-color: #7f8c8d;">Volver al Listado</a>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="editar_categoria.php?id=<?php echo $id_categoria; ?>" method="POST">
            <div class="form-group">
                <label>Nombre de la Categoría *</label>
                <input type="text" name="nombre" required class="form-control" 
                       value="<?php echo htmlspecialchars($categoria['nombre']); ?>">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="4" class="form-control" style="resize: vertical;"><?php echo htmlspecialchars($categoria['descripcion']); ?></textarea>
            </div>

            <button type="submit" class="btn-nuevo" style="width: 100%; border: none; cursor: pointer;">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>