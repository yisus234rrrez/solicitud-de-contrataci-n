<?php
require_once 'php/Auth.php';
Auth::requireLogin();

// Solo el administrador puede acceder
if ($_SESSION['usuario'] !== 'Administrador') {
    header("Location: dashboard.php");
    exit();
}

// Leer solicitudes desde JSON - (Carga dinámica de solicitudes)
$jsonPath = 'php/data/solicitudes.json';
$solicitudes = [];
if (file_exists($jsonPath)) {
    $solicitudes = json_decode(file_get_contents($jsonPath), true) ?? [];
}

// Filtrar solo las que están en estado 'revision' (o no tienen estado aún)
$pendientes = array_filter($solicitudes, function ($s) {
    // Si no tiene estado, asumimos que es nueva/en revisión
    if (!isset($s['estado']))
        return true;
    return $s['estado'] === 'revision';
});
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel revisor</title>
    <link href="css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <link rel="icon" href="img/logoIco.ico" type="image/x-icon">
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- NAVIGATION (Breadcrumbs) -->
    <nav class="bg-brand-dark border-b border-brand-main py-4 shadow-md text-white">
        <div class="max-w-6xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center text-sm">
                <a href="dashboard.php" class="opacity-70 hover:opacity-100 transition-opacity">Dashboard</a>
                <svg class="w-4 h-4 mx-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="font-medium text-brand-light">Panel Revisor</span>
            </div>
            <img src="img/logo.png" alt="Logo CECAR" class="h-8 w-auto brightness-0 invert">
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Solicitudes para Revisión</h1>
            <p class="text-brand-gray mt-1 font-medium">Gestiona las aprobaciones pendientes del sistema.</p>
        </div>

        <!-- BARRA DE FILTROS ORIGINAL SIN FONDO -->
        <div class="mb-8 flex flex-col md:flex-row gap-6 items-center">
            <div class="flex-1 w-full">
                <input type="text" id="revSearchInput" placeholder="Buscar por ID o nombre de solicitante..." 
                    class="w-full px-4 py-4 bg-white shadow-sm border border-gray-200 rounded-2xl focus:ring-4 focus:ring-brand-soft focus:border-brand-main outline-none transition-all text-gray-700 text-sm font-medium">
            </div>
            <div class="text-right">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">Pendientes Hoy</p>
                <p id="revCount" class="text-3xl font-black text-brand-main"><?php echo count($pendientes); ?></p>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('revSearchInput');
                const revCountDisplay = document.getElementById('revCount');
                const rows = document.querySelectorAll('tbody tr');

                if (rows.length === 1 && rows[0].cells.length < 3) return;

                searchInput.addEventListener('input', function() {
                    const searchTerm = searchInput.value.toLowerCase();
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const id = row.cells[0].textContent.toLowerCase();
                        const nombre = row.cells[1].textContent.toLowerCase();

                        if (id.includes(searchTerm) || nombre.includes(searchTerm)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    revCountDisplay.textContent = visibleCount;
                });
            });
        </script>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div
                class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center font-medium">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                ¡Acción procesada correctamente!
            </div>
        <?php endif; ?>

        <!-- TABLA REVISIÓN -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center text-sm font-semibold text-brand-gray uppercase tracking-wider">
                <span>Total Pendientes: <?php echo count($pendientes); ?></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-8 py-5 text-sm font-semibold text-brand-gray uppercase tracking-wider">ID</th>
                            <th class="px-8 py-5 text-sm font-semibold text-brand-gray uppercase tracking-wider">
                                Solicitante</th>
                            <th class="px-8 py-5 text-sm font-semibold text-brand-gray uppercase tracking-wider">
                                Acciones de Decisión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($pendientes)): ?>
                            <tr>
                                <td colspan="3" class="px-8 py-10 text-center text-gray-400 italic">No hay solicitudes
                                    pendientes de revisión.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendientes as $s): ?>
                                <tr class="hover:bg-brand-bg/20 transition-colors">
                                    <td
                                        class="px-8 py-8 font-extrabold text-xl text-brand-dark underline decoration-brand-light decoration-4 underline-offset-4">
                                        #<?php echo $s['id']; ?></td>
                                    <td class="px-8 py-8">
                                        <div class="flex items-center">
                                            <div
                                                class="w-12 h-12 rounded-2xl bg-brand-soft flex items-center justify-center text-brand-main font-black text-xl mr-4 shadow-sm">
                                                <?php echo strtoupper(substr($s['nombre'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-brand-dark text-lg">
                                                    <?php echo htmlspecialchars($s['nombre']); ?>
                                                </p>
                                                <p class="text-xs text-brand-gray font-medium uppercase tracking-tighter">
                                                    <?php echo htmlspecialchars($s['dependencia']); ?> •
                                                    <?php echo date('d M', strtotime($s['fecha'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-8">
                                        <div class="flex flex-col space-y-4">
                                            <a href="detalle.php?id=<?php echo $s['id']; ?>" target="_blank"
                                                class="text-brand-main font-bold text-sm hover:underline flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" />
                                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2" />
                                                </svg>
                                                VER DETALLES (TABLA)
                                            </a>

                                            <form action="php/procesar_revision.php" method="POST"
                                                class="space-y-4">
                                                <input type="hidden" name="id_solicitud" value="<?php echo $s['id']; ?>">

                                                <div>
                                                    <label class="block text-xs font-bold text-brand-gray uppercase mb-2">Comentario de Revisión</label>
                                                    <textarea name="comentario_revision" rows="2" 
                                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-main focus:ring-2 focus:ring-brand-soft outline-none transition-all text-sm"
                                                        placeholder="Escribe el motivo de la decisión..."></textarea>
                                                </div>

                                                <div class="flex items-center space-x-4">
                                                    <button type="submit" name="accion" value="aprobar"
                                                        class="flex-1 px-6 py-3 bg-brand-main hover:bg-brand-hover text-white text-sm font-black rounded-2xl shadow-xl shadow-brand-soft transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        APROBAR
                                                    </button>

                                                    <button type="submit" name="accion" value="rechazar"
                                                        class="flex-1 px-6 py-3 bg-white border-2 border-red-50 hover:bg-red-50 text-red-500 text-sm font-extrabold rounded-2xl transition-all flex items-center justify-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        RECHAZAR
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

</body>

</html>