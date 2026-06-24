<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Models/Venta.php';
require_once '../Controllers/ClienteController.php';

$page_title = "Punto de Venta - Almacen";
requireLogin();

$mensaje = null;
$tipo_alerta = 'success';

if (isset($_SESSION['ventas_flash'])) {
    $mensaje = $_SESSION['ventas_flash']['mensaje'] ?? null;
    $tipo_alerta = $_SESSION['ventas_flash']['tipo'] ?? 'success';
    unset($_SESSION['ventas_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detalle_carrito'])) {
    $carrito = json_decode($_POST['detalle_carrito'], true);
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? '';
    $total = floatval($_POST['total_venta'] ?? 0);
    $monto_recibido = isset($_POST['monto_recibido']) ? floatval($_POST['monto_recibido']) : 0;
    $metodo_pago = $metodo_pago === 'Débito' ? 'Debito' : $metodo_pago;
    $metodos_validos = ['Efectivo', 'Debito', 'Fiado'];
    $carrito_valido = is_array($carrito) && !empty($carrito);
    $total_calculado = 0;

    if ($carrito_valido) {
        foreach ($carrito as $item) {
            $cantidad = intval($item['cantidad'] ?? 0);
            $precio = floatval($item['precio'] ?? 0);

            if (empty($item['id']) || $cantidad <= 0 || $precio <= 0) {
                $carrito_valido = false;
                break;
            }

            $total_calculado += $cantidad * $precio;
        }
    }

    if (!$carrito_valido) {
        $mensaje = ['success' => false, 'message' => 'El carrito esta vacio.'];
    } elseif (abs($total_calculado - $total) > 0.01) {
        $mensaje = ['success' => false, 'message' => 'El total de la venta no coincide con el carrito.'];
    } elseif ($total <= 0) {
        $mensaje = ['success' => false, 'message' => 'El total de la venta debe ser mayor que cero.'];
    } elseif (!in_array($metodo_pago, $metodos_validos, true)) {
        $mensaje = ['success' => false, 'message' => 'Selecciona un metodo de pago valido.'];
    } elseif ($monto_recibido < 0) {
        $mensaje = ['success' => false, 'message' => 'El monto recibido no puede ser negativo.'];
    } elseif ($metodo_pago === 'Efectivo' && $monto_recibido < $total) {
        $mensaje = ['success' => false, 'message' => 'El monto en efectivo no cubre el total de la venta.'];
    } elseif ($metodo_pago === 'Debito' && $monto_recibido !== $total) {
        $mensaje = ['success' => false, 'message' => 'El monto en debito debe ser igual al total de la venta.'];
    } elseif ($metodo_pago === 'Fiado' && empty($cliente_id)) {
        $mensaje = ['success' => false, 'message' => 'Selecciona un cliente para registrar la venta fiada.'];
    } elseif ($metodo_pago === 'Fiado' && $monto_recibido > $total) {
        $mensaje = ['success' => false, 'message' => 'El abono inicial no puede superar el total de la venta.'];
    } else {
        $ventaModel = new Venta();
        $resultado = $ventaModel->registrarVenta($cliente_id, $metodo_pago, $total, $carrito, $monto_recibido);
        $mensaje = $resultado
            ? ['success' => true, 'message' => 'Venta procesada exitosamente. Stock actualizado.']
            : ['success' => false, 'message' => 'Error al procesar la venta.'];
    }

    $_SESSION['ventas_flash'] = [
        'mensaje' => $mensaje['message'],
        'tipo' => $mensaje['success'] ? 'success' : 'error'
    ];

    header('Location: ventas.php');
    exit;
}

$clienteController = new ClienteController();
$clientes_bd = $clienteController->listarClientes();

$page_css = '/Software_Almacen/public/css/ventas/ventas.css';
require_once 'layouts/header.php';
?>

<div class="view-stack ventas-page">
    <div class="modulo-header">
        <div>
            <h2>Terminal de Ventas</h2>
        </div>
    </div>

    <div id="ventas_alert_container">
        <?php if ($mensaje): ?>
            <div class="ventas-alert ventas-alert-<?php echo htmlspecialchars($tipo_alerta, ENT_QUOTES, 'UTF-8'); ?>" data-temporary-alert>
                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="pos-container">
        <div class="pos-panel">
            <div class="search-wrapper">
                <input type="text" id="buscador_producto" class="search-input" placeholder="Escanea o escribe el nombre del producto..." autocomplete="off" autofocus>
                <div id="resultados_busqueda" class="resultados-busqueda"></div>
            </div>

            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Precio U.</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="contenido_carrito"></tbody>
                </table>
            </div>
        </div>

        <div class="pos-panel pos-payment-panel">
            <h3>Resumen de Pago</h3>
            <div class="total-display">Total: $<span id="total_display_text">0</span></div>

            <form action="ventas.php" method="POST" id="formVenta">
                <input type="hidden" name="detalle_carrito" id="detalle_carrito">
                <input type="hidden" name="total_venta" id="total_venta_input">

                <div class="form-group-pos">
                    <label class="form-label-pos" for="metodo_pago">M&eacute;todo de Pago *</label>
                    <select name="metodo_pago" id="metodo_pago" class="form-control-pos" required>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Debito">D&eacute;bito</option>
                        <option value="Fiado">Fiado</option>
                    </select>
                </div>

                <div class="cliente-fiado-div" id="cliente_fiado_div">
                    <label class="form-label-pos" for="cliente_id">Seleccionar Cliente *</label>
                    <select name="cliente_id" id="cliente_id" class="form-control-pos">
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach ($clientes_bd as $cli): ?>
                            <option value="<?php echo (int) $cli['id']; ?>"><?php echo htmlspecialchars($cli['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn_abrir_modal" class="btn-registrar-cliente">+ Nuevo Cliente</button>
                </div>

                <div class="form-group-pos" id="monto_pago_group">
                    <label class="form-label-pos" for="monto_recibido" id="monto_recibido_label">Monto Recibido ($)</label>
                    <input type="number" name="monto_recibido" id="monto_recibido" class="form-control-pos" placeholder="Ej: 5000" min="0" step="1">
                    <small id="monto_hint" class="monto-hint"></small>
                    <small id="vuelto_display" class="vuelto-display"></small>
                </div>

                <button type="submit" id="btn_confirmar" class="btn-pos-confirm">Confirmar Venta</button>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal_cliente">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Registrar Cliente</h3>
            <button type="button" class="btn-close-modal" id="btn_cerrar_modal">X</button>
        </div>
        <form id="form_nuevo_cliente">
            <div class="form-group-pos">
                <label class="form-label-pos" for="nuevo_nombre">Nombre Completo *</label>
                <input type="text" name="nombre" id="nuevo_nombre" class="form-control-pos" required>
            </div>
            <div class="form-group-pos">
                <label class="form-label-pos" for="nuevo_rut">RUT *</label>
                <input type="text" name="rut" id="nuevo_rut" class="form-control-pos" placeholder="Ej: 11.111.111-1" required>
            </div>
            <div class="form-group-pos">
                <label class="form-label-pos" for="nuevo_telefono">Telefono</label>
                <input type="text" name="telefono" id="nuevo_telefono" class="form-control-pos">
            </div>
            <button type="submit" class="btn-pos-confirm">Guardar Cliente</button>
        </form>
    </div>
</div>

<div class="ventas-confirm-overlay" id="ventas_confirm_overlay" aria-hidden="true">
    <div class="ventas-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="ventas_confirm_title">
        <h3 id="ventas_confirm_title">Confirmar accion</h3>
        <p id="ventas_confirm_message"></p>
        <div class="ventas-confirm-actions">
            <button type="button" class="btn-accion ventas-confirm-cancel" id="ventas_confirm_cancel">Cancelar</button>
            <button type="button" class="btn-accion ventas-confirm-accept" id="ventas_confirm_accept">Confirmar</button>
        </div>
    </div>
</div>

<script>
let carrito = [];
let ventaConfirmada = false;

const buscador = document.getElementById('buscador_producto');
const resultadosDiv = document.getElementById('resultados_busqueda');
const totalDisplay = document.getElementById('total_display_text');
const totalInput = document.getElementById('total_venta_input');
const detalleInput = document.getElementById('detalle_carrito');
const metodoPago = document.getElementById('metodo_pago');
const montoPagoGroup = document.getElementById('monto_pago_group');
const montoRecibido = document.getElementById('monto_recibido');
const montoLabel = document.getElementById('monto_recibido_label');
const montoHint = document.getElementById('monto_hint');
const vueltoDisplay = document.getElementById('vuelto_display');
const clienteFiadoDiv = document.getElementById('cliente_fiado_div');
const clienteSelect = document.getElementById('cliente_id');
const modalCliente = document.getElementById('modal_cliente');
const confirmOverlay = document.getElementById('ventas_confirm_overlay');
const confirmMessage = document.getElementById('ventas_confirm_message');
const confirmCancel = document.getElementById('ventas_confirm_cancel');
const confirmAccept = document.getElementById('ventas_confirm_accept');

function formatearMonto(valor) {
    return Number(valor || 0).toLocaleString('es-CL');
}

function crearAlerta(mensaje, tipo = 'error') {
    const contenedor = document.getElementById('ventas_alert_container');
    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = '';
    const alerta = document.createElement('div');
    alerta.className = 'ventas-alert ventas-alert-' + tipo;
    alerta.dataset.temporaryAlert = '';
    alerta.textContent = mensaje;
    contenedor.appendChild(alerta);
    ocultarAlerta(alerta);
}

function ocultarAlerta(alerta) {
    setTimeout(() => {
        alerta.classList.add('ventas-alert-hidden');
        setTimeout(() => alerta.remove(), 300);
    }, 3500);
}

document.querySelectorAll('[data-temporary-alert]').forEach(ocultarAlerta);

function obtenerTotal() {
    return parseInt(totalInput.value, 10) || 0;
}

function obtenerMonto() {
    return parseInt(montoRecibido.value, 10) || 0;
}

function limitarMontoAlTotal(mensaje) {
    const total = obtenerTotal();
    const monto = obtenerMonto();

    montoRecibido.max = total;

    if (total > 0 && monto > total) {
        montoRecibido.value = total;
        crearAlerta(mensaje);
    }
}

function actualizarMontoPago() {
    const total = obtenerTotal();
    const metodo = metodoPago.value;

    vueltoDisplay.textContent = '';
    montoHint.textContent = '';
    montoPagoGroup.classList.remove('is-debito');
    montoRecibido.readOnly = false;
    montoRecibido.removeAttribute('max');

    if (metodo === 'Fiado') {
        clienteFiadoDiv.classList.add('is-visible');
        clienteSelect.required = true;
        montoLabel.textContent = 'Abono inicial ($)';
        montoRecibido.placeholder = 'Opcional';
        montoRecibido.max = total;
        montoHint.textContent = total > 0 ? 'Maximo permitido: $' + formatearMonto(total) : '';
        limitarMontoAlTotal('El abono inicial no puede superar el total de la venta.');
    } else {
        clienteFiadoDiv.classList.remove('is-visible');
        clienteSelect.required = false;
        clienteSelect.value = '';
    }

    if (metodo === 'Debito') {
        montoLabel.textContent = 'Monto cobrado ($)';
        montoRecibido.placeholder = total > 0 ? String(total) : '0';
        montoRecibido.value = total > 0 ? total : '';
        montoRecibido.max = total;
        montoRecibido.readOnly = true;
        montoPagoGroup.classList.add('is-debito');
        montoHint.textContent = total > 0 ? 'El cobro con debito queda igual al total; no hay vuelto.' : '';
        return;
    }

    if (metodo === 'Efectivo') {
        montoLabel.textContent = 'Monto recibido ($)';
        montoRecibido.placeholder = 'Ej: 5000';

        if (monto > total && total > 0) {
            vueltoDisplay.textContent = 'Vuelto a entregar: $' + formatearMonto(monto - total);
        }
    }
}

buscador.addEventListener('input', function() {
    const termino = this.value.trim();

    if (termino.length < 2) {
        resultadosDiv.style.display = 'none';
        return;
    }

    fetch('buscar_producto.php?q=' + encodeURIComponent(termino))
        .then(response => response.json())
        .then(data => {
            resultadosDiv.innerHTML = '';

            if (data.length > 0) {
                resultadosDiv.style.display = 'block';
                data.forEach((prod) => {
                    const div = document.createElement('div');
                    div.className = 'resultado-item';
                    div.textContent = `${prod.codigo} - ${prod.nombre} ($${formatearMonto(prod.precio)}) - Stock: ${prod.stock}`;
                    div.addEventListener('click', () => agregarAlCarrito(prod));
                    resultadosDiv.appendChild(div);
                });
            } else {
                resultadosDiv.style.display = 'none';
            }
        })
        .catch(() => crearAlerta('No se pudo buscar productos. Revisa la conexion.'));
});

function agregarAlCarrito(prod) {
    const existe = carrito.find((item) => item.id === prod.id);

    if (existe) {
        if (existe.cantidad >= prod.stock) {
            crearAlerta('Stock maximo alcanzado para este producto.');
            return;
        }

        existe.cantidad++;
    } else {
        carrito.push({
            id: prod.id,
            nombre: prod.nombre,
            precio: parseInt(prod.precio, 10) || 0,
            cantidad: 1,
            stock: parseInt(prod.stock, 10) || 0
        });
    }

    buscador.value = '';
    resultadosDiv.style.display = 'none';
    renderCarrito();
}

function renderCarrito() {
    const tbody = document.getElementById('contenido_carrito');
    tbody.innerHTML = '';
    let total = 0;

    carrito.forEach((item, index) => {
        const subtotal = item.precio * item.cantidad;
        total += subtotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nombre}</td>
            <td><input type="number" value="${item.cantidad}" class="input-qty" min="1" max="${item.stock}" aria-label="Cantidad"></td>
            <td>$${formatearMonto(item.precio)}</td>
            <td>$${formatearMonto(subtotal)}</td>
            <td><button type="button" class="btn-delete" aria-label="Eliminar producto">X</button></td>
        `;

        tr.querySelector('.input-qty').addEventListener('change', (event) => cambiarCantidad(index, event.target.value));
        tr.querySelector('.btn-delete').addEventListener('click', () => eliminarDelCarrito(index));
        tbody.appendChild(tr);
    });

    totalDisplay.textContent = formatearMonto(total);
    totalInput.value = total;
    detalleInput.value = JSON.stringify(carrito);
    actualizarMontoPago();
}

function cambiarCantidad(index, cant) {
    let nuevaCant = parseInt(cant, 10) || 1;

    if (nuevaCant < 1) {
        nuevaCant = 1;
    }

    if (nuevaCant > carrito[index].stock) {
        nuevaCant = carrito[index].stock;
        crearAlerta('Sin stock suficiente para esa cantidad.');
    }

    carrito[index].cantidad = nuevaCant;
    renderCarrito();
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    renderCarrito();
}

montoRecibido.addEventListener('input', actualizarMontoPago);
metodoPago.addEventListener('change', actualizarMontoPago);

document.getElementById('btn_abrir_modal').addEventListener('click', () => {
    modalCliente.classList.add('is-visible');
});

document.getElementById('btn_cerrar_modal').addEventListener('click', () => {
    modalCliente.classList.remove('is-visible');
});

modalCliente.addEventListener('click', (event) => {
    if (event.target === modalCliente) {
        modalCliente.classList.remove('is-visible');
    }
});

document.getElementById('form_nuevo_cliente').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('guardar_cliente.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const option = document.createElement('option');
                option.value = data.id;
                option.text = data.nombre;
                option.selected = true;
                clienteSelect.add(option);

                modalCliente.classList.remove('is-visible');
                this.reset();
                crearAlerta('Cliente registrado correctamente.', 'success');
                return;
            }

            crearAlerta(data.message || 'No se pudo registrar el cliente.');
        })
        .catch(() => crearAlerta('Error de conexion al registrar el cliente.'));
});

function validarVenta() {
    const total = obtenerTotal();
    const recibidoStr = montoRecibido.value.trim();
    const recibido = obtenerMonto();
    const metodo = metodoPago.value;

    if (carrito.length === 0) {
        crearAlerta('El carrito esta vacio. Agrega productos primero.');
        return false;
    }

    if (total <= 0) {
        crearAlerta('El total de la venta debe ser mayor que cero.');
        return false;
    }

    if (metodo === 'Efectivo') {
        if (recibidoStr === '') {
            crearAlerta('Ingresa el monto en efectivo que entrego el cliente.');
            montoRecibido.focus();
            return false;
        }

        if (recibido < total) {
            crearAlerta('Dinero insuficiente. Faltan $' + formatearMonto(total - recibido) + ' para completar la venta.');
            montoRecibido.focus();
            return false;
        }
    }

    if (metodo === 'Debito') {
        montoRecibido.value = total;
    }

    if (metodo === 'Fiado') {
        if (!clienteSelect.value) {
            crearAlerta('Selecciona un cliente para registrar la venta fiada.');
            clienteSelect.focus();
            return false;
        }

        if (recibido > total) {
            crearAlerta('El abono inicial no puede superar el total de la venta.');
            montoRecibido.focus();
            return false;
        }
    }

    return true;
}

function abrirConfirmacionVenta() {
    const metodo = metodoPago.value;
    const total = obtenerTotal();
    confirmMessage.textContent = 'Se registrara una venta ' + metodo.toLowerCase() + ' por $' + formatearMonto(total) + '. \u00bfConfirmas la venta?';
    confirmOverlay.classList.add('is-visible');
    confirmOverlay.setAttribute('aria-hidden', 'false');
}

function cerrarConfirmacionVenta() {
    confirmOverlay.classList.remove('is-visible');
    confirmOverlay.setAttribute('aria-hidden', 'true');
}

document.getElementById('formVenta').addEventListener('submit', function(event) {
    if (ventaConfirmada) {
        return;
    }

    event.preventDefault();

    if (!validarVenta()) {
        return;
    }

    abrirConfirmacionVenta();
});

confirmCancel.addEventListener('click', cerrarConfirmacionVenta);

confirmAccept.addEventListener('click', () => {
    ventaConfirmada = true;
    cerrarConfirmacionVenta();
    document.getElementById('formVenta').submit();
});

confirmOverlay.addEventListener('click', (event) => {
    if (event.target === confirmOverlay) {
        cerrarConfirmacionVenta();
    }
});

actualizarMontoPago();
</script>

<?php require_once 'layouts/footer.php'; ?>
