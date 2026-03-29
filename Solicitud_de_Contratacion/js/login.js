// Verificar si hay un error en la URL para mostrar el mensaje de alerta
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === '1') {
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.classList.remove('hidden');
        }
    }
});
