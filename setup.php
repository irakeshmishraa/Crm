<?php
/**
 * SmartLead CRM Pro - Subdirectory Setup Script
 * 
 * For installing at: yourdomain.com/crm/
 * 
 * Upload this file to public_html/crm/setup.php
 * Then visit: https://yourdomain.com/crm/setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

$basePath = dirname(__FILE__);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'run') {
    header('Content-Type: application/json');
    
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $adminName = $_POST['admin_name'] ?? '';
    $adminEmail = $_POST['admin_email'] ?? '';
    $adminPass = $_POST['admin_password'] ?? '';
    $appUrl = $_POST['app_url'] ?? '';

    $results = [];

    // Step 1: Test DB connection
    try {
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName}", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET NAMES utf8mb4");
        $results[] = "✓ Database connected";
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }

    // Step 2: Create .env
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $envContent = "APP_NAME=\"SmartLead CRM Pro\"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$appUrl}
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@" . str_replace(['https://', 'http://'], '', $appUrl) . "
MAIL_FROM_NAME=\"SmartLead CRM\"
";
    file_put_contents($basePath . '/.env', $envContent);
    $results[] = "✓ .env file created";

    // Step 3: Create storage directories
    $dirs = [
        $basePath . '/storage/app/public',
        $basePath . '/storage/framework/cache/data',
        $basePath . '/storage/framework/sessions',
        $basePath . '/storage/framework/views',
        $basePath . '/storage/logs',
        $basePath . '/bootstrap/cache',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0755, true);
    }
    $results[] = "✓ Storage directories created";

    // Step 4: Run migrations using artisan (if vendor exists)
    if (file_exists($basePath . '/vendor/autoload.php') && file_exists($basePath . '/artisan')) {
        $cwd = getcwd();
        chdir($basePath);
        
        $output = [];
        exec("php artisan migrate --force 2>&1", $output, $code);
        if ($code === 0) {
            $results[] = "✓ Migrations completed";
        } else {
            // Try with full php path
            exec("/usr/local/bin/php artisan migrate --force 2>&1", $output, $code);
            if ($code === 0) {
                $results[] = "✓ Migrations completed";
            } else {
                chdir($cwd);
                echo json_encode(['success' => false, 'message' => "Migration failed:\n" . implode("\n", $output), 'results' => $results]);
                exit;
            }
        }

        // Run seeder
        $output2 = [];
        exec("php artisan db:seed --force 2>&1", $output2, $code2);
        if ($code2 === 0) {
            $results[] = "✓ Database seeded with demo data";
        } else {
            exec("/usr/local/bin/php artisan db:seed --force 2>&1", $output2, $code2);
            if ($code2 === 0) {
                $results[] = "✓ Database seeded";
            } else {
                $results[] = "⚠ Seeder skipped (not critical)";
            }
        }
        
        chdir($cwd);
    } else {
        // No vendor - create tables manually via SQL
        $results[] = "⚠ vendor/ not found - creating tables manually";
        
        try {
            // Create essential tables
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                username VARCHAR(255) UNIQUE,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NULL,
                avatar VARCHAR(255) NULL,
                designation VARCHAR(255) NULL,
                department_id BIGINT UNSIGNED NULL,
                reporting_to BIGINT UNSIGNED NULL,
                status ENUM('active','inactive','suspended') DEFAULT 'active',
                google_id VARCHAR(255) NULL,
                google_token TEXT NULL,
                google_refresh_token TEXT NULL,
                two_factor_enabled BOOLEAN DEFAULT FALSE,
                two_factor_secret VARCHAR(255) NULL,
                two_factor_recovery_codes TEXT NULL,
                last_login_at TIMESTAMP NULL,
                last_login_ip VARCHAR(45) NULL,
                timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
                notification_preferences JSON NULL,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $results[] = "✓ Users table created";

            $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT NULL,
                is_system BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS user_role (
                user_id BIGINT UNSIGNED NOT NULL,
                role_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (user_id, role_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Insert default role
            $pdo->exec("INSERT IGNORE INTO roles (name, slug, description, is_system, created_at, updated_at) VALUES ('Super Admin', 'super-admin', 'Full system access', 1, NOW(), NOW())");
            $results[] = "✓ Roles table created";

            $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(255) PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload LONGTEXT NOT NULL,
                last_activity INT NOT NULL,
                INDEX(user_id), INDEX(last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS personal_access_tokens (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tokenable_type VARCHAR(255) NOT NULL,
                tokenable_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255) NOT NULL,
                token VARCHAR(64) UNIQUE NOT NULL,
                abilities TEXT NULL,
                last_used_at TIMESTAMP NULL,
                expires_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                INDEX(tokenable_type, tokenable_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $results[] = "✓ Session & token tables created";

        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $e->getMessage(), 'results' => $results]);
            exit;
        }
    }

    // Step 5: Create admin user
    try {
        $hashedPassword = password_hash($adminPass, PASSWORD_BCRYPT);
        
        // Check if admin already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$adminEmail]);
        if ($check->fetch()) {
            $results[] = "⚠ Admin user already exists, skipping";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, email_verified_at, designation, status, timezone, created_at, updated_at) VALUES (?, ?, 'admin', ?, NOW(), 'Administrator', 'active', 'Asia/Kolkata', NOW(), NOW())");
            $stmt->execute([$adminName, $adminEmail, $hashedPassword]);
            $userId = $pdo->lastInsertId();

            // Assign super-admin role
            $roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'super-admin' LIMIT 1")->fetchColumn();
            if ($roleId) {
                $pdo->prepare("INSERT IGNORE INTO user_role (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
            }
            $results[] = "✓ Admin account created: {$adminEmail}";
        }
    } catch (PDOException $e) {
        $results[] = "⚠ Admin creation: " . $e->getMessage();
    }

    // Step 6: Create storage link
    $storageLinkPath = $basePath . '/storage-link';
    if (!file_exists($storageLinkPath)) {
        @symlink($basePath . '/storage/app/public', $storageLinkPath);
    }

    // Mark installed
    file_put_contents($basePath . '/storage/installed', date('Y-m-d H:i:s'));
    $results[] = "✓ Installation complete!";

    echo json_encode(['success' => true, 'results' => $results, 'message' => implode("\n", $results)]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLead CRM Pro - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .setup-card { max-width: 600px; margin: auto; }
        .log-box { background: #1e1e2d; color: #10b981; font-family: monospace; font-size: 13px; padding: 15px; border-radius: 8px; min-height: 100px; white-space: pre-wrap; }
        .btn-setup { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; }
        .btn-setup:hover { background: linear-gradient(135deg, #5a67d8, #6b46c1); color: white; }
    </style>
</head>
<body class="d-flex align-items-center py-5">
<div class="setup-card w-100 px-3">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-gear-wide-connected display-3 text-primary d-block mb-3"></i>
                <h3 class="fw-bold">SmartLead CRM Pro</h3>
                <p class="text-muted">Quick Setup - Subdirectory Install</p>
                <small class="text-muted">Installing at: <?php echo 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?></small>
            </div>

            <form id="setupForm">
                <h6 class="fw-bold mb-3"><i class="bi bi-database me-2"></i>Database</h6>
                <div class="row g-2 mb-3">
                    <div class="col-8"><input type="text" name="db_host" class="form-control form-control-sm" value="localhost" placeholder="DB Host"></div>
                    <div class="col-4"><input type="number" name="db_port" class="form-control form-control-sm" value="3306" placeholder="Port"></div>
                    <div class="col-12"><input type="text" name="db_name" class="form-control form-control-sm" placeholder="Database Name (e.g. user_crm)" required></div>
                    <div class="col-6"><input type="text" name="db_user" class="form-control form-control-sm" placeholder="DB Username" required></div>
                    <div class="col-6"><input type="password" name="db_pass" class="form-control form-control-sm" placeholder="DB Password" required></div>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Admin Account</h6>
                <div class="row g-2 mb-3">
                    <div class="col-12"><input type="text" name="admin_name" class="form-control form-control-sm" placeholder="Full Name" required></div>
                    <div class="col-12"><input type="email" name="admin_email" class="form-control form-control-sm" placeholder="Email Address" required></div>
                    <div class="col-12"><input type="password" name="admin_password" class="form-control form-control-sm" placeholder="Password (min 8 chars)" minlength="8" required></div>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-globe me-2"></i>Site URL</h6>
                <div class="mb-4">
                    <input type="url" name="app_url" class="form-control form-control-sm" value="https://<?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?>" required>
                    <small class="text-muted">Your CRM will be accessible at this URL</small>
                </div>

                <button type="submit" class="btn btn-setup w-100" id="btnSetup">
                    <i class="bi bi-rocket-takeoff me-2"></i>Install Now
                </button>
            </form>

            <div id="logSection" class="mt-4 d-none">
                <h6 class="fw-bold mb-2">Installation Log:</h6>
                <div class="log-box" id="logBox">Starting installation...</div>
                <a href="login" class="btn btn-success w-100 mt-3 d-none" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
                </a>
            </div>
        </div>
    </div>
    <p class="text-center text-white-50 mt-3 small">After setup, delete this setup.php file!</p>
</div>

<script>
document.getElementById('setupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnSetup');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Installing...';
    
    document.getElementById('logSection').classList.remove('d-none');
    const log = document.getElementById('logBox');
    log.textContent = 'Connecting to database...\n';

    const data = new FormData(this);
    data.append('action', 'run');

    fetch('', { method: 'POST', body: new URLSearchParams(data) })
    .then(r => r.text())
    .then(text => {
        try {
            const res = JSON.parse(text);
            if (res.success) {
                log.textContent = res.results.join('\n');
                document.getElementById('btnLogin').classList.remove('d-none');
                btn.classList.add('d-none');
            } else {
                log.textContent = 'ERROR: ' + res.message + '\n\n' + (res.results ? res.results.join('\n') : '');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-rocket-takeoff me-2"></i>Retry';
            }
        } catch(e) {
            log.textContent = 'Server returned invalid response:\n\n' + text.substring(0, 1000);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-rocket-takeoff me-2"></i>Retry';
        }
    })
    .catch(err => {
        log.textContent = 'Network error: ' + err.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-rocket-takeoff me-2"></i>Retry';
    });
});
</script>
</body>
</html>
