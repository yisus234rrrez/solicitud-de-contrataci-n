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
    <link rel="icon" href="img/logoIco.ico" type="image/x-icon">
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

                <?php if (!empty($solicitud['servicios_list']) && is_array($solicitud['servicios_list'])): ?>
                <!-- DETALLE DE SERVICIOS -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 table-container">
                    <div class="flex items-center gap-3 mb-6 pb-2 border-b border-gray-50">
                        <div class="w-8 h-8 bg-brand-soft rounded-lg flex items-center justify-center text-brand-main">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Detalle de Servicios</h2>
                    </div>
                    <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-inner bg-gray-50/30">
                        <table class="w-full text-xs text-left text-gray-600">
                            <thead class="text-[10px] text-brand-gray uppercase bg-gray-50 border-b border-gray-100 font-black tracking-widest">
                                <tr>
                                    <th class="px-4 py-4">Servicio / Descripción</th>
                                    <th class="px-4 py-4 text-center">Cant.</th>
                                    <th class="px-4 py-4">Centro Costos</th>
                                    <th class="px-4 py-4">Rubro</th>
                                    <th class="px-4 py-4 text-center">Disp.</th>
                                    <th class="px-4 py-4 text-center">Fondo</th>
                                    <th class="px-4 py-4">Función</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($solicitud['servicios_list'] as $s): ?>
                                <tr class="bg-white hover:bg-brand-bg/10 transition-colors">
                                    <td class="px-4 py-4 font-semibold text-brand-dark">
                                        <?php echo htmlspecialchars($s['servicio']); ?>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-2 py-1 bg-gray-100 rounded-md font-bold"><?php echo htmlspecialchars($s['cantidad']); ?></span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-700"><?php echo htmlspecialchars($s['centro_costos']); ?></div>
                                        <div class="text-[10px] text-gray-400 font-bold"><?php echo htmlspecialchars($s['cc_codigo']); ?></div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-700"><?php echo htmlspecialchars($s['rubro']); ?></div>
                                        <div class="text-[10px] text-gray-400 font-bold"><?php echo htmlspecialchars($s['rubro_codigo']); ?></div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-brand-main font-bold"><?php echo htmlspecialchars($s['disponibilidad']); ?></td>
                                    <td class="px-4 py-4 text-center"><?php echo htmlspecialchars($s['fondo']); ?></td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-700"><?php echo htmlspecialchars($s['funcion']); ?></div>
                                        <div class="text-[10px] text-gray-400 font-bold"><?php echo htmlspecialchars($s['funcion_codigo']); ?></div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100 text-amber-700 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-width="2"/></svg>
                    <p class="text-sm font-medium">Esta solicitud fue creada con una versión antigua del sistema y no tiene detalles de tabla guardados.</p>
                </div>
                <?php endif; ?>

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
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-50 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-6-6H7zm7 1.5L18.5 9H14V3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>
                        Soporte Adjunto
                    </h2>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Documento de Soporte</p>
                                <p class="text-xs text-gray-400">Formato PDF validado</p>
                            </div>
                        </div>
                        <a href="uploads/<?php echo $solicitud['archivo']; ?>" target="_blank"
                           class="px-6 py-2 bg-brand-main hover:bg-brand-hover text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-brand-soft">
                            Ver PDF
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- COMENTARIO DE REVISIÓN -->
                <?php 
                if ($estado !== 'revision'): 
                    $comentRev = $solicitud['comentario_revision'] ?? $solicitud['comentarios_revisor'] ?? 'Sin observaciones reportadas en la decisión final.';
                ?>
                <div class="bg-brand-bg/20 p-8 rounded-3xl border border-brand-light shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 <?php echo $estado === 'rechazado' ? 'bg-red-500' : 'bg-brand-main'; ?> text-white rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-width="2"/></svg>
                        </div>
                        <h2 class="text-xl font-bold text-brand-dark">Comentario de Revisión</h2>
                    </div>
                    <p class="text-brand-dark leading-relaxed font-medium bg-white p-5 rounded-2xl border border-brand-soft italic">
                        "<?php echo htmlspecialchars($comentRev); ?>"
                    </p>
                </div>
                <?php endif; ?>

                <!-- HISTORIAL DE CAMBIOS -->
                <?php if (!empty($solicitud['historial'])): ?>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-50 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                        Historial de Cambios
                    </h2>
                    <div class="space-y-4">
                        <?php foreach (array_reverse($solicitud['historial']) as $h): ?>
                        <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                            <div class="mt-1">
                                <?php 
                                    $dotColor = 'bg-gray-400';
                                    if($h['estado'] === 'aprobado') $dotColor = 'bg-emerald-500';
                                    if($h['estado'] === 'rechazado') $dotColor = 'bg-red-500';
                                    if($h['estado'] === 'revision') $dotColor = 'bg-amber-500';
                                ?>
                                <div class="w-3 h-3 rounded-full <?php echo $dotColor; ?> shadow-sm"></div>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-black uppercase text-gray-900 tracking-tight"><?php echo $h['estado']; ?></span>
                                    <span class="text-[10px] font-bold text-gray-400"><?php echo date('d M Y, H:i', strtotime($h['fecha'])); ?></span>
                                </div>
                                <p class="text-xs text-gray-600 font-medium">Por: <span class="text-brand-main"><?php echo htmlspecialchars($h['usuario']); ?></span></p>
                                <p class="text-sm text-gray-500 mt-2 leading-tight italic"><?php echo htmlspecialchars($h['observacion']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

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
                                <?php echo $paso3 ? ($estado === 'rechazado' ? 'bg-status-danger' : 'bg-status-success') . ' text-white shadow-lg' : 'bg-gray-200'; ?>">
                                
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