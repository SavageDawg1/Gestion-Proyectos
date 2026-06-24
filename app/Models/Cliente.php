<?php
require_once __DIR__ . '/../../config/database.php';

class Cliente {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->db->set_charset("utf8mb4");
    }

    public function obtenerTodos() {
        try {
            $query = "
                SELECT
                    c.*,
                    GREATEST(0, COALESCE(v.total_fiado, 0) - COALESCE(p.pagos_manuales, 0)) AS deuda
                FROM clientes c
                LEFT JOIN (
                    SELECT cliente_id, SUM(total) AS total_fiado
                    FROM ventas
                    WHERE metodo_pago = 'Fiado'
                    GROUP BY cliente_id
                ) v ON v.cliente_id = c.id
                LEFT JOIN (
                    SELECT pf.cliente_id, SUM(pf.monto) AS pagos_manuales
                    FROM pagos_fiados pf
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM ventas vx
                        WHERE vx.cliente_id = pf.cliente_id
                          AND vx.metodo_pago = 'Fiado'
                          AND vx.total = pf.monto
                          AND vx.fecha = pf.fecha
                    )
                    GROUP BY pf.cliente_id
                ) p ON p.cliente_id = c.id
                ORDER BY c.fecha_registro DESC, c.id DESC
            ";
            $resultado = $this->db->query($query);
            $clientes = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
            return $this->adjuntarHistorial($clientes);
        } catch(Exception $e) {
            return [];
        }
    }

    private function adjuntarHistorial($clientes) {
        if (empty($clientes)) {
            return $clientes;
        }

        $ids = array_map('intval', array_column($clientes, 'id'));
        $ids = array_filter($ids, function($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            return $clientes;
        }

        $idsSql = implode(',', $ids);
        $ventasPorCliente = [];
        $abonosPorCliente = [];
        $ultimoMovimientoPorCliente = [];

        $queryMovimientos = "
            SELECT cliente_id, tipo, monto, fecha
            FROM (
                SELECT id, cliente_id, 'Venta fiada' AS tipo, total AS monto, fecha, 1 AS orden_tipo
                FROM ventas
                WHERE metodo_pago = 'Fiado' AND cliente_id IN ($idsSql)
                UNION ALL
                SELECT pf.id, pf.cliente_id, 'Abono' AS tipo, pf.monto, pf.fecha, 2 AS orden_tipo
                FROM pagos_fiados pf
                WHERE pf.cliente_id IN ($idsSql)
                  AND NOT EXISTS (
                      SELECT 1
                      FROM ventas vx
                      WHERE vx.cliente_id = pf.cliente_id
                        AND vx.metodo_pago = 'Fiado'
                        AND vx.total = pf.monto
                        AND vx.fecha = pf.fecha
                  )
            ) movimientos
            ORDER BY fecha DESC, orden_tipo ASC, id DESC
        ";

        $resultado = $this->db->query($queryMovimientos);
        if ($resultado) {
            while ($movimiento = $resultado->fetch_assoc()) {
                $clienteId = (int) $movimiento['cliente_id'];

                if (!isset($ultimoMovimientoPorCliente[$clienteId])) {
                    $ultimoMovimientoPorCliente[$clienteId] = $movimiento;
                }
            }
        }

        $queryVentas = "
            SELECT cliente_id, 'Venta fiada' AS tipo, total AS monto, fecha
            FROM ventas
            WHERE metodo_pago = 'Fiado' AND cliente_id IN ($idsSql)
            ORDER BY fecha DESC, id DESC
        ";
        $resultadoVentas = $this->db->query($queryVentas);
        if ($resultadoVentas) {
            while ($venta = $resultadoVentas->fetch_assoc()) {
                $clienteId = (int) $venta['cliente_id'];
                if (!isset($ventasPorCliente[$clienteId])) {
                    $ventasPorCliente[$clienteId] = [];
                }
                $ventasPorCliente[$clienteId][] = $venta;
            }
        }

        $queryAbonos = "
            SELECT pf.cliente_id, 'Abono' AS tipo, pf.monto, pf.fecha
            FROM pagos_fiados pf
            WHERE pf.cliente_id IN ($idsSql)
              AND NOT EXISTS (
                  SELECT 1
                  FROM ventas vx
                  WHERE vx.cliente_id = pf.cliente_id
                    AND vx.metodo_pago = 'Fiado'
                    AND vx.total = pf.monto
                    AND vx.fecha = pf.fecha
              )
            ORDER BY pf.fecha DESC, pf.id DESC
        ";
        $resultadoAbonos = $this->db->query($queryAbonos);
        if ($resultadoAbonos) {
            while ($abono = $resultadoAbonos->fetch_assoc()) {
                $clienteId = (int) $abono['cliente_id'];
                if (!isset($abonosPorCliente[$clienteId])) {
                    $abonosPorCliente[$clienteId] = [];
                }
                $abonosPorCliente[$clienteId][] = $abono;
            }
        }

        foreach ($clientes as &$cliente) {
            $clienteId = (int) $cliente['id'];
            $cliente['historial_ventas'] = $ventasPorCliente[$clienteId] ?? [];
            $cliente['historial_abonos'] = $abonosPorCliente[$clienteId] ?? [];
            $cliente['ultimo_movimiento'] = $ultimoMovimientoPorCliente[$clienteId] ?? null;
        }
        unset($cliente);

        return $clientes;
    }

    public function crear($nombre, $rut, $telefono) {
        try {
            $query = "INSERT INTO clientes (nombre, rut, telefono) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sss", $nombre, $rut, $telefono);
            $ejecutado = $stmt->execute();
            $id_insertado = $stmt->insert_id;
            $stmt->close();
            return $ejecutado ? $id_insertado : false;
        } catch(Exception $e) {
            return false;
        }
    }

    public function actualizar($id, $nombre, $rut, $telefono) {
        try {
            $query = "UPDATE clientes SET nombre = ?, rut = ?, telefono = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssi", $nombre, $rut, $telefono, $id);
            $ejecutado = $stmt->execute();
            $stmt->close();
            return $ejecutado;
        } catch(Exception $e) {
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $this->db->begin_transaction();

            $stmtPagos = $this->db->prepare("DELETE FROM pagos_fiados WHERE cliente_id = ?");
            $stmtPagos->bind_param("i", $id);
            $stmtPagos->execute();
            $stmtPagos->close();

            $stmtCliente = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
            $stmtCliente->bind_param("i", $id);
            $ejecutado = $stmtCliente->execute();
            $filas = $stmtCliente->affected_rows;
            $stmtCliente->close();

            if (!$ejecutado || $filas < 1) {
                throw new Exception('Cliente no eliminado');
            }

            $this->db->commit();
            return true;
        } catch(Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function saldarDeudaCompleta($id) {
        try {
            $this->db->begin_transaction();

            $deuda = $this->obtenerDeudaActual($id);
            if ($deuda <= 0) {
                $this->db->commit();
                return 0;
            }

            $stmtPago = $this->db->prepare("INSERT INTO pagos_fiados (cliente_id, monto) VALUES (?, ?)");
            $stmtPago->bind_param("id", $id, $deuda);
            $stmtPago->execute();
            $stmtPago->close();

            $stmtDeuda = $this->db->prepare("UPDATE clientes SET deuda = 0 WHERE id = ?");
            $stmtDeuda->bind_param("i", $id);
            $stmtDeuda->execute();
            $stmtDeuda->close();

            $this->db->commit();
            return $deuda;
        } catch(Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function abonarDeuda($id, $monto) {
        try {
            $this->db->begin_transaction();

            $deuda = $this->obtenerDeudaActual($id);
            if ($deuda <= 0 || $monto <= 0 || $monto > $deuda) {
                throw new Exception('Monto invalido');
            }

            $stmtPago = $this->db->prepare("INSERT INTO pagos_fiados (cliente_id, monto) VALUES (?, ?)");
            $stmtPago->bind_param("id", $id, $monto);
            $stmtPago->execute();
            $stmtPago->close();

            $restante = max(0, $deuda - $monto);
            $stmtDeuda = $this->db->prepare("UPDATE clientes SET deuda = ? WHERE id = ?");
            $stmtDeuda->bind_param("di", $restante, $id);
            $stmtDeuda->execute();
            $stmtDeuda->close();

            $this->db->commit();
            return $restante;
        } catch(Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    private function obtenerDeudaActual($id) {
        $query = "
            SELECT
                GREATEST(0, COALESCE(v.total_fiado, 0) - COALESCE(p.pagos_manuales, 0)) AS deuda
            FROM clientes c
            LEFT JOIN (
                SELECT cliente_id, SUM(total) AS total_fiado
                FROM ventas
                WHERE metodo_pago = 'Fiado'
                GROUP BY cliente_id
            ) v ON v.cliente_id = c.id
            LEFT JOIN (
                SELECT pf.cliente_id, SUM(pf.monto) AS pagos_manuales
                FROM pagos_fiados pf
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM ventas vx
                    WHERE vx.cliente_id = pf.cliente_id
                      AND vx.metodo_pago = 'Fiado'
                      AND vx.total = pf.monto
                      AND vx.fecha = pf.fecha
                )
                GROUP BY pf.cliente_id
            ) p ON p.cliente_id = c.id
            WHERE c.id = ?
            FOR UPDATE
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $cliente = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        if (!$cliente) {
            throw new Exception('Cliente no encontrado');
        }

        return (float) $cliente['deuda'];
    }
}
?>
