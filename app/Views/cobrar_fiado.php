<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

$page_title = "Cobrar Fiados - Almacén";
requireLogin();

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset("utf8mb4");
$mensaje = null;
$tipo_alerta = "";

// Procesar el pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente_id'], $_POST['monto_pago'])) {
    $cliente_id = intval($_POST['cliente_id']);
    $monto = floatval($_POST['monto_pago']);

    if ($monto > 0) {
        $db->begin_transaction();
        try {
            // PROTECCIÓN ANTI-NULL APLICADA AQUÍ TAMBIÉN
            $stmtDeuda = $db->prepare("UPDATE clientes SET deuda = GREATEST(0, COALESCE(deuda, 0) - ?) WHERE id = ?");            $stmtDeuda->bind_param("di", $monto, $cliente_id);
            $stmtDeuda->execute();
            
            $stmtPago = $db->prepare("INSERT INTO pagos_fiados (cliente_id, monto) VALUES (?, ?)");
            $stmtPago->bind_param("id", $cliente_id, $monto);
            $stmtPago->execute();
            
            $db->commit();
            $mensaje = "Pago registrado exitosamente. Deuda rebajada.";
            $tipo_alerta = "success";
        } catch (Exception $e) {
            $db->rollback();
            $mensaje = "Error al procesar el pago.";
            $tipo_alerta = "error";
        }
    }
}

// Obtener SOLO clientes que actualmente deben dinero
$clientes_deudores = [];
$resultado = $db->query("SELECT id, nombre, rut, deuda FROM clientes WHERE deuda > 0 ORDER BY nombre ASC");
if ($resultado) {
    $clientes_deudores = $resultado->fetch_all(MYSQLI_ASSOC);
}

$page_css = '/Software_Almacen/public/css/dashboard/dashboard.css';
require_once 'layouts/header.php';
?>

<div class="main-content">
    <div class="welcome-section">
        <h2>Gestión de Cobranzas</h2>
        <p>Registra el abono o pago total de cuentas por cobrar.</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="dashboard-panel" id="alerta-mensaje">
            <h3 class="<?php echo $tipo_alerta === 'success' ? 'text-warning' : 'text-danger'; ?>">
                <?php echo $mensaje; ?>
            </h3>
        </div>
    <?php endif; ?>

    <div class="cobro-panel">
        <form action="cobrar_fiado.php" method="POST">
            <div class="form-group-cobro">
                <label class="form-label-cobro">Seleccionar Cliente con Deuda</label>
                <select name="cliente_id" id="cliente_id" class="form-control-cobro" required>
                    <option value="">-- Seleccione un deudor --</option>
                    
                    <?php if(empty($clientes_deudores)): ?>
                        <option value="" disabled>No hay clientes con deudas pendientes</option>
                    <?php else: ?>
                        <?php foreach($clientes_deudores as $cli): ?>
                            <option value="<?php echo $cli['id']; ?>" data-deuda="<?php echo $cli['deuda']; ?>">
                                <?php echo htmlspecialchars($cli['nombre']); ?> (Debe: $<?php echo number_format($cli['deuda'], 0, ',', '.'); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                </select>
            </div>

            <div class="form-group-cobro">
                <label class="form-label-cobro">Monto a Pagar ($)</label>
                <input type="number" name="monto_pago" id="monto_pago" class="form-control-cobro" required min="1">
            </div>

            <button type="submit" id="btn_pagar" class="btn-submit-cobro" onclick="this.innerText='Procesando...'; this.style.opacity='0.7'; setTimeout(() => { this.disabled=true; }, 50);">
                Registrar Pago
            </button>
        </form>
    </div>
</div>

<script>
    document.getElementById('cliente_id').addEventListener('change', function() {
        let opcionSeleccionada = this.options[this.selectedIndex];
        let deuda = opcionSeleccionada.getAttribute('data-deuda');
        
        if (deuda) {
            document.getElementById('monto_pago').value = parseFloat(deuda);
            document.getElementById('monto_pago').max = parseFloat(deuda);
        } else {
            document.getElementById('monto_pago').value = '';
            document.getElementById('monto_pago').removeAttribute('max');
        }
    });
</script>

<?php require_once 'layouts/footer.php'; ?>