<?php
require_once __DIR__ . '/../Models/Categoria.php';

class CategoriaController {
    private $categoriaModel;

    public function __construct() {
        $this->categoriaModel = new Categoria();
    }

    public function listarCategorias() {
        return $this->categoriaModel->obtenerTodas();
    }

    public function guardarCategoria($datos) {
        if (empty($datos['nombre'])) {
            return ["success" => false, "message" => "El nombre de la categoría es obligatorio."];
        }

        $resultado = $this->categoriaModel->crear(
            $datos['nombre'],
            $datos['descripcion'] ?? ''
        );

        if ($resultado) {
            return ["success" => true, "message" => "Categoría creada exitosamente."];
        } else {
            return ["success" => false, "message" => "Error al guardar la categoría."];
        }
    }

    // ==========================================
    // NUEVAS FUNCIONES PARA EL MÓDULO DE EDICIÓN
    // ==========================================

    // Obtener una categoría específica para mostrarla en el formulario
    public function obtenerCategoria($id) {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }
        return $this->categoriaModel->obtenerPorId($id);
    }

    // Procesar los cambios y enviarlos al modelo
    public function modificarCategoria($id, $datos) {
        if (empty($id) || empty($datos['nombre'])) {
            return ["success" => false, "message" => "El nombre de la categoría es obligatorio."];
        }

        $resultado = $this->categoriaModel->actualizar(
            $id,
            $datos['nombre'],
            $datos['descripcion'] ?? ''
        );

        if ($resultado) {
            return ["success" => true, "message" => "Categoría actualizada exitosamente."];
        } else {
            return ["success" => false, "message" => "Error al actualizar la categoría."];
        }
    }

    // ==========================================

    public function contarCategorias() {
        return $this->categoriaModel->contarTodas();
    }

    // Procesar la eliminación de la categoría
    public function eliminarCategoria($id) {
        if (empty($id) || !is_numeric($id)) {
            return ["success" => false, "message" => "ID inválido."];
        }

        $resultado = $this->categoriaModel->eliminar($id);

        if ($resultado) {
            return ["success" => true, "message" => "Categoría eliminada exitosamente."];
        } else {
            return ["success" => false, "message" => "Error al eliminar. Asegúrate de que no haya productos usando esta categoría."];
        }
    }
}
?>