<?php
/**
 * API de Autenticación
 * Maneja login, logout y registro
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/session.php';
require_once 'response.php';

$action = '';
if (isset($_REQUEST['action'])) {
    $action = sanitizeInput($_REQUEST['action']);
}

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
        echo errorResponse("Acción no permitida");
}

function handleLogin() {
    global $conexion;
    
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $password = isset($_POST['password']) ? sanitizeInput($_POST['password']) : '';
    
    // Validaciones
    if (!isNotEmpty($email) || !isNotEmpty($password)) {
        echo errorResponse("Correo y contraseña son requeridos");
        return;
    }
    
    if (!isValidEmail($email)) {
        echo errorResponse("Correo inválido");
        return;
    }
    
    // Buscar registro
    $query = "SELECT rut, nombre_Apellidos, correo, contraseña FROM registro WHERE correo = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        echo errorResponse("Error en la base de datos");
        logError("Error prepare: " . $conexion->error);
        return;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar contraseña
        if (password_verify($password, $user['contraseña'])) {
            setUserSession($user['rut'], $user['nombre_Apellidos'], $user['correo']);
            echo successResponse(['redirect' => 'dashboard.php'], "Login exitoso");
        } else {
            echo errorResponse("Contraseña incorrecta");
        }
    } else {
        echo errorResponse("Correo no registrado");
    }
    
    $stmt->close();
}

function handleLogout() {
    logoutUser();
}

function handleRegister() {
    global $conexion;
    
    $nombre = isset($_POST['nombre']) ? sanitizeInput($_POST['nombre']) : '';
    $rut = isset($_POST['rut']) ? sanitizeInput($_POST['rut']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $password = isset($_POST['password']) ? sanitizeInput($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? sanitizeInput($_POST['confirm_password']) : '';
    
    // Validaciones
    if (!isNotEmpty($nombre) || !isNotEmpty($rut) || !isNotEmpty($email) || !isNotEmpty($password)) {
        echo errorResponse("Todos los campos son requeridos");
        return;
    }
    
    if (!isValidEmail($email)) {
        echo errorResponse("Correo inválido");
        return;
    }
    
    if (!isValidPassword($password)) {
        echo errorResponse("Contraseña debe tener al menos 6 caracteres");
        return;
    }
    
    if (!passwordsMatch($password, $confirm_password)) {
        echo errorResponse("Las contraseñas no coinciden");
        return;
    }
    
    $cleaned_rut = cleanRut($rut);
    if (empty($cleaned_rut) || !isValidRut($cleaned_rut)) {
        echo errorResponse("R.U.T. inválido");
        return;
    }
    
    // Verificar que el correo no exista
    $query = "SELECT correo FROM registro WHERE correo = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        echo errorResponse("Error en la base de datos");
        logError("Error prepare: " . $conexion->error);
        return;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo errorResponse("El correo ya está registrado");
        $stmt->close();
        return;
    }
    $stmt->close();

    // Verificar que el RUT no exista
    $query = "SELECT rut FROM registro WHERE rut = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        echo errorResponse("Error en la base de datos");
        logError("Error prepare: " . $conexion->error);
        return;
    }
    $stmt->bind_param("s", $cleaned_rut);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo errorResponse("El R.U.T. ya está registrado");
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Hash de contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar registro
    $query = "INSERT INTO registro (`nombre_Apellidos`, `rut`, `correo`, `contraseña`) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        echo errorResponse("Error en la base de datos");
        logError("Error prepare: " . $conexion->error);
        return;
    }
    
    $stmt->bind_param("ssss", $nombre, $cleaned_rut, $email, $hashed_password);
    
    if ($stmt->execute()) {
        echo successResponse(null, "Registro exitoso. Por favor inicia sesión");
    } else {
        echo errorResponse("Error al registrar usuario");
        logError("Error ejecutar: " . $stmt->error);
    }
    
    $stmt->close();
}
?>
