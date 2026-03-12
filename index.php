<?php
/**
 * PHO Budgeting System — Dashboard & History Records
 * 2026 Conso Proposal V3
 *
 * Tech: PHP 8 + PDO | Tailwind CSS | FontAwesome | DataTables.net | SweetAlert2
 */

// ──────────────────────────────────────────────
// DATABASE CONNECTION
// ──────────────────────────────────────────────
$DB_HOST    = '127.0.0.1';
$DB_NAME    = 'pho_budgeting';
$DB_USER    = 'root';
$DB_PASS    = '';
$DB_CHARSET = 'utf8mb4';

function getConnection(): PDO
{
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ──────────────────────────────────────────────
// FETCH DATA
// ──────────────────────────────────────────────
try {
    $pdo  = getConnection();
    $rows = $pdo->query("SELECT id, program_project, unit, expense_class, total_target, total_allocation, created_at FROM budget_proposals ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    $rows = [];
    $dbError = true;
}

// Unique values for filters
$units    = array_unique(array_column($rows, 'unit'));
$expenses = array_unique(array_column($rows, 'expense_class'));
sort($units);
sort($expenses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — 2026 Conso Proposal V3</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'},
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* DataTables overrides for Tailwind look */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db; border-radius: .5rem; padding: .4rem .75rem; font-size: .875rem;
            outline: none; transition: border-color .2s;
        }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db; border-radius: .5rem; padding: .3rem .5rem; font-size: .875rem; outline: none;
        }
        table.dataTable thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
        table.dataTable tbody td { font-size: .875rem; vertical-align: middle; }
        table.dataTable tbody tr:hover { background: #f5f3ff !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important; color: #fff !important; border-radius: .5rem !important; border: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: .5rem !important; }
        .dataTables_wrapper .dataTables_info { font-size: .8rem; color: #9ca3af; }

        /* scrollbar */
        ::-webkit-scrollbar{width:6px}
        ::-webkit-scrollbar-thumb{background:#c7d2fe;border-radius:4px}
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans text-gray-700">

<!-- ═══════════════════════════════════════════
     TOP NAV
     ═══════════════════════════════════════════ -->
<nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
            <div class="bg-brand-600 text-white w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold shadow">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <span class="text-lg font-semibold text-gray-800 tracking-tight">2026 Conso Proposal V3</span>
                <span class="hidden sm:inline text-xs text-gray-400 ml-2">Provincial Health Office</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-500">
            <span class="hidden md:inline"><i class="fa-regular fa-calendar mr-1"></i> FY 2026</span>
            <span class="bg-brand-50 text-brand-700 px-3 py-1 rounded-full text-xs font-medium">
                <i class="fa-solid fa-user mr-1"></i> Staff
            </span>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════
     MAIN CONTENT
     ═══════════════════════════════════════════ -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Row -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Budget Proposals</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage and review all submitted proposals for FY 2026.</p>
        </div>
        <a href="create.php"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-md whitespace-nowrap">
            <i class="fa-solid fa-plus text-xs"></i> Create New Budget Proposal
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="bg-brand-100 text-brand-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-file-lines text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800"><?= count($rows) ?></span>
                <span class="block text-xs text-gray-400">Total Proposals</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="bg-emerald-100 text-emerald-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-bullseye text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800"><?= number_format(array_sum(array_column($rows, 'total_target'))) ?></span>
                <span class="block text-xs text-gray-400">Total Targets</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="bg-amber-100 text-amber-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-peso-sign text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800">₱<?= number_format(array_sum(array_column($rows, 'total_allocation')), 2) ?></span>
                <span class="block text-xs text-gray-400">Total Allocation</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="bg-violet-100 text-violet-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-building text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800"><?= count($units) ?></span>
                <span class="block text-xs text-gray-400">Active Units</span>
            </div>
        </div>
    </div>

    <!-- ── Table Card ──────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

        <?php if (!empty($rows)): ?>

        <!-- Custom Filters -->
        <div class="px-6 pt-6 pb-2 flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label for="filterUnit" class="block text-xs font-medium text-gray-500 mb-1">Filter by Unit</label>
                <select id="filterUnit" class="w-full sm:w-56 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                    <option value="">All Units</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1">
                <label for="filterExpense" class="block text-xs font-medium text-gray-500 mb-1">Filter by Expense Class</label>
                <select id="filterExpense" class="w-full sm:w-56 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                    <option value="">All Expense Classes</option>
                    <?php foreach ($expenses as $ex): ?>
                        <option value="<?= htmlspecialchars($ex, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($ex, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1 flex justify-end">
                <button type="button" id="btnReset" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 transition">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Reset Filters
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="px-6 pb-6 pt-2 overflow-x-auto">
            <table id="proposalsTable" class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-3 px-2">ID</th>
                        <th class="py-3 px-2">Program / Project / Activity</th>
                        <th class="py-3 px-2">Unit</th>
                        <th class="py-3 px-2">Expense Class</th>
                        <th class="py-3 px-2 text-right">Total Target</th>
                        <th class="py-3 px-2 text-right">Total Allocation</th>
                        <th class="py-3 px-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-b border-gray-50 hover:bg-brand-50/40 transition">
                        <td class="py-3 px-2 font-mono text-xs text-gray-400">#<?= (int)$r['id'] ?></td>
                        <td class="py-3 px-2 font-medium text-gray-800 max-w-xs truncate"><?= htmlspecialchars($r['program_project'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="py-3 px-2">
                            <span class="inline-block bg-brand-50 text-brand-700 text-xs font-medium px-2.5 py-1 rounded-full"><?= htmlspecialchars($r['unit'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td class="py-3 px-2">
                            <span class="inline-block bg-amber-50 text-amber-700 text-xs font-medium px-2.5 py-1 rounded-full"><?= htmlspecialchars($r['expense_class'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td class="py-3 px-2 text-right font-semibold text-gray-700"><?= number_format((int)$r['total_target']) ?></td>
                        <td class="py-3 px-2 text-right font-semibold text-emerald-700">₱<?= number_format((float)$r['total_allocation'], 2) ?></td>
                        <td class="py-3 px-2 text-center">
                            <a href="view.php?id=<?= (int)$r['id'] ?>"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-50 text-brand-700 text-xs font-medium hover:bg-brand-100 transition"
                               title="View Details">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>

        <!-- ── Empty State ─────────────────────── -->
        <div class="px-6 py-20 text-center">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <i class="fa-solid fa-folder-open text-4xl text-gray-300"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-600 mb-2">No Budget Proposals Yet</h2>
            <p class="text-sm text-gray-400 mb-6 max-w-md mx-auto">
                It looks like no budget proposals have been submitted for FY 2026. Get started by creating your first proposal.
            </p>
            <a href="create.php"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-md">
                <i class="fa-solid fa-plus text-xs"></i> Create First Proposal
            </a>
            <?php if (isset($dbError)): ?>
            <p class="mt-4 text-xs text-red-400"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Could not connect to the database. Please check your configuration.</p>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>

    <p class="text-center text-xs text-gray-400 mt-8">&copy; <?= date('Y') ?> Provincial Health Office — Budget Management System</p>
</main>

<!-- ═══════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════ -->
<!-- jQuery (DataTables dep) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables core -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(function () {
    // Bail out if no table
    if (!$('#proposalsTable').length) return;

    const table = $('#proposalsTable').DataTable({
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        order: [],                     // respect server-side order (created_at DESC)
        language: {
            search: '',
            searchPlaceholder: 'Search proposals…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ proposals',
            emptyTable: 'No matching proposals found.',
        },
        columnDefs: [
            { orderable: false, targets: [6] },          // Actions col
            { className: 'whitespace-nowrap', targets: '_all' },
        ],
    });

    // ── Custom dropdown filters ─────────────
    $('#filterUnit').on('change', function () {
        table.column(2).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
    });
    $('#filterExpense').on('change', function () {
        table.column(3).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
    });
    $('#btnReset').on('click', function () {
        $('#filterUnit').val('');
        $('#filterExpense').val('');
        table.search('').columns().search('').draw();
    });
});
</script>

</body>
</html>
