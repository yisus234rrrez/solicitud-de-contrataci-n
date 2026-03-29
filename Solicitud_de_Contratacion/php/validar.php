<?php
session_start();

// Credenciales fijas proporcionadas por el usuario
$usuario_admin = "usuario";
$clave_admin = "1234";

// Obtenemos los datos del formulario de forma segura
$usuario = $_POST['usuario'] ?? '';
$clave = $_POST['password'] ?? '';

if ($usuario === $usuario_admin && $clave === $clave_admin) {
    // Si es correcto, iniciamos sesión y redirigimos al dashboard
    $_SESSION['usuario'] = $usuario;
    header("Location: ../dashboard.html");
    exit();
} else {
    // Si falla, redirigimos de vuelta al login con un parámetro de error
    header("Location: ../index.html?error=1");
    exit();
}
?>