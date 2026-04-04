<?php

require_once 'php/Auth.php';

Auth::requireLogin();


$id = $_GET['id'] ?? '';

$jsonPath = 'php/data/solicitudes.json';

$solicitud = null;


if (!empty($id) && file_exists($jsonPath)) {
    $solicitudes = json_decode(file_get_contents($jsonPath), true) ?? [];

    foreach ($solicitudes as $s) {
        if ($s['id'] === $id) {
            $solicitud = $s;
            break;
        }
    }
}

if (!$solicitud) {
    header("Location: solicitudes.php");
    exit();
}

// Variables sencillas de estado - (Cambio Realizado para simplicidad)
$estado = $solicitud['estado'] ?? 'revision';
$paso1 = true; 
$paso2 = ($estado === 'revision' || $estado === 'aprobado' || $estado === 'rechazado');
$paso3 = ($estado === 'aprobado' || $estado === 'rechazado');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Solicitud #<?php echo $solicitud['id']; ?> - Proyecto Premium</title>

    <link href="css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- NAVIGATION -->
    <nav class="bg-white border-b border-gray-200 py-4">
        <div class="max-w-4xl mx-auto px-6 flex items-center justify-between">

            <div class="flex items-center text-sm text-brand-main">
                <a href="solicitudes.php" class="hover:underline transition-colors">
                    Mis Solicitudes
                </a>

                <svg class="w-4 h-4 mx-2 text-brand-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <span class="text-brand-dark font-medium">
                    Detalle #<?php echo $solicitud['id']; ?>
                </span>
            </div>

            <img src="img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-10">

        <!-- HEADER -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detalle de Solicitud</h1>
                <p class="text-gray-500 mt-1">
                    Información detallada registrada en el sistema.
                </p>
            </div>

            <?php 
            $statusText = 'EN REVISIÓN';
            $statusClass = 'bg-amber-100 text-amber-600 border-amber-200';
            if ($estado === 'aprobado') {
                $statusText = 'APROBADO';
                $statusClass = 'bg-emerald-100 text-emerald-600 border-emerald-200';
            } elseif ($estado === 'rechazado') {
                $statusText = 'RECHAZADO';
                $statusClass = 'bg-red-100 text-red-600 border-red-200';
            }
            ?>
            <span class="px-4 py-2 <?php echo $statusClass; ?> text-sm font-bold rounded-xl border shadow-sm">
                <?php echo $statusText; ?>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- INFORMACIÓN PRINCIPAL -->
            <div class="md:col-span-2 space-y-6">

                <!-- RESUMEN -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-50">
                        Resumen de Datos
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6">

                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1">
                                Solicitante
                            </p>
                            <p class="text-gray-900 font-medium text-lg">
                                <?php echo htmlspecialchars($solicitud['nombre']); ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1">
                                Fecha de Solicitud
                            </p>
                            <p class="text-gray-900 font-medium">
                                <?php echo date('d M, Y', strtotime($solicitud['fecha'])); ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1">
                                Tipo de Solicitud
                            </p>
                            <p class="text-gray-900 font-medium">
                                <?php echo htmlspecialchars($solicitud['tipo']); ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1">
                                Dependencia
                            </p>
                            <p class="text-gray-900 font-medium">
                                <?php echo htmlspecialchars($solicitud['dependencia']); ?>
                            </p>
                        </div>

                        <?php if (!empty($solicitud['cargo'])): ?>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1">
                                Cargo
                            </p>
                            <p class="text-gray-900 font-medium">
                                <?php echo htmlspecialchars($solicitud['cargo']); ?>
                            </p>
                        </div>
                        <?php
endif; ?>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1">
                                Items registrados
                            </p>
                            <p class="text-gray-900 font-medium">
                                <?php echo $solicitud['servicios_count']; ?> servicio(s)
                            </p>
                        </div>

                    </div>
                </div>

                <!-- JUSTIFICACIÓN -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-50">
                        Justificación
                    </h2>

                    <p class="text-gray-600 leading-relaxed italic">
                        "<?php echo htmlspecialchars($solicitud['justificacion']); ?>"
                    </p>
                </div>

                <!-- ARCHIVO -->
                <?php if (!empty($solicitud['archivo'])): ?>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-50">
                        Soporte Adjunto
                    </h2>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">

                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M7 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-6-6H7zm7 1.5L18.5 9H14V3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-bold text-gray-900">
                                    Documento de Soporte.pdf
                                </p>
                                <p class="text-xs text-gray-500">
                                    Archivo PDF validado
                                </p>
                            </div>
                        </div>

                        <a href="uploads/<?php echo $solicitud['archivo']; ?>" target="_blank"
                           class="px-6 py-2 bg-brand-main hover:bg-brand-hover text-white text-sm font-bold rounded-xl transition-all">
                            Ver PDF
                        </a>
                    </div>
                </div>
                <?php
endif; ?>

            </div>

            <!-- SEGUIMIENTO DINÁMICO -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-6">Estado del Trámite</h2>

                    <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-brand-main before:via-gray-200 before:to-gray-200">

                        <!-- PASO 1 -->
                        <div class="relative flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full z-10
                                <?php echo $paso1 ? 'bg-brand-main text-white shadow-lg shadow-brand-soft' : 'bg-gray-200 text-gray-400'; ?>">
                                
                                <?php if ($paso1): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-width="2"/>
                                    </svg>
                                <?php
else: ?>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <?php
endif; ?>
                            </div>

                            <div class="ml-4">
                                <p class="text-sm font-bold text-gray-900">Enviado</p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('d M, H:i A', strtotime($solicitud['registro_at'] ?? $solicitud['fecha'])); ?>
                                </p>
                            </div>
                        </div>

                        <!-- PASO 2 -->
                        <div class="relative flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full z-10 <?php echo $paso2 ? ($estado === 'revision' ? 'bg-amber-100 border-2 border-amber-400' : 'bg-brand-main text-white shadow-lg') : 'bg-gray-200'; ?>">
                                
                                <?php if ($estado === 'revision'): ?>
                                    <svg class="w-5 h-5 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?php elseif ($paso2): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2"/></svg>
                                <?php else: ?>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <?php endif; ?>
                            </div>

                            <div class="ml-4">
                                <p class="text-sm font-bold <?php echo $paso2 ? 'text-gray-900' : 'text-gray-400'; ?>">En Revisión</p>
                                <p class="text-xs text-gray-400"><?php echo $estado === 'revision' ? 'Esperando revisión...' : ($paso2 ? 'Completado' : 'Pendiente'); ?></p>
                            </div>
                        </div>

                        <!-- PASO 3 -->
                        <div class="relative flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full z-10
                                <?php echo $paso3 ? ($estado === 'rechazado' ? 'bg-red-500' : 'bg-emerald-500') . ' text-white shadow-lg' : 'bg-gray-200'; ?>">
                                
                                <?php if ($paso3): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($estado === 'rechazado'): ?>
                                            <!-- Icono X para rechazada - (Cambio Realizado) -->
                                            <path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        <?php else: ?>
                                            <!-- Icono Check para aprobada - (Cambio Realizado) -->
                                            <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        <?php endif; ?>
                                    </svg>
                                <?php else: ?>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <?php endif; ?>
                            </div>

                            <div class="ml-4">
                                <p class="text-sm font-bold <?php echo $paso3 ? 'text-gray-900' : 'text-gray-400'; ?>">
                                    <?php echo ($estado === 'rechazado') ? 'Rechazada' : 'Aprobada'; ?>
                                </p>
                                <p class="text-xs text-gray-400"><?php echo $paso3 ? 'Finalizado' : 'En espera'; ?></p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- BOTÓN -->
                <a href="solicitudes.php"
                   class="inline-flex w-full items-center justify-center px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-2xl transition-all">
                    Volver al Listado
                </a>

            </div>

        </div>
    </main>

</body>
</html>