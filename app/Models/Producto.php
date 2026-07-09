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
            $this->asegurarEstructura();
        }

        private function asegurarEstructura() {
            $this->db->query("ALTER TABLE productos ADD COLUMN IF NOT EXISTS costo DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER descripcion");
            $this->db->query("ALTER TABLE productos ADD COLUMN IF NOT EXISTS porcentaje_ganancia DECIMAL(5,2) NOT NULL DEFAULT 30.00 AFTER costo");
            $this->db->query("ALTER TABLE productos ADD COLUMN IF NOT EXISTS tipo_venta ENUM('unidad','granel') NOT NULL DEFAULT 'unidad' AFTER precio");
            $this->db->query("ALTER TABLE productos ADD COLUMN IF NOT EXISTS unidad_granel ENUM('250g','500g','1000g') NOT NULL DEFAULT '1000g' AFTER tipo_venta");
            $this->asegurarIndiceNombreUnico();
            $this->db->query("UPDATE productos SET costo = precio WHERE costo = 0");
        }

        private function asegurarIndiceNombreUnico() {
            try {
                $query = "SELECT 1
                          FROM information_schema.statistics
                          WHERE table_schema = DATABASE()
                            AND table_name = 'productos'
                            AND index_name = 'nombre_unico'
                          LIMIT 1";
                $resultado = $this->db->query($query);
                if ($resultado && $resultado->num_rows > 0) {
                    return;
                }

                $this->db->query("ALTER TABLE productos ADD UNIQUE KEY nombre_unico (nombre)");
            } catch(Exception $e) {
                error_log("Error en Producto::asegurarIndiceNombreUnico: " . $e->getMessage());
            }
        }

        public function obtenerTodos() {
            try {
                $query = "SELECT p.*, c.nombre as categoria_nombre,
                          EXISTS(SELECT 1 FROM detalle_ventas dv WHERE dv.producto_id = p.id) as tiene_ventas
                          FROM productos p 
                          LEFT JOIN categorias c ON p.categoria_id = c.id 
                          WHERE p.estado = 'activo'
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

        public function obtenerPorCodigo($codigo, $estado = null) {
            try {
                $query = "SELECT * FROM productos WHERE codigo = ?";
                if ($estado !== null) {
                    $query .= " AND estado = ?";
                }
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $codigo = htmlspecialchars(strip_tags($codigo));
                if ($estado !== null) {
                    $stmt->bind_param("ss", $codigo, $estado);
                } else {
                    $stmt->bind_param("s", $codigo);
                }
                $stmt->execute();
                $resultado = $stmt->get_result();
                $producto = $resultado->fetch_assoc();
                $stmt->close();
                return $producto;
            } catch(Exception $e) {
                error_log("Error en Producto::obtenerPorCodigo: " . $e->getMessage());
                return null;
            }
        }

        public function obtenerPorNombre($nombre, $estado = null, $excluirId = null) {
            try {
                $query = "SELECT * FROM productos WHERE TRIM(nombre) = TRIM(CONVERT(? USING utf8mb4)) COLLATE utf8mb4_general_ci";
                if ($estado !== null) {
                    $query .= " AND estado = ?";
                }
                if ($excluirId !== null) {
                    $query .= " AND id <> ?";
                }
                $query .= " LIMIT 1";

                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $nombre = htmlspecialchars(strip_tags($nombre));
                if ($estado !== null && $excluirId !== null) {
                    $excluirId = intval($excluirId);
                    $stmt->bind_param("ssi", $nombre, $estado, $excluirId);
                } elseif ($estado !== null) {
                    $stmt->bind_param("ss", $nombre, $estado);
                } elseif ($excluirId !== null) {
                    $excluirId = intval($excluirId);
                    $stmt->bind_param("si", $nombre, $excluirId);
                } else {
                    $stmt->bind_param("s", $nombre);
                }

                $stmt->execute();
                $resultado = $stmt->get_result();
                $producto = $resultado->fetch_assoc();
                $stmt->close();
                return $producto;
            } catch(Exception $e) {
                error_log("Error en Producto::obtenerPorNombre: " . $e->getMessage());
                return null;
            }
        }

        public function tieneVentasAsociadas($id) {
            try {
                $query = "SELECT 1 FROM detalle_ventas WHERE producto_id = ? LIMIT 1";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $id_int = intval($id);
                $stmt->bind_param("i", $id_int);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $tieneVentas = $resultado->num_rows > 0;
                $stmt->close();
                return $tieneVentas;
            } catch(Exception $e) {
                error_log("Error en Producto::tieneVentasAsociadas: " . $e->getMessage());
                return false;
            }
        }

        public function crear($codigo, $nombre, $descripcion, $costo, $porcentaje_ganancia, $precio, $tipo_venta, $unidad_granel, $stock, $stock_minimo, $categoria_id, $fecha_vencimiento = null) {
            try {
                $codigo = htmlspecialchars(strip_tags($codigo));
                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));

                $productoActivo = $this->obtenerPorCodigo($codigo, 'activo');
                if ($productoActivo) {
                    return false;
                }

                $productoInactivo = $this->obtenerPorCodigo($codigo, 'inactivo');
                $productoMismoNombre = $this->obtenerPorNombre($nombre);
                if ($productoMismoNombre && (!$productoInactivo || (int) $productoMismoNombre['id'] !== (int) $productoInactivo['id'])) {
                    return false;
                }

                if ($productoInactivo) {
                    $productoMismoNombre = $this->obtenerPorNombre($nombre, null, $productoInactivo['id']);
                    if ($productoMismoNombre) {
                        return false;
                    }

                    return $this->reactivarProducto(
                        $productoInactivo['id'],
                        $codigo,
                        $nombre,
                        $descripcion,
                        $costo,
                        $porcentaje_ganancia,
                        $precio,
                        $tipo_venta,
                        $unidad_granel,
                        $stock,
                        $stock_minimo,
                        $categoria_id,
                        $fecha_vencimiento
                    );
                }

                $query = "INSERT INTO productos (codigo, nombre, descripcion, costo, porcentaje_ganancia, precio, tipo_venta, unidad_granel, stock, stock_minimo, categoria_id, fecha_vencimiento, estado)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $costo = floatval($costo);
                $porcentaje_ganancia = floatval($porcentaje_ganancia);
                $precio = floatval($precio);
                $tipo_venta = $tipo_venta === 'granel' ? 'granel' : 'unidad';
                $unidad_granel = in_array($unidad_granel, ['250g', '500g', '1000g'], true) ? $unidad_granel : '1000g';
                $stock = intval($stock);
                $stock_minimo = intval($stock_minimo);
                $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
                $fecha_param = empty($fecha_vencimiento) ? null : $fecha_vencimiento;

                $stmt->bind_param("sssdddssiiss", $codigo, $nombre, $descripcion, $costo, $porcentaje_ganancia, $precio, $tipo_venta, $unidad_granel, $stock, $stock_minimo, $categoria_param, $fecha_param);
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Producto::crear: " . $e->getMessage());
                return false;
            }
            
        }

        public function reactivarProducto($id, $codigo, $nombre, $descripcion, $costo, $porcentaje_ganancia, $precio, $tipo_venta, $unidad_granel, $stock, $stock_minimo, $categoria_id, $fecha_vencimiento = null) {
            try {
            $query = "UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, costo = ?, porcentaje_ganancia = ?, precio = ?, tipo_venta = ?, unidad_granel = ?, stock = ?, stock_minimo = ?, categoria_id = ?, fecha_vencimiento = ?, estado = 'activo' WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $codigo = htmlspecialchars(strip_tags($codigo));
                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
            $costo = floatval($costo);
            $porcentaje_ganancia = floatval($porcentaje_ganancia);
                $precio = floatval($precio);
            $tipo_venta = $tipo_venta === 'granel' ? 'granel' : 'unidad';
            $unidad_granel = in_array($unidad_granel, ['250g', '500g', '1000g'], true) ? $unidad_granel : '1000g';
                $stock = intval($stock);
                $stock_minimo = intval($stock_minimo);
                $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
                $fecha_param = empty($fecha_vencimiento) ? null : $fecha_vencimiento;
                $id_int = intval($id);

            $stmt->bind_param("sssdddssiissi", $codigo, $nombre, $descripcion, $costo, $porcentaje_ganancia, $precio, $tipo_venta, $unidad_granel, $stock, $stock_minimo, $categoria_param, $fecha_param, $id_int);
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado;
            } catch(Exception $e) {
                error_log("Error en Producto::reactivarProducto: " . $e->getMessage());
                return false;
            }
        }

        public function actualizar($id, $codigo, $nombre, $descripcion, $costo, $porcentaje_ganancia, $precio, $tipo_venta, $unidad_granel, $stock, $stock_minimo, $categoria_id, $fecha_vencimiento = null) {
            try {
                $query = "UPDATE productos 
                          SET codigo = ?, nombre = ?, descripcion = ?, costo = ?, porcentaje_ganancia = ?, precio = ?, tipo_venta = ?, unidad_granel = ?, stock = ?, stock_minimo = ?, categoria_id = ?, fecha_vencimiento = ?
                          WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $codigo = htmlspecialchars(strip_tags($codigo));
                $nombre = htmlspecialchars(strip_tags($nombre));
                $descripcion = htmlspecialchars(strip_tags($descripcion));
                $costo = floatval($costo);
                $porcentaje_ganancia = floatval($porcentaje_ganancia);
                $precio = floatval($precio);
                $tipo_venta = $tipo_venta === 'granel' ? 'granel' : 'unidad';
                $unidad_granel = in_array($unidad_granel, ['250g', '500g', '1000g'], true) ? $unidad_granel : '1000g';
                $stock = intval($stock);
                $stock_minimo = intval($stock_minimo);
                $categoria_param = empty($categoria_id) ? null : intval($categoria_id);
                $fecha_param = empty($fecha_vencimiento) ? null : $fecha_vencimiento;
                $id_int = intval($id);

                if ($this->obtenerPorNombre($nombre, null, $id_int)) {
                    return false;
                }

                $stmt->bind_param("sssdddssiissi", $codigo, $nombre, $descripcion, $costo, $porcentaje_ganancia, $precio, $tipo_venta, $unidad_granel, $stock, $stock_minimo, $categoria_param, $fecha_param, $id_int);
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
                if ($this->tieneVentasAsociadas($id)) {
                    $query = "UPDATE productos SET estado = 'inactivo' WHERE id = ?";
                    $stmt = $this->db->prepare($query);
                    if (!$stmt) throw new Exception($this->db->error);

                    $id_int = intval($id);
                    $stmt->bind_param("i", $id_int);
                    $ejecutado = $stmt->execute();
                    $stmt->close();
                    return $ejecutado ? 'inactive' : false;
                }

                $query = "DELETE FROM productos WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) throw new Exception($this->db->error);

                $id_int = intval($id);
                $stmt->bind_param("i", $id_int);
                $ejecutado = $stmt->execute();
                $stmt->close();
                return $ejecutado ? 'deleted' : false;
            } catch(Exception $e) {
                error_log("Error en Producto::eliminar: " . $e->getMessage());
                return false;
            }
        }

        public function contarTodos() {
            try {
                $query = "SELECT COUNT(*) as total FROM productos WHERE estado = 'activo'";
                $resultado = $this->db->query($query);
                return $resultado ? $resultado->fetch_assoc()['total'] : 0;
            } catch(Exception $e) {
                return 0;
            }
        }

        public function sumarStockTotal() {
            try {
                $query = "SELECT SUM(stock) as total_stock FROM productos WHERE estado = 'activo'";
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

        public function obtenerStockCritico() {
            try {
                $query = "SELECT codigo, nombre, stock, stock_minimo FROM productos WHERE estado = 'activo' AND stock <= stock_minimo ORDER BY stock ASC LIMIT 10";
                $stmt = $this->db->prepare($query);
                if (!$stmt) return [];
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
                          WHERE estado = 'activo' 
                          AND fecha_vencimiento IS NOT NULL 
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

        public function buscarPorTermino($termino) {
            try {
                $termino_like = "%" . $termino . "%";
                $query = "SELECT id, codigo, nombre, precio, stock, tipo_venta, unidad_granel FROM productos WHERE estado = 'activo' AND (codigo LIKE ? OR nombre LIKE ?) AND stock > 0 LIMIT 10";
                $stmt = $this->db->prepare($query);
                if (!$stmt) return [];
                
                $stmt->bind_param("ss", $termino_like, $termino_like);
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
