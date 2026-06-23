<?php
require_once __DIR__ . '/../Models/Producto.php';

class ProductoController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new Producto();
    }

    public function listarProductos() {
        return $this->productoModel->obtenerTodos();
    }

    public function obtenerProducto($id) {
        if (empty($id) || !is_numeric($id)) return null;
        return $this->productoModel->obtenerPorId($id);
    }

    public function guardarProducto($datos) {
        if (empty($datos['codigo']) || empty($datos['nombre']) || empty($datos['precio'])) {
            return ["success" => false, "message" => "Código, Nombre y Precio son obligatorios."];
        }

        if ($datos['precio'] < 0 || ($datos['stock'] ?? 0) < 0 || ($datos['stock_minimo'] ?? 0) < 0) {
            return ["success" => false, "message" => "El precio, el stock y el stock minimo no pueden ser negativos."];
        }

        $stock = !empty($datos['stock']) ? intval($datos['stock']) : 0;
        $stock_minimo = isset($datos['stock_minimo']) && $datos['stock_minimo'] !== '' ? intval($datos['stock_minimo']) : 5;
        $categoria_id = !empty($datos['categoria_id']) ? intval($datos['categoria_id']) : null;
        $fecha_vencimiento = !empty($datos['fecha_vencimiento']) ? $datos['fecha_vencimiento'] : null;

        $resultado = $this->productoModel->crear(
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $datos['precio'],
            $stock,
            $stock_minimo,
            $categoria_id,
            $fecha_vencimiento
        );

        return $resultado 
            ? ["success" => true, "message" => "¡Producto guardado exitosamente!"]
            : ["success" => false, "message" => "Error al guardar el producto. El código podría estar duplicado."];
    }

    public function modificarProducto($id, $datos) {
        if (empty($id) || empty($datos['codigo']) || empty($datos['nombre']) || empty($datos['precio'])) {
            return ["success" => false, "message" => "Código, Nombre y Precio son obligatorios."];
        }

        if ($datos['precio'] < 0 || ($datos['stock'] ?? 0) < 0 || ($datos['stock_minimo'] ?? 0) < 0) {
            return ["success" => false, "message" => "El precio, el stock y el stock minimo no pueden ser negativos."];
        }

        $stock = !empty($datos['stock']) ? intval($datos['stock']) : 0;
        $stock_minimo = isset($datos['stock_minimo']) && $datos['stock_minimo'] !== '' ? intval($datos['stock_minimo']) : 5;
        $categoria_id = !empty($datos['categoria_id']) ? intval($datos['categoria_id']) : null;
        $fecha_vencimiento = !empty($datos['fecha_vencimiento']) ? $datos['fecha_vencimiento'] : null;

        $resultado = $this->productoModel->actualizar(
            $id,
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $datos['precio'],
            $stock,
            $stock_minimo,
            $categoria_id,
            $fecha_vencimiento
        );

        return $resultado 
            ? ["success" => true, "message" => "¡Producto actualizado exitosamente!"]
            : ["success" => false, "message" => "Error al actualizar el producto."];
    }

    public function eliminarProducto($id) {
        if (empty($id) || !is_numeric($id)) return false;
        return $this->productoModel->eliminar($id);
    }

    public function contarProductos() {
        return $this->productoModel->contarTodos();
    }

    public function obtenerStockTotal() {
        return $this->productoModel->sumarStockTotal();
    }

    public function listarStockCritico() {
        return $this->productoModel->obtenerStockCritico();
    }

    public function listarProximosVencimientos() {
        return $this->productoModel->obtenerProximosVencimientos(30);
    }
}
?>
