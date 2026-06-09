<?php

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/session.php';
require_once 'response.php';
require_once '../vendor/autoload.php';

//Para enviar el correo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    case 'recover':
        handleRecover();
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

function handleRecover() {
    global $conexion;

    $correo = postValue(['correo', 'email']);

    if (!isNotEmpty($correo)) {
        echo errorResponse("El correo electrónico es requerido");
        return;
    }

    if (!isValidEmail($correo)) {
        echo errorResponse("Correo electrónico inválido");
        return;
    }

    $query = "SELECT id, nombre_apellido FROM registro WHERE correo = ? AND activo = 1 LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $token = bin2hex(random_bytes(32)); 
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $updateQuery = "UPDATE registro SET reset_token = ?, token_expiracion = ? WHERE id = ?";
        $updateStmt = $conexion->prepare($updateQuery);
        $updateStmt->bind_param("ssi", $token, $expiracion, $user['id']);

        if ($updateStmt->execute()) {
            
            // Ruta actualizada con tu nueva carpeta Software_Almacen
            $link = "http://localhost/Software_Almacen/pages/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);

            try {
                // Configuración de Gmail
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                
                // --- PON TUS DATOS AQUÍ ---
                $mail->Username   = 'gaspar.ar.03@gmail.com'; 
                $mail->Password   = 'vtwqwnrpxijxekjp'; 
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('gaspar.ar.03@gmail.com', 'Sistema de Bodega');
                $mail->addAddress($correo, $user['nombre_apellido']);

                $mail->isHTML(true);
                $mail->Subject = 'Recuperacion de Contrasena';
                $mail->Body    = "
                    <h2>Hola, {$user['nombre_apellido']}</h2>
                    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva. Este enlace es válido por 1 hora.</p>
                    <br>
                    <a href='{$link}' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Restablecer mi contraseña</a>
                    <br><br>
                    <p>Si no solicitaste esto, puedes ignorar este correo.</p>
                ";

                $mail->send();
                echo successResponse(null, "Se han enviado las instrucciones a tu correo electrónico.");
                
            } catch (Exception $e) {
                logError("Error al enviar correo: {$mail->ErrorInfo}");
                echo errorResponse("No se pudo enviar el correo. Revisa tus credenciales de Google.");
            }

        } else {
            echo errorResponse("Error al procesar la solicitud.");
        }
        $updateStmt->close();
    } else {
        echo errorResponse("El correo electrónico no se encuentra registrado.");
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
