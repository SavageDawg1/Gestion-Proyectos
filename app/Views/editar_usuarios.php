<?php
/**
 * Edicion de usuarios del sistema.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';
require_once '../Models/Usuario.php';

$page_title = "Editar Usuarios - Almacen";

requireLogin();

if (!isset($_SESSION['rol_id']) || (int) $_SESSION['rol_id'] !== 1) {
    header("Location: dashboard.php");
    exit;
}

$usuarioModel = new Usuario($conexion);
$mensaje = null;
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $formAction = isset($_POST['form_action']) ? sanitizeInput($_POST['form_action']) : 'save';
    $nombre = isset($_POST['nombre_apellido']) ? sanitizeInput($_POST['nombre_apellido']) : '';
    $rut = isset($_POST['rut']) ? cleanRut($_POST['rut']) : '';
    $correo = isset($_POST['correo']) ? sanitizeInput($_POST['correo']) : '';
    $rolId = isset($_POST['rol_id']) ? (int) $_POST['rol_id'] : 0;
    $activo = isset($_POST['activo']) ? (int) $_POST['activo'] : 0;
    $password = isset($_POST['password']) ? trim((string) $_POST['password']) : '';
    $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    $usuarioActual = $id > 0 ? $usuarioModel->obtenerPorId($id) : false;

    if ($formAction === 'delete') {
        if ($id <= 0 || !$usuarioActual) {
            $mensaje = "Seleccione un usuario valido para eliminar.";
            $tipoMensaje = 'danger';
        } elseif ($id === $currentUserId) {
            $mensaje = "No puede eliminar su propio usuario actual.";
            $tipoMensaje = 'danger';
        } elseif ((int) $usuarioActual['rol_id'] === 1 && (int) $usuarioActual['activo'] === 1 && $usuarioModel->contarAdministradoresActivos() <= 1) {
            $mensaje = "No puede eliminar el ultimo administrador activo.";
            $tipoMensaje = 'danger';
        } else {
            $resultado = $usuarioModel->eliminarUsuario($id);
            if ($resultado['success']) {
                $mensaje = "Usuario eliminado correctamente.";
                $tipoMensaje = 'success';
            } else {
                $mensaje = "No fue posible eliminar el usuario.";
                $tipoMensaje = 'danger';
            }
        }
    } elseif ($id <= 0 || !$usuarioActual || !isNotEmpty($nombre) || !isNotEmpty($rut) || !isNotEmpty($correo)) {
        $mensaje = "Complete los datos obligatorios del usuario.";
        $tipoMensaje = 'danger';
    } elseif (!isValidRut($rut)) {
        $mensaje = "El R.U.T. ingresado no es valido.";
        $tipoMensaje = 'danger';
    } elseif (!isValidEmail($correo)) {
        $mensaje = "El correo ingresado no es valido.";
        $tipoMensaje = 'danger';
    } elseif (!$usuarioModel->existeRol($rolId)) {
        $mensaje = "Seleccione un rol valido.";
        $tipoMensaje = 'danger';
    } elseif (!in_array($activo, [0, 1], true)) {
        $mensaje = "Seleccione un estado valido.";
        $tipoMensaje = 'danger';
    } elseif ($id === $currentUserId && ($rolId !== 1 || $activo !== 1)) {
        $mensaje = "No puede quitarse su propio rol administrador ni desactivar su usuario actual.";
        $tipoMensaje = 'danger';
    } elseif ((int) $usuarioActual['rol_id'] === 1 && (int) $usuarioActual['activo'] === 1 && ($rolId !== 1 || $activo !== 1) && $usuarioModel->contarAdministradoresActivos() <= 1) {
        $mensaje = "No puede dejar el sistema sin administradores activos.";
        $tipoMensaje = 'danger';
    } else {
        $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
        $resultado = $usuarioModel->actualizarUsuario($id, $nombre, $rut, $correo, $rolId, $activo, $passwordHash);

        if ($resultado['success']) {
            $mensaje = "Usuario actualizado correctamente.";
            $tipoMensaje = 'success';

            if ($id === $currentUserId) {
                $_SESSION['user'] = $nombre;
                $_SESSION['email'] = $correo;
                $_SESSION['rol_id'] = $rolId;
            }
        } else {
            $mensaje = isset($resultado['errno']) && (int) $resultado['errno'] === 1062
                ? "El correo o R.U.T. ya esta registrado en otro usuario."
                : "No fue posible actualizar el usuario.";
            $tipoMensaje = 'danger';
        }
    }
}

$usuarios = $usuarioModel->listarUsuarios();
$roles = $usuarioModel->listarRoles();
$currentSessionUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$page_css = [
    '/Software_Almacen/public/css/dashboard/dashboard.css',
    '/Software_Almacen/public/css/login/login.css'
];
?>
<?php require_once 'layouts/header.php'; ?>

    <div class="modulo-header">
        <div>
            <h2>Editar Usuarios</h2>
        </div>
        <a href="/Software_Almacen/app/Views/registro_usuario.php" class="btn-nuevo">+ Registrar Usuario</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="page-alert <?php echo $tipoMensaje === 'success' ? 'page-alert-success' : 'page-alert-danger'; ?>" data-page-alert>
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div class="users-toolbar">
        <input type="text" id="buscadorUsuarios" class="buscador" placeholder="Buscar usuario, R.U.T., correo o rol...">
        <span class="users-result-count" id="usuariosResultCount"></span>
    </div>

    <div class="table-card users-list">
        <div class="users-list-header">
            <span>Nombre</span>
            <span>R.U.T.</span>
            <span>Correo</span>
            <span>Rol</span>
            <span>Estado</span>
            <span>Nueva Contrasena</span>
            <span>Acciones</span>
        </div>

        <?php if (empty($usuarios)): ?>
            <div class="users-empty">No hay usuarios registrados.</div>
        <?php else: ?>
            <?php foreach ($usuarios as $usuario): ?>
                <?php
                    $usuarioId = (int) $usuario['id'];
                    $esUsuarioActual = $usuarioId === $currentSessionUserId;
                    $estadoTexto = (int) $usuario['activo'] === 1 ? 'Activo' : 'Inactivo';
                    $textoBusqueda = trim(
                        ($usuario['nombre_apellido'] ?? '') . ' ' .
                        ($usuario['rut'] ?? '') . ' ' .
                        ($usuario['correo'] ?? '') . ' ' .
                        ($usuario['rol_nombre'] ?? '') . ' ' .
                        $estadoTexto
                    );
                ?>
                <form method="POST" action="editar_usuarios.php" class="user-edit-row" data-user-row data-user-search="<?php echo htmlspecialchars($textoBusqueda, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" value="<?php echo $usuarioId; ?>">
                    <label class="user-field">
                        <span>Nombre</span>
                        <input type="text" name="nombre_apellido" class="form-control table-input"
                               value="<?php echo htmlspecialchars($usuario['nombre_apellido']); ?>" required>
                    </label>
                    <label class="user-field">
                        <span>R.U.T.</span>
                        <input type="text" name="rut" class="form-control table-input"
                               value="<?php echo htmlspecialchars($usuario['rut']); ?>" required>
                    </label>
                    <label class="user-field">
                        <span>Correo</span>
                        <input type="email" name="correo" class="form-control table-input"
                               value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                    </label>
                    <label class="user-field">
                        <span>Rol</span>
                        <select name="rol_id" class="form-control table-input" required>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo (int) $rol['id']; ?>" <?php echo (int) $usuario['rol_id'] === (int) $rol['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="user-field">
                        <span>Estado</span>
                        <select name="activo" class="form-control table-input" required>
                            <option value="1" <?php echo (int) $usuario['activo'] === 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo (int) $usuario['activo'] === 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </label>
                    <label class="user-field">
                        <span>Nueva Contrase&ntilde;a</span>
                        <input type="password" name="password" class="form-control table-input" placeholder="Sin cambios" minlength="6" autocomplete="new-password">
                    </label>
                    <div class="user-row-actions">
                        <button type="submit" name="form_action" value="save" class="btn-accion btn-editar btn-save-user" data-confirm-message="Se guardar&aacute;n los cambios de este usuario. &iquest;Confirmas la edici&oacute;n?" disabled>Guardar</button>
                        <button type="submit" name="form_action" value="delete" class="btn-accion btn-eliminar"
                                formnovalidate
                                data-confirm-message="Se eliminar&aacute; este usuario. &iquest;Deseas continuar?"
                                <?php echo $esUsuarioActual ? 'disabled' : ''; ?>>
                            Eliminar
                        </button>
                    </div>
                </form>
            <?php endforeach; ?>
            <div class="users-empty users-no-results" id="usuariosNoResults" hidden>No hay usuarios para esta busqueda.</div>
        <?php endif; ?>
    </div>

<script>
const buscadorUsuarios = document.getElementById('buscadorUsuarios');
const filasUsuarios = document.querySelectorAll('[data-user-row]');
const contadorUsuarios = document.getElementById('usuariosResultCount');
const usuariosNoResults = document.getElementById('usuariosNoResults');

function normalizarTextoUsuarios(texto) {
    return texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();
}

function actualizarUsuariosVisibles() {
    const termino = buscadorUsuarios ? normalizarTextoUsuarios(buscadorUsuarios.value.trim()) : '';
    let visibles = 0;

    filasUsuarios.forEach((fila) => {
        const texto = normalizarTextoUsuarios(fila.dataset.userSearch || '');
        const visible = texto.includes(termino);
        fila.hidden = !visible;
        if (visible) visibles++;
    });

    if (contadorUsuarios) {
        contadorUsuarios.textContent = 'Mostrando ' + visibles + ' de ' + filasUsuarios.length;
    }

    if (usuariosNoResults) {
        usuariosNoResults.hidden = visibles > 0 || filasUsuarios.length === 0;
    }
}

if (buscadorUsuarios) {
    buscadorUsuarios.addEventListener('input', actualizarUsuariosVisibles);
}

filasUsuarios.forEach((formulario) => {
    const campos = Array.from(formulario.querySelectorAll('input:not([type="hidden"]), select'));
    const botonGuardar = formulario.querySelector('.btn-save-user');
    const valoresOriginales = new Map();

    campos.forEach((campo) => {
        valoresOriginales.set(campo.name, campo.value);
    });

    function actualizarGuardar() {
        const hayCambios = campos.some((campo) => campo.value !== valoresOriginales.get(campo.name));
        if (botonGuardar) {
            botonGuardar.disabled = !hayCambios;
        }
    }

    campos.forEach((campo) => {
        campo.addEventListener('input', actualizarGuardar);
        campo.addEventListener('change', actualizarGuardar);
    });

    actualizarGuardar();
});

actualizarUsuariosVisibles();
</script>

<?php require_once 'layouts/footer.php'; ?>
