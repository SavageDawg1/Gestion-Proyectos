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

    public function listarUsuarios() {
        $query = "SELECT r.id, r.nombre_apellido, r.rut, r.correo, r.rol_id, r.activo, r.creado_en,
                         CASE
                            WHEN r.rol_id = 1 THEN 'Administrador'
                            WHEN r.rol_id = 2 THEN 'Vendedor'
                            ELSE COALESCE(roles.nombre, 'Vendedor')
                         END AS rol_nombre
                  FROM registro r
                  LEFT JOIN roles ON roles.id = r.rol_id
                  ORDER BY r.nombre_apellido ASC";
        $result = $this->conexion->query($query);

        if (!$result) {
            return [];
        }

        $roles = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($roles as &$rol) {
            if ((int) $rol['id'] === 2) {
                $rol['nombre'] = 'Vendedor';
            }
        }
        unset($rol);

        return $roles;
    }

    public function listarRoles() {
        $query = "SELECT id, nombre FROM roles WHERE id IN (1, 2) ORDER BY id ASC";
        $result = $this->conexion->query($query);

        if (!$result) {
            return [
                ['id' => 1, 'nombre' => 'Administrador'],
                ['id' => 2, 'nombre' => 'Vendedor'],
            ];
        }

        $roles = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($roles as &$rol) {
            if ((int) $rol['id'] === 2) {
                $rol['nombre'] = 'Vendedor';
            }
        }
        unset($rol);

        return $roles;
    }

    public function existeRol($rol_id) {
        $query = "SELECT id FROM roles WHERE id = ? LIMIT 1";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $rol_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result && $result->num_rows > 0;
    }

    public function obtenerPorId($id) {
        $query = "SELECT id, nombre_apellido, rut, correo, rol_id, activo FROM registro WHERE id = ? LIMIT 1";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }

    public function contarAdministradoresActivos() {
        $query = "SELECT COUNT(*) AS total FROM registro WHERE rol_id = 1 AND activo = 1";
        $result = $this->conexion->query($query);

        if (!$result) {
            return 0;
        }

        $row = $result->fetch_assoc();
        return isset($row['total']) ? (int) $row['total'] : 0;
    }

    public function actualizarUsuario($id, $nombre_apellido, $rut, $correo, $rol_id, $activo, $contrasena_hash = null) {
        if ($contrasena_hash !== null && $contrasena_hash !== '') {
            $query = "UPDATE registro
                      SET nombre_apellido = ?, rut = ?, correo = ?, rol_id = ?, activo = ?, contrasena = ?
                      WHERE id = ?";
            $stmt = $this->conexion->prepare($query);

            if (!$stmt) {
                return ['success' => false, 'error' => 'Error de preparacion en la BD'];
            }

            $stmt->bind_param("sssiisi", $nombre_apellido, $rut, $correo, $rol_id, $activo, $contrasena_hash, $id);
        } else {
            $query = "UPDATE registro
                      SET nombre_apellido = ?, rut = ?, correo = ?, rol_id = ?, activo = ?
                      WHERE id = ?";
            $stmt = $this->conexion->prepare($query);

            if (!$stmt) {
                return ['success' => false, 'error' => 'Error de preparacion en la BD'];
            }

            $stmt->bind_param("sssiii", $nombre_apellido, $rut, $correo, $rol_id, $activo, $id);
        }

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'errno' => $stmt->errno, 'error' => $stmt->error];
    }

    public function eliminarUsuario($id) {
        $query = "DELETE FROM registro WHERE id = ?";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'error' => 'Error de preparacion en la BD'];
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'errno' => $stmt->errno, 'error' => $stmt->error];
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
