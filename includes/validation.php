<?php
/**
 * Validaciones de Formularios
 */

// Validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validar contraseña (mínimo 6 caracteres)
function isValidPassword($password) {
    return strlen($password) >= 6;
}

// Validar nombre de usuario
function isValidUsername($username) {
    return strlen($username) >= 3 && preg_match('/^[a-zA-Z0-9_-]+$/', $username);
}

// Validar que la contraseña y confirmación coincidan
function passwordsMatch($password, $confirm_password) {
    return $password === $confirm_password;
}

// Validar que un campo no esté vacío
function isNotEmpty($field) {
    return !empty(trim($field));
}

// Limpiar RUT para dejar solo dígitos
function cleanRut($rut) {
    return preg_replace('/\D+/', '', $rut);
}

// Validar RUT válido: 8 u 9 dígitos totales (incluye verificador)
function isValidRut($rut) {
    $digits = cleanRut($rut);
    return preg_match('/^[0-9]{8,9}$/', $digits);
}
?>
