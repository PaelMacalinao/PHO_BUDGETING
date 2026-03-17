<?php
/**
 * PHO Budgeting System — 2026 Consolidated Budget Proposal
 * Single-file data-entry wizard (index.php)
 *
 * Tech  : PHP 8 + PDO (prepared statements) | Tailwind CSS | FontAwesome | SweetAlert2
 * Layout: 4-step wizard with auto-computed totals
 */

// ──────────────────────────────────────────────
// 1. DATABASE CONNECTION
// ──────────────────────────────────────────────
$DB_HOST = '127.0.0.1';
$DB_NAME = 'pho_budgeting';
$DB_USER = 'root';
$DB_PASS = '';
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
// 2. HANDLE FORM SUBMISSION (POST)
// ──────────────────────────────────────────────
$response = null; // Will hold JSON-ready response for SweetAlert

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // --- Sanitise & collect ------------------------------------------------
        $fields = [
            'program_project', 'account_code', 'account_title', 'performance_indicator',
            'q1_target', 'q2_target', 'q3_target', 'q4_target',
            'jan', 'feb', 'mar', 'apr', 'may', 'jun',
            'jul', 'aug', 'sep', 'oct', 'nov', 'dec_amt',
            'unit', 'expense_class', 'fund_source', 'lbp_code', 'justification',
        ];

        $data = [];
        foreach ($fields as $f) {
            $data[$f] = trim($_POST[$f] ?? '');
        }

        // --- Basic validation --------------------------------------------------
        $textRequired = [
            'program_project', 'account_code', 'account_title',
            'performance_indicator', 'unit', 'expense_class',
            'fund_source', 'justification',
        ];
        foreach ($textRequired as $r) {
            if ($data[$r] === '') {
                throw new InvalidArgumentException("The field '{$r}' is required.");
            }
        }

        // Whitelist ENUMs
        $allowedUnits   = ['PHO CLINIC', 'ADMINISTRATIVE SUPPORT', 'ORAL HEALTH PROGRAM', 'PESU'];
        $allowedExpense = ['MOOE', 'CAPITAL OUTLAY', 'PERSONAL SERVICES'];
        $allowedFund    = ['GENERAL FUND', 'SPECIAL PROJECT'];

        if (!in_array($data['unit'], $allowedUnits, true)) {
            throw new InvalidArgumentException('Invalid unit selected.');
        }
        if (!in_array($data['expense_class'], $allowedExpense, true)) {
            throw new InvalidArgumentException('Invalid expense class selected.');
        }
        if (!in_array($data['fund_source'], $allowedFund, true)) {
            throw new InvalidArgumentException('Invalid fund source selected.');
        }

        // Cast numeric values
        $quarters = ['q1_target', 'q2_target', 'q3_target', 'q4_target'];
        foreach ($quarters as $q) {
            $data[$q] = max(0, (int)$data[$q]);
        }
        $data['total_target'] = $data['q1_target'] + $data['q2_target'] + $data['q3_target'] + $data['q4_target'];

        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec_amt'];
        $totalAlloc = 0;
        foreach ($months as $m) {
            $data[$m] = max(0, round((float)$data[$m], 2));
            $totalAlloc += $data[$m];
        }
        $data['total_allocation'] = round($totalAlloc, 2);

        // --- INSERT ------------------------------------------------------------
        $pdo = getConnection();
        $sql = "INSERT INTO budget_proposals (
                    program_project, account_code, account_title, performance_indicator,
                    q1_target, q2_target, q3_target, q4_target, total_target,
                    jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec_amt, total_allocation,
                    unit, expense_class, fund_source, lbp_code, justification
                ) VALUES (
                    :program_project, :account_code, :account_title, :performance_indicator,
                    :q1_target, :q2_target, :q3_target, :q4_target, :total_target,
                    :jan, :feb, :mar, :apr, :may, :jun, :jul, :aug, :sep, :oct, :nov, :dec_amt, :total_allocation,
                    :unit, :expense_class, :fund_source, :lbp_code, :justification
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':program_project'       => $data['program_project'],
            ':account_code'          => $data['account_code'],
            ':account_title'         => $data['account_title'],
            ':performance_indicator' => $data['performance_indicator'],
            ':q1_target'             => $data['q1_target'],
            ':q2_target'             => $data['q2_target'],
            ':q3_target'             => $data['q3_target'],
            ':q4_target'             => $data['q4_target'],
            ':total_target'          => $data['total_target'],
            ':jan'                   => $data['jan'],
            ':feb'                   => $data['feb'],
            ':mar'                   => $data['mar'],
            ':apr'                   => $data['apr'],
            ':may'                   => $data['may'],
            ':jun'                   => $data['jun'],
            ':jul'                   => $data['jul'],
            ':aug'                   => $data['aug'],
            ':sep'                   => $data['sep'],
            ':oct'                   => $data['oct'],
            ':nov'                   => $data['nov'],
            ':dec_amt'               => $data['dec_amt'],
            ':total_allocation'      => $data['total_allocation'],
            ':unit'                  => $data['unit'],
            ':expense_class'         => $data['expense_class'],
            ':fund_source'           => $data['fund_source'],
            ':lbp_code'              => $data['lbp_code'],
            ':justification'         => $data['justification'],
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Budget proposal saved successfully!', 'id' => $pdo->lastInsertId()]);
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('DB Error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'A database error occurred. Please try again later.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Proposal — 2026 Conso Proposal</title>

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

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* PHO Brand Theme — Provincial Health Office */
        .tab-panel{display:none;animation:fadeSlide .35s ease}
        .tab-panel.active{display:block}
        @keyframes fadeSlide{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

        input[type="number"]{-moz-appearance:textfield}
        input::-webkit-outer-spin-button,input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}

        /* Step indicator — brand green */
        .step-dot{transition:all .3s ease}
        .step-dot.active{background:#0b4d26;color:#fff;box-shadow:0 0 0 4px rgba(11,77,38,.25)}
        .step-dot.done{background:#0b4d26;color:#fff}
        .step-line{transition:background .3s ease}
        .step-line.done{background:#0b4d26}

        ::-webkit-scrollbar{width:6px}
        ::-webkit-scrollbar-thumb{background:#aae0bc;border-radius:4px}
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans text-gray-700">

<!-- ═══════════════════════════════════════════════════════════
     TOP NAVIGATION BAR
     ═══════════════════════════════════════════════════════════ -->
<nav class="bg-brand-600 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
            <div class="bg-gold-500 text-brand-900 w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold shadow">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <span class="text-lg font-semibold text-white tracking-tight">PHO Budgeting</span>
                <span class="hidden sm:inline text-xs text-brand-200 ml-2">Provincial Health Office</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm text-brand-100">
            <a href="index.php" class="inline-flex items-center gap-1.5 text-gold-400 hover:text-white font-medium transition"><i class="fa-solid fa-arrow-left text-xs"></i> Dashboard</a>
            <span class="hidden md:inline"><i class="fa-regular fa-calendar mr-1"></i> FY 2026</span>
            <span class="bg-gold-500 text-brand-900 px-3 py-1 rounded-full text-xs font-medium"><i class="fa-solid fa-user mr-1"></i> Staff</span>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     MAIN CONTENT
     ═══════════════════════════════════════════════════════════ -->
<main class="max-w-5xl mx-auto px-4 py-8">

    <!-- Page heading -->
    <div class="mb-8 text-center">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">2026 Consolidated Budget Proposal</h1>
        <p class="mt-1 text-sm text-gray-500">Complete the form below. All fields with <span class="text-red-500">*</span> are required.</p>
    </div>

    <!-- ── Step Indicator ─────────────────────────────── -->
    <div class="flex items-center justify-center mb-10 select-none" id="stepIndicator">
        <!-- Steps are rendered by JS -->
    </div>

    <!-- ── Form Card ──────────────────────────────────── -->
    <form id="budgetForm" novalidate autocomplete="off"
          class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

        <!-- ─── TAB 1 : Program & Account Details ───── -->
        <div class="tab-panel active p-6 sm:p-10" data-step="1">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-6">
                <span class="bg-brand-100 text-brand-700 w-8 h-8 rounded-lg flex items-center justify-center text-sm">
                    <i class="fa-solid fa-folder-open"></i>
                </span>
                Program &amp; Account Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Program/Project/Activity -->
                <div class="md:col-span-2 mb-3">
                    <label for="program_project" class="block text-sm font-semibold text-gray-700 mb-1">Program / Project / Activity <span class="text-red-500">*</span></label>
                    <input type="text" id="program_project" name="program_project" placeholder="Enter program, project, or activity" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>

                <!-- Account Code -->
                <div class="mb-3">
                    <label for="account_code" class="block text-sm font-semibold text-gray-700 mb-1">Account Code <span class="text-red-500">*</span></label>
                    <input type="text" id="account_code" name="account_code" placeholder="Enter account code" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>

                <!-- Account Title -->
                <div class="mb-3">
                    <label for="account_title" class="block text-sm font-semibold text-gray-700 mb-1">Account Title <span class="text-red-500">*</span></label>
                    <input type="text" id="account_title" name="account_title" placeholder="Enter account title" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>

                <!-- Performance Indicator -->
                <div class="md:col-span-2 mb-3">
                    <label for="performance_indicator" class="block text-sm font-semibold text-gray-700 mb-1">Performance Indicator <span class="text-red-500">*</span></label>
                    <textarea id="performance_indicator" name="performance_indicator" rows="3" placeholder="Describe the performance indicator" required
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- ─── TAB 2 : Physical Targets ──────────────── -->
        <div class="tab-panel p-6 sm:p-10" data-step="2">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-6">
                <span class="bg-brand-100 text-brand-600 w-8 h-8 rounded-lg flex items-center justify-center text-sm">
                    <i class="fa-solid fa-bullseye"></i>
                </span>
                Physical Targets
            </h2>
            <p class="text-sm text-gray-500 mb-6">Enter the quarterly physical targets. The total is auto-computed.</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-center">
                    <span class="block text-xs font-medium text-gray-400 mb-2">Q1 Target</span>
                    <input type="number" name="q1_target" min="0" value="0"
                           class="quarter-input w-full text-center text-lg font-semibold border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-center">
                    <span class="block text-xs font-medium text-gray-400 mb-2">Q2 Target</span>
                    <input type="number" name="q2_target" min="0" value="0"
                           class="quarter-input w-full text-center text-lg font-semibold border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-center">
                    <span class="block text-xs font-medium text-gray-400 mb-2">Q3 Target</span>
                    <input type="number" name="q3_target" min="0" value="0"
                           class="quarter-input w-full text-center text-lg font-semibold border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-center">
                    <span class="block text-xs font-medium text-gray-400 mb-2">Q4 Target</span>
                    <input type="number" name="q4_target" min="0" value="0"
                           class="quarter-input w-full text-center text-lg font-semibold border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>
            </div>

            <!-- Total Card -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-emerald-500 text-white w-10 h-10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <span class="block text-xs text-emerald-600 font-medium">Annual Physical Target</span>
                        <span class="block text-2xl font-bold text-emerald-800" id="totalTarget">0</span>
                    </div>
                </div>
                <span class="text-xs text-emerald-500 italic">Auto-computed</span>
            </div>
        </div>

        <!-- ─── TAB 3 : Financial Allocation ──────────── -->
        <div class="tab-panel p-6 sm:p-10" data-step="3">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-6">
                <span class="bg-gold-100 text-gold-600 w-8 h-8 rounded-lg flex items-center justify-center text-sm">
                    <i class="fa-solid fa-coins"></i>
                </span>
                Financial Allocation
            </h2>
            <p class="text-sm text-gray-500 mb-6">Enter the monthly budget allocation in pesos. The total is auto-computed.</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                <?php
                $monthLabels = [
                    'jan' => 'January', 'feb' => 'February', 'mar' => 'March',
                    'apr' => 'April',   'may' => 'May',      'jun' => 'June',
                    'jul' => 'July',    'aug' => 'August',   'sep' => 'September',
                    'oct' => 'October', 'nov' => 'November', 'dec_amt' => 'December',
                ];
                foreach ($monthLabels as $key => $label): ?>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <span class="block text-xs font-medium text-gray-400 mb-2"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" min="0" step="0.01" value="0.00"
                               class="month-input w-full pl-7 pr-2 text-right text-sm font-medium border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Total Card -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-500 text-white w-10 h-10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-peso-sign"></i>
                    </div>
                    <div>
                        <span class="block text-xs text-amber-600 font-medium">Total Annual Allocation</span>
                        <span class="block text-2xl font-bold text-amber-800" id="totalAllocation">₱ 0.00</span>
                    </div>
                </div>
                <span class="text-xs text-amber-500 italic">Auto-computed</span>
            </div>
        </div>

        <!-- ─── TAB 4 : Classifications ───────────────── -->
        <div class="tab-panel p-6 sm:p-10" data-step="4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-6">
                <span class="bg-gold-100 text-gold-600 w-8 h-8 rounded-lg flex items-center justify-center text-sm">
                    <i class="fa-solid fa-tags"></i>
                </span>
                Classifications
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Unit -->
                <div class="mb-3">
                    <label for="unit" class="block text-sm font-semibold text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                    <select id="unit" name="unit" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">— Select Unit —</option>
                        <option value="PHO CLINIC">PHO CLINIC</option>
                        <option value="ADMINISTRATIVE SUPPORT">ADMINISTRATIVE SUPPORT</option>
                        <option value="ORAL HEALTH PROGRAM">ORAL HEALTH PROGRAM</option>
                        <option value="PESU">PESU</option>
                    </select>
                </div>

                <!-- Expense Class -->
                <div class="mb-3">
                    <label for="expense_class" class="block text-sm font-semibold text-gray-700 mb-1">MOOE / CO <span class="text-red-500">*</span></label>
                    <select id="expense_class" name="expense_class" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">— Select Expense Class —</option>
                        <option value="MOOE">MOOE</option>
                        <option value="CAPITAL OUTLAY">CAPITAL OUTLAY</option>
                        <option value="PERSONAL SERVICES">PERSONAL SERVICES</option>
                    </select>
                </div>

                <!-- Fund Source -->
                <div class="mb-3">
                    <label for="fund_source" class="block text-sm font-semibold text-gray-700 mb-1">General Fund / Special Project <span class="text-red-500">*</span></label>
                    <select id="fund_source" name="fund_source" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">— Select Fund Source —</option>
                        <option value="GENERAL FUND">GENERAL FUND</option>
                        <option value="SPECIAL PROJECT">SPECIAL PROJECT</option>
                    </select>
                </div>

                <!-- LBP Code -->
                <div class="mb-3">
                    <label for="lbp_code" class="block text-sm font-semibold text-gray-700 mb-1">LBP 4 Code Gen Fund</label>
                    <input type="text" id="lbp_code" name="lbp_code" placeholder="Enter LBP 4 code"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
                </div>

                <!-- Justification -->
                <div class="md:col-span-2 mb-3">
                    <label for="justification" class="block text-sm font-semibold text-gray-700 mb-1">Justification <span class="text-red-500">*</span></label>
                    <textarea id="justification" name="justification" rows="4" placeholder="Provide justification for this budget proposal" required
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             NAVIGATION BUTTONS
             ═══════════════════════════════════════════════ -->
        <div class="bg-gray-50 border-t border-gray-100 px-6 sm:px-10 py-5 flex items-center justify-between">
            <button type="button" id="btnPrev"
                    class="hidden inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-100 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i> Previous
            </button>
            <div class="flex-1"></div>
            <button type="button" id="btnNext"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 hover:ring-2 hover:ring-gold-500 transition shadow-md">
                Next <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
            <button type="submit" id="btnSubmit"
                    class="hidden inline-flex items-center gap-2 px-7 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 hover:ring-2 hover:ring-gold-500 transition shadow-md">
                <i class="fa-solid fa-paper-plane text-xs"></i> Submit Proposal
            </button>
        </div>

    </form>

    <p class="text-center text-xs text-gray-400 mt-8">&copy; <?= date('Y') ?> Provincial Health Office — Budget Management System</p>
</main>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT — Wizard, Auto-calc, Submission
     ═══════════════════════════════════════════════════════════ -->
<script>
(() => {
    'use strict';

    // ── Constants ─────────────────────────────────
    const STEPS = [
        { label: 'Details',     icon: 'fa-folder-open' },
        { label: 'Targets',     icon: 'fa-bullseye' },
        { label: 'Allocation',  icon: 'fa-coins' },
        { label: 'Classify',    icon: 'fa-tags' },
    ];

    let currentStep = 1;
    const totalSteps = STEPS.length;

    // ── DOM refs ──────────────────────────────────
    const form          = document.getElementById('budgetForm');
    const panels        = document.querySelectorAll('.tab-panel');
    const btnPrev       = document.getElementById('btnPrev');
    const btnNext       = document.getElementById('btnNext');
    const btnSubmit     = document.getElementById('btnSubmit');
    const indicator     = document.getElementById('stepIndicator');
    const quarterInputs = document.querySelectorAll('.quarter-input');
    const monthInputs   = document.querySelectorAll('.month-input');

    // ── Build step indicator ──────────────────────
    function buildIndicator() {
        let html = '';
        STEPS.forEach((s, i) => {
            const num = i + 1;
            if (i > 0) html += `<div class="step-line w-8 sm:w-16 h-1 rounded bg-gray-200 mx-1" data-line="${num}"></div>`;
            html += `
                <div class="flex flex-col items-center gap-1 cursor-pointer" data-goto="${num}">
                    <div class="step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold border-2 border-gray-200 bg-white text-gray-400" data-dot="${num}">
                        <i class="fa-solid ${s.icon}"></i>
                    </div>
                    <span class="text-[10px] sm:text-xs font-medium text-gray-400" data-label="${num}">${s.label}</span>
                </div>`;
        });
        indicator.innerHTML = html;

        // Click on step dots to navigate (only allow going to completed steps or next)
        indicator.querySelectorAll('[data-goto]').forEach(el => {
            el.addEventListener('click', () => {
                const target = parseInt(el.dataset.goto);
                if (target < currentStep) goTo(target);
            });
        });
    }

    function updateIndicator() {
        for (let i = 1; i <= totalSteps; i++) {
            const dot   = indicator.querySelector(`[data-dot="${i}"]`);
            const label = indicator.querySelector(`[data-label="${i}"]`);
            const line  = indicator.querySelector(`[data-line="${i}"]`);

            dot.classList.remove('active', 'done');
            dot.classList.add('border-gray-200', 'bg-white', 'text-gray-400');
            label.classList.remove('text-brand-600', 'text-brand-500');
            label.classList.add('text-gray-400');

            if (i < currentStep) {
                dot.classList.remove('border-gray-200', 'bg-white', 'text-gray-400');
                dot.classList.add('done');
                label.classList.remove('text-gray-400');
                label.classList.add('text-brand-500');
            } else if (i === currentStep) {
                dot.classList.remove('border-gray-200', 'bg-white', 'text-gray-400');
                dot.classList.add('active');
                label.classList.remove('text-gray-400');
                label.classList.add('text-brand-600');
            }

            if (line) {
                line.classList.remove('done');
                line.classList.add('bg-gray-200');
                if (i <= currentStep) {
                    line.classList.remove('bg-gray-200');
                    line.classList.add('done');
                }
            }
        }
    }

    // ── Panel navigation ──────────────────────────
    function goTo(step) {
        currentStep = step;
        panels.forEach(p => p.classList.remove('active'));
        document.querySelector(`.tab-panel[data-step="${step}"]`).classList.add('active');

        btnPrev.classList.toggle('hidden', step === 1);
        btnNext.classList.toggle('hidden', step === totalSteps);
        btnSubmit.classList.toggle('hidden', step !== totalSteps);

        updateIndicator();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Step validation ───────────────────────────
    function validateStep(step) {
        const panel = document.querySelector(`.tab-panel[data-step="${step}"]`);
        const required = panel.querySelectorAll('[required]');
        let valid = true;

        required.forEach(el => {
            el.classList.remove('border-red-400', 'ring-2', 'ring-red-200');
            if (!el.value.trim()) {
                el.classList.add('border-red-400', 'ring-2', 'ring-red-200');
                valid = false;
            }
        });

        if (!valid) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in all required fields before proceeding.',
                confirmButtonColor: '#0b4d26',
            });
        }
        return valid;
    }

    // ── Navigation handlers ───────────────────────
    btnNext.addEventListener('click', () => {
        if (validateStep(currentStep) && currentStep < totalSteps) goTo(currentStep + 1);
    });
    btnPrev.addEventListener('click', () => {
        if (currentStep > 1) goTo(currentStep - 1);
    });

    // ── Auto-compute: Physical Targets ────────────
    function computeTargets() {
        let total = 0;
        quarterInputs.forEach(inp => { total += parseInt(inp.value) || 0; });
        document.getElementById('totalTarget').textContent = total.toLocaleString();
    }
    quarterInputs.forEach(inp => inp.addEventListener('input', computeTargets));

    // ── Auto-compute: Financial Allocation ────────
    function computeAllocation() {
        let total = 0;
        monthInputs.forEach(inp => { total += parseFloat(inp.value) || 0; });
        document.getElementById('totalAllocation').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    monthInputs.forEach(inp => inp.addEventListener('input', computeAllocation));

    // ── Form submission (AJAX) ────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validate last step
        if (!validateStep(currentStep)) return;

        // Confirm dialog
        const confirm = await Swal.fire({
            title: 'Submit Budget Proposal?',
            text: 'Please review all details before submitting.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-paper-plane"></i> Yes, Submit',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0b4d26',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        });
        if (!confirm.isConfirmed) return;

        // Disable button
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting…';

        try {
            const body = new FormData(form);
            const res  = await fetch(window.location.href, { method: 'POST', body });
            const json = await res.json();

            if (json.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Proposal Saved!',
                    html: `Your budget proposal has been recorded.<br><small class="text-gray-400">Reference ID: <strong>#${json.id}</strong></small>`,
                    confirmButtonColor: '#0b4d26',
                });
                window.location.href = 'index.php';
            } else {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: json.message, confirmButtonColor: '#ef4444' });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach the server. Please try again.', confirmButtonColor: '#ef4444' });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> Submit Proposal';
        }
    });

    // ── Remove red border on focus ────────────────
    form.querySelectorAll('[required]').forEach(el => {
        el.addEventListener('focus', () => el.classList.remove('border-red-400', 'ring-2', 'ring-red-200'));
    });

    // ── Keyboard navigation (Enter = Next) ────────
    form.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            if (currentStep < totalSteps) btnNext.click();
        }
    });

    // ── Init ──────────────────────────────────────
    buildIndicator();
    updateIndicator();
    computeTargets();
    computeAllocation();
})();
</script>

</body>
</html>
