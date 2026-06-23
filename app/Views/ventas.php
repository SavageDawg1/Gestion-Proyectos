<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../Models/Venta.php';
require_once '../Controllers/ClienteController.php';

$page_title = "Punto de Venta - Almacén";
requireLogin();

$mensaje = null;

// Procesar Venta Real
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detalle_carrito'])) {
    $carrito = json_decode($_POST['detalle_carrito'], true);
    $metodo_pago = $_POST['metodo_pago'];
    $cliente_id = $_POST['cliente_id'];
    $total = floatval($_POST['total_venta']);

    // AQUÍ ESTÁ LA CORRECCIÓN: Capturamos el dinero entregado en el momento (si lo hay)
    $monto_recibido = isset($_POST['monto_recibido']) ? floatval($_POST['monto_recibido']) : 0;

    if (empty($carrito)) {
        $mensaje = ["success" => false, "message" => "El carrito está vacío."];
    } else {
        $ventaModel = new Venta();
        // AQUÍ ESTÁ LA CORRECCIÓN: Le pasamos el $monto_recibido a la función
        $resultado = $ventaModel->registrarVenta($cliente_id, $metodo_pago, $total, $carrito, $monto_recibido);
        
        if ($resultado) {
            $mensaje = ["success" => true, "message" => "¡Venta procesada exitosamente! Stock actualizado."];
        } else {
            $mensaje = ["success" => false, "message" => "Error al procesar la venta."];
        }
    }
}

// Cargar clientes reales desde la BD
$clienteController = new ClienteController();
$clientes_bd = $clienteController->listarClientes();

$page_css = '/Software_Almacen/public/css/ventas/ventas.css';
require_once 'layouts/header.php';
?>

<style>
    .search-wrapper { position: relative; width: 100%; margin-bottom: 2rem; }
    .resultados-busqueda { position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-height: 250px; overflow-y: auto; z-index: 1000; display: none; }
    .resultado-item { padding: 12px 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; color: #333; font-weight: bold; }
    .resultado-item:hover { background: #f5f1eb; color: #d55b22; }
</style>

<div class="main-content">
    <div class="welcome-header">
        <div class="welcome-text"><h2>Terminal de Ventas</h2></div>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; background: <?php echo $mensaje['success'] ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $mensaje['success'] ? '#155724' : '#721c24'; ?>;">
            <?php echo $mensaje['message']; ?>
        </div>
    <?php endif; ?>

    <div class="pos-container">
        <div class="pos-panel">
            <div class="search-wrapper">
                <input type="text" id="buscador_producto" class="search-input" style="width: 100%;" placeholder="🔍 Escanea o escribe el nombre del producto..." autocomplete="off" autofocus>
                <div id="resultados_busqueda" class="resultados-busqueda"></div>
            </div>

            <table class="cart-table">
                <thead><tr><th>Producto</th><th>Cant.</th><th>Precio U.</th><th>Subtotal</th><th>X</th></tr></thead>
                <tbody id="contenido_carrito"></tbody>
            </table>
        </div>

        <div class="pos-panel">
            <h3>Resumen de Pago</h3>
            <div class="total-display">Total: $<span id="total_display_text">0</span></div>

            <form action="ventas.php" method="POST" id="formVenta">
                <input type="hidden" name="detalle_carrito" id="detalle_carrito">
                <input type="hidden" name="total_venta" id="total_venta_input">

                <div class="form-group-pos">
                    <label class="form-label-pos">Método de Pago *</label>
                    <select name="metodo_pago" id="metodo_pago" class="form-control-pos" required>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Débito">Débito</option>
                        <option value="Fiado">Fiado</option>
                    </select>
                </div>

                <div class="cliente-fiado-div" id="cliente_fiado_div" style="display: none;">
                    <label class="form-label-pos">Seleccionar Cliente *</label>
                    <select name="cliente_id" id="cliente_id" class="form-control-pos">
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach($clientes_bd as $cli): ?>
                            <option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn_abrir_modal" class="btn-registrar-cliente">+ Nuevo Cliente</button>
                </div>

                <div class="form-group-pos">
                    <label class="form-label-pos">Monto Recibido ($)</label>
                    <input type="number" name="monto_recibido" id="monto_recibido" class="form-control-pos" placeholder="Ej: 5000">
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
            <button class="btn-close-modal" id="btn_cerrar_modal">X</button>
        </div>
        <form id="form_nuevo_cliente">
            <div class="form-group-pos">
                <label class="form-label-pos">Nombre Completo *</label>
                <input type="text" name="nombre" id="nuevo_nombre" class="form-control-pos" required>
            </div>
            <div class="form-group-pos">
                <label class="form-label-pos">RUT *</label>
                <input type="text" name="rut" id="nuevo_rut" class="form-control-pos" placeholder="Ej: 11.111.111-1" required>
            </div>
            <div class="form-group-pos">
                <label class="form-label-pos">Teléfono</label>
                <input type="text" name="telefono" id="nuevo_telefono" class="form-control-pos">
            </div>
            <button type="submit" class="btn-pos-confirm" style="margin-top: 10px;">Guardar Cliente</button>
        </form>
    </div>
</div>

<script>
let carrito = [];
const buscador = document.getElementById('buscador_producto');
const resultadosDiv = document.getElementById('resultados_busqueda');

// 1. Buscador
buscador.addEventListener('input', function() {
    let termino = this.value.trim();
    if (termino.length < 2) { resultadosDiv.style.display = 'none'; return; }

    fetch('buscar_producto.php?q=' + encodeURIComponent(termino))
    .then(response => response.json())
    .then(data => {
        resultadosDiv.innerHTML = '';
        if (data.length > 0) {
            resultadosDiv.style.display = 'block';
            data.forEach(prod => {
                let div = document.createElement('div');
                div.className = 'resultado-item';
                div.innerText = `${prod.codigo} - ${prod.nombre} ($${prod.precio}) - Stock: ${prod.stock}`;
                div.onclick = () => agregarAlCarrito(prod);
                resultadosDiv.appendChild(div);
            });
        } else { resultadosDiv.style.display = 'none'; }
    });
});

// 2. Carrito
function agregarAlCarrito(prod) {
    let existe = carrito.find(p => p.id === prod.id);
    if (existe) {
        if (existe.cantidad < prod.stock) { existe.cantidad++; } 
        else { alert("¡Stock máximo alcanzado!"); return; }
    } else {
        carrito.push({ id: prod.id, nombre: prod.nombre, precio: prod.precio, cantidad: 1, stock: prod.stock });
    }
    buscador.value = '';
    resultadosDiv.style.display = 'none';
    renderCarrito();
}

function renderCarrito() {
    let tbody = document.getElementById('contenido_carrito');
    tbody.innerHTML = '';
    let total = 0;

    carrito.forEach((item, index) => {
        let subtotal = item.precio * item.cantidad;
        total += subtotal;
        
        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nombre}</td>
            <td><input type="number" value="${item.cantidad}" class="input-qty" onchange="cambiarCantidad(${index}, this.value)" min="1" max="${item.stock}"></td>
            <td>$${item.precio}</td>
            <td>$${subtotal}</td>
            <td><button type="button" class="btn-delete" onclick="eliminarDelCarrito(${index})">X</button></td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('total_display_text').innerText = total;
    document.getElementById('total_venta_input').value = total;
    document.getElementById('detalle_carrito').value = JSON.stringify(carrito);
    calcularVuelto();
}

function cambiarCantidad(index, cant) {
    let nuevaCant = parseInt(cant);
    if (nuevaCant > carrito[index].stock) { alert("Sin stock suficiente"); nuevaCant = carrito[index].stock; }
    carrito[index].cantidad = nuevaCant;
    renderCarrito();
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    renderCarrito();
}

// 3. Fiados y Vuelto
document.getElementById('monto_recibido').addEventListener('input', calcularVuelto);

function calcularVuelto() {
    let total = parseInt(document.getElementById('total_venta_input').value) || 0;
    let recibido = parseInt(document.getElementById('monto_recibido').value) || 0;
    document.getElementById('vuelto_display').innerText = (recibido > total) ? 'Vuelto a entregar: $' + (recibido - total) : '';
}

document.getElementById('metodo_pago').addEventListener('change', function() {
    let clienteDiv = document.getElementById('cliente_fiado_div');
    let clienteSelect = document.getElementById('cliente_id');
    if (this.value === 'Fiado') {
        clienteDiv.style.display = 'block'; clienteSelect.required = true; 
    } else {
        clienteDiv.style.display = 'none'; clienteSelect.required = false; clienteSelect.value = ''; 
    }
});

// 4. Modal de Cliente
const modal = document.getElementById('modal_cliente');
document.getElementById('btn_abrir_modal').addEventListener('click', () => modal.style.display = 'flex');
document.getElementById('btn_cerrar_modal').addEventListener('click', (e) => { e.preventDefault(); modal.style.display = 'none'; });

// 5. Guardar Cliente Silenciosamente (AJAX)
document.getElementById('form_nuevo_cliente').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('guardar_cliente.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar al Select y seleccionarlo automáticamente
            let select = document.getElementById('cliente_id');
            let option = document.createElement('option');
            option.value = data.id;
            option.text = data.nombre;
            option.selected = true;
            select.add(option);
            
            // Cerrar modal y limpiar form
            modal.style.display = 'none';
            this.reset();
            alert("Cliente registrado correctamente");
        } else {
            alert(data.message);
        }
    })
    .catch(error => alert("Error de conexión."));
});


// 6. Ejecutar Venta (Blindado contra la tecla Enter)
document.getElementById('formVenta').addEventListener('submit', function(e) {
    // 1. Frenamos el envío automático del formulario
    e.preventDefault();

    // 2. Validamos el carrito
    if (carrito.length === 0) { 
        alert("El carrito está vacío. Agrega productos primero."); 
        return; 
    }

    // 3. Capturamos los valores
    let total = parseInt(document.getElementById('total_venta_input').value) || 0;
    let recibidoStr = document.getElementById('monto_recibido').value;
    let recibido = parseInt(recibidoStr) || 0;
    let metodo = document.getElementById('metodo_pago').value;

    // 4. Validación estricta de dinero
    if (metodo === 'Efectivo') {
        if (recibidoStr === '') {
            alert("Por favor, ingresa el monto en efectivo que entregó el cliente.");
            document.getElementById('monto_recibido').focus();
            return;
        }
        if (recibido < total) {
            let faltante = total - recibido;
            alert("Dinero insuficiente. El cliente entregó $" + recibido + " y la cuenta es $" + total + ".\nFaltan $" + faltante + " para completar la venta.");
            return; // Aquí cortamos la ejecución, la venta NO se procesa
        }
    } else if (metodo === 'Débito' && recibidoStr !== '') {
        if (recibido < total) {
            alert("Error: Las ventas con tarjeta deben cubrir el 100% del total.");
            return; // Tampoco se procesa
        }
    }
    
    // 5. Si todo está correcto y el dinero alcanza, enviamos la venta
    this.submit(); 
});
</script>

<?php require_once 'layouts/footer.php'; ?>