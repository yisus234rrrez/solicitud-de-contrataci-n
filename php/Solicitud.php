<?php
/**
 * Clase para gestionar el procesamiento de solicitudes y archivos.
 */
class Solicitud
{
    private static $uploadDir = '../uploads/';

    /**
     * Procesa y guarda un archivo adjunto.
     * Evalúa seguridad (extensiones válidas, peso máximo).
     * @return array [success => bool, message => string, filename => string|null]
     */
    public static function uploadFile($file)
    {
        // Revisamos si el archivo se envió y si no hay errores en su transmisión
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo o no se adjuntó ninguno.'];
        }

        // Validación estricta 1: Extensión del archivo.
        $allowedExtensions = ['pdf'];
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Solo se permiten archivos PDF.'];
        }

        // Validación estricta 2: Tamaño máximo del archivo (5 MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo excede el tamaño máximo de 5MB.'];
        }

        // Crear directorio de destino si no existe (con permisos)
        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0777, true);
        }

        // Se usa `time()` para asegurar que el nombre del archivo no se sobreescriba con otro igual
        $newFilename = time() . '_' . basename($file['name']);
        $targetPath = self::$uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'message' => 'Archivo cargado correctamente.', 'filename' => $newFilename];
        }

        return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
    }

    /**
     * Guarda la solicitud en un archivo JSON (Requerimiento 5).
     * Toda la transaccionalidad de datos ocurre mediante lecturas y escrituras en archivos JSON planos.
     */
    public static function saveToJson($data)
    {
        $filePath = __DIR__ . '/data/solicitudes.json';

        // Asegurarse de que la carpeta data/ exista
        if (!is_dir(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0777, true);
        }

        $solicitudes = [];
        // Intentar leer y parsear el archivo existente si fuese necesario
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $solicitudes = json_decode($jsonContent, true) ?? [];
        }

        // Añadir ID único incremental basado en la longitud actual + 1, formateado en 3 dígitos (Ej. "003")
        $data['id'] = str_pad(count($solicitudes) + 1, 3, '0', STR_PAD_LEFT);
        $data['registro_at'] = date('Y-m-d H:i:s');
        $data['estado'] = 'revision'; // Estado inicial por defecto
        
        // Historial de cambios
        $data['historial'] = [
            [
                'estado' => 'revision',
                'fecha' => $data['registro_at'],
                'usuario' => $data['nombre'],
                'observacion' => 'Solicitud creada y enviada a revisión'
            ]
        ];

        // Añadir la solicitud procesada al listado y reescribir el JSON
        // JSON_PRETTY_PRINT lo formatea de manera humana y UNESCAPED_UNICODE permite tildes y caracteres hispanos.
        $solicitudes[] = $data;
        $saved = file_put_contents($filePath, json_encode($solicitudes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Retornar el ID que fue asignado o false en caso error
        return $saved ? $data['id'] : false;
    }
}
?>
