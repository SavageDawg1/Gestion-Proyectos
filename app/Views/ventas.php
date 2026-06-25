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
            $tipo_venta = ($item['tipo_venta'] ?? 'unidad') === 'granel' ? 'granel' : 'unidad';
            $gramos_base = intval($item['gramos_base'] ?? 1000);

            if (empty($item['id']) || $cantidad <= 0 || $precio <= 0) {
                $carrito_valido = false;
                break;
            }

            if ($tipo_venta === 'granel') {
                if (!in_array($gramos_base, [250, 500, 1000], true)) {
                    $carrito_valido = false;
                    break;
                }
                $total_calculado += round($cantidad * ($precio / $gramos_base));
            } else {
                $total_calculado += $cantidad * $precio;
            }
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
        // Si es pago en efectivo, no registrar un monto recibido mayor al total
        if ($metodo_pago === 'Efectivo' && $monto_recibido > $total) {
            $monto_recibido = $total;
        }

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
                <input type="text" name="rut" id="nuevo_rut" class="form-control-pos" placeholder="Ej: 11.111.111-1" pattern="^[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9kK]$" title="Formato válido: xx.xxx.xxx-x o x.xxx.xxx-k" required>
            </div>
            <div class="form-group-pos">
                <label class="form-label-pos" for="nuevo_telefono_local">Telefono</label>
                <div class="phone-input-group">
                    <span class="phone-prefix">+56</span>
                    <input type="text" id="nuevo_telefono_local" class="form-control-pos phone-input" placeholder="9 dígitos" pattern="^[0-9]{9}$" title="Ingrese los 9 dígitos del teléfono sin el código +56" inputmode="tel" maxlength="9">
                </div>
                <input type="hidden" name="telefono" id="nuevo_telefono" value="">
            </div>
            <button type="submit" class="btn-pos-confirm">Guardar Cliente</button>
        </form>
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

function formatearMonto(valor) {
    return Number(valor || 0).toLocaleString('es-CL');
}

function obtenerGramosBase(unidadGranel) {
    if (unidadGranel === '250g') return 250;
    if (unidadGranel === '500g') return 500;
    return 1000;
}

function obtenerEtiquetaBase(unidadGranel) {
    if (unidadGranel === '250g') return '1/4 kg';
    if (unidadGranel === '500g') return '1/2 kg';
    return '1 kg';
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
    return Math.round(parseFloat(totalInput.value) || 0);
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
    const monto = obtenerMonto();

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

        if (total > 0 && monto > total) {
            vueltoDisplay.textContent = 'Vuelto a entregar: $' + formatearMonto(monto - total);
        } else {
            vueltoDisplay.textContent = '';
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
                    const esGranel = prod.tipo_venta === 'granel';
                    const etiquetaTipo = esGranel ? `Granel (${obtenerEtiquetaBase(prod.unidad_granel)})` : 'Unidad';
                    const div = document.createElement('div');
                    div.className = 'resultado-item';
                    div.textContent = `${prod.codigo} - ${prod.nombre} ($${formatearMonto(prod.precio)}) - ${etiquetaTipo} - Stock: ${prod.stock}`;
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
    const esGranel = prod.tipo_venta === 'granel';
    const gramosBase = obtenerGramosBase(prod.unidad_granel);

    if (existe) {
        const incremento = existe.tipo_venta === 'granel' ? gramosBase : 1;
        if (existe.cantidad + incremento > prod.stock) {
            crearAlerta('Stock maximo alcanzado para este producto.');
            return;
        }

        existe.cantidad += incremento;
    } else {
        carrito.push({
            id: prod.id,
            nombre: prod.nombre,
            precio: parseFloat(prod.precio) || 0,
            cantidad: esGranel ? gramosBase : 1,
            stock: parseInt(prod.stock, 10) || 0,
            tipo_venta: esGranel ? 'granel' : 'unidad',
            unidad_granel: prod.unidad_granel || '1000g',
            gramos_base: gramosBase
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
        const esGranel = item.tipo_venta === 'granel';
        const subtotalRaw = esGranel
            ? (item.precio / item.gramos_base) * item.cantidad
            : item.precio * item.cantidad;
        const subtotal = Math.round(subtotalRaw);
        total += subtotal;

        const precioTexto = esGranel
            ? `$${formatearMonto(item.precio)} / ${obtenerEtiquetaBase(item.unidad_granel)}`
            : `$${formatearMonto(item.precio)}`;

        const cantidadLabel = esGranel ? 'Gramos' : 'Cant.';
        const cantidadValue = esGranel ? item.cantidad : item.cantidad;
        const cantidadMin = 1;
        const cantidadMax = item.stock;
        const inputStep = 1;
        const nombreMostrar = esGranel ? `${item.nombre} (granel)` : item.nombre;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td data-label="Producto">${nombreMostrar}</td>
            <td data-label="${cantidadLabel}"><input type="number" value="${cantidadValue}" class="input-qty" min="${cantidadMin}" max="${cantidadMax}" step="${inputStep}" aria-label="Cantidad"></td>
            <td data-label="Precio U.">${precioTexto}</td>
            <td data-label="Subtotal">$${formatearMonto(subtotal)}</td>
            <td data-label="Accion"><button type="button" class="btn-delete" aria-label="Eliminar producto">X</button></td>
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
    const esGranel = carrito[index].tipo_venta === 'granel';

    if (nuevaCant < 1) {
        nuevaCant = 1;
    }

    if (nuevaCant > carrito[index].stock) {
        nuevaCant = carrito[index].stock;
        crearAlerta(esGranel ? 'Sin stock suficiente para ese peso en gramos.' : 'Sin stock suficiente para esa cantidad.');
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

document.getElementById('nuevo_rut').addEventListener('input', function() {
    const valorFormateado = formatRutField(this.value);
    this.value = valorFormateado;
});

document.getElementById('nuevo_telefono_local').addEventListener('input', function() {
    this.value = this.value.replace(/\D+/g, '').slice(0, 9);
});

document.getElementById('form_nuevo_cliente').addEventListener('submit', function(event) {
    event.preventDefault();

    if (!validarNuevoCliente()) {
        return;
    }

    setTelefonoCompleto();
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

function cleanRutField(rut) {
    return rut.replace(/\D+/g, '').toUpperCase().slice(0, 9);
}

function formatRutField(rut) {
    const digits = cleanRutField(rut);
    if (digits.length <= 1) {
        return digits;
    }

    const body = digits.slice(0, -1);
    const dv = digits.slice(-1);
    const reversed = body.split('').reverse().join('');
    const chunks = reversed.match(/.{1,3}/g) || [];
    const formattedBody = chunks.join('.').split('').reverse().join('');
    return `${formattedBody}-${dv}`;
}

function validarRutFormato(rut) {
    return /^[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9kK]$/.test(rut.trim());
}

function validarTelefonoChile(telefono) {
    return telefono.trim() === '' || /^[0-9]{9}$/.test(telefono.trim());
}

function setTelefonoCompleto() {
    const telefonoLocal = document.getElementById('nuevo_telefono_local').value.replace(/\D+/g, '');
    const telefonoOculto = document.getElementById('nuevo_telefono');
    telefonoOculto.value = telefonoLocal ? `+56${telefonoLocal}` : '';
}

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

function validarNuevoCliente() {
    const rut = document.getElementById('nuevo_rut').value.trim();
    const telefono = document.getElementById('nuevo_telefono').value.trim();

    if (!validarRutFormato(rut)) {
        crearAlerta('RUT inválido. Use el formato xx.xxx.xxx-x o x.xxx.xxx-x.');
        return false;
    }

    if (!validarTelefonoChile(telefono)) {
        crearAlerta('Teléfono inválido. Debe comenzar con +56 y tener 9 dígitos.');
        return false;
    }

    return true;
}

document.getElementById('formVenta').addEventListener('submit', function(event) {
    if (ventaConfirmada) {
        return;
    }

    event.preventDefault();

    if (!validarVenta()) {
        return;
    }

    const formulario = this;
    const totalNum = obtenerTotal();
    const enteredMonto = obtenerMonto();
    const metodoRaw = metodoPago.value;
    const metodo = metodoRaw.toLowerCase();

    // Calcular monto que se registrará y el vuelto a entregar
    let recordedMonto = totalNum;
    let vuelto = 0;

    if (metodoRaw === 'Efectivo') {
        recordedMonto = totalNum;
        vuelto = Math.max(0, enteredMonto - totalNum);
    } else if (metodoRaw === 'Debito') {
        recordedMonto = totalNum;
        vuelto = 0;
    } else if (metodoRaw === 'Fiado') {
        recordedMonto = Math.min(enteredMonto, totalNum);
        vuelto = Math.max(0, enteredMonto - totalNum);
    }

    let mensaje = 'Se registrará una venta ' + metodo + ' por $' + formatearMonto(totalNum) + '\n' +
        'Monto recibido: $' + formatearMonto(enteredMonto) + '\n' +
        'Vuelto a entregar: $' + formatearMonto(vuelto) + '\n';

    if (metodoRaw === 'Fiado') {
        const deuda = Math.max(0, totalNum - enteredMonto);
        mensaje = 'Se registrará una venta fiado por $' + formatearMonto(totalNum) + '\n' +
            'Abono inicial: $' + formatearMonto(enteredMonto) + '\n' +
            'Deuda pendiente: $' + formatearMonto(deuda) + '\n';
    }

    mensaje += '\n¿Confirmas la venta?';

    const doSubmit = function() {
        // Antes de enviar, ajustar el campo monto_recibido según la regla (en efectivo se registra el total)
        if (metodoRaw === 'Efectivo') {
            montoRecibido.value = String(recordedMonto);
        }
        ventaConfirmada = true;
        formulario.submit();
    };

    if (window.appConfirm) {
        window.appConfirm(mensaje, doSubmit);
        return;
    }

    // Fallback al confirm nativo
    if (confirm(mensaje)) {
        doSubmit();
    }
});

actualizarMontoPago();
</script>

<?php require_once 'layouts/footer.php'; ?>
