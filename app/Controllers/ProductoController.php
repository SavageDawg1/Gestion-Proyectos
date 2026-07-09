<?php
require_once __DIR__ . '/../Models/Producto.php';
require_once __DIR__ . '/../Models/Configuracion.php';
require_once __DIR__ . '/../Models/Categoria.php';

class ProductoController {
    private $productoModel;
    private $configuracionModel;
    private $categoriaModel;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->configuracionModel = new Configuracion();
        $this->categoriaModel = new Categoria();
    }

    private function calcularPrecioVenta($costo, $porcentajeGanancia, $impuestoPorcentaje) {
        $costo = floatval($costo);
        $porcentajeGanancia = floatval($porcentajeGanancia);
        $impuestoPorcentaje = floatval($impuestoPorcentaje);

        if ($costo <= 0 || $porcentajeGanancia < 0 || $porcentajeGanancia >= 100 || $impuestoPorcentaje < 0) {
            return 0;
        }

        $gananciaDecimal = $porcentajeGanancia / 100;
        $impuestoDecimal = $impuestoPorcentaje / 100;
        $precioSinImpuesto = $costo / (1 - $gananciaDecimal);
        $precioConImpuesto = $precioSinImpuesto * (1 + $impuestoDecimal);

        return round($precioConImpuesto, 2);
    }

    public function obtenerImpuestoGlobal() {
        return $this->configuracionModel->obtenerImpuestoPorcentaje();
    }

    public function listarProductos() {
        return $this->productoModel->obtenerTodos();
    }

    public function obtenerProducto($id) {
        if (empty($id) || !is_numeric($id)) return null;
        return $this->productoModel->obtenerPorId($id);
    }

    public function guardarProducto($datos) {
        if (empty($datos['codigo']) || empty($datos['nombre']) || !isset($datos['costo']) || !isset($datos['porcentaje_ganancia'])) {
            return ["success" => false, "message" => "Código, Nombre, Costo y % de ganancia son obligatorios."];
        }

        $costo = floatval($datos['costo']);
        $porcentaje_ganancia = floatval($datos['porcentaje_ganancia']);

        if ($costo < 0 || ($datos['stock'] ?? 0) < 0 || ($datos['stock_minimo'] ?? 0) < 0) {
            return ["success" => false, "message" => "El costo, el stock y el stock minimo no pueden ser negativos."];
        }

        if ($porcentaje_ganancia < 0 || $porcentaje_ganancia >= 100) {
            return ["success" => false, "message" => "El porcentaje de ganancia debe ser entre 0 y 99.99."];
        }

        $stock = !empty($datos['stock']) ? intval($datos['stock']) : 0;
        $stock_minimo = isset($datos['stock_minimo']) && $datos['stock_minimo'] !== '' ? intval($datos['stock_minimo']) : 5;
        $categoria_id = $this->resolverCategoriaId($datos);
        $fecha_vencimiento = !empty($datos['fecha_vencimiento']) ? $datos['fecha_vencimiento'] : null;
        $tipo_venta = ($datos['tipo_venta'] ?? 'unidad') === 'granel' ? 'granel' : 'unidad';
        $unidad_granel = $datos['unidad_granel'] ?? '1000g';
        if (!in_array($unidad_granel, ['250g', '500g', '1000g'], true)) {
            $unidad_granel = '1000g';
        }

        $impuesto = $this->configuracionModel->obtenerImpuestoPorcentaje();
        $precioVenta = $this->calcularPrecioVenta($costo, $porcentaje_ganancia, $impuesto);
        if ($precioVenta <= 0) {
            return ["success" => false, "message" => "No se pudo calcular el precio de venta. Revisa costo y porcentaje de ganancia."];
        }

        $codigo = $datos['codigo'];
        if ($this->productoModel->obtenerPorNombre($datos['nombre'])) {
            return [
                "success" => false,
                "type" => "warning",
                "message" => "Ya existe un producto con este nombre. Usa un nombre diferente, aunque el codigo sea distinto."
            ];
        }

        $resultado = $this->productoModel->crear(
            $codigo,
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $costo,
            $porcentaje_ganancia,
            $precioVenta,
            $tipo_venta,
            $unidad_granel,
            $stock,
            $stock_minimo,
            $categoria_id,
            $fecha_vencimiento
        );

        if ($resultado) {
            return ["success" => true, "message" => "¡Producto guardado exitosamente!"];
        }

        $productoActivo = $this->productoModel->obtenerPorCodigo($codigo, 'activo');
        if ($productoActivo) {
            return ["success" => false, "message" => "Ya existe un producto activo con este código."];
        }

        if ($this->productoModel->obtenerPorNombre($datos['nombre'])) {
            return [
                "success" => false,
                "type" => "warning",
                "message" => "Ya existe un producto con este nombre. Usa un nombre diferente, aunque el codigo sea distinto."
            ];
        }

        return ["success" => false, "message" => "Error al guardar el producto. Intenta revisar el código o los datos ingresados."];
    }

    public function modificarProducto($id, $datos) {
        if (empty($id) || empty($datos['codigo']) || empty($datos['nombre']) || !isset($datos['costo']) || !isset($datos['porcentaje_ganancia'])) {
            return ["success" => false, "message" => "Código, Nombre, Costo y % de ganancia son obligatorios."];
        }

        $costo = floatval($datos['costo']);
        $porcentaje_ganancia = floatval($datos['porcentaje_ganancia']);

        if ($costo < 0 || ($datos['stock'] ?? 0) < 0 || ($datos['stock_minimo'] ?? 0) < 0) {
            return ["success" => false, "message" => "El costo, el stock y el stock minimo no pueden ser negativos."];
        }

        if ($porcentaje_ganancia < 0 || $porcentaje_ganancia >= 100) {
            return ["success" => false, "message" => "El porcentaje de ganancia debe ser entre 0 y 99.99."];
        }

        $stock = !empty($datos['stock']) ? intval($datos['stock']) : 0;
        $stock_minimo = isset($datos['stock_minimo']) && $datos['stock_minimo'] !== '' ? intval($datos['stock_minimo']) : 5;
        $categoria_id = $this->resolverCategoriaId($datos);
        $fecha_vencimiento = !empty($datos['fecha_vencimiento']) ? $datos['fecha_vencimiento'] : null;
        $tipo_venta = ($datos['tipo_venta'] ?? 'unidad') === 'granel' ? 'granel' : 'unidad';
        $unidad_granel = $datos['unidad_granel'] ?? '1000g';
        if (!in_array($unidad_granel, ['250g', '500g', '1000g'], true)) {
            $unidad_granel = '1000g';
        }

        $impuesto = $this->configuracionModel->obtenerImpuestoPorcentaje();
        $precioVenta = $this->calcularPrecioVenta($costo, $porcentaje_ganancia, $impuesto);
        if ($precioVenta <= 0) {
            return ["success" => false, "message" => "No se pudo calcular el precio de venta. Revisa costo y porcentaje de ganancia."];
        }

        if ($this->productoModel->obtenerPorNombre($datos['nombre'], null, $id)) {
            return [
                "success" => false,
                "type" => "warning",
                "message" => "Ya existe otro producto con este nombre. Usa un nombre diferente."
            ];
        }

        $resultado = $this->productoModel->actualizar(
            $id,
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $costo,
            $porcentaje_ganancia,
            $precioVenta,
            $tipo_venta,
            $unidad_granel,
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
        if (empty($id) || !is_numeric($id)) {
            return ["success" => false, "message" => "ID de producto inválido."];
        }

        $resultado = $this->productoModel->eliminar($id);
        if ($resultado === 'inactive') {
            return [
                "success" => true,
                "message" => "El producto tiene ventas asociadas y se ocultó como inactivo. Podrás reactivarlo registrándolo nuevamente con el mismo código."
            ];
        }

        if ($resultado === 'deleted') {
            return [
                "success" => true,
                "message" => "Producto eliminado correctamente.",
            ];
        }

        return ["success" => false, "message" => "No fue posible eliminar el producto."];
    }

    private function resolverCategoriaId($datos) {
        if (!empty($datos['categoria_id']) && is_numeric($datos['categoria_id'])) {
            return intval($datos['categoria_id']);
        }

        $nombreCategoria = trim($datos['categoria_nombre'] ?? '');
        if ($nombreCategoria !== '') {
            $categoria = $this->categoriaModel->obtenerOCrearPorNombre($nombreCategoria);
            return $categoria ? intval($categoria['id']) : null;
        }

        return null;
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
