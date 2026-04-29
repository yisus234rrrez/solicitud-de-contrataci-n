<?php
require_once 'Auth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Se intenta validar usando la función estática de Auth
    if (Auth::login($email, $password)) {
        
        // Si el usuario marcó la opción "Recordarme"
        if (isset($_POST['remember'])) {
            // Guardar cookies no sensibles en el navegador por 30 días
            setcookie('pref_email', $email, time() + (30 * 24 * 60 * 60), "/");
            setcookie('pref_nombre', $_SESSION['usuario'], time() + (30 * 24 * 60 * 60), "/");
        }
        
        // Ingreso exitoso, redirección directa a la pantalla interna
        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../index.php?error=1");
        exit();
    }
}
?>
