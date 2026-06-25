<?php
require_once __DIR__ . '/../../config/database.php';

class Configuracion {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->db->connect_error) {
            throw new RuntimeException('Error de conexion en Configuracion: ' . $this->db->connect_error);
        }
        $this->db->set_charset('utf8mb4');
        $this->asegurarEstructura();
    }

    private function asegurarEstructura() {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS configuracion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                clave VARCHAR(100) NOT NULL UNIQUE,
                valor VARCHAR(255) NOT NULL,
                actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->db->query("INSERT IGNORE INTO configuracion (clave, valor) VALUES ('impuesto_porcentaje', '19')");
    }

    public function obtenerImpuestoPorcentaje() {
        $query = "SELECT valor FROM configuracion WHERE clave = 'impuesto_porcentaje' LIMIT 1";
        $resultado = $this->db->query($query);
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            return floatval($fila['valor']);
        }

        return 19.0;
    }

    public function actualizarImpuestoPorcentaje($impuesto) {
        $valor = number_format(floatval($impuesto), 2, '.', '');
        $stmt = $this->db->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'impuesto_porcentaje'");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $valor);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
