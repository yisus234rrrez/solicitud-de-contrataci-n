<?php
require_once 'conexion.php';
require_once 'Solicitud.php';

/**
 * Guarda la solicitud enviada desde el formulario.
 * Este archivo actúa como el "Controlador" para procesar la nueva solicitud.
 */

// Verificamos que la petición venga estrictamente a través del método POST (envío de formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Arreglo para almacenar los mensajes de error en caso de que alguna validación falle.
    $errors = [];

    // 1. Mandatory General Fields
    $nombre = trim($_POST['nombre_solicitante'] ?? '');
    $dependencia = trim($_POST['dependencia'] ?? '');
    $fecha = trim($_POST['fecha_solicitud'] ?? '');
    $justificacion = trim($_POST['justificacion'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $tipo = trim($_POST['tipo_solicitud'] ?? '');

    if (empty($nombre))
        $errors[] = "El nombre del solicitante es obligatorio.";
    if (empty($dependencia))
        $errors[] = "La dependencia es obligatoria.";
    if (empty($fecha))
        $errors[] = "La fecha de solicitud es obligatoria.";
    if (empty($justificacion))
        $errors[] = "La justificación es obligatoria.";
    if (empty($tipo))
        $errors[] = "Debes seleccionar un tipo de solicitud.";

    // 2. Services Table Validation
    // Se reciben arrays (listas) desde el formulario porque la tabla puede tener múltiples filas.
    $servicios = $_POST['servicios'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    $cc_nombres = $_POST['cc_nombres'] ?? [];
    $cc_codigos = $_POST['cc_codigos'] ?? [];
    $rubro_nombres = $_POST['rubro_nombres'] ?? [];
    $rubro_codigos = $_POST['rubro_codigos'] ?? [];
    $disponibilidades = $_POST['disponibilidades'] ?? [];
    $fondos = $_POST['fondos'] ?? [];
    $funcion_nombres = $_POST['funcion_nombres'] ?? [];
    $funcion_codigos = $_POST['funcion_codigos'] ?? [];

    $validRows = 0; // Contador para saber cuántas filas fueron llenadas completas
    // Iteramos sobre todos los servicios enviados
    foreach ($servicios as $i => $s) {
        $rowFields = [
            $servicios[$i] ?? '',
            $cantidades[$i] ?? '',
            $cc_nombres[$i] ?? '',
            $cc_codigos[$i] ?? '',
            $rubro_nombres[$i] ?? '',
            $rubro_codigos[$i] ?? '',
            $disponibilidades[$i] ?? '',
            $fondos[$i] ?? '',
            $funcion_nombres[$i] ?? '',
            $funcion_codigos[$i] ?? ''
        ];

        // Revisamos si el usuario empezó a llenar esta fila (al menos un campo tiene texto)
        $isRowStarted = false;
        foreach ($rowFields as $val) {
            if (!empty(trim((string) $val))) {
                $isRowStarted = true;
                break;
            }
        }

        if ($isRowStarted) {
            $isRowComplete = true;
            foreach ($rowFields as $val) {
                if (empty(trim((string) $val))) {
                    $isRowComplete = false;
                    break;
                }
            }

            if (!$isRowComplete) {
                $errors[] = "La fila de servicio #" . ($i + 1) . " está incompleta. Todos los 10 campos son requeridos.";
            } else {
                $validRows++;
            }
        }
    }

    if ($validRows === 0) {
        $errors[] = "Debes completar al menos una fila de servicios íntegramente.";
    }

    // 3. Handle File Upload (Requirement 3)
    // Delegamos la subida del archivo a un método estático de la clase Solicitud
    $uploadResult = Solicitud::uploadFile($_FILES['adjunto'] ?? null);
    if (!$uploadResult['success']) {
        $errors[] = $uploadResult['message'];
    } else {
        $archivo_nombre = $uploadResult['filename'];
    }

    // 4. Handle Errors or Proceed
    // Detectamos si la petición fue hecha con AJAX (Fetch API) o de forma tradicional (recarga).
    $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

    // Si hubo algún error en las validaciones anteriores (campos vacíos, tabla incompleta, error PDF)
    if (!empty($errors)) {
        if ($isAjax) {
            // Si es AJAX, devolvemos un JSON puro para que JavaScript lo procese y lo muestre
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #fecaca; background: #fef2f2; border-radius: 12px; color: #991b1b;'>";
        echo "<h2 style='margin-top:0; display: flex; align-items: center; gap: 8px;'>
            <svg style='width: 1em; height: 1em;' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path>
            </svg> 
            Errores de Validación</h2>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        echo "<br><a href='javascript:history.back()' style='display:inline-block; padding:10px 20px; background:#ef4444; color:white; text-decoration:none; border-radius:8px;'>Volver a corregir</a>";
        echo "</div>";
        exit;
    }

    // Preparar lista detallada de servicios para persistencia completa
    $listaServicios = [];
    foreach ($servicios as $i => $s) {
        // Si al menos el servicio tiene contenido, capturamos la fila
        $servicioVal = trim((string)($servicios[$i] ?? ''));
        if ($servicioVal !== '') {
            $listaServicios[] = [
                'servicio'       => $servicioVal,
                'cantidad'       => trim((string)($cantidades[$i] ?? '0')),
                'centro_costos'  => trim((string)($cc_nombres[$i] ?? '')),
                'cc_codigo'      => trim((string)($cc_codigos[$i] ?? '')),
                'rubro'          => trim((string)($rubro_nombres[$i] ?? '')),
                'rubro_codigo'   => trim((string)($rubro_codigos[$i] ?? '')),
                'disponibilidad' => trim((string)($disponibilidades[$i] ?? '')),
                'fondo'          => trim((string)($fondos[$i] ?? '')),
                'funcion'        => trim((string)($funcion_nombres[$i] ?? '')),
                'funcion_codigo' => trim((string)($funcion_codigos[$i] ?? ''))
            ];
        }
    }

    // Preparar datos para guardar
    $data = [
        'nombre'          => $nombre,
        'dependencia'     => $dependencia,
        'fecha'           => $fecha,
        'tipo'            => $tipo,
        'justificacion'   => $justificacion,
        'cargo'           => $cargo,
        'archivo'         => $archivo_nombre ?? null,
        'servicios_count' => count($listaServicios),
        'servicios_list'  => $listaServicios
    ];

    // Guardar en persistencia (JSON en este caso, Requirement 5)
    // El método retorna el ID generado de la solicitud si todo va bien.
    $nuevoId = Solicitud::saveToJson($data);

    // Si es AJAX devolvemos la URL de redirección en un JSON (para que JS haga el cambio de ventana)
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente', 'id' => $nuevoId]);
        exit;
    }

    // Código legacy para cuando NO es AJAX (Muestra un HTML de éxito)

    echo "<!DOCTYPE html><html lang='es'><head><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-gray-50 flex items-center justify-center min-h-screen'>";
    echo "<div class='bg-white p-10 rounded-3xl shadow-xl border border-gray-100 max-w-lg text-center'>";
    echo "<div class='w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6'><svg class='w-12 h-12' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path d='M5 13l4 4L19 7' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/></svg></div>";
    echo "<h1 class='text-3xl font-bold text-gray-900 mb-2'>Solicitud Procesada</h1>";
    echo "<p class='text-gray-600 mb-8'>Gracias, <strong>" . htmlspecialchars($nombre) . "</strong>. Tu requerimiento ha sido validado y guardado correctamente en el sistema.</p>";
    echo "<a href='../detalle.php?id=" . $nuevoId . "' class='inline-block w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-green-100'>Finalizar</a>";
    echo "</div></body></html>";
} else {
    header("Location: ../solicitud.html");
}
?>