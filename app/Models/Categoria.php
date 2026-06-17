<?php
require_once __DIR__ . '/../../config/database.php';

if (!class_exists('Categoria')) {
    class Categoria {
        private $db;

        public function __construct() {
            // Conexión segura usando las constantes de database.php
            $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->db->connect_error) {
                die("Error de conexión en Modelo Categoria: " . $this->db->connect_error);
            }
            $this->db->set_charset("utf8mb4");
        }

        // Listar todas las categorías
        public function obtenerTodas() {
            try {
                $query = "SELECT * FROM categorias ORDER BY id DESC";
                $resultado = $this->db->query($query);
                
                if ($resultado) {
                    return $resultado->fetch_all(MYSQLI_ASSOC);
                }
                return [];
            } catch(Exception $e) {
                error_log("Error en Categoria::obtenerTodas - " . $e->getMessage());
                return [];
            }
        }

        // CORRECCIÓN: Buscar una sola categoría por ID (Necesario para rellenar el formulario de edición)
        public function obtenerPorId($id) {
            try {
                $query = "SELECT * FROM categorias WHERE id = ?";
                $stmt = $this->db->prepare($query);
                
                if (!$stmt) throw new Exception($this->db->error);

                $id_int = intval($id);
                $stmt->bind_param("i", $id_int);
                $stmt->execute();
                
                $resultado = $stmt->get_result();
                $categoria = $resultado->fetch_assoc();
                $stmt->close();
                
                return $categoria;
            } catch(Exception $e) {
                error_log("Error en Categoria::obtenerPorId - " . $e->getMessage());
                return null;
            }
        }

        // Crear nueva categoría
        public function crear($nombre, $descripcion) {
            try {
                $query = "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)";
                $stmt = $this->db->prepare($query);
                
                if (!$stmt) throw new Exception($this->db->error);

                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
                
                $stmt->bind_param("ss", $nombre, $descripcion);
                $ejecutado = $stmt->execute();
                $stmt->close();
                
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Categoria::crear - " . $e->getMessage());
                return false;
            }
        }

        // CORRECCIÓN: Actualizar una categoría existente (Necesario para guardar los cambios editados)
        public function actualizar($id, $nombre, $descripcion) {
            try {
                $query = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?";
                $stmt = $this->db->prepare($query);
                
                if (!$stmt) throw new Exception($this->db->error);

                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
                $id_int = intval($id);
                
                $stmt->bind_param("ssi", $nombre, $descripcion, $id_int);
                $ejecutado = $stmt->execute();
                $stmt->close();
                
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Categoria::actualizar - " . $e->getMessage());
                return false;
            }
        }

        // CORRECCIÓN: Bloque try-catch agregado para evitar caídas en el Dashboard
        public function contarTodas() {
            try {
                $query = "SELECT COUNT(*) as total FROM categorias";
                $resultado = $this->db->query($query);
                return $resultado ? $resultado->fetch_assoc()['total'] : 0;
            } catch(Exception $e) {
                error_log("Error en Categoria::contarTodas - " . $e->getMessage());
                return 0;
            }
        }

        // Eliminar una categoría
        public function eliminar($id) {
            try {
                $query = "DELETE FROM categorias WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $id_int = intval($id);
                $stmt->bind_param("i", $id_int);
                
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Categoria::eliminar - " . $e->getMessage());
                return false;
            }
        }
    }
}
?>