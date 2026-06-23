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
            $query = "SELECT * FROM clientes ORDER BY nombre ASC";
            $resultado = $this->db->query($query);
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } catch(Exception $e) {
            return [];
        }
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
}
?>