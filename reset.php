<?php
/**
 * PHO Budgeting System — Factory Reset
 * Truncates the budget_proposals table to clear all test data.
 *
 * Tech: PHP 8 + PDO | Bootstrap 5 | FontAwesome | SweetAlert2
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
// HANDLE TRUNCATE (POST only)
// ──────────────────────────────────────────────
$success = false;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    session_start();
    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['reset_token'] ?? '', $token)) {
        $error = 'Invalid or expired security token. Please go back and try again.';
    } else {
        try {
            $pdo = getConnection();
            $pdo->exec('TRUNCATE TABLE budget_proposals');
            $success = true;
        } catch (PDOException $e) {
            error_log('Reset DB Error: ' . $e->getMessage());
            $error = 'A database error occurred. Please check your configuration.';
        }
    }
    // Consume the token so it can't be reused
    unset($_SESSION['reset_token']);
} else {
    // Generate CSRF token for the form
    session_start();
    $_SESSION['reset_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factory Reset — 2026 Conso Proposal V3</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

<div class="vh-100 d-flex justify-content-center align-items-center px-3">

    <?php if ($success): ?>
    <!-- ════════════════ SUCCESS STATE ════════════════ -->
    <div class="card border-success shadow" style="max-width:480px;width:100%">
        <div class="card-body text-center py-5 px-4">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                 style="width:72px;height:72px">
                <i class="fa-solid fa-check text-success" style="font-size:2rem"></i>
            </div>
            <h4 class="fw-bold text-success mb-2">Reset Complete</h4>
            <p class="text-muted mb-4">
                All budget proposal records have been deleted and auto-increment IDs have been reset.
                The system is ready for production deployment.
            </p>
            <a href="index.php" class="btn btn-success px-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'success',
                title: 'Database Reset!',
                text: 'All records have been deleted. The system is clean.',
                confirmButtonColor: '#198754',
            });
        });
    </script>

    <?php elseif ($error): ?>
    <!-- ════════════════ ERROR STATE ════════════════ -->
    <div class="card border-danger shadow" style="max-width:480px;width:100%">
        <div class="card-body text-center py-5 px-4">
            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                 style="width:72px;height:72px">
                <i class="fa-solid fa-xmark text-danger" style="font-size:2rem"></i>
            </div>
            <h4 class="fw-bold text-danger mb-2">Reset Failed</h4>
            <p class="text-muted mb-4"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <a href="reset.php" class="btn btn-outline-danger me-2">
                <i class="fa-solid fa-rotate-left me-1"></i> Try Again
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <?php else: ?>
    <!-- ════════════════ CONFIRMATION STATE ════════════════ -->
    <div class="card border-danger shadow" style="max-width:520px;width:100%">
        <div class="card-header bg-danger text-white text-center py-3">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Factory Reset Database</h5>
        </div>
        <div class="card-body text-center py-5 px-4">
            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                 style="width:72px;height:72px">
                <i class="fa-solid fa-database text-danger" style="font-size:2rem"></i>
            </div>
            <p class="text-muted mb-1 fw-semibold">Are you sure you want to delete <span class="text-danger fw-bold">ALL</span> budget proposal records?</p>
            <p class="text-muted small mb-4">
                This action <strong>cannot be undone</strong> and will reset the system for deployment.<br>
                Table <code>budget_proposals</code> will be truncated and auto-increment IDs will restart from 1.
            </p>

            <form method="POST" action="reset.php">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['reset_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-danger btn-lg px-4 mb-3 w-100">
                    <i class="fa-solid fa-trash me-2"></i> Yes, Delete All Records
                </button>
            </form>
            <a href="index.php" class="btn btn-outline-secondary px-4 w-100">
                <i class="fa-solid fa-arrow-left me-1"></i> Cancel / Back to Dashboard
            </a>
        </div>
        <div class="card-footer bg-light text-center py-2">
            <small class="text-muted"><i class="fa-solid fa-shield-halved me-1"></i> Protected by CSRF token verification</small>
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
