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
        // Leemos todo el archivo JSON y lo convertimos en un arreglo asociativo de PHP
        $solicitudes = json_decode(file_get_contents($jsonPath), true) ?? [];
        
        // Literal para indicar si encontramos la solicitud que buscamos
        $found = false;
        
        // Estandarización de Comentario de Revisión:
        // Si el admin no escribe nada, inyectamos un comentario por defecto.
        $rawComentario = trim($_POST['comentario_revision'] ?? '');
        $comentario = !empty($rawComentario) ? $rawComentario : 'Sin observaciones técnicas por parte del revisor';
        
        // Iteramos el arreglo de solicitudes por referencia (simbolizado con &)
        // Esto permite que los cambios hechos en $s modifiquen directamente el arreglo original $solicitudes.
        foreach ($solicitudes as &$s) {
            if ($s['id'] === $id) {
                // Evaluamos la acción y cambiamos el estado
                $nuevoEstado = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';
                $s['estado'] = $nuevoEstado;
                $s['decision_at'] = date('Y-m-d H:i:s');
                $s['comentario_revision'] = $comentario;
                
                // Actualizar historial del trámite agregando el nuevo registro de decisión
                if (!isset($s['historial'])) $s['historial'] = [];
                $s['historial'][] = [
                    'estado' => $nuevoEstado,
                    'fecha' => $s['decision_at'],
                    'usuario' => 'Administrador',
                    'observacion' => $comentario
                ];
                
                $found = true;
                break;
            }
        }

        // Si encontramos y modificamos el registro, guardamos de nuevo en disco
        if ($found) {
            file_put_contents($jsonPath, json_encode($solicitudes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            // Redirige al panel con un flag de éxito
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
