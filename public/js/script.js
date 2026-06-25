/**
 * Script General del Proyecto
 */

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    document.body.insertBefore(alertDiv, document.body.firstChild);
    scrollToTop();
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function scrollToTop() {
    if (typeof window.scrollTo === 'function') {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}

// Función para hacer AJAX requests
function ajaxRequest(url, method = 'POST', data = {}) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error en la solicitud', 'danger');
    });
}

// Función para validar email
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Función para validar password
function isValidPassword(password) {
    return password.length >= 6;
}

console.log('Script general cargado');
