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
// CORRECCIÓN 1: Interceptar la orden de eliminar antes de cargar la lista
if (isset($_GET['eliminar_id'])) {
    $mensaje = $controller->eliminarCategoria($_GET['eliminar_id']);
}

// Obtener la lista (se actualizará sola si acabamos de borrar algo)
$listaCategorias = $controller->listarCategorias();

$page_title = "Categorías - Sistema de Almacén";
$page_css = ['/Software_Almacen/public/css/categorias/categorias.css'];


require_once 'layouts/header.php';
?>

    <?php if ($mensaje): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; 
                    background-color: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; 
                    color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <input type="text" id="buscadorCategorias" class="buscador vista-ajustada" placeholder="Buscar categoría...">

    <div class="table-card table-responsive">
    <table class="tabla-categorias" id="tablaCategorias">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listaCategorias)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No hay categorías registradas.</td>
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
                               onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?');">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
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

<div style="position: absolute; right: 40px; top: 120px; z-index: 100;">
    <a href="/Software_Almacen/app/Views/nueva_categoria.php" class="navbar-action">
        + Agregar Categoría
    </a>
</div>

<?php require_once 'layouts/footer.php'; ?>
