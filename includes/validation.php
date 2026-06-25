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

// Limpiar RUT para dejar solo dígitos y el dígito verificador
function cleanRut($rut) {
    return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
}

// Validar RUT válido: 8 u 9 caracteres totales (incluye verificador)
function isValidRut($rut) {
    $digits = cleanRut($rut);
    return preg_match('/^[0-9]{7,8}[0-9K]$/', $digits);
}

// Validar formato de RUT chileno con puntos y guión
function isValidRutFormat($rut) {
    return preg_match('/^[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9kK]$/', $rut);
}

// Validar teléfono chileno con código +56 y 9 dígitos
function isValidChilePhone($telefono) {
    return preg_match('/^\+56[0-9]{9}$/', $telefono);
}
?>
