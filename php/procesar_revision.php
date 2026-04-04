<?php
require_once 'Auth.php';
Auth::requireLogin();

if ($_SESSION['usuario'] !== 'Administrador') {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_solicitud'] ?? '';
    $accion = $_POST['accion'] ?? '';

    if (empty($id) || empty($accion)) {
        header("Location: ../revision.php?error=missing_data");
        exit();
    }

    $jsonPath = 'data/solicitudes.json';
    if (file_exists($jsonPath)) {
        $solicitudes = json_decode(file_get_contents($jsonPath), true) ?? [];
        
        // Actualización de estado en JSON - (Motor de decisiones del revisor)
        $found = false;
        foreach ($solicitudes as &$s) {
            if ($s['id'] === $id) {
                $s['estado'] = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';
                $s['decision_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }

        if ($found) {
            file_put_contents($jsonPath, json_encode($solicitudes, JSON_PRETTY_PRINT));
            header("Location: ../revision.php?status=success");
            exit();
        }
    }

    header("Location: ../revision.php?error=not_found");
    exit();
} else {
    header("Location: ../revision.php");
    exit();
}
?>
