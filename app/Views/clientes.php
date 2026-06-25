<?php
require_once '../../includes/session.php';
require_once '../Controllers/ClienteController.php';

$page_title = "Clientes - Almacen";
$page_css = '/Software_Almacen/public/css/clientes/clientes.css';
requireLogin();

$clienteController = new ClienteController();
$mensaje = null;
$tipo_alerta = 'success';

if (isset($_SESSION['clientes_flash'])) {
    $mensaje = $_SESSION['clientes_flash']['mensaje'] ?? null;
    $tipo_alerta = $_SESSION['clientes_flash']['tipo'] ?? 'success';
    unset($_SESSION['clientes_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'editar') {
        $ok = $clienteController->actualizarCliente($_POST);
        $mensaje = $ok ? 'Cliente actualizado correctamente.' : 'No se pudo actualizar el cliente. Revisa que el RUT no este repetido.';
        $tipo_alerta = $ok ? 'success' : 'error';
    } elseif ($accion === 'eliminar') {
        $ok = $clienteController->eliminarCliente($_POST['cliente_id'] ?? 0);
        $mensaje = $ok ? 'Cliente eliminado correctamente.' : 'No se pudo eliminar el cliente.';
        $tipo_alerta = $ok ? 'success' : 'error';
    } elseif ($accion === 'saldar_total') {
        $abonado = $clienteController->saldarDeudaCompleta($_POST['cliente_id'] ?? 0);
        $ok = $abonado !== false;
        $mensaje = $ok
            ? 'Deuda saldada completamente. Abono registrado: $' . number_format((float) $abonado, 0, ',', '.')
            : 'No se pudo saldar la deuda del cliente.';
        $tipo_alerta = $ok ? 'success' : 'error';
    } elseif ($accion === 'saldar_parcial') {
        $restante = $clienteController->abonarDeuda($_POST['cliente_id'] ?? 0, $_POST['monto_abono'] ?? 0);
        $ok = $restante !== false;
        $mensaje = $ok
            ? 'Abono registrado. Deuda restante: $' . number_format((float) $restante, 0, ',', '.')
            : 'No se pudo registrar el abono. El monto debe ser mayor que cero y no superar la deuda actual.';
        $tipo_alerta = $ok ? 'success' : 'error';
    }

    if ($mensaje) {
        $_SESSION['clientes_flash'] = [
            'mensaje' => $mensaje,
            'tipo' => $tipo_alerta
        ];
    }

    header('Location: clientes.php');
    exit;
}

$clientes = $clienteController->listarClientes();
$totalClientes = count($clientes);
$clientesConDeuda = 0;
$deudaTotal = 0;

foreach ($clientes as $clienteResumen) {
    $deudaResumen = (float) ($clienteResumen['deuda'] ?? 0);
    $deudaTotal += $deudaResumen;

    if ($deudaResumen > 0) {
        $clientesConDeuda++;
    }
}

require_once 'layouts/header.php';
?>

<div class="view-stack clientes-page">
    <div class="modulo-header">
        <div>
            <h2>Clientes</h2>
        </div>
    </div>

    <div class="clientes-search-row">
        <input type="text" id="buscador_clientes" class="buscador" placeholder="Buscar cliente, RUT o telefono...">
        <button type="button" class="btn-accion clientes-clear-btn" id="clientes_clear_filters" disabled>Limpiar</button>
    </div>

    <div class="clientes-filter-bar" aria-label="Filtros de clientes">
        <button type="button" class="clientes-filter-btn is-active" data-filter="todos">Todos</button>
        <button type="button" class="clientes-filter-btn" data-filter="deuda">Con deuda</button>
        <button type="button" class="clientes-filter-btn" data-filter="saldados">Saldados</button>
        <span class="clientes-result-count" id="clientes_result_count"></span>
    </div>

    <div class="clientes-summary-grid">
        <div class="clientes-summary-card">
            <span>Total clientes</span>
            <strong><?php echo $totalClientes; ?></strong>
        </div>
        <div class="clientes-summary-card">
            <span>Con deuda</span>
            <strong><?php echo $clientesConDeuda; ?></strong>
        </div>
        <div class="clientes-summary-card">
            <span>Deuda total</span>
            <strong>$<?php echo number_format($deudaTotal, 0, ',', '.'); ?></strong>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="clientes-alert clientes-alert-<?php echo $tipo_alerta; ?>" data-temporary-alert>
            <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="clientes-table-card">
        <div class="clientes-list">
            <?php if (empty($clientes)): ?>
                <div class="clientes-empty">
                    <strong>No hay clientes con fiado todav&iacute;a.</strong>
                    <span>Cuando registres una venta fiada, el cliente aparecer&aacute; aqu&iacute; para gestionar su deuda.</span>
                </div>
            <?php else: ?>
                <?php foreach ($clientes as $cliente):
                    $clienteId = (int) $cliente['id'];
                    $deuda = (float) ($cliente['deuda'] ?? 0);
                    $deudaFormateada = '$' . number_format($deuda, 0, ',', '.');
                    $fecha = !empty($cliente['fecha_registro']) ? date('d/m/Y H:i', strtotime($cliente['fecha_registro'])) : '-';
                    $estado = $deuda > 0 ? 'Debe' : 'Saldado';
                    $ultimoMovimiento = $cliente['ultimo_movimiento'] ?? null;
                    $ultimoMovimientoTexto = 'Sin movimientos';
                    if ($ultimoMovimiento) {
                        $ultimoMovimientoTexto = $ultimoMovimiento['tipo'] . ' - $' . number_format((float) $ultimoMovimiento['monto'], 0, ',', '.') . ' - ' . date('d/m/Y H:i', strtotime($ultimoMovimiento['fecha']));
                    }
                    $historialVentas = $cliente['historial_ventas'] ?? [];
                    $historialAbonos = $cliente['historial_abonos'] ?? [];
                ?>
                    <div
                        class="cliente-row"
                        data-cliente-nombre="<?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-cliente-rut="<?php echo htmlspecialchars($cliente['rut'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-cliente-telefono="<?php echo htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        data-deuda="<?php echo $deuda; ?>"
                    >
                        <form class="cliente-edit-form" action="clientes.php" method="POST" data-confirm-scope="clientes" data-confirm-message="Se guardar&aacute;n los cambios de este cliente. &iquest;Confirmas la edici&oacute;n?">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="cliente_id" value="<?php echo $clienteId; ?>">

                            <label>
                                <span>Cliente</span>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </label>

                            <label>
                                <span>RUT</span>
                                <input type="text" name="rut" value="<?php echo htmlspecialchars($cliente['rut'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </label>

                            <label>
                                <span>Telefono</span>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </label>

                            <div class="cliente-deuda <?php echo $deuda > 0 ? 'cliente-deuda-pendiente' : 'cliente-deuda-ok'; ?>">
                                <?php echo $deudaFormateada; ?>
                            </div>

                            <div class="cliente-fecha">
                                <span class="cliente-status-badge <?php echo $deuda > 0 ? 'is-debt' : 'is-paid'; ?>"><?php echo $estado; ?></span>
                                <span>Ingreso: <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <div class="cliente-actions">
                                <button type="submit" class="btn-accion btn-editar btn-guardar-cliente" disabled>Guardar</button>
                            </div>
                        </form>

                        <div class="cliente-last-movement">
                            <span>&Uacute;ltimo movimiento</span>
                            <strong><?php echo htmlspecialchars($ultimoMovimientoTexto, ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>

                        <div class="cliente-danger-actions">
                            <form action="clientes.php" method="POST" data-confirm-scope="clientes" data-confirm-message="<?php echo $deuda > 0 ? 'Este cliente mantiene una deuda de ' . $deudaFormateada . '. Se eliminar&aacute; el cliente y sus pagos asociados; las ventas hist&oacute;ricas quedar&aacute;n sin cliente. &iquest;Deseas continuar?' : 'Se eliminar&aacute; este cliente y sus pagos fiados asociados. Las ventas hist&oacute;ricas quedar&aacute;n sin cliente. &iquest;Deseas continuar?'; ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="cliente_id" value="<?php echo $clienteId; ?>">
                                <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
                            </form>

                            <form action="clientes.php" method="POST" data-confirm-scope="clientes" data-confirm-message="Se registrar&aacute; un pago por el total pendiente y la deuda quedar&aacute; en cero. &iquest;Confirmas saldar la deuda completa?">
                                <input type="hidden" name="accion" value="saldar_total">
                                <input type="hidden" name="cliente_id" value="<?php echo $clienteId; ?>">
                                <button type="submit" class="btn-accion btn-saldar" <?php echo $deuda <= 0 ? 'disabled' : ''; ?>>Saldar Deuda Completa</button>
                            </form>
                        </div>

                        <form class="cliente-abono-form" action="clientes.php" method="POST" data-confirm-scope="clientes" data-confirm-message="Se descontar&aacute; este abono de la deuda actual. &iquest;Confirmas registrar el pago parcial?">
                            <input type="hidden" name="accion" value="saldar_parcial">
                            <input type="hidden" name="cliente_id" value="<?php echo $clienteId; ?>">
                            <input type="number" name="monto_abono" min="1" max="<?php echo max(0, $deuda); ?>" step="1" placeholder="Monto abonado" data-deuda-actual="<?php echo $deuda; ?>" <?php echo $deuda <= 0 ? 'disabled' : 'required'; ?>>
                            <button type="submit" class="btn-accion btn-abono btn-abono-cliente" disabled>Saldar Deuda Parcial</button>
                            <small class="cliente-abono-preview" aria-live="polite"></small>
                        </form>

                        <details class="cliente-history">
                            <summary>Ver historial</summary>
                            <?php if (empty($historialVentas) && empty($historialAbonos)): ?>
                                <p class="cliente-history-empty">No hay movimientos registrados.</p>
                            <?php else: ?>
                                <div class="cliente-history-sections">
                                    <section class="cliente-history-section">
                                        <h4>Ventas fiadas</h4>
                                        <?php if (empty($historialVentas)): ?>
                                            <p class="cliente-history-empty">No hay ventas fiadas registradas.</p>
                                        <?php else: ?>
                                            <div class="cliente-history-list">
                                                <?php foreach ($historialVentas as $indice => $movimiento): ?>
                                                    <div class="cliente-history-item <?php echo $indice >= 8 ? 'is-extra' : ''; ?>" <?php echo $indice >= 8 ? 'hidden' : ''; ?>>
                                                        <span><?php echo htmlspecialchars($movimiento['tipo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <strong>$<?php echo number_format((float) $movimiento['monto'], 0, ',', '.'); ?></strong>
                                                        <time><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></time>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($historialVentas) > 8): ?>
                                                <button type="button" class="cliente-history-more" data-history-more>Ver <?php echo count($historialVentas) - 8; ?> ventas m&aacute;s</button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </section>

                                    <section class="cliente-history-section">
                                        <h4>Abonos</h4>
                                        <?php if (empty($historialAbonos)): ?>
                                            <p class="cliente-history-empty">No hay abonos manuales registrados.</p>
                                        <?php else: ?>
                                            <div class="cliente-history-list">
                                                <?php foreach ($historialAbonos as $indice => $movimiento): ?>
                                                    <div class="cliente-history-item <?php echo $indice >= 8 ? 'is-extra' : ''; ?>" <?php echo $indice >= 8 ? 'hidden' : ''; ?>>
                                                        <span><?php echo htmlspecialchars($movimiento['tipo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <strong>$<?php echo number_format((float) $movimiento['monto'], 0, ',', '.'); ?></strong>
                                                        <time><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></time>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($historialAbonos) > 8): ?>
                                                <button type="button" class="cliente-history-more" data-history-more>Ver <?php echo count($historialAbonos) - 8; ?> abonos m&aacute;s</button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </section>
                                </div>
                            <?php endif; ?>
                        </details>
                    </div>
                <?php endforeach; ?>
                <div class="clientes-empty clientes-no-results" id="clientes_no_results" hidden>
                    <strong>No hay resultados para esta búsqueda.</strong>
                    <span>Prueba limpiar el buscador o cambiar el filtro activo.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="clientes-confirm-overlay" id="clientes_confirm_overlay" aria-hidden="true">
    <div class="clientes-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="clientes_confirm_title">
        <h3 id="clientes_confirm_title">Confirmar acci&oacute;n</h3>
        <p id="clientes_confirm_message"></p>
        <div class="clientes-confirm-actions">
            <button type="button" class="btn-accion clientes-confirm-cancel" id="clientes_confirm_cancel">Cancelar</button>
            <button type="button" class="btn-accion clientes-confirm-accept" id="clientes_confirm_accept">Confirmar</button>
        </div>
    </div>
</div>

<script>
const buscadorClientes = document.getElementById('buscador_clientes');
const filasClientes = document.querySelectorAll('.cliente-row');
const botonesFiltro = document.querySelectorAll('.clientes-filter-btn');
const contadorResultados = document.getElementById('clientes_result_count');
const limpiarFiltrosBtn = document.getElementById('clientes_clear_filters');
const sinResultados = document.getElementById('clientes_no_results');
const confirmOverlay = document.getElementById('clientes_confirm_overlay');
const confirmMessage = document.getElementById('clientes_confirm_message');
const confirmCancel = document.getElementById('clientes_confirm_cancel');
const confirmAccept = document.getElementById('clientes_confirm_accept');
let pendingConfirmForm = null;
let filtroClientes = 'todos';

function normalizarBusqueda(texto) {
    return texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();
}

function coincideDesdeInicio(texto, termino) {
    if (termino === '') {
        return true;
    }

    return normalizarBusqueda(texto)
        .split(/\s+/)
        .some((parte) => parte.startsWith(termino));
}

function actualizarVisibilidadClientes() {
    const termino = buscadorClientes ? normalizarBusqueda(buscadorClientes.value.trim()) : '';
    let visibles = 0;

    filasClientes.forEach((fila) => {
        const deuda = Number(fila.dataset.deuda || 0);
        const coincideBusqueda =
            coincideDesdeInicio(fila.dataset.clienteNombre || '', termino) ||
            coincideDesdeInicio(fila.dataset.clienteRut || '', termino) ||
            coincideDesdeInicio(fila.dataset.clienteTelefono || '', termino);
        const coincideFiltro =
            filtroClientes === 'todos' ||
            (filtroClientes === 'deuda' && deuda > 0) ||
            (filtroClientes === 'saldados' && deuda <= 0);
        const visible = coincideBusqueda && coincideFiltro;

        fila.style.display = visible ? 'grid' : 'none';
        if (visible) {
            visibles++;
        }
    });

    if (contadorResultados) {
        contadorResultados.textContent = 'Mostrando ' + visibles + ' de ' + filasClientes.length;
    }

    if (sinResultados) {
        sinResultados.hidden = visibles > 0 || filasClientes.length === 0;
    }

    if (limpiarFiltrosBtn) {
        limpiarFiltrosBtn.disabled = termino === '' && filtroClientes === 'todos';
    }
}

function programarFiltroClientes() {
    requestAnimationFrame(actualizarVisibilidadClientes);
}

if (buscadorClientes) {
    ['input', 'keyup', 'change', 'search'].forEach((evento) => {
        buscadorClientes.addEventListener(evento, programarFiltroClientes);
    });

    buscadorClientes.addEventListener('paste', function() {
        setTimeout(actualizarVisibilidadClientes, 0);
    });
}

botonesFiltro.forEach((boton) => {
    boton.addEventListener('click', function() {
        filtroClientes = this.dataset.filter || 'todos';
        botonesFiltro.forEach((otroBoton) => otroBoton.classList.remove('is-active'));
        this.classList.add('is-active');
        actualizarVisibilidadClientes();
    });
});

if (limpiarFiltrosBtn) {
    limpiarFiltrosBtn.addEventListener('click', function() {
        if (buscadorClientes) {
            buscadorClientes.value = '';
        }

        filtroClientes = 'todos';
        botonesFiltro.forEach((boton) => {
            boton.classList.toggle('is-active', boton.dataset.filter === 'todos');
        });
        actualizarVisibilidadClientes();
    });
}

actualizarVisibilidadClientes();

document.querySelectorAll('[data-temporary-alert]').forEach((alerta) => {
    setTimeout(() => {
        alerta.classList.add('clientes-alert-hidden');
        setTimeout(() => alerta.remove(), 300);
    }, 3500);
});

document.querySelectorAll('.cliente-edit-form').forEach((formulario) => {
    const campos = Array.from(formulario.querySelectorAll('input[name="nombre"], input[name="rut"], input[name="telefono"]'));
    const botonGuardar = formulario.querySelector('.btn-guardar-cliente');
    const valoresOriginales = new Map();

    campos.forEach((campo) => {
        valoresOriginales.set(campo.name, campo.value);
    });

    function actualizarEstadoGuardar() {
        const hayCambios = campos.some((campo) => campo.value !== valoresOriginales.get(campo.name));
        botonGuardar.disabled = !hayCambios;
    }

    campos.forEach((campo) => {
        campo.addEventListener('input', actualizarEstadoGuardar);
    });

    actualizarEstadoGuardar();
});

document.querySelectorAll('.cliente-abono-form').forEach((formulario) => {
    const inputAbono = formulario.querySelector('input[name="monto_abono"]');
    const preview = formulario.querySelector('.cliente-abono-preview');
    const botonAbono = formulario.querySelector('.btn-abono-cliente');

    if (!inputAbono || !preview || !botonAbono) {
        return;
    }

    function formatearMonto(valor) {
        return '$' + Math.max(0, valor).toLocaleString('es-CL');
    }

    function actualizarPreviewAbono() {
        const deuda = Number(inputAbono.dataset.deudaActual || 0);
        const abono = Number(inputAbono.value || 0);

        if (!abono || abono <= 0) {
            preview.textContent = '';
            preview.classList.remove('is-warning');
            botonAbono.disabled = true;
            return;
        }

        if (abono > deuda) {
            preview.textContent = 'El abono supera la deuda actual.';
            preview.classList.add('is-warning');
            botonAbono.disabled = true;
            return;
        }

        preview.classList.remove('is-warning');
        preview.textContent = 'Deuda restante: ' + formatearMonto(deuda - abono);
        botonAbono.disabled = false;
    }

    inputAbono.addEventListener('input', actualizarPreviewAbono);
    actualizarPreviewAbono();
});

document.querySelectorAll('[data-history-more]').forEach((boton) => {
    boton.addEventListener('click', function() {
        const seccion = this.closest('.cliente-history-section');
        if (!seccion) {
            return;
        }

        seccion.querySelectorAll('.cliente-history-item.is-extra[hidden]').forEach((item) => {
            item.hidden = false;
        });
        this.remove();
    });
});

document.querySelectorAll('form[data-confirm-scope="clientes"][data-confirm-message]').forEach((formulario) => {
    formulario.addEventListener('submit', function(event) {
        if (this.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();
        pendingConfirmForm = this;
        confirmMessage.innerHTML = this.dataset.confirmMessage;
        confirmOverlay.classList.add('is-visible');
        confirmOverlay.setAttribute('aria-hidden', 'false');
        confirmAccept.focus();
    });
});

function cerrarConfirmacion() {
    confirmOverlay.classList.remove('is-visible');
    confirmOverlay.setAttribute('aria-hidden', 'true');
    pendingConfirmForm = null;
}

confirmCancel.addEventListener('click', cerrarConfirmacion);

confirmAccept.addEventListener('click', function() {
    if (!pendingConfirmForm) {
        return;
    }

    pendingConfirmForm.dataset.confirmed = 'true';
    pendingConfirmForm.submit();
});

confirmOverlay.addEventListener('click', function(event) {
    if (event.target === confirmOverlay) {
        cerrarConfirmacion();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && confirmOverlay.classList.contains('is-visible')) {
        cerrarConfirmacion();
    }
});
</script>

<?php require_once 'layouts/footer.php'; ?>
