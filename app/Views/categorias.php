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

if (isset($_GET['eliminar_id'])) {
    $mensaje = $controller->eliminarCategoria($_GET['eliminar_id']);
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
        <div class="page-alert <?php echo $mensaje['success'] ? 'page-alert-success' : 'page-alert-danger'; ?>">
            <?php echo htmlspecialchars($mensaje['message']); ?>
        </div>
    <?php endif; ?>

    <input type="text" id="buscadorCategorias" class="buscador vista-ajustada" placeholder="Buscar categoria...">

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
                            <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($categoria['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($categoria['descripcion']); ?></td>
                            <td>
                                <a href="editar_categoria.php?id=<?php echo $categoria['id']; ?>" class="btn-accion btn-editar">Editar</a>
                                <a href="categorias.php?eliminar_id=<?php echo $categoria['id']; ?>"
                                   class="btn-accion btn-eliminar"
                                   onclick="return confirm('Seguro que deseas eliminar esta categoria?');">
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
    document.getElementById('buscadorCategorias').addEventListener('keyup', function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll('#tablaCategorias tbody tr');

        filas.forEach(function(fila) {
            if (fila.cells.length === 1) return;
            let nombre = fila.cells[1].textContent.toLowerCase();
            fila.style.display = nombre.includes(filtro) ? '' : 'none';
        });
    });
</script>

<?php require_once 'layouts/footer.php'; ?>
