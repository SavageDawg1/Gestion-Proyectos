<?php
require_once __DIR__ . '/../../config/database.php';

if (!class_exists('Producto')) {
    class Producto {
        private $db;

        public function __construct() {
            // Usamos las constantes globales de database.php para crear una conexión dedicada
            // Esto evita cualquier problema de variables vacías o alcances (scope)
            $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->db->connect_error) {
                die("Error de conexión en Modelo Producto: " . $this->db->connect_error);
            }
            
            $this->db->set_charset("utf8mb4");
        }

        // Obtener todos los productos para listarlos en la tabla
        public function obtenerTodos() {
            try {
                $query = "SELECT p.*, c.nombre as categoria_nombre 
                          FROM productos p 
                          LEFT JOIN categorias c ON p.categoria_id = c.id 
                          ORDER BY p.id DESC";
                
                $resultado = $this->db->query($query);
                
                if ($resultado) {
                    return $resultado->fetch_all(MYSQLI_ASSOC);
                }
                return [];
            } catch(Exception $e) {
                error_log("Error en Producto::obtenerTodos - " . $e->getMessage());
                return [];
            }
        }
        
        // Función para contar el total de productos (Ideal para el Dashboard)
        public function contarTodos() {
            try {
                $query = "SELECT COUNT(*) as total FROM productos";
                $resultado = $this->db->query($query);
                
                if ($resultado) {
                    $fila = $resultado->fetch_assoc();
                    return $fila['total'];
                }
                return 0;
            } catch(Exception $e) {
                error_log("Error en Producto::contarTodos - " . $e->getMessage());
                return 0;
            }
        }

        // Sumar el stock total de todos los productos físicos (Para el Dashboard)
        public function sumarStockTotal() {
            try {
                $query = "SELECT SUM(stock) as total_stock FROM productos";
                $resultado = $this->db->query($query);
                
                if ($resultado) {
                    $fila = $resultado->fetch_assoc();
                    // Si no hay productos, SUM devuelve null, así que nos aseguramos de devolver 0
                    return $fila['total_stock'] ? $fila['total_stock'] : 0;
                }
                return 0;
            } catch(Exception $e) {
                error_log("Error en Producto::sumarStockTotal - " . $e->getMessage());
                return 0;
            }
        }

        // Insertar un producto nuevo de forma segura
        public function crear($codigo, $nombre, $descripcion, $precio, $stock, $categoria_id) {
            try {
                $query = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, categoria_id) 
                          VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($query);
                
                if (!$stmt) {
                    throw new Exception($this->db->error);
                }

                // Limpieza básica de etiquetas HTML
                $codigo = htmlspecialchars(strip_tags($codigo));
                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
                
                $precio = floatval($precio);
                $stock = intval($stock);
                $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
                
                // Vincular parámetros (s = string, d = double, i = integer)
                $stmt->bind_param("sssdii", $codigo, $nombre, $descripcion, $precio, $stock, $categoria_param);
                
                $ejecutado = $stmt->execute();
                $stmt->close();
                
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Producto::crear - " . $e->getMessage());
                return false;
            }
        }
        // Función para eliminar un producto por su ID
    public function eliminar($id) {
        try {
            $query = "DELETE FROM productos WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception($this->db->error);
            }

            $id_int = intval($id);
            $stmt->bind_param("i", $id_int);
            
            $ejecutado = $stmt->execute();
            $stmt->close();
            
            return $ejecutado;
        } catch(Exception $e) {
            error_log("Error en Producto::eliminar - " . $e->getMessage());
            return false;
        }
    }
    // Obtener un solo producto por su ID (para cargar el formulario de edición)
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM productos WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception($this->db->error);
            }

            $id_int = intval($id);
            $stmt->bind_param("i", $id_int);
            $stmt->execute();
            
            $resultado = $stmt->get_result();
            $producto = $resultado->fetch_assoc();
            
            $stmt->close();
            return $producto;
        } catch(Exception $e) {
            error_log("Error en Producto::obtenerPorId - " . $e->getMessage());
            return null;
        }
    }

    // Actualizar los datos de un producto existente
    public function actualizar($id, $codigo, $nombre, $descripcion, $precio, $stock, $categoria_id) {
        try {
            $query = "UPDATE productos 
                      SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ? 
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception($this->db->error);
            }

            // Limpieza básica
            $codigo = htmlspecialchars(strip_tags($codigo));
            $nombre = htmlspecialchars(strip_tags($nombre));
            $descripcion = htmlspecialchars(strip_tags($descripcion));
            
            $precio = floatval($precio);
            $stock = intval($stock);
            $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
            $id_int = intval($id);
            
            // Vincular (s = string, d = double, i = integer). Añadimos el id_int al final.
            $stmt->bind_param("sssdiii", $codigo, $nombre, $descripcion, $precio, $stock, $categoria_param, $id_int);
            
            $ejecutado = $stmt->execute();
            $stmt->close();
            
            return $ejecutado;
        } catch(Exception $e) {
            error_log("Error en Producto::actualizar - " . $e->getMessage());
            return false;
        }
    }
    }
    
}
?>