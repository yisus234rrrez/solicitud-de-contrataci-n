document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');
    const statusContainer = document.getElementById('status-container');
    const statusMessage = document.getElementById('status-message');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        // Interceptamos el envío por defecto del formulario (recarga de la página).
        // Las validaciones de validaciones.js ocurren simultáneamente.
        e.preventDefault();

        // Limpiar estados previos y mostrar icono de carga en el botón
        statusContainer.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg style="width: 0.9em; height: 0.9em;" class="animate-spin -ml-1 mr-3 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...`;

        const formData = new FormData(form);

        try {
            // Utilizamos la API Fetch para enviar los datos al archivo procesador
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    // Cabecera clave para que el servidor (PHP) detecte que es una llamada asíncrona AJAX
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Convertimos la respuesta binaria a un objeto JSON usable
            const result = await response.json();

            if (result.success) {
                // ÉXITO
                statusContainer.className = 'mb-6 p-4 rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 animate-fade-in';
                statusMessage.textContent = '¡Éxito! Redirigiendo al detalle de tu solicitud...';
                
                // Redirigir al detalle después de un breve momento
                setTimeout(() => {
                    window.location.href = `detalle.php?id=${result.id}`;
                }, 1500);

            } else {
                // ERROR (Validación backend)
                statusContainer.className = "mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 block animate-fade-in";
                statusMessage.innerHTML = "<strong>Error:</strong><br>" + result.errors.join('<br>');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Solicitud';
            }

        } catch (error) {
            console.error('Error AJAX:', error);
            statusContainer.className = "mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 block animate-fade-in";
            statusMessage.textContent = "Ocurrió un problema de conexión al enviar la solicitud.";
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Solicitud';
        }
    });
});
