<?php
/**
 * API de autenticacion.
 * Maneja login, logout y registro de usuarios.
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/session.php';
require_once 'response.php';

$action = isset($_REQUEST['action']) ? sanitizeInput($_REQUEST['action']) : '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'register':
        handleRegister();
        break;
    default:
        echo errorResponse("Accion no permitida");
}

function handleLogin() {
    global $conexion;

    $correo = postValue(['correo', 'email']);
    $contrasena = postRawValue(['contrasena', 'password']);

    if (!isNotEmpty($correo) || !isNotEmpty($contrasena)) {
        echo errorResponse("Correo y contrasena son requeridos");
        return;
    }

    if (!isValidEmail($correo)) {
        echo errorResponse("Correo invalido");
        return;
    }

    $query = "SELECT id, nombre_apellido, correo, contrasena FROM registro WHERE correo = ? AND activo = 1 LIMIT 1";
    $stmt = $conexion->prepare($query);

    if (!$stmt) {
        echo errorResponse("Error en la base de datos");
        logError("Error prepare login: " . $conexion->error);
        return;
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($contrasena, $user['contrasena'])) {
            setUserSession($user['id'], $user['nombre_apellido'], $user['correo']);
            echo successResponse(['redirect' => 'dashboard.php'], "Login exitoso");
        } else {
            echo errorResponse("Contrasena incorrecta");
        }
    } else {
        echo errorResponse("Correo no registrado o usuario inactivo");
    }

    $stmt->close();
}

function handleLogout() {
    logoutUser();
}

function handleRegister() {
    global $conexion;

    $nombre_apellido = postValue(['nombre_apellido', 'nombre']);
    $rut = postValue(['rut']);
    $correo = postValue(['correo', 'email']);
    $contrasena = postRawValue(['contrasena', 'password']);
    $confirm_password = postRawValue(['confirm_password']);

    if ($confirm_password === '') {
        $confirm_password = $contrasena;
    }

    if (!isNotEmpty($nombre_apellido) || !isNotEmpty($rut) || !isNotEmpty($correo) || !isNotEmpty($contrasena)) {
        echo errorResponse("Todos los campos son requeridos");
        return;
    }

    if (!isValidEmail($correo)) {
        echo errorResponse("Correo invalido");
        return;
    }

    if (!isValidPassword($contrasena)) {
        echo errorResponse("Contrasena debe tener al menos 6 caracteres");
        return;
    }

    if (!passwordsMatch($contrasena, $confirm_password)) {
        echo errorResponse("Las contrasenas no coinciden");
        return;
    }

    $cleaned_rut = cleanRut($rut);
    if (empty($cleaned_rut) || !isValidRut($cleaned_rut)) {
        echo errorResponse("R.U.T. invalido");
        return;
    }

    // El rol se asigna siempre en backend. El formulario no puede escogerlo.
    $rol_id = 2;
    $hashed_password = password_hash($contrasena, PASSWORD_BCRYPT);

    $query = "INSERT INTO registro (nombre_apellido, rut, correo, contrasena, rol_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);

    if (!$stmt) {
        echo errorResponse("Error en la base de datos");
        logError("Error prepare register: " . $conexion->error);
        return;
    }

    $stmt->bind_param("ssssi", $nombre_apellido, $cleaned_rut, $correo, $hashed_password, $rol_id);

    if ($stmt->execute()) {
        echo successResponse(null, "Registro exitoso. Por favor inicia sesion");
    } else {
        if ($stmt->errno === 1062) {
            echo errorResponse(getDuplicateRegisterMessage($stmt->error));
        } else {
            echo errorResponse("Error al registrar usuario");
        }

        logError("Error ejecutar register: " . $stmt->error);
    }

    $stmt->close();
}

function postValue($keys) {
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            return sanitizeInput($_POST[$key]);
        }
    }

    return '';
}

function postRawValue($keys) {
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            return trim((string) $_POST[$key]);
        }
    }

    return '';
}

function getDuplicateRegisterMessage($mysql_error) {
    if (stripos($mysql_error, 'correo') !== false) {
        return "El correo ya esta registrado";
    }

    if (stripos($mysql_error, 'rut') !== false) {
        return "El R.U.T. ya esta registrado";
    }

    return "El correo o R.U.T. ya esta registrado";
}
?>
