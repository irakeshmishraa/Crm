<?php
/**
 * SmartLead CRM Pro - Web Installer
 * 
 * HOW TO USE:
 * 1. Upload this single file to your public_html/ via DirectAdmin File Manager
 * 2. Open https://yourdomain.com/installer.php in your browser
 * 3. Follow the steps
 * 4. Delete this file after installation!
 * 
 * This installer will:
 * - Check server requirements
 * - Upload & extract CRM files
 * - Configure database
 * - Run migrations
 * - Create admin account
 * - Set permissions
 * - Clean up
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');
ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');

session_start();

$step = $_GET['step'] ?? 'welcome';
$basePath = dirname(__FILE__);
$privatePath = dirname($basePath) . '/private';
$publicPath = $basePath;

// Process AJAX/POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'check_requirements':
            echo json_encode(checkRequirements());
            exit;
            
        case 'upload_zip':
            echo json_encode(handleUpload($basePath, $privatePath));
            exit;


        case 'test_database':
            echo json_encode(testDatabase($_POST));
            exit;
            
        case 'setup_database':
            echo json_encode(setupDatabase($_POST, $privatePath));
            exit;
            
        case 'create_admin':
            echo json_encode(createAdmin($_POST, $privatePath));
            exit;
            
        case 'finalize':
            echo json_encode(finalize($privatePath, $publicPath));
            exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

// ========== HELPER FUNCTIONS ==========

function checkRequirements(): array {
    $checks = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'Mbstring Extension' => extension_loaded('mbstring'),
        'OpenSSL Extension' => extension_loaded('openssl'),
        'Curl Extension' => extension_loaded('curl'),
        'JSON Extension' => extension_loaded('json'),
        'Fileinfo Extension' => extension_loaded('fileinfo'),
        'Tokenizer Extension' => extension_loaded('tokenizer'),
        'XML Extension' => extension_loaded('xml'),
        'Ctype Extension' => extension_loaded('ctype'),
        'BCMath Extension' => extension_loaded('bcmath'),
        'GD Extension' => extension_loaded('gd') || extension_loaded('imagick'),
        'ZIP Extension' => class_exists('ZipArchive'),
        'Storage Writable' => is_writable(dirname(__FILE__)),
        'Parent Dir Writable' => is_writable(dirname(dirname(__FILE__))),
    ];
    
    $allPassed = !in_array(false, $checks, true);
    return ['success' => true, 'checks' => $checks, 'all_passed' => $allPassed, 'php_version' => PHP_VERSION];
}


function handleUpload(string $basePath, string $privatePath): array {
    if (!isset($_FILES['crm_zip']) || $_FILES['crm_zip']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed. Error code: ' . ($_FILES['crm_zip']['error'] ?? 'none')];
    }
    
    $zipFile = $_FILES['crm_zip']['tmp_name'];
    $zip = new ZipArchive();
    
    if ($zip->open($zipFile) !== true) {
        return ['success' => false, 'message' => 'Cannot open ZIP file. Please ensure it is a valid ZIP archive.'];
    }
    
    // Create private directory
    if (!is_dir($privatePath)) {
        mkdir($privatePath, 0755, true);
    }
    
    // Extract to a temp location first
    $tempDir = $basePath . '/temp_extract_' . time();
    mkdir($tempDir, 0755, true);
    $zip->extractTo($tempDir);
    $zip->close();
    
    // Find the root of the Laravel project (look for artisan file)
    $rootDir = findLaravelRoot($tempDir);
    
    if (!$rootDir) {
        removeDir($tempDir);
        return ['success' => false, 'message' => 'Could not find Laravel project in ZIP. Make sure the ZIP contains the CRM files.'];
    }
    
    // Move public files to public_html
    $publicSource = $rootDir . '/public';
    if (is_dir($publicSource)) {
        copyDir($publicSource, $basePath);
    }
    
    // Move everything else to private
    $excludeFromPrivate = ['public', 'installer.php'];
    $items = scandir($rootDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || in_array($item, $excludeFromPrivate)) continue;
        $source = $rootDir . '/' . $item;
        $dest = $privatePath . '/' . $item;
        if (is_dir($source)) {
            copyDir($source, $dest);
        } else {
            copy($source, $dest);
        }
    }
    
    // Clean up temp
    removeDir($tempDir);
    
    // Create required storage directories
    $storageDirs = [
        $privatePath . '/storage/app/public',
        $privatePath . '/storage/framework/cache/data',
        $privatePath . '/storage/framework/sessions',
        $privatePath . '/storage/framework/views',
        $privatePath . '/storage/logs',
        $privatePath . '/bootstrap/cache',
    ];
    foreach ($storageDirs as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0755, true);
    }

    
    // Update index.php to point to private directory
    $indexContent = '<?php

use Illuminate\Http\Request;

define(\'LARAVEL_START\', microtime(true));

if (file_exists($maintenance = __DIR__.\'/../private/storage/framework/maintenance.php\')) {
    require $maintenance;
}

require __DIR__.\'/../private/vendor/autoload.php\';

(require_once __DIR__.\'/../private/bootstrap/app.php\')
    ->handleRequest(Request::capture());
';
    file_put_contents($basePath . '/index.php', $indexContent);
    
    return ['success' => true, 'message' => 'Files extracted successfully!'];
}

function testDatabase(array $data): array {
    try {
        $dsn = "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$data['db_name']}";
        $pdo = new PDO($dsn, $data['db_user'], $data['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ['success' => true, 'message' => 'Database connection successful!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
    }
}

function setupDatabase(array $data, string $privatePath): array {
    // Create .env file
    $env = "APP_NAME=\"SmartLead CRM Pro\"
APP_ENV=production
APP_KEY=base64:" . base64_encode(random_bytes(32)) . "
APP_DEBUG=false
APP_URL={$data['app_url']}
APP_TIMEZONE={$data['timezone']}

DB_CONNECTION=mysql
DB_HOST={$data['db_host']}
DB_PORT={$data['db_port']}
DB_DATABASE={$data['db_name']}
DB_USERNAME={$data['db_user']}
DB_PASSWORD={$data['db_pass']}

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
MAIL_FROM_ADDRESS=noreply@" . parse_url($data['app_url'], PHP_URL_HOST) . "
MAIL_FROM_NAME=\"\${APP_NAME}\"
";
    
    file_put_contents($privatePath . '/.env', $env);

    
    // Run migrations directly via PDO
    try {
        $dsn = "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$data['db_name']}";
        $pdo = new PDO($dsn, $data['db_user'], $data['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET NAMES utf8mb4");
        
        // Run migration SQL files
        $migrationDir = $privatePath . '/database/migrations';
        if (is_dir($migrationDir)) {
            $files = glob($migrationDir . '/*.php');
            sort($files);
            
            // We'll use artisan if vendor exists, otherwise manual SQL
            if (file_exists($privatePath . '/vendor/autoload.php')) {
                // Bootstrap Laravel and run artisan migrate
                $_SERVER['SCRIPT_FILENAME'] = $privatePath . '/artisan';
                $cwd = getcwd();
                chdir($privatePath);
                
                // Run artisan commands
                $output = [];
                exec("php artisan migrate --force 2>&1", $output, $returnCode);
                $migrateResult = implode("\n", $output);
                
                if ($returnCode !== 0) {
                    chdir($cwd);
                    return ['success' => false, 'message' => "Migration failed: " . $migrateResult];
                }
                
                // Run seeder
                exec("php artisan db:seed --force 2>&1", $output, $returnCode);
                chdir($cwd);
                
                return ['success' => true, 'message' => 'Database setup complete! Tables created and demo data seeded.'];
            }
        }
        
        return ['success' => true, 'message' => 'Environment configured. Run migrations via /install after setup.'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database setup failed: ' . $e->getMessage()];
    }
}

function createAdmin(array $data, string $privatePath): array {
    $envFile = $privatePath . '/.env';
    if (!file_exists($envFile)) {
        return ['success' => false, 'message' => '.env file not found. Complete database setup first.'];
    }
    
    // Parse .env to get DB credentials
    $env = parse_ini_file($envFile);
    
    try {
        $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']}";
        $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Check if users table exists
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('users', $tables)) {
            // Insert admin user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, email_verified_at, designation, status, timezone, created_at, updated_at) VALUES (?, ?, 'admin', ?, NOW(), 'Administrator', 'active', 'Asia/Kolkata', NOW(), NOW())");
            $stmt->execute([$data['name'], $data['email'], $password]);
            $userId = $pdo->lastInsertId();
            
            // Assign super-admin role
            $roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'super-admin' LIMIT 1")->fetchColumn();
            if ($roleId) {
                $pdo->prepare("INSERT INTO user_role (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
            }
            
            return ['success' => true, 'message' => "Admin account created! Email: {$data['email']}"];
        } else {
            return ['success' => false, 'message' => 'Users table not found. Please run migrations first via yourdomain.com/install'];
        }
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}


function finalize(string $privatePath, string $publicPath): array {
    $results = [];
    
    // Set permissions
    chmodRecursive($privatePath . '/storage', 0755);
    chmodRecursive($privatePath . '/bootstrap/cache', 0755);
    $results[] = 'Permissions set';
    
    // Create storage symlink
    $linkTarget = $privatePath . '/storage/app/public';
    $linkPath = $publicPath . '/storage';
    if (!file_exists($linkPath)) {
        if (function_exists('symlink')) {
            @symlink($linkTarget, $linkPath);
            $results[] = 'Storage symlink created';
        } else {
            $results[] = 'Symlink not available - create manually or use .htaccess redirect';
        }
    }
    
    // Try running optimize commands
    $cwd = getcwd();
    chdir($privatePath);
    if (file_exists($privatePath . '/artisan')) {
        @exec("php artisan config:cache 2>&1");
        @exec("php artisan route:cache 2>&1");
        @exec("php artisan view:cache 2>&1");
        $results[] = 'Cache optimized';
    }
    chdir($cwd);
    
    // Mark as installed
    file_put_contents($privatePath . '/storage/installed', date('Y-m-d H:i:s'));
    $results[] = 'Installation marked complete';
    
    return ['success' => true, 'message' => implode(', ', $results), 'results' => $results];
}

// ========== UTILITY FUNCTIONS ==========

function findLaravelRoot(string $dir): ?string {
    // Check current dir
    if (file_exists($dir . '/artisan')) return $dir;
    if (file_exists($dir . '/composer.json') && is_dir($dir . '/app')) return $dir;
    
    // Check one level deep (GitHub ZIP extracts to a subfolder like Crm-main/)
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            if (file_exists($path . '/artisan')) return $path;
            if (file_exists($path . '/composer.json') && is_dir($path . '/app')) return $path;
        }
    }
    
    // Check two levels deep (in case ZIP has extra nesting)
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $subItems = scandir($path);
            foreach ($subItems as $subItem) {
                if ($subItem === '.' || $subItem === '..') continue;
                $subPath = $path . '/' . $subItem;
                if (is_dir($subPath)) {
                    if (file_exists($subPath . '/artisan')) return $subPath;
                    if (file_exists($subPath . '/composer.json') && is_dir($subPath . '/app')) return $subPath;
                }
            }
        }
    }
    
    return null;
}

function copyDir(string $src, string $dst): void {
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $s = $src . '/' . $item;
        $d = $dst . '/' . $item;
        if (is_dir($s)) { copyDir($s, $d); }
        else { copy($s, $d); }
    }
}

function removeDir(string $dir): void {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) { removeDir($path); }
        else { unlink($path); }
    }
    rmdir($dir);
}

function chmodRecursive(string $path, int $mode): void {
    if (!is_dir($path)) return;
    chmod($path, $mode);
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($items as $item) {
        chmod($item->getPathname(), $mode);
    }
}


// ========== HTML OUTPUT ==========
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLead CRM Pro - Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .installer-card { max-width: 700px; margin: auto; }
        .step-indicator { display: flex; justify-content: center; gap: 8px; margin-bottom: 2rem; }
        .step-dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.3); }
        .step-dot.active { background: #fff; }
        .step-dot.done { background: #10b981; }
        .check-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .log-box { background: #1e1e2d; color: #10b981; font-family: monospace; font-size: 13px; padding: 15px; border-radius: 8px; max-height: 200px; overflow-y: auto; }
        .upload-area { border: 2px dashed #dee2e6; border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .upload-area:hover, .upload-area.dragover { border-color: #4f46e5; background: rgba(79,70,229,0.05); }
        .btn-installer { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; }
        .btn-installer:hover { background: linear-gradient(135deg, #5a67d8, #6b46c1); color: white; transform: translateY(-1px); }
    </style>
</head>
<body class="py-5">
<div class="installer-card px-3">
    <!-- Step Indicators -->
    <div class="step-indicator" id="stepIndicator">
        <div class="step-dot active" data-step="1"></div>
        <div class="step-dot" data-step="2"></div>
        <div class="step-dot" data-step="3"></div>
        <div class="step-dot" data-step="4"></div>
        <div class="step-dot" data-step="5"></div>
    </div>

    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div class="card-body p-5">


            <!-- STEP 1: Welcome & Requirements -->
            <div class="step-content" id="step1">
                <div class="text-center mb-4">
                    <i class="bi bi-rocket-takeoff display-3 text-primary mb-3 d-block"></i>
                    <h3 class="fw-bold">SmartLead CRM Pro</h3>
                    <p class="text-muted">Web-Based Installer</p>
                </div>
                <p class="text-center text-muted mb-4">This installer will set up SmartLead CRM Pro on your hosting. Make sure you have:</p>
                <ul class="list-unstyled px-3">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>CRM ZIP file downloaded from GitHub</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>MySQL database already created in DirectAdmin</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Database username and password ready</li>
                </ul>
                <hr>
                <h6 class="fw-bold mb-3">Server Requirements Check:</h6>
                <div id="requirementsResult">
                    <div class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div> Checking...</div>
                </div>
                <button class="btn btn-installer w-100 mt-4" id="btnStep1Next" disabled onclick="goToStep(2)">
                    Next: Upload Files <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>

            <!-- STEP 2: Upload ZIP -->
            <div class="step-content d-none" id="step2">
                <div class="text-center mb-4">
                    <i class="bi bi-cloud-arrow-up display-3 text-primary mb-3 d-block"></i>
                    <h3 class="fw-bold">Upload CRM Files</h3>
                    <p class="text-muted">Upload the SmartLead CRM Pro ZIP file (with vendor/ folder included)</p>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-file-earmark-zip fs-1 text-muted d-block mb-2"></i>
                        <p class="mb-1 fw-semibold">Click to browse or drag & drop</p>
                        <small class="text-muted">ZIP file up to 256MB</small>
                        <input type="file" id="fileInput" name="crm_zip" accept=".zip" class="d-none">
                    </div>
                    <div id="uploadFileName" class="text-center mt-2 text-success fw-semibold d-none"></div>
                    <div id="uploadProgress" class="mt-3 d-none">
                        <div class="progress" style="height: 8px;"><div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%"></div></div>
                        <small class="text-muted mt-1 d-block text-center" id="progressText">Uploading...</small>
                    </div>
                </form>
                <div id="uploadResult" class="mt-3"></div>
                <button class="btn btn-installer w-100 mt-4" id="btnStep2Next" disabled onclick="goToStep(3)">
                    Next: Database Setup <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>


            <!-- STEP 3: Database Configuration -->
            <div class="step-content d-none" id="step3">
                <div class="text-center mb-4">
                    <i class="bi bi-database display-3 text-primary mb-3 d-block"></i>
                    <h3 class="fw-bold">Database Configuration</h3>
                    <p class="text-muted">Enter your MySQL database credentials from DirectAdmin</p>
                </div>
                <form id="dbForm">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Database Host</label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Port</label>
                            <input type="number" name="db_port" class="form-control" value="3306" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Database Name</label>
                            <input type="text" name="db_name" class="form-control" placeholder="username_smartlead_crm" required>
                            <small class="text-muted">From DirectAdmin MySQL Management (includes your username prefix)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Database Username</label>
                            <input type="text" name="db_user" class="form-control" placeholder="username_dbuser" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Database Password</label>
                            <input type="password" name="db_pass" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Your Domain URL</label>
                            <input type="url" name="app_url" class="form-control" value="https://<?php echo $_SERVER['HTTP_HOST']; ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="Asia/Kolkata">Asia/Kolkata (India)</option>
                                <option value="America/New_York">America/New_York (US East)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (US West)</option>
                                <option value="Europe/London">Europe/London (UK)</option>
                                <option value="Asia/Dubai">Asia/Dubai (UAE)</option>
                                <option value="Asia/Singapore">Asia/Singapore</option>
                                <option value="Australia/Sydney">Australia/Sydney</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary w-100 mt-3" onclick="testDb()">
                        <i class="bi bi-plug me-2"></i>Test Connection
                    </button>
                </form>
                <div id="dbResult" class="mt-3"></div>
                <button class="btn btn-installer w-100 mt-3" id="btnStep3Next" disabled onclick="setupDb()">
                    Setup Database & Continue <i class="bi bi-arrow-right ms-2"></i>
                </button>
                <div id="dbSetupResult" class="mt-3"></div>
            </div>


            <!-- STEP 4: Admin Account -->
            <div class="step-content d-none" id="step4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-badge display-3 text-primary mb-3 d-block"></i>
                    <h3 class="fw-bold">Create Admin Account</h3>
                    <p class="text-muted">Set up your Super Admin login credentials</p>
                </div>
                <form id="adminForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@yourdomain.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" placeholder="Min 8 characters" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirm" class="form-control" minlength="8" required>
                    </div>
                </form>
                <div id="adminResult" class="mt-3"></div>
                <button class="btn btn-installer w-100 mt-3" onclick="createAdminAccount()">
                    Create Admin & Finish <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>

            <!-- STEP 5: Complete -->
            <div class="step-content d-none" id="step5">
                <div class="text-center mb-4">
                    <i class="bi bi-check-circle-fill display-1 text-success mb-3 d-block"></i>
                    <h3 class="fw-bold">Installation Complete!</h3>
                    <p class="text-muted">SmartLead CRM Pro has been installed successfully.</p>
                </div>
                <div class="log-box mb-4" id="finalLog">Finalizing installation...</div>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>IMPORTANT:</strong> Delete this <code>installer.php</code> file from your server for security!
                </div>
                <div class="card bg-light border-0 p-3 mb-3">
                    <h6>Next Steps:</h6>
                    <ul class="mb-0 small">
                        <li>Login at <a href="/" target="_blank">yourdomain.com/login</a></li>
                        <li>Configure Google OAuth in Settings</li>
                        <li>Set up SMTP for emails</li>
                        <li>Configure WhatsApp API</li>
                        <li>Add cron job in DirectAdmin (see below)</li>
                    </ul>
                </div>
                <div class="card bg-dark text-light p-3 mb-3">
                    <h6 class="text-light">Cron Job Command:</h6>
                    <code class="text-success small">* * * * * /usr/local/bin/php <?php echo dirname($basePath); ?>/private/artisan schedule:run >> /dev/null 2>&1</code>
                </div>
                <a href="/login" class="btn btn-installer w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
                </a>
            </div>
        </div>
    </div>

    <p class="text-center text-white-50 mt-3 small">&copy; <?php echo date('Y'); ?> SmartLead CRM Pro - Installer v1.0</p>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentStep = 1;

// Auto-check requirements on load
window.addEventListener('DOMContentLoaded', () => { checkRequirements(); });

function goToStep(step) {
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
    document.getElementById('step' + step).classList.remove('d-none');
    document.querySelectorAll('.step-dot').forEach((dot, i) => {
        dot.classList.remove('active', 'done');
        if (i + 1 < step) dot.classList.add('done');
        if (i + 1 === step) dot.classList.add('active');
    });
    currentStep = step;
    if (step === 5) finalize();
}

function checkRequirements() {
    fetch('', { method: 'POST', body: new URLSearchParams({ action: 'check_requirements' }) })
    .then(r => r.json())
    .then(data => {
        let html = '';
        for (let [name, passed] of Object.entries(data.checks)) {
            html += `<div class="check-item"><span>${name}</span><i class="bi bi-${passed ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'}"></i></div>`;
        }
        document.getElementById('requirementsResult').innerHTML = html;
        if (data.all_passed) {
            document.getElementById('btnStep1Next').disabled = false;
        } else {
            document.getElementById('requirementsResult').innerHTML += '<div class="alert alert-danger mt-3"><i class="bi bi-exclamation-triangle me-2"></i>Some requirements not met. Contact your hosting provider.</div>';
        }
    });
}

// File upload handling
const fileInput = document.getElementById('fileInput');
const uploadArea = document.getElementById('uploadArea');

uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
uploadArea.addEventListener('dragleave', () => { uploadArea.classList.remove('dragover'); });
uploadArea.addEventListener('drop', (e) => { e.preventDefault(); uploadArea.classList.remove('dragover'); fileInput.files = e.dataTransfer.files; handleFile(); });
fileInput.addEventListener('change', handleFile);

function handleFile() {
    const file = fileInput.files[0];
    if (!file) return;
    document.getElementById('uploadFileName').textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';
    document.getElementById('uploadFileName').classList.remove('d-none');
    uploadFile(file);
}

function uploadFile(file) {
    const formData = new FormData();
    formData.append('action', 'upload_zip');
    formData.append('crm_zip', file);
    
    document.getElementById('uploadProgress').classList.remove('d-none');
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    
    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressText').textContent = pct < 100 ? `Uploading... ${pct}%` : 'Extracting files... please wait';
        }
    };
    
    xhr.onload = function() {
        const data = JSON.parse(xhr.responseText);
        document.getElementById('uploadProgress').classList.add('d-none');
        if (data.success) {
            document.getElementById('uploadResult').innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${data.message}</div>`;
            document.getElementById('btnStep2Next').disabled = false;
        } else {
            document.getElementById('uploadResult').innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${data.message}</div>`;
        }
    };
    
    xhr.onerror = function() {
        document.getElementById('uploadProgress').classList.add('d-none');
        document.getElementById('uploadResult').innerHTML = '<div class="alert alert-danger">Upload failed. File may be too large.</div>';
    };
    
    xhr.send(formData);
}
</script>
<script>
function testDb() {
    const form = document.getElementById('dbForm');
    const data = new FormData(form);
    data.append('action', 'test_database');
    
    document.getElementById('dbResult').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Testing...</div>';
    
    fetch('', { method: 'POST', body: new URLSearchParams(data) })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('dbResult').innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${res.message}</div>`;
            document.getElementById('btnStep3Next').disabled = false;
        } else {
            document.getElementById('dbResult').innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${res.message}</div>`;
        }
    });
}

function setupDb() {
    const form = document.getElementById('dbForm');
    const data = new FormData(form);
    data.append('action', 'setup_database');
    
    document.getElementById('dbSetupResult').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Setting up database... this may take a minute</div>';
    document.getElementById('btnStep3Next').disabled = true;
    
    fetch('', { method: 'POST', body: new URLSearchParams(data) })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('dbSetupResult').innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${res.message}</div>`;
            setTimeout(() => goToStep(4), 1500);
        } else {
            document.getElementById('dbSetupResult').innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
            document.getElementById('btnStep3Next').disabled = false;
        }
    });
}

function createAdminAccount() {
    const form = document.getElementById('adminForm');
    const data = new FormData(form);
    
    if (data.get('password') !== data.get('password_confirm')) {
        document.getElementById('adminResult').innerHTML = '<div class="alert alert-danger">Passwords do not match!</div>';
        return;
    }
    if (data.get('password').length < 8) {
        document.getElementById('adminResult').innerHTML = '<div class="alert alert-danger">Password must be at least 8 characters!</div>';
        return;
    }
    
    data.append('action', 'create_admin');
    document.getElementById('adminResult').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Creating account...</div>';
    
    fetch('', { method: 'POST', body: new URLSearchParams(data) })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('adminResult').innerHTML = `<div class="alert alert-success">${res.message}</div>`;
            setTimeout(() => goToStep(5), 1500);
        } else {
            document.getElementById('adminResult').innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
        }
    });
}

function finalize() {
    const log = document.getElementById('finalLog');
    log.textContent = 'Running final setup...\n';
    
    fetch('', { method: 'POST', body: new URLSearchParams({ action: 'finalize' }) })
    .then(r => r.json())
    .then(res => {
        if (res.results) {
            log.textContent = res.results.map(r => '✓ ' + r).join('\n') + '\n\n✓ Installation complete!';
        } else {
            log.textContent += res.message;
        }
    });
}
</script>
</body>
</html>
