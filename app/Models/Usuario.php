<?php
/**
 * Modelo de Usuario
 * Se encarga exclusivamente de la comunicación con la tabla 'registro'
 */
class Usuario {
    private $conexion;

    // Cuando llamemos al modelo, le pasamos la conexión a la base de datos
    public function __construct($db_conexion) {
        $this->conexion = $db_conexion;
    }

    /**
     * Busca un usuario en la base de datos usando su correo
     */
    public function buscarPorCorreo($correo) {
        $query = "SELECT id, nombre_apellido, correo, contrasena, rol_id FROM registro WHERE correo = ? AND activo = 1 LIMIT 1";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Devuelve los datos del usuario
        }
        
        return false; // No encontró al usuario
    }

    /**
     * Inserta un nuevo usuario en la base de datos
     */
    public function registrarUsuario($nombre_apellido, $rut, $correo, $contrasena_hash, $rol_id) {
        $query = "INSERT INTO registro (nombre_apellido, rut, correo, contrasena, rol_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'error' => 'Error de preparación en la BD'];
        }

        $stmt->bind_param("ssssi", $nombre_apellido, $rut, $correo, $contrasena_hash, $rol_id);

        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errno' => $stmt->errno, 'error' => $stmt->error];
        }
    }
}