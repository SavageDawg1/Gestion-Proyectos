/**
 * Script para la pÃ¡gina de Login
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

    // Validaciones bÃ¡sicas
    if (!email || !password) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Por favor completa todos los campos</div>');
        return;
    }

    if (!isValidEmail(email)) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Email inválido</div>');
        return;
    }

    if (!isValidPassword(password)) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Contraseña debe tener al menos 6 caracteres</div>');
        return;
    }

    // Enviar AJAX
    ajaxRequest('../Controllers/AuthController.php', 'POST', {
        action: 'login',
        email: email,
        password: password
    }).then(response => {
        if (response.success) {
            setMessage(messagesDiv, '<div class="alert alert-success">' + response.message + '</div>');
            // Redirigir después de 1 segundo
            setTimeout(() => {
                window.location.href = response.data.redirect;
            }, 1000);
        } else {
            setMessage(messagesDiv, '<div class="alert alert-danger">' + response.message + '</div>');
        }
    });
}

function handleRegister(e) {
    e.preventDefault();

    const nombre = document.getElementById('nombre').value;
    const rutDisplay = document.getElementById('rut_display').value;
    const rut = cleanRut(rutDisplay);
    const email = document.getElementById('register-email').value;
    const roleInput = document.getElementById('register-role');
    const rolId = roleInput ? roleInput.value : '2';
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const messagesDiv = document.getElementById('register-messages');

    if (!nombre || !rutDisplay || !email || !rolId || !password || !confirmPassword) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Por favor completa todos los campos</div>');
        return;
    }

    if (!isValidEmail(email)) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Correo inválido</div>');
        return;
    }

    if (!isValidPassword(password)) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Contraseña debe tener al menos 6 caracteres</div>');
        return;
    }

    if (password !== confirmPassword) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Las contraseñas no coinciden</div>');
        return;
    }

    if (!rut || rut.length < 8 || rut.length > 9) {
        setMessage(messagesDiv, '<div class="alert alert-warning">R.U.T. inválido</div>');
        return;
    }

    const submitRegistration = function() {
        ajaxRequest('../Controllers/AuthController.php', 'POST', {
            action: 'register',
            nombre: nombre,
            rut: rut,
            email: email,
            rol_id: rolId,
            password: password,
            confirm_password: confirmPassword
        }).then(response => {
            if (response.success) {
                setMessage(messagesDiv, '<div class="alert alert-success">' + response.message + '</div>');
                document.getElementById('register-form').reset();
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
            } else {
                setMessage(messagesDiv, '<div class="alert alert-danger">' + response.message + '</div>');
            }
        });
    };

    if (window.appConfirm) {
        window.appConfirm('Se creara un nuevo usuario con el rol seleccionado. ¿Confirmas el registro?', submitRegistration);
        return;
    }

    submitRegistration();
}
function cleanRut(rut) {
    return rut.replace(/\D+/g, '');
}

function setMessage(messagesDiv, html) {
    if (!messagesDiv) {
        return;
    }
    messagesDiv.innerHTML = html;
    if (typeof window.scrollTo === 'function') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
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
        setMessage(messagesDiv, '<div class="alert alert-warning">Por favor ingresa tu email</div>');
        return;
    }

    if (!isValidEmail(email)) {
        setMessage(messagesDiv, '<div class="alert alert-warning">Email inválido</div>');
        return;
    }

    // Enviar AJAX
    ajaxRequest('../Controllers/AuthController.php', 'POST', {
        action: 'recover',
        email: email
    }).then(response => {
        if (response.success) {
            setMessage(messagesDiv, '<div class="alert alert-success">' + response.message + '</div>');
            document.getElementById('recover-form').reset();
            // Cambiar a login después de 3 segundos
            setTimeout(() => {
                toggleRecover();
            }, 3000);
        } else {
            setMessage(messagesDiv, '<div class="alert alert-danger">' + response.message + '</div>');
        }
    });
}

function toggleRegister() {
    const loginBox = document.getElementById('login-box');
    const registerBox = document.getElementById('register-box');
    const recoverBox = document.getElementById('recover-box');

    if (!loginBox || !registerBox) {
        return;
    }

    if (registerBox.style.display === 'none') {
        loginBox.style.display = 'none';
        if (recoverBox) {
            recoverBox.style.display = 'none';
        }
        registerBox.style.display = 'block';
    } else {
        loginBox.style.display = 'block';
        registerBox.style.display = 'none';
        if (recoverBox) {
            recoverBox.style.display = 'none';
        }
        const loginMessages = document.getElementById('login-messages');
        if (loginMessages) {
            loginMessages.innerHTML = '';
        }
    }
}

function toggleRecover() {
    const loginBox = document.getElementById('login-box');
    const registerBox = document.getElementById('register-box');
    const recoverBox = document.getElementById('recover-box');

    if (!loginBox || !recoverBox) {
        return;
    }

    if (recoverBox.style.display === 'none') {
        loginBox.style.display = 'none';
        if (registerBox) {
            registerBox.style.display = 'none';
        }
        recoverBox.style.display = 'block';
    } else {
        loginBox.style.display = 'block';
        if (registerBox) {
            registerBox.style.display = 'none';
        }
        recoverBox.style.display = 'none';
        const loginMessages = document.getElementById('login-messages');
        if (loginMessages) {
            loginMessages.innerHTML = '';
        }
    }
}

