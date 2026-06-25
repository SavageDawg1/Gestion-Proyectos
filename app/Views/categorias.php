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

if (isset($_SESSION['categorias_flash'])) {
    $mensaje = $_SESSION['categorias_flash'];
    unset($_SESSION['categorias_flash']);
} elseif (isset($_GET['status'])) {
    if ($_GET['status'] === 'creado') {
        $mensaje = ['success' => true, 'message' => 'Categoria registrada correctamente.'];
    } elseif ($_GET['status'] === 'editado') {
        $mensaje = ['success' => true, 'message' => 'Categoria actualizada correctamente.'];
    }
}

if (isset($_GET['eliminar_id'])) {
    $_SESSION['categorias_flash'] = $controller->eliminarCategoria($_GET['eliminar_id']);
    header("Location: categorias.php");
    exit;
}

$listaCategorias = $controller->listarCategorias();

$page_title = "Categorias - Sistema de Almacen";
$page_css = ['/Software_Almacen/public/css/categorias/categorias.css'];

require_once 'layouts/header.php';
?>

    <div class="modulo-header">
        <h2>Categorias</h2>
        <a href="/Software_Almacen/app/Views/nueva_categoria.php" class="btn-nuevo">+ Agregar Categoria</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="page-alert <?php echo $mensaje['success'] ? 'page-alert-success' : 'page-alert-danger'; ?>" data-page-alert>
            <?php echo htmlspecialchars($mensaje['message']); ?>
        </div>
    <?php endif; ?>

    <div class="search-row">
        <input type="text" id="buscadorCategorias" class="buscador vista-ajustada" placeholder="Buscar categoria...">
        <button type="button" class="btn-accion" id="categorias_clear_filters" disabled>Limpiar</button>
    </div>

    <div class="table-card table-responsive">
        <table class="tabla-categorias" id="tablaCategorias">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripcion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listaCategorias)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No hay categorias registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listaCategorias as $categoria): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($categoria['id']); ?></td>
                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($categoria['nombre']); ?></strong></td>
                            <td data-label="Descripcion"><?php echo htmlspecialchars($categoria['descripcion']); ?></td>
                            <td data-label="Acciones">
                                <a href="editar_categoria.php?id=<?php echo $categoria['id']; ?>" class="btn-accion btn-editar">Editar</a>
                                <a href="categorias.php?eliminar_id=<?php echo $categoria['id']; ?>"
                                   class="btn-accion btn-eliminar"
                                   data-confirm-message="Se eliminar&aacute; esta categor&iacute;a. &iquest;Deseas continuar?">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<script>
    const buscadorCategorias = document.getElementById('buscadorCategorias');
    const limpiarCategoriasBtn = document.getElementById('categorias_clear_filters');

    function actualizarVisibilidadCategorias() {
        const filtro = buscadorCategorias.value.toLowerCase();
        let filas = document.querySelectorAll('#tablaCategorias tbody tr');

        filas.forEach(function(fila) {
            if (fila.cells.length === 1) return;
            let nombre = fila.cells[1].textContent.toLowerCase();
            fila.style.display = nombre.includes(filtro) ? '' : 'none';
        });
        limpiarCategoriasBtn.disabled = filtro === '';
    }

    buscadorCategorias.addEventListener('input', actualizarVisibilidadCategorias);
    buscadorCategorias.addEventListener('keyup', actualizarVisibilidadCategorias);

    if (limpiarCategoriasBtn) {
        limpiarCategoriasBtn.addEventListener('click', function() {
            buscadorCategorias.value = '';
            actualizarVisibilidadCategorias();
            buscadorCategorias.focus();
        });
    }
</script>

<?php require_once 'layouts/footer.php'; ?>
