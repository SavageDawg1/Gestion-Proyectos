<?php
require_once __DIR__ . '/../Models/Producto.php';

class ProductoController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new Producto();
    }

    // Retorna el array de productos para la vista
    public function listarProductos() {
        return $this->productoModel->obtenerTodos();
    }

    // Valida y procesa el formulario de un nuevo producto
    public function guardarProducto($datos) {
        if (empty($datos['codigo']) || empty($datos['nombre']) || empty($datos['precio'])) {
            return ["success" => false, "message" => "Código, Nombre y Precio son campos obligatorios."];
        }

        if ($datos['precio'] < 0 || $datos['stock'] < 0) {
            return ["success" => false, "message" => "El precio y el stock no pueden ser negativos."];
        }

        $stock = !empty($datos['stock']) ? intval($datos['stock']) : 0;
        $categoria_id = !empty($datos['categoria_id']) ? intval($datos['categoria_id']) : null;

        $resultado = $this->productoModel->crear(
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $datos['precio'],
            $stock,
            $categoria_id
        );

        if ($resultado) {
            return ["success" => true, "message" => "¡Producto guardado exitosamente!"];
        } else {
            return ["success" => false, "message" => "Error al guardar. Asegúrate de que el código de barras no esté repetido."];
        }
    }
    // Método para procesar la eliminación
    public function eliminarProducto($id) {
        if (empty($id) || !is_numeric($id)) {
            return ["success" => false, "message" => "ID de producto inválido."];
        }

        $resultado = $this->productoModel->eliminar($id);

        if ($resultado) {
            return ["success" => true, "message" => "Producto eliminado correctamente."];
        } else {
            return ["success" => false, "message" => "Error al intentar eliminar el producto."];
        }
    }
    // Obtener un producto para la vista de edición
    public function obtenerProducto($id) {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }
        return $this->productoModel->obtenerPorId($id);
    }

    // Procesar la actualización de los datos
    public function modificarProducto($id, $datos) {
        if (empty($id) || empty($datos['codigo']) || empty($datos['nombre']) || empty($datos['precio'])) {
            return ["success" => false, "message" => "Código, Nombre y Precio son obligatorios."];
        }

        if ($datos['precio'] < 0 || $datos['stock'] < 0) {
            return ["success" => false, "message" => "El precio y el stock no pueden ser negativos."];
        }

        $stock = !empty($datos['stock']) ? intval($datos['stock']) : 0;
        $categoria_id = !empty($datos['categoria_id']) ? intval($datos['categoria_id']) : null;

        $resultado = $this->productoModel->actualizar(
            $id,
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $datos['precio'],
            $stock,
            $categoria_id
        );

        if ($resultado) {
            return ["success" => true, "message" => "¡Producto actualizado exitosamente!"];
        } else {
            return ["success" => false, "message" => "Error al actualizar. Verifica que el código no pertenezca a otro producto."];
        }
    }
    
    // Método para obtener la cantidad total de productos
    public function contarProductos() {
        return $this->productoModel->contarTodos();
    }
    
    // Método para obtener la suma total de artículos en stock
    public function obtenerStockTotal() {
        return $this->productoModel->sumarStockTotal();
    }
}
?>