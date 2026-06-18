<?php
require_once __DIR__ . '/../../config/database.php';

if (!class_exists('Producto')) {
    class Producto {
        private $db;

        public function __construct() {
            $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->db->connect_error) {
                die("Error de conexión en Modelo Producto: " . $this->db->connect_error);
            }
            $this->db->set_charset("utf8mb4");
        }

        public function obtenerTodos() {
            try {
                $query = "SELECT p.*, c.nombre as categoria_nombre 
                          FROM productos p 
                          LEFT JOIN categorias c ON p.categoria_id = c.id 
                          ORDER BY p.id DESC";
                $resultado = $this->db->query($query);
                return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
            } catch(Exception $e) {
                error_log("Error en Producto::obtenerTodos: " . $e->getMessage());
                return [];
            }
        }

        public function obtenerPorId($id) {
            try {
                $query = "SELECT * FROM productos WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $id_int = intval($id);
                $stmt->bind_param("i", $id_int);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $producto = $resultado->fetch_assoc();
                $stmt->close();
                return $producto;
            } catch(Exception $e) {
                error_log("Error en Producto::obtenerPorId: " . $e->getMessage());
                return null;
            }
        }

        public function crear($codigo, $nombre, $descripcion, $precio, $stock, $categoria_id, $fecha_vencimiento = null) {
            try {
                $query = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, categoria_id, fecha_vencimiento) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $codigo = htmlspecialchars(strip_tags($codigo));
                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
                $precio = floatval($precio);
                $stock = intval($stock);
                $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
                $fecha_param = empty($fecha_vencimiento) ? null : $fecha_vencimiento;

                $stmt->bind_param("sssdiss", $codigo, $nombre, $descripcion, $precio, $stock, $categoria_param, $fecha_param);
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Producto::crear: " . $e->getMessage());
                return false;
            }
        }

        public function actualizar($id, $codigo, $nombre, $descripcion, $precio, $stock, $categoria_id, $fecha_vencimiento = null) {
            try {
                $query = "UPDATE productos 
                          SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ?, fecha_vencimiento = ? 
                          WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $codigo = htmlspecialchars(strip_tags($codigo));
                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
                $precio = floatval($precio);
                $stock = intval($stock);
                $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
                $fecha_param = empty($fecha_vencimiento) ? null : $fecha_vencimiento;
                $id_int = intval($id);

                $stmt->bind_param("sssdissi", $codigo, $nombre, $descripcion, $precio, $stock, $categoria_param, $fecha_param, $id_int);
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Producto::actualizar: " . $e->getMessage());
                return false;
            }
        }

        public function eliminar($id) {
            try {
                $query = "DELETE FROM productos WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $id_int = intval($id);
                $stmt->bind_param("i", $id_int);
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Producto::eliminar: " . $e->getMessage());
                return false;
            }
        }

        public function contarTodos() {
            try {
                $query = "SELECT COUNT(*) as total FROM productos";
                $resultado = $this->db->query($query);
                return $resultado ? $resultado->fetch_assoc()['total'] : 0;
            } catch(Exception $e) {
                return 0;
            }
        }

        public function sumarStockTotal() {
            try {
                $query = "SELECT SUM(stock) as total_stock FROM productos";
                $resultado = $this->db->query($query);
                if ($resultado) {
                    $fila = $resultado->fetch_assoc();
                    return $fila['total_stock'] ? $fila['total_stock'] : 0;
                }
                return 0;
            } catch(Exception $e) {
                return 0;
            }
        }

        public function obtenerStockCritico($limite = 5) {
            try {
                $query = "SELECT codigo, nombre, stock FROM productos WHERE stock <= ? ORDER BY stock ASC LIMIT 10";
                $stmt = $this->db->prepare($query);
                if (!$stmt) return [];
                $stmt->bind_param("i", $limite);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $datos = $resultado->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                return $datos;
            } catch(Exception $e) {
                return [];
            }
        }

        public function obtenerProximosVencimientos($dias = 30) {
            try {
                $query = "SELECT codigo, nombre, fecha_vencimiento 
                          FROM productos 
                          WHERE fecha_vencimiento IS NOT NULL 
                          AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                          ORDER BY fecha_vencimiento ASC LIMIT 10";
                $stmt = $this->db->prepare($query);
                if (!$stmt) return [];
                $stmt->bind_param("i", $dias);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $datos = $resultado->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                return $datos;
            } catch(Exception $e) {
                return [];
            }
        }
    }
}
?>