<?php
require_once __DIR__ . '/../../config/database.php';

class Venta {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->db->set_charset("utf8mb4");
    }

    public function registrarVenta($cliente_id, $metodo_pago, $total, $carrito, $monto_recibido = 0) {
        try {
            $this->db->begin_transaction(); 

            // 1. Insertar Venta
            $queryVenta = "INSERT INTO ventas (cliente_id, metodo_pago, total) VALUES (?, ?, ?)";
            $stmtVenta = $this->db->prepare($queryVenta);
            $c_id = empty($cliente_id) ? null : intval($cliente_id);
            $stmtVenta->bind_param("isd", $c_id, $metodo_pago, $total);
            $stmtVenta->execute();
            $venta_id = $stmtVenta->insert_id;
            $stmtVenta->close();

            // 2. Insertar Detalles y Descontar Stock
            $queryDetalle = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmtDetalle = $this->db->prepare($queryDetalle);
            $queryStock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
            $stmtStock = $this->db->prepare($queryStock);

            foreach ($carrito as $item) {
                $p_id = intval($item['id']);
                $cant = intval($item['cantidad']);
                $precio = floatval($item['precio']);
                $tipo_venta = ($item['tipo_venta'] ?? 'unidad') === 'granel' ? 'granel' : 'unidad';
                $gramos_base = intval($item['gramos_base'] ?? 1000);
                if (!in_array($gramos_base, [250, 500, 1000], true)) {
                    $gramos_base = 1000;
                }

                if ($tipo_venta === 'granel') {
                    $precioUnitarioReal = $precio / $gramos_base;
                    $sub = $cant * $precioUnitarioReal;
                } else {
                    $precioUnitarioReal = $precio;
                    $sub = $cant * $precio;
                }

                $stmtDetalle->bind_param("iiidd", $venta_id, $p_id, $cant, $precioUnitarioReal, $sub);
                $stmtDetalle->execute();
                $stmtStock->bind_param("ii", $cant, $p_id);
                $stmtStock->execute();
            }
            $stmtDetalle->close();
            $stmtStock->close();

            // 3. LÓGICA DE FIADO BLINDADA ANTI-NULL
            if ($metodo_pago === 'Fiado' && $c_id !== null) {
                // COALESCE convierte el NULL en 0 mágicamente antes de sumar
                $queryDeuda = "UPDATE clientes SET deuda = COALESCE(deuda, 0) + ? WHERE id = ?";
                $stmtDeuda = $this->db->prepare($queryDeuda);
                $stmtDeuda->bind_param("di", $total, $c_id);
                $stmtDeuda->execute();
                $stmtDeuda->close();

                // Si entregó dinero en el momento
                if ($monto_recibido > 0) {
                    $abono_real = min($monto_recibido, $total); 
                    
                    // COALESCE convierte el NULL en 0 mágicamente antes de restar
                    $queryAbono = "UPDATE clientes SET deuda = GREATEST(0, COALESCE(deuda, 0) - ?) WHERE id = ?";
                    $stmtAbono = $this->db->prepare($queryAbono);
                    $stmtAbono->bind_param("di", $abono_real, $c_id);
                    $stmtAbono->execute();
                    $stmtAbono->close();

                    $queryPago = "INSERT INTO pagos_fiados (cliente_id, monto) VALUES (?, ?)";
                    $stmtPago = $this->db->prepare($queryPago);
                    $stmtPago->bind_param("id", $c_id, $abono_real);
                    $stmtPago->execute();
                    $stmtPago->close();
                }
            }

            $this->db->commit(); 
            return true;
        } catch (Exception $e) {
            $this->db->rollback(); 
            return false;
        }
    }

    public function obtenerVentasUltimos7Dias() {
        try {
            $query = "
                SELECT 
                    dias.dia,
                    COALESCE(v.total_ingresos, 0) + COALESCE(pf.total_pagos, 0) as total_ingresos,
                    COALESCE(v.total_fiado, 0) as total_fiado
                FROM (
                    SELECT DATE(fecha) as dia FROM ventas WHERE fecha >= DATE(NOW()) - INTERVAL 6 DAY
                    UNION
                    SELECT DATE(fecha) as dia FROM pagos_fiados WHERE fecha >= DATE(NOW()) - INTERVAL 6 DAY
                ) as dias
                LEFT JOIN (
                    SELECT DATE(fecha) as dia, 
                           SUM(CASE WHEN metodo_pago IN ('Efectivo', 'Débito', 'Debito') THEN total ELSE 0 END) as total_ingresos,
                           SUM(CASE WHEN metodo_pago = 'Fiado' THEN total ELSE 0 END) as total_fiado
                    FROM ventas GROUP BY DATE(fecha)
                ) v ON dias.dia = v.dia
                LEFT JOIN (
                    SELECT DATE(fecha) as dia, SUM(monto) as total_pagos
                    FROM pagos_fiados GROUP BY DATE(fecha)
                ) pf ON dias.dia = pf.dia
                ORDER BY dias.dia ASC
            ";
            $resultado = $this->db->query($query);
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } catch(Exception $e) {
            return [];
        }
    }

    public function obtenerDetalleProductosVendidosUltimos7Dias() {
        try {
            $query = "
                SELECT
                    DATE(v.fecha) AS dia,
                    p.nombre AS producto,
                    p.tipo_venta,
                    p.unidad_granel,
                    SUM(dv.cantidad) AS cantidad_total,
                    SUM(dv.subtotal) AS total_vendido
                FROM detalle_ventas dv
                INNER JOIN ventas v ON v.id = dv.venta_id
                INNER JOIN productos p ON p.id = dv.producto_id
                WHERE v.fecha >= DATE(NOW()) - INTERVAL 6 DAY
                GROUP BY DATE(v.fecha), dv.producto_id, p.nombre, p.tipo_venta, p.unidad_granel
                ORDER BY DATE(v.fecha) ASC, p.nombre ASC
            ";

            $resultado = $this->db->query($query);
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
