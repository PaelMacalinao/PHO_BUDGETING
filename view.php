<?php
/**
 * PHO Budgeting System — Read-Only Proposal Viewer
 * 2026 Conso Proposal V3
 *
 * Tech: PHP 8 + PDO (prepared statements) | Tailwind CSS | FontAwesome
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
// FETCH RECORD
// ──────────────────────────────────────────────
$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$row  = null;

if ($id) {
    try {
        $pdo  = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM budget_proposals WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('DB Error: ' . $e->getMessage());
    }
}

// Helper: format money value
function peso(float $v): string
{
    return '₱ ' . number_format($v, 2);
}

// Helper: html-safe output
function e(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $row ? 'Proposal #' . (int)$row['id'] : 'Not Found' ?> — 2026 Conso Proposal V3</title>

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

    <style>
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
            <a href="index.php" class="inline-flex items-center gap-1.5 text-brand-600 hover:text-brand-800 font-medium transition">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to Dashboard
            </a>
            <span class="hidden md:inline"><i class="fa-regular fa-calendar mr-1"></i> FY 2026</span>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<?php if (!$row): ?>
    <!-- ── Not Found State ─────────────────── -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 px-6 py-20 text-center">
        <div class="mx-auto w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-6">
            <i class="fa-solid fa-triangle-exclamation text-3xl text-red-300"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Proposal Not Found</h2>
        <p class="text-sm text-gray-400 mb-6">The record you requested does not exist or the ID is invalid.</p>
        <a href="index.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-md">
            <i class="fa-solid fa-arrow-left text-xs"></i> Return to Dashboard
        </a>
    </div>

<?php else: ?>

    <!-- Page heading -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Proposal <span class="text-brand-600">#<?= (int)$row['id'] ?></span>
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">
                Submitted <?= date('F j, Y \a\t g:i A', strtotime($row['created_at'])) ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 text-xs font-medium px-3 py-1.5 rounded-full">
                <i class="fa-solid fa-building"></i> <?= e($row['unit']) ?>
            </span>
            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-xs font-medium px-3 py-1.5 rounded-full">
                <i class="fa-solid fa-tags"></i> <?= e($row['expense_class']) ?>
            </span>
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-medium px-3 py-1.5 rounded-full">
                <i class="fa-solid fa-coins"></i> <?= e($row['fund_source']) ?>
            </span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECTION 1: Program Details
         ═══════════════════════════════════════ -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 mb-6 overflow-hidden">
        <div class="bg-brand-50 px-6 py-4 border-b border-brand-100 flex items-center gap-3">
            <div class="bg-brand-600 text-white w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h2 class="text-base font-semibold text-brand-800">Program &amp; Account Details</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-5 gap-x-8">
                <div class="md:col-span-2">
                    <span class="block text-xs font-medium text-gray-400 mb-1">Program / Project / Activity</span>
                    <span class="block text-sm font-semibold text-gray-800"><?= e($row['program_project']) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-400 mb-1">Account Code</span>
                    <span class="block text-sm font-semibold text-gray-800"><?= e($row['account_code']) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-400 mb-1">Account Title</span>
                    <span class="block text-sm font-semibold text-gray-800"><?= e($row['account_title']) ?></span>
                </div>
                <div class="md:col-span-2">
                    <span class="block text-xs font-medium text-gray-400 mb-1">Performance Indicator</span>
                    <span class="block text-sm text-gray-700 leading-relaxed whitespace-pre-line"><?= e($row['performance_indicator']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECTION 2: Quarterly Physical Targets
         ═══════════════════════════════════════ -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 mb-6 overflow-hidden">
        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100 flex items-center gap-3">
            <div class="bg-emerald-600 text-white w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <h2 class="text-base font-semibold text-emerald-800">Quarterly Physical Targets</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                <?php
                $quarters = [
                    'q1_target' => 'Q1',
                    'q2_target' => 'Q2',
                    'q3_target' => 'Q3',
                    'q4_target' => 'Q4',
                ];
                foreach ($quarters as $col => $label): ?>
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-center">
                    <span class="block text-xs font-medium text-gray-400 mb-1"><?= $label ?> Target</span>
                    <span class="block text-2xl font-bold text-gray-800"><?= number_format((int)$row[$col]) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-emerald-500 text-white w-10 h-10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <span class="block text-xs text-emerald-600 font-medium">Annual Physical Target</span>
                        <span class="block text-2xl font-bold text-emerald-800"><?= number_format((int)$row['total_target']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECTION 3: Monthly Financial Allocation
         ═══════════════════════════════════════ -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 mb-6 overflow-hidden">
        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex items-center gap-3">
            <div class="bg-amber-500 text-white w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                <i class="fa-solid fa-coins"></i>
            </div>
            <h2 class="text-base font-semibold text-amber-800">Monthly Financial Allocation</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-5">
                <?php
                $months = [
                    'jan' => 'January',   'feb' => 'February',  'mar' => 'March',
                    'apr' => 'April',     'may' => 'May',       'jun' => 'June',
                    'jul' => 'July',      'aug' => 'August',    'sep' => 'September',
                    'oct' => 'October',   'nov' => 'November',  'dec_amt' => 'December',
                ];
                foreach ($months as $col => $label): ?>
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <span class="block text-xs font-medium text-gray-400 mb-1"><?= $label ?></span>
                    <span class="block text-sm font-bold text-gray-800"><?= peso((float)$row[$col]) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-500 text-white w-10 h-10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-peso-sign"></i>
                    </div>
                    <div>
                        <span class="block text-xs text-amber-600 font-medium">Total Annual Allocation</span>
                        <span class="block text-2xl font-bold text-amber-800"><?= peso((float)$row['total_allocation']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECTION 4: Classifications
         ═══════════════════════════════════════ -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 mb-6 overflow-hidden">
        <div class="bg-violet-50 px-6 py-4 border-b border-violet-100 flex items-center gap-3">
            <div class="bg-violet-600 text-white w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                <i class="fa-solid fa-tags"></i>
            </div>
            <h2 class="text-base font-semibold text-violet-800">Classifications</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-5 gap-x-8">
                <div>
                    <span class="block text-xs font-medium text-gray-400 mb-1">Unit</span>
                    <span class="inline-block bg-brand-50 text-brand-700 text-sm font-semibold px-3 py-1 rounded-full"><?= e($row['unit']) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-400 mb-1">Expense Class (MOOE / CO)</span>
                    <span class="inline-block bg-amber-50 text-amber-700 text-sm font-semibold px-3 py-1 rounded-full"><?= e($row['expense_class']) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-400 mb-1">Fund Source</span>
                    <span class="inline-block bg-emerald-50 text-emerald-700 text-sm font-semibold px-3 py-1 rounded-full"><?= e($row['fund_source']) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-400 mb-1">LBP 4 Code Gen Fund</span>
                    <span class="block text-sm font-semibold text-gray-800"><?= e($row['lbp_code']) ?: '<span class="text-gray-300 italic">—</span>' ?></span>
                </div>
                <div class="md:col-span-2">
                    <span class="block text-xs font-medium text-gray-400 mb-1">Justification</span>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-line"><?= e($row['justification']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Bottom nav ───────────────────────── -->
    <div class="flex items-center justify-between">
        <a href="index.php"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-100 transition shadow-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Dashboard
        </a>
        <span class="text-xs text-gray-400">
            Last updated: <?= date('M j, Y g:i A', strtotime($row['updated_at'])) ?>
        </span>
    </div>

<?php endif; ?>

    <p class="text-center text-xs text-gray-400 mt-8">&copy; <?= date('Y') ?> Provincial Health Office — Budget Management System</p>
</main>

</body>
</html>
