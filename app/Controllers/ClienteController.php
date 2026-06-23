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
        $nombre = htmlspecialchars(strip_tags($datos['nombre']));
        $rut = htmlspecialchars(strip_tags($datos['rut']));
        $telefono = htmlspecialchars(strip_tags($datos['telefono'] ?? ''));

        return $this->clienteModel->crear($nombre, $rut, $telefono);
    }
}
?>