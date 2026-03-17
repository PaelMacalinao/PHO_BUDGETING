<?php
/**
<<<<<<< HEAD
 * PHO Budgeting System — Dashboard & History Records
 * 2026 Conso Proposal
 *
 * Tech: PHP 8 + PDO | Tailwind CSS | FontAwesome | DataTables.net | SweetAlert2
=======
 * PHO Budgeting System — Dashboard & Budget Proposals List
>>>>>>> 0e79cfd5ff954df6e3431363895ba330bd79af01
 */
require_once __DIR__ . '/config.php';

$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';

try {
    $pdo = getConnection();

    $rows = $pdo->query("
        SELECT bp.id, bp.ppa_description, bp.target_total, bp.total_allocation, bp.created_at,
               pu.program_name,
               ac.account_code, ac.account_title, ac.expense_class,
               fs.fund_name,
               un.unit_name
        FROM   tbl_budget_proposals bp
        JOIN   tbl_programs_units pu ON bp.program_id     = pu.id
        JOIN   tbl_account_codes ac  ON bp.account_id     = ac.id
        JOIN   tbl_fund_sources  fs  ON bp.fund_source_id = fs.id
        JOIN   tbl_units         un  ON bp.unit_id        = un.id
        ORDER BY bp.created_at DESC
    ")->fetchAll();

    $programs    = $pdo->query("SELECT DISTINCT program_name FROM tbl_programs_units ORDER BY program_name")->fetchAll(PDO::FETCH_COLUMN);
    $fundSources = $pdo->query("SELECT DISTINCT fund_name FROM tbl_fund_sources ORDER BY fund_name")->fetchAll(PDO::FETCH_COLUMN);
    $unitNames   = $pdo->query("SELECT DISTINCT unit_name FROM tbl_units ORDER BY unit_name")->fetchAll(PDO::FETCH_COLUMN);
    $expClasses  = ['MOOE', 'CO', 'PS'];
} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    $rows = [];
    $programs = $fundSources = $unitNames = $expClasses = [];
    $dbError = true;
}

<<<<<<< HEAD
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
    <title>Dashboard — 2026 Conso Proposal</title>
=======
$totalProposals  = count($rows);
$totalTargets    = array_sum(array_column($rows, 'target_total'));
$totalAllocation = array_sum(array_column($rows, 'total_allocation'));
$uniquePrograms  = count(array_unique(array_column($rows, 'program_name')));
>>>>>>> 0e79cfd5ff954df6e3431363895ba330bd79af01

require_once __DIR__ . '/includes/header.php';
?>

<<<<<<< HEAD
<!-- Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="bg-brand-100 text-brand-600 w-11 h-11 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-file-lines text-lg"></i>
        </div>
        <div>
            <span class="block text-2xl font-bold text-gray-800"><?= number_format($totalProposals) ?></span>
            <span class="block text-xs text-gray-400">Total Proposals</span>
=======
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {50:'#f0faf3',100:'#d4f0dc',200:'#aae0bc',300:'#72c990',400:'#3fb068',500:'#14864a',600:'#0b4d26',700:'#093f1f',800:'#073218',900:'#052611'},
                        gold: {50:'#fef9e7',100:'#fdf0c4',200:'#fbe59d',300:'#f9d875',400:'#f9c93e',500:'#f9ba15',600:'#d9a00e',700:'#b3830b'},
                    }
                }
            }
        }
    </script>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* PHO Brand Theme — Provincial Health Office */
        .bg-custom-green { background-color: #0b4d26; }

        .dataTables_wrapper .dataTables_filter input { font-size: .875rem; }
        .dataTables_wrapper .dataTables_length select { font-size: .875rem; }

        table.dataTable thead th {
            font-size: .75rem; text-transform: uppercase; letter-spacing: .05em;
            color: #fff !important; background: #0b4d26 !important;
            border-bottom: 2px solid #093f1f !important;
            padding: .85rem .5rem;
        }
        table.dataTable tbody td { font-size: .875rem; vertical-align: middle; }
        table.dataTable tbody tr:hover { background: #f0faf3 !important; }

        .dataTables_wrapper .pagination .page-item.active .page-link {
            background-color: #0b4d26 !important; border-color: #0b4d26 !important; color: #fff !important;
        }
        .dataTables_wrapper .pagination .page-link {
            color: #0b4d26; border-radius: .375rem; margin: 0 2px; font-size: .85rem;
        }
        .dataTables_wrapper .pagination .page-link:hover {
            background-color: #f0faf3; border-color: #aae0bc;
        }
        .dataTables_wrapper .dataTables_info { font-size: .8rem; color: #9ca3af; }

        .dt-bootstrap5 { font-family: inherit; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #aae0bc; border-radius: 4px; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans text-gray-700">

<!-- ═══════════════════════════════════════════
     TOP NAV
     ═══════════════════════════════════════════ -->
<nav class="bg-brand-600 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
            <div class="bg-gold-500 text-brand-900 w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold shadow">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <span class="text-lg font-semibold text-white tracking-tight">2026 Conso Proposal</span>
                <span class="hidden sm:inline text-xs text-brand-200 ml-2">Provincial Health Office</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm text-brand-100">
            <span class="hidden md:inline"><i class="fa-regular fa-calendar mr-1"></i> FY 2026</span>
            <span class="bg-gold-500 text-brand-900 px-3 py-1 rounded-full text-xs font-medium">
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
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 hover:ring-2 hover:ring-gold-500 transition shadow-md whitespace-nowrap">
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
            <div class="bg-gold-100 text-gold-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-bullseye text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800"><?= number_format(array_sum(array_column($rows, 'total_target'))) ?></span>
                <span class="block text-xs text-gray-400">Total Targets</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="bg-brand-100 text-brand-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-peso-sign text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800">₱<?= number_format(array_sum(array_column($rows, 'total_allocation')), 2) ?></span>
                <span class="block text-xs text-gray-400">Total Allocation</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="bg-gold-100 text-gold-600 w-11 h-11 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-building text-lg"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-800"><?= count($units) ?></span>
                <span class="block text-xs text-gray-400">Active Units</span>
            </div>
>>>>>>> 232a0ccf1171d35af27e82becc4dd6961bc6f867
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="bg-emerald-100 text-emerald-600 w-11 h-11 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-bullseye text-lg"></i>
        </div>
<<<<<<< HEAD
        <div>
            <span class="block text-2xl font-bold text-gray-800"><?= number_format($totalTargets) ?></span>
            <span class="block text-xs text-gray-400">Total Targets</span>
=======

        <!-- Data Table -->
        <div class="px-6 pb-6 pt-2 overflow-x-auto">
            <table id="proposalsTable" class="w-full text-left">
                <thead>
                    <tr class="bg-custom-green text-white">
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
>>>>>>> 232a0ccf1171d35af27e82becc4dd6961bc6f867
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="bg-amber-100 text-amber-600 w-11 h-11 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-peso-sign text-lg"></i>
        </div>
        <div>
            <span class="block text-2xl font-bold text-gray-800"><?= peso($totalAllocation) ?></span>
            <span class="block text-xs text-gray-400">Total Allocation</span>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="bg-violet-100 text-violet-600 w-11 h-11 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-building text-lg"></i>
        </div>
        <div>
            <span class="block text-2xl font-bold text-gray-800"><?= $uniquePrograms ?></span>
            <span class="block text-xs text-gray-400">Active Programs</span>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

<<<<<<< HEAD
    <!-- Action Bar -->
    <div class="px-6 pt-6 pb-2 flex flex-col sm:flex-row sm:items-end gap-4">
        <div>
            <label for="filterProgram" class="block text-xs font-medium text-gray-500 mb-1">Program (PPA)</label>
            <select id="filterProgram" class="w-full sm:w-52 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                <option value="">All Programs</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= e($p) ?>"><?= e($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filterUnit" class="block text-xs font-medium text-gray-500 mb-1">Unit</label>
            <select id="filterUnit" class="w-full sm:w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                <option value="">All Units</option>
                <?php foreach ($unitNames as $u): ?>
                    <option value="<?= e($u) ?>"><?= e($u) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filterFund" class="block text-xs font-medium text-gray-500 mb-1">Fund Source</label>
            <select id="filterFund" class="w-full sm:w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                <option value="">All Funds</option>
                <?php foreach ($fundSources as $f): ?>
                    <option value="<?= e($f) ?>"><?= e($f) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filterExpense" class="block text-xs font-medium text-gray-500 mb-1">Expense Class</label>
            <select id="filterExpense" class="w-full sm:w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                <option value="">All</option>
                <?php foreach ($expClasses as $ec): ?>
                    <option value="<?= e($ec) ?>"><?= e($ec) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2 ml-auto">
            <button id="btnReset" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 transition px-3 py-2">
                <i class="fa-solid fa-rotate-left text-xs"></i> Reset
            </button>
            <a href="create.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-md whitespace-nowrap">
                <i class="fa-solid fa-plus text-xs"></i> New Proposal
=======
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
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 hover:ring-2 hover:ring-gold-500 transition shadow-md">
                <i class="fa-solid fa-plus text-xs"></i> Create First Proposal
>>>>>>> 232a0ccf1171d35af27e82becc4dd6961bc6f867
            </a>
        </div>
    </div>

    <?php if (!empty($rows)): ?>
    <!-- Data Table -->
    <div class="px-6 pb-6 pt-2 overflow-x-auto">
        <table id="proposalsTable" class="w-full text-left" style="min-width:900px">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="py-3 px-2">ID</th>
                    <th class="py-3 px-2">PPA Description</th>
                    <th class="py-3 px-2">Program</th>
                    <th class="py-3 px-2">Unit</th>
                    <th class="py-3 px-2">Expense Class</th>
                    <th class="py-3 px-2">Fund Source</th>
                    <th class="py-3 px-2 text-right">Target</th>
                    <th class="py-3 px-2 text-right">Allocation</th>
                    <th class="py-3 px-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr class="border-b border-gray-50">
                    <td class="py-3 px-2 font-mono text-xs text-gray-400">#<?= (int)$r['id'] ?></td>
                    <td class="py-3 px-2 font-medium text-gray-800 max-w-xs truncate"><?= e($r['ppa_description']) ?></td>
                    <td class="py-3 px-2"><span class="inline-block bg-brand-50 text-brand-700 text-xs font-medium px-2.5 py-1 rounded-full"><?= e($r['program_name']) ?></span></td>
                    <td class="py-3 px-2"><span class="inline-block bg-violet-50 text-violet-700 text-xs font-medium px-2.5 py-1 rounded-full"><?= e($r['unit_name']) ?></span></td>
                    <td class="py-3 px-2"><span class="inline-block bg-amber-50 text-amber-700 text-xs font-medium px-2.5 py-1 rounded-full"><?= e($r['expense_class']) ?></span></td>
                    <td class="py-3 px-2"><span class="inline-block bg-emerald-50 text-emerald-700 text-xs font-medium px-2.5 py-1 rounded-full"><?= e($r['fund_name']) ?></span></td>
                    <td class="py-3 px-2 text-right font-semibold text-gray-700"><?= number_format((int)$r['target_total']) ?></td>
                    <td class="py-3 px-2 text-right font-semibold text-emerald-700"><?= peso((float)$r['total_allocation']) ?></td>
                    <td class="py-3 px-2 text-center whitespace-nowrap">
                        <a href="view.php?id=<?= (int)$r['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-brand-50 text-brand-700 text-xs font-medium hover:bg-brand-100 transition" title="View">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?= (int)$r['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button onclick="deleteProposal(<?= (int)$r['id'] ?>)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition" title="Delete">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="px-6 py-20 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <i class="fa-solid fa-folder-open text-4xl text-gray-300"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-600 mb-2">No Budget Proposals Yet</h2>
        <p class="text-sm text-gray-400 mb-6 max-w-md mx-auto">Get started by creating your first proposal.</p>
        <a href="create.php" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Create First Proposal
        </a>
        <?php if (isset($dbError)): ?>
        <p class="mt-4 text-xs text-red-400"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Could not connect to the database.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
$(function () {
    if (!$('#proposalsTable').length) return;

    const table = $('#proposalsTable').DataTable({
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        order: [],
        language: {
            search: '', searchPlaceholder: 'Search proposals…',
            lengthMenu: 'Show _MENU_',
            info: 'Showing _START_–_END_ of _TOTAL_',
            emptyTable: 'No matching proposals found.',
        },
        columnDefs: [
            { orderable: false, targets: [8] },
            { className: 'whitespace-nowrap', targets: '_all' },
        ],
    });

    function applyFilters() {
        const esc = $.fn.dataTable.util.escapeRegex;
        const prog = $('#filterProgram').val();
        const unit = $('#filterUnit').val();
        const fund = $('#filterFund').val();
        const exp  = $('#filterExpense').val();
        table.column(2).search(prog ? '^' + esc(prog) + '$' : '', true, false);
        table.column(3).search(unit ? '^' + esc(unit) + '$' : '', true, false);
        table.column(4).search(exp  ? '^' + esc(exp)  + '$' : '', true, false);
        table.column(5).search(fund ? '^' + esc(fund) + '$' : '', true, false);
        table.draw();
    }

    $('#filterProgram, #filterUnit, #filterFund, #filterExpense').on('change', applyFilters);
    $('#btnReset').on('click', function () {
        $('#filterProgram, #filterUnit, #filterFund, #filterExpense').val('');
        table.search('').columns().search('').draw();
    });
});

function deleteProposal(id) {
    Swal.fire({
        title: 'Delete Proposal #' + id + '?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('delete_proposal.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({icon:'success', title:'Deleted!', text: data.message, confirmButtonColor:'#4f46e5'})
                     .then(() => location.reload());
            } else {
                Swal.fire({icon:'error', title:'Error', text: data.message, confirmButtonColor:'#ef4444'});
            }
        })
        .catch(() => Swal.fire({icon:'error', title:'Network Error', text:'Could not reach the server.'}));
    });
}
</script>
