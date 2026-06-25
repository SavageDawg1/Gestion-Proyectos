<?php
/**
 * Registro de usuarios.
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../Models/Usuario.php';

$page_title = "Registrar Usuario - Almacen";

requireLogin();

if (!isset($_SESSION['rol_id']) || (int) $_SESSION['rol_id'] !== 1) {
    header("Location: dashboard.php");
    exit;
}

$isLoggedIn = isAuthenticated();
$currentPage = 'registro_usuario';
$user = getCurrentUser();
$usuarioModel = new Usuario($conexion);
$roles = $usuarioModel->listarRoles();
$page_css = [
    '/Software_Almacen/public/css/dashboard/dashboard.css',
    '/Software_Almacen/public/css/login/login.css'
];
?>
<?php require_once 'layouts/header.php'; ?>

    <div class="auth-box admin-register-box">
        <div class="auth-header">
            <img src="/Software_Almacen/public/assets/images/logo_el_legado.png" alt="El Legado" class="logo">
            <h1>REGISTRAR USUARIO</h1>
        </div>

        <div id="register-messages"></div>

        <form id="register-form" class="admin-register-form">
            <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre Completo" required>
            </div>

            <div class="form-group">
                <label for="rut_visual">R.U.T *</label>
                <input type="text" id="rut_visual" placeholder="Ej: 12.345.678-K" maxlength="12" pattern="[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9Kk]" title="Debe ingresar el R.U.T completo (ej: 12.345.678-K)" required>
                
                <input type="hidden" id="rut_display">
            </div>

            <div class="form-group">
                <label for="register-email">Correo Electrónico *</label>
                <input type="email" id="register-email" name="email" placeholder="Correo Electrónico" required>
            </div>

            <div class="form-group">
                <label for="register-role">Rol *</label>
                <select id="register-role" name="rol_id" required>
                    <option value="">Seleccione Rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo (int) $rol['id']; ?>">
                            <?php echo htmlspecialchars($rol['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="register-password">Contraseña *</label>
                <input type="password" id="register-password" name="password" placeholder="Contraseña" minlength="6" autocomplete="new-password" required>
                <small>Mínimo 6 caracteres.</small>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirmar Contraseña *</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirmar Contraseña" minlength="6" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-ingresar">REGISTRAR USUARIO</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('register-form');
            const rutVisual = document.getElementById('rut_visual');
            const rutOculto = document.getElementById('rut_display');
            
            // Formateador de RUT Dinámico con puntos
            if (rutVisual) {
                rutVisual.addEventListener('input', function(e) {
                    let valor = this.value.replace(/[^0-9kK]/g, '').toUpperCase();
                    
                    if (valor.length === 0) {
                        this.value = '';
                        if (rutOculto) rutOculto.value = '';
                        return;
                    }
                    
                    if (valor.length > 9) {
                        valor = valor.substring(0, 9);
                    }
                    
                    let cuerpo = valor.slice(0, -1);
                    let dv = valor.slice(-1);
                    
                    cuerpo = cuerpo.replace(/K/g, '');
                    cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    
                    if (valor.length > 1) {
                        this.value = cuerpo + '-' + dv;
                    } else {
                        this.value = valor.replace(/K/g, '');
                    }
                    
                    if (rutOculto) {
                        rutOculto.value = this.value;
                    }
                });
            }
            
            // Control de scroll inteligente hacia campos con errores
            if (form) {
                form.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    
                    const primerCampoInvalido = form.querySelector(':invalid');
                    
                    if (primerCampoInvalido) {
                        const rect = primerCampoInvalido.getBoundingClientRect();
                        const margenSuperior = 120; 
                        
                        // Comprobamos si el elemento está fuera de la pantalla visible actual
                        const estaFueraDePantalla = (
                            rect.top < margenSuperior || 
                            rect.bottom > (window.innerHeight || document.documentElement.clientHeight)
                        );
                        
                        // Solo mueve la pantalla si el campo no se está viendo
                        if (estaFueraDePantalla) {
                            const posicionElemento = rect.top + window.pageYOffset;
                            window.scrollTo({
                                top: posicionElemento - margenSuperior,
                                behavior: 'smooth'
                            });
                        }
                        
                        // Sin importar si se movió o no, hace focus y pone el borde rojo
                        setTimeout(() => {
                            primerCampoInvalido.focus({ preventScroll: true });
                        }, 200);
                        
                        primerCampoInvalido.style.border = '2px solid red';
                        setTimeout(() => {
                            primerCampoInvalido.style.border = '';
                        }, 2000);
                    }
                }, true);
            }
        });
    </script>

    <script src="/Software_Almacen/public/js/script.js?v=<?php echo $asset_version; ?>"></script>
    <script src="/Software_Almacen/public/js/login/login.js?v=<?php echo $asset_version; ?>"></script>

<?php require_once 'layouts/footer.php'; ?>