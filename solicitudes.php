<?php
require_once 'php/Auth.php';
Auth::requireLogin();

// Leer solicitudes desde JSON (Requerimiento 5)
$jsonPath = 'php/data/solicitudes.json';
$solicitudes = [];
if (file_exists($jsonPath)) {
    $solicitudes = json_decode(file_get_contents($jsonPath), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes - Proyecto Premium</title>
    <link href="css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="img/logoIco.ico" type="image/x-icon">
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- NAVIGATION (Breadcrumbs) -->
    <nav class="bg-white border-b border-gray-200 py-4">
        <div class="max-w-6xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center text-sm text-brand-main">
                <a href="dashboard.php" class="hover:underline transition-colors">Dashboard</a>
                <svg class="w-4 h-4 mx-2 text-brand-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-brand-dark font-medium">Mis Solicitudes</span>
            </div>
            <img src="img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Mis Solicitudes</h1>
                <p class="text-gray-500 mt-1">Listado de requerimientos realizados y su estado actual.</p>
            </div>
            <a href="solicitud.php"
                class="inline-flex items-center px-6 py-3 bg-brand-main hover:bg-brand-hover text-white font-bold rounded-xl shadow-lg shadow-brand-soft transition-all transform hover:-translate-y-0.5 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Nueva Solicitud
            </a>
        </div>

        <!-- BARRA DE FILTROS ORIGINAL SIN FONDO -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Buscar Solicitud</label>
                <input type="text" id="searchInput" placeholder="ID o Nombre..." 
                    class="w-full px-4 py-3 bg-white shadow-sm border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-brand-soft focus:border-brand-main outline-none transition-all text-sm font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Estado</label>
                <select id="statusFilter"
                    class="w-full px-4 py-3 bg-white shadow-sm border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-brand-soft focus:border-brand-main outline-none transition-all text-sm font-bold text-gray-700">
                    <option value="all">Todos los estados</option>
                    <option value="revision">En Revisión</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Fecha exacta</label>
                <input type="date" id="dateFilter" 
                    class="w-full px-4 py-3 bg-white shadow-sm border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-brand-soft focus:border-brand-main outline-none transition-all text-sm font-medium text-gray-700 cursor-pointer">
            </div>
            <div class="flex items-center justify-end">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest text-right">
                    Filtrado dinámico<br><span id="rowCount"
                        class="text-brand-main text-xl"><?php echo count($solicitudes); ?></span> registros
                </p>
            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');
                const statusFilter = document.getElementById('statusFilter');
                const dateFilter = document.getElementById('dateFilter');
                const rowCountDisplay = document.getElementById('rowCount');
                const tableBody = document.querySelector('tbody');
                const rows = tableBody.querySelectorAll('tr');

                // Si no hay solicitudes (solo fila de mensaje vacío), no hacemos nada
                if (rows.length === 1 && rows[0].cells.length < 5) return;

                function filterTable() {
                    const searchTerm = searchInput.value.toLowerCase().trim();
                    const statusTerm = statusFilter.value;
                    const dateTerm = dateFilter.value;
                    let visibleCount = 0;

                    rows.forEach(row => {
                        if (row.id === 'noResultsRow' || row.cells.length < 5) return;

                        // ID está en celda 0, Nombre en celda 1
                        const idText = row.cells[0].textContent.toLowerCase();
                        const nombreText = row.cells[1].textContent.toLowerCase();

                        // Estado está en celda 4
                        const statusCell = row.cells[4];
                        const badgeText = statusCell ? statusCell.innerText.toLowerCase() : '';
                        
                        const rowDate = row.getAttribute('data-date') || '';

                        // Determinar estado actual basado en el texto del badge
                        let currentStatus = 'revision'; // Default
                        if (badgeText.includes('aprobado')) {
                            currentStatus = 'aprobado';
                        } else if (badgeText.includes('rechazado')) {
                            currentStatus = 'rechazado';
                        } else if (badgeText.includes('revis')) {
                            currentStatus = 'revision';
                        }

                        const matchesSearch = idText.includes(searchTerm) || nombreText.includes(searchTerm);
                        const matchesStatus = statusTerm === 'all' || currentStatus === statusTerm;
                        const matchesDate = !dateTerm || rowDate === dateTerm;

                        if (matchesSearch && matchesStatus && matchesDate) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    rowCountDisplay.textContent = visibleCount;

                    // Manejar estado vacío si el filtrado no arroja resultados
                    let noResultsRow = document.getElementById('noResultsRow');
                    if (visibleCount === 0) {
                        if (!noResultsRow) {
                            noResultsRow = document.createElement('tr');
                            noResultsRow.id = 'noResultsRow';
                            noResultsRow.innerHTML = `<td colspan="6" class="px-8 py-20 text-center text-gray-400 italic">No se encontraron resultados para los filtros aplicados.</td>`;
                            tableBody.appendChild(noResultsRow);
                        } else {
                            noResultsRow.style.display = '';
                        }
                    } else if (noResultsRow) {
                        noResultsRow.style.display = 'none';
                    }
                }

                searchInput.addEventListener('input', filterTable);
                statusFilter.addEventListener('change', filterTable);
                dateFilter.addEventListener('change', filterTable);
            });
        </script>

        <!-- TABLA -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">
                                Solicitante</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Fecha
                            </th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Estado
                            </th>
                            <th
                                class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider text-right">
                                Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($solicitudes)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-10 text-center text-gray-400 italic">No hay solicitudes
                                    registradas aún.</td>
                            </tr>
                            <?php
                        else: ?>
                            <?php foreach ($solicitudes as $s): ?>
                                <tr class="hover:bg-emerald-50/30 transition-colors" data-date="<?php echo date('Y-m-d', strtotime($s['fecha'])); ?>">
                                    <td class="px-8 py-6 font-medium text-gray-900">#<?php echo $s['id']; ?></td>
                                    <td class="px-8 py-6 font-semibold text-brand-dark">
                                        <?php echo htmlspecialchars($s['nombre']); ?>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span
                                            class="px-3 py-1 bg-brand-soft text-brand-main text-xs font-bold rounded-full uppercase border border-brand-light">
                                            <?php echo htmlspecialchars($s['tipo']); ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-gray-600"><?php echo date('d M, Y', strtotime($s['fecha'])); ?>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php
                                        // Visualización de estados con badges - (Cambio Realizado)
                                        $est = $s['estado'] ?? 'revision';
                                        $color = 'bg-amber-100 text-amber-700 border-amber-200';
                                        $txt = 'En Revisión';
                                        if ($est === 'aprobado') {
                                            $color = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                            $txt = 'Aprobado';
                                        } elseif ($est === 'rechazado') {
                                            $color = 'bg-red-100 text-red-700 border-red-200';
                                            $txt = 'Rechazado';
                                        }
                                        ?>
                                        <span
                                            class="inline-flex items-center px-3 py-1 <?php echo $color; ?> text-[10px] font-black rounded-full uppercase border shadow-sm">
                                            <?php if ($est === 'aprobado'): ?>
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            <?php elseif ($est === 'rechazado'): ?>
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-3 h-3 mr-1 animate-pulse" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            <?php endif; ?>
                                            <?php echo $txt; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <a href="detalle.php?id=<?php echo $s['id']; ?>"
                                            class="inline-flex items-center text-brand-main font-bold hover:text-brand-hover group">
                                            Detalles
                                            <svg class="ml-1 w-4 h-4 transform group-hover:translate-x-1 transition-transform"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            endforeach; ?>
                            <?php
                        endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-sm text-gray-500">
                Mostrando <?php echo count($solicitudes); ?> solicitudes encontradas.
            </div>
        </div>

    </main>

</body>

</html>