/**
 * Script para la página de Login
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const recoverForm = document.getElementById('recover-form');
    const rutDisplayInput = document.getElementById('rut_display');
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    if (recoverForm) {
        recoverForm.addEventListener('submit', handleRecover);
    }
    
    if (rutDisplayInput) {
        const formatRutField = function() {
            const currentPosition = rutDisplayInput.selectionStart;
            rutDisplayInput.value = formatRut(rutDisplayInput.value);
            if (currentPosition !== null) {
                rutDisplayInput.setSelectionRange(rutDisplayInput.value.length, rutDisplayInput.value.length);
            }
        };

        rutDisplayInput.addEventListener('input', formatRutField);
        rutDisplayInput.addEventListener('blur', formatRutField);
        rutDisplayInput.addEventListener('paste', function() {
            setTimeout(formatRutField, 0);
        });
    }
});

function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const messagesDiv = document.getElementById('login-messages');
    
    // Validaciones básicas
    if (!email || !password) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Por favor completa todos los campos</div>';
        return;
    }
    
    if (!isValidEmail(email)) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Email inválido</div>';
        return;
    }
    
    if (!isValidPassword(password)) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Contraseña debe tener al menos 6 caracteres</div>';
        return;
    }
    
    // Enviar AJAX
    ajaxRequest('../api/auth.php', 'POST', {
        action: 'login',
        email: email,
        password: password
    }).then(response => {
        if (response.success) {
            messagesDiv.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';
            // Redirigir después de 1 segundo
            setTimeout(() => {
                window.location.href = response.data.redirect;
            }, 1000);
        } else {
            messagesDiv.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
        }
    });
}

function handleRegister(e) {
    e.preventDefault();
    
    const nombre = document.getElementById('nombre').value;
    const rutDisplay = document.getElementById('rut_display').value;
    const rut = cleanRut(rutDisplay);
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const messagesDiv = document.getElementById('register-messages');
    
    // Validaciones
    if (!nombre || !rutDisplay || !email || !password || !confirmPassword) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Por favor completa todos los campos</div>';
        return;
    }
    
    if (!isValidEmail(email)) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Correo inválido</div>';
        return;
    }
    
    if (!isValidPassword(password)) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Contraseña debe tener al menos 6 caracteres</div>';
        return;
    }
    
    if (password !== confirmPassword) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Las contraseñas no coinciden</div>';
        return;
    }
    
    if (!rut || rut.length < 8 || rut.length > 9) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">R.U.T. inválido</div>';
        return;
    }
    
    // Enviar AJAX
    ajaxRequest('../api/auth.php', 'POST', {
        action: 'register',
        nombre: nombre,
        rut: rut,
        email: email,
        password: password,
        confirm_password: confirmPassword
    }).then(response => {
        if (response.success) {
            messagesDiv.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';
            document.getElementById('register-form').reset();
            // Cambiar a login después de 2 segundos
            setTimeout(() => {
                toggleRegister();
            }, 2000);
        } else {
            messagesDiv.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
        }
    });
}

function cleanRut(rut) {
    return rut.replace(/\D+/g, '');
}

function formatRut(rut) {
    const digits = cleanRut(rut);
    if (digits.length <= 1) {
        return digits;
    }
    const body = digits.slice(0, -1);
    const dv = digits.slice(-1);
    const reversed = body.split('').reverse().join('');
    const chunks = reversed.match(/.{1,3}/g) || [];
    const formattedBody = chunks.join('.').split('').reverse().join('');
    return `${formattedBody}-${dv}`;
}

function handleRecover(e) {
    e.preventDefault();
    
    const email = document.getElementById('recover-email').value;
    const messagesDiv = document.getElementById('recover-messages');
    
    // Validaciones
    if (!email) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Por favor ingresa tu email</div>';
        return;
    }
    
    if (!isValidEmail(email)) {
        messagesDiv.innerHTML = '<div class="alert alert-warning">Email inválido</div>';
        return;
    }
    
    // Enviar AJAX
    ajaxRequest('../api/auth.php', 'POST', {
        action: 'recover',
        email: email
    }).then(response => {
        if (response.success) {
            messagesDiv.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';
            document.getElementById('recover-form').reset();
            // Cambiar a login después de 3 segundos
            setTimeout(() => {
                toggleRecover();
            }, 3000);
        } else {
            messagesDiv.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
        }
    });
}

function toggleRegister() {
    const loginBox = document.getElementById('login-box');
    const registerBox = document.getElementById('register-box');
    const recoverBox = document.getElementById('recover-box');
    
    if (registerBox.style.display === 'none') {
        loginBox.style.display = 'none';
        recoverBox.style.display = 'none';
        registerBox.style.display = 'block';
    } else {
        loginBox.style.display = 'block';
        registerBox.style.display = 'none';
        recoverBox.style.display = 'none';
        document.getElementById('login-messages').innerHTML = '';
    }
}

function toggleRecover() {
    const loginBox = document.getElementById('login-box');
    const registerBox = document.getElementById('register-box');
    const recoverBox = document.getElementById('recover-box');
    
    if (recoverBox.style.display === 'none') {
        loginBox.style.display = 'none';
        registerBox.style.display = 'none';
        recoverBox.style.display = 'block';
    } else {
        loginBox.style.display = 'block';
        registerBox.style.display = 'none';
        recoverBox.style.display = 'none';
        document.getElementById('login-messages').innerHTML = '';
    }
}

