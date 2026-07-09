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

            $queryVenta = "INSERT INTO ventas (cliente_id, metodo_pago, total) VALUES (?, ?, ?)";
            $stmtVenta = $this->db->prepare($queryVenta);
            $c_id = empty($cliente_id) ? null : intval($cliente_id);
            $stmtVenta->bind_param("isd", $c_id, $metodo_pago, $total);
            $stmtVenta->execute();
            $venta_id = $stmtVenta->insert_id;
            $stmtVenta->close();

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

            if ($metodo_pago === 'Fiado' && $c_id !== null) {
                $queryDeuda = "UPDATE clientes SET deuda = COALESCE(deuda, 0) + ? WHERE id = ?";
                $stmtDeuda = $this->db->prepare($queryDeuda);
                $stmtDeuda->bind_param("di", $total, $c_id);
                $stmtDeuda->execute();
                $stmtDeuda->close();

                if ($monto_recibido > 0) {
                    $abono_real = min($monto_recibido, $total);

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

    private function construirFiltroFechas($campoFecha, $fechaInicio = null, $fechaFin = null, &$types = '', &$params = []) {
        $condiciones = [];

        if (!empty($fechaInicio)) {
            $condiciones[] = "DATE($campoFecha) >= ?";
            $types .= 's';
            $params[] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $condiciones[] = "DATE($campoFecha) <= ?";
            $types .= 's';
            $params[] = $fechaFin;
        }

        return empty($condiciones) ? '' : ' WHERE ' . implode(' AND ', $condiciones);
    }

    public function obtenerVentasPorPeriodo($fechaInicio = null, $fechaFin = null) {
        try {
            $types = '';
            $params = [];
            $filtroVentasDias = $this->construirFiltroFechas('fecha', $fechaInicio, $fechaFin, $types, $params);
            $filtroPagosDias = $this->construirFiltroFechas('fecha', $fechaInicio, $fechaFin, $types, $params);
            $filtroVentas = $this->construirFiltroFechas('fecha', $fechaInicio, $fechaFin, $types, $params);
            $filtroPagos = $this->construirFiltroFechas('fecha', $fechaInicio, $fechaFin, $types, $params);

            $query = "
                SELECT
                    dias.dia,
                    COALESCE(v.total_ingresos, 0) + COALESCE(pf.total_pagos, 0) as total_ingresos,
                    COALESCE(v.total_fiado, 0) as total_fiado
                FROM (
                    SELECT DATE(fecha) as dia FROM ventas" . $filtroVentasDias . "
                    UNION
                    SELECT DATE(fecha) as dia FROM pagos_fiados" . $filtroPagosDias . "
                ) as dias
                LEFT JOIN (
                    SELECT DATE(fecha) as dia,
                           SUM(CASE WHEN metodo_pago IN ('Efectivo', 'Debito', 'Débito') THEN total ELSE 0 END) as total_ingresos,
                           SUM(CASE WHEN metodo_pago = 'Fiado' THEN total ELSE 0 END) as total_fiado
                    FROM ventas" . $filtroVentas . " GROUP BY DATE(fecha)
                ) v ON dias.dia = v.dia
                LEFT JOIN (
                    SELECT DATE(fecha) as dia, SUM(monto) as total_pagos
                    FROM pagos_fiados" . $filtroPagos . " GROUP BY DATE(fecha)
                ) pf ON dias.dia = pf.dia
                ORDER BY dias.dia ASC
            ";

            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return [];
            }

            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } catch(Exception $e) {
            return [];
        }
    }

    public function obtenerVentasUltimos7Dias() {
        return $this->obtenerVentasPorPeriodo(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'));
    }

    public function obtenerDetalleProductosVendidosPorPeriodo($fechaInicio = null, $fechaFin = null) {
        try {
            $types = '';
            $params = [];
            $filtroFechas = $this->construirFiltroFechas('v.fecha', $fechaInicio, $fechaFin, $types, $params);

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
                " . $filtroFechas . "
                GROUP BY DATE(v.fecha), dv.producto_id, p.nombre, p.tipo_venta, p.unidad_granel
                ORDER BY DATE(v.fecha) ASC, p.nombre ASC
            ";

            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return [];
            }

            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerDetalleProductosVendidosUltimos7Dias() {
        return $this->obtenerDetalleProductosVendidosPorPeriodo(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'));
    }
}
?>
