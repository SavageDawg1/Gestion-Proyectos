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

    /**
     * Guarda el token de recuperación en la base de datos
     */
    public function guardarTokenRecuperacion($id, $token, $expiracion) {
        $query = "UPDATE registro SET reset_token = ?, token_expiracion = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ssi", $token, $expiracion, $id);
            return $stmt->execute();
        }
        return false;
    }

    /**
     * Busca un usuario por su token válido
     */
    public function buscarPorToken($token) {
        $query = "SELECT id FROM registro WHERE reset_token = ? AND token_expiracion > NOW() LIMIT 1";
        $stmt = $this->conexion->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        return false;
    }

    /**
     * Actualiza la contraseña y borra el token
     */
    public function actualizarContrasenaYLimpiarToken($id, $password_hash) {
        $query = "UPDATE registro SET contrasena = ?, reset_token = NULL, token_expiracion = NULL WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        if ($stmt) {
            $stmt->bind_param("si", $password_hash, $id);
            return $stmt->execute();
        }
        return false;
    }
}