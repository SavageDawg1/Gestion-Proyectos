<?php
require_once __DIR__ . '/../Models/Cliente.php';

class ClienteController {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new Cliente();
    }

    public function listarClientes() {
        return $this->clienteModel->obtenerTodos();
    }

    public function registrarClienteRapido($datos) {
        $nombre = htmlspecialchars(strip_tags(trim($datos['nombre'] ?? '')));
        $rut = strtoupper(htmlspecialchars(strip_tags(trim($datos['rut'] ?? ''))));
        $telefono = htmlspecialchars(strip_tags(trim($datos['telefono'] ?? '')));

        return $this->clienteModel->crear($nombre, $rut, $telefono);
    }

    public function actualizarCliente($datos) {
        $id = intval($datos['cliente_id'] ?? 0);
        $nombre = htmlspecialchars(strip_tags(trim($datos['nombre'] ?? '')));
        $rut = htmlspecialchars(strip_tags(trim($datos['rut'] ?? '')));
        $telefono = htmlspecialchars(strip_tags(trim($datos['telefono'] ?? '')));

        if ($id <= 0 || $nombre === '' || $rut === '') {
            return false;
        }

        return $this->clienteModel->actualizar($id, $nombre, $rut, $telefono);
    }

    public function eliminarCliente($id) {
        $id = intval($id);
        return $id > 0 ? $this->clienteModel->eliminar($id) : false;
    }

    public function saldarDeudaCompleta($id) {
        $id = intval($id);
        return $id > 0 ? $this->clienteModel->saldarDeudaCompleta($id) : false;
    }

    public function abonarDeuda($id, $monto) {
        $id = intval($id);
        $monto = floatval($monto);
        return ($id > 0 && $monto > 0) ? $this->clienteModel->abonarDeuda($id, $monto) : false;
    }
}
?>
