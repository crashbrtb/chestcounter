<?php
declare(strict_types=1);

/**
 * Chest Counter - Auto Installation Script
 *
 * This script automates the complete installation of the Chest Counter application.
 * It performs the following steps:
 *
 * 1. Checks PHP version and required extensions
 * 2. Installs Composer dependencies (auto-downloads Composer if needed)
 * 3. Collects database credentials from the user
 * 4. Tests the database connection
 * 5. Generates config/app_local.php with secure settings
 * 6. Creates required directories with proper permissions
 * 7. Runs CakePHP migrations (creates tables)
 * 8. Runs CakePHP seeds (inserts initial data)
 * 9. Collects admin user details and creates the first administrator
 *
 * Usage:
 *   php install.php
 *
 * IMPORTANT: Delete this file after installation for security reasons.
 */

// ─── Configuration ─────────────────────────────────────────────────────────────

define('ROOT', dirname(__FILE__));
define('MIN_PHP_VERSION', '8.1.0');
define('REQUIRED_EXTENSIONS', ['pdo_mysql', 'mbstring', 'intl', 'openssl', 'json', 'xml']);
define('APP_LOCAL_FILE', ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app_local.php');
define('APP_LOCAL_EXAMPLE', ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app_local.example.php');
define('CAKE_BIN', ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'cake.php');
define('TOTAL_STEPS', 9);

// ─── Helper Functions ──────────────────────────────────────────────────────────

function printBanner(): void
{
    echo PHP_EOL;
    echo "╔══════════════════════════════════════════════════════════╗" . PHP_EOL;
    echo "║          🏰 Chest Counter - Auto Installer 🏰          ║" . PHP_EOL;
    echo "║                  Total Battle Game                      ║" . PHP_EOL;
    echo "╚══════════════════════════════════════════════════════════╝" . PHP_EOL;
    echo PHP_EOL;
}

function printStep(int $step, string $message): void
{
    echo PHP_EOL;
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
    echo "  [{$step}/" . TOTAL_STEPS . "] {$message}" . PHP_EOL;
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
    echo PHP_EOL;
}

function printSuccess(string $message): void
{
    echo "  ✅ {$message}" . PHP_EOL;
}

function printError(string $message): void
{
    echo "  ❌ {$message}" . PHP_EOL;
}

function printWarning(string $message): void
{
    echo "  ⚠️  {$message}" . PHP_EOL;
}

function printInfo(string $message): void
{
    echo "  ℹ️  {$message}" . PHP_EOL;
}

function prompt(string $question, string $default = ''): string
{
    $defaultHint = $default !== '' ? " [{$default}]" : '';
    echo "  {$question}{$defaultHint}: ";
    $input = trim((string)fgets(STDIN));

    return $input !== '' ? $input : $default;
}

function promptPassword(string $question): string
{
    // Try to hide password input on Unix-like systems
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && function_exists('shell_exec')) {
        echo "  {$question}: ";
        $password = shell_exec('stty -echo 2>/dev/null; head -1; stty echo 2>/dev/null');
        echo PHP_EOL;
        return trim((string)$password);
    }

    // Fallback: show password input
    echo "  {$question}: ";
    $input = trim((string)fgets(STDIN));

    return $input;
}

function confirm(string $question, bool $default = true): bool
{
    $hint = $default ? '[Y/n]' : '[y/N]';
    echo "  {$question} {$hint}: ";
    $input = strtolower(trim((string)fgets(STDIN)));

    if ($input === '') {
        return $default;
    }

    return $input === 'y' || $input === 'yes';
}

function runCommand(string $command, bool $showOutput = false): array
{
    if ($showOutput) {
        // Stream output in real-time
        $exitCode = 0;
        passthru($command . ' 2>&1', $exitCode);
        return [
            'output' => '',
            'exitCode' => $exitCode,
        ];
    }

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    return [
        'output' => implode(PHP_EOL, $output),
        'exitCode' => $exitCode,
    ];
}

function generateSecuritySalt(): string
{
    return hash('sha256', random_bytes(64));
}

function abortInstallation(string $reason): void
{
    echo PHP_EOL;
    printError("Installation aborted: {$reason}");
    echo PHP_EOL;
    exit(1);
}

/**
 * Detect how to run Composer.
 * Returns the command string or null if not found.
 */
function detectComposer(): ?string
{
    // 1. Global composer command
    $result = runCommand('composer --version');
    if ($result['exitCode'] === 0) {
        return 'composer';
    }

    // 2. composer.phar in project root
    $localPhar = ROOT . DIRECTORY_SEPARATOR . 'composer.phar';
    if (file_exists($localPhar)) {
        return 'php ' . escapeshellarg($localPhar);
    }

    // 3. composer.phar accessible via php
    $result = runCommand('php composer.phar --version');
    if ($result['exitCode'] === 0) {
        return 'php composer.phar';
    }

    return null;
}

/**
 * Download Composer to the project root.
 */
function downloadComposer(): ?string
{
    printInfo("Downloading Composer...");

    $installerPath = ROOT . DIRECTORY_SEPARATOR . 'composer-setup.php';
    $pharPath = ROOT . DIRECTORY_SEPARATOR . 'composer.phar';

    // Download installer
    $installer = @file_get_contents('https://getcomposer.org/installer');
    if ($installer === false) {
        // Try with curl
        $result = runCommand('php -r "copy(\'https://getcomposer.org/installer\', \'' . addcslashes($installerPath, "'\\") . '\');"');
        if (!file_exists($installerPath)) {
            return null;
        }
    } else {
        file_put_contents($installerPath, $installer);
    }

    // Run installer
    $result = runCommand('php ' . escapeshellarg($installerPath) . ' --install-dir=' . escapeshellarg(ROOT) . ' --filename=composer.phar');

    // Cleanup installer
    @unlink($installerPath);

    if (file_exists($pharPath)) {
        printSuccess("Composer downloaded successfully");
        return 'php ' . escapeshellarg($pharPath);
    }

    return null;
}

// ─── Step 1: Check Prerequisites ───────────────────────────────────────────────

function checkPrerequisites(): void
{
    printStep(1, 'Checking prerequisites');

    // Check PHP version
    if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
        printError("PHP " . MIN_PHP_VERSION . "+ required. Current: " . PHP_VERSION);
        abortInstallation('PHP version too old.');
    }
    printSuccess("PHP " . PHP_VERSION . " detected");

    // Check required extensions
    $missingExtensions = [];
    foreach (REQUIRED_EXTENSIONS as $ext) {
        if (extension_loaded($ext)) {
            printSuccess("Extension '{$ext}' loaded");
        } else {
            printError("Extension '{$ext}' NOT loaded");
            $missingExtensions[] = $ext;
        }
    }

    if (!empty($missingExtensions)) {
        abortInstallation('Missing PHP extensions: ' . implode(', ', $missingExtensions));
    }

    // Check if CakePHP binary exists
    if (!file_exists(CAKE_BIN)) {
        abortInstallation('CakePHP binary not found at: ' . CAKE_BIN . '. Make sure you cloned the repository correctly.');
    }
    printSuccess("CakePHP binary found");

    // Check if already installed
    if (file_exists(APP_LOCAL_FILE)) {
        printWarning("config/app_local.php already exists!");
        if (!confirm("Overwrite existing configuration?", false)) {
            abortInstallation('Configuration already exists. Remove config/app_local.php to reinstall.');
        }
    }
}

// ─── Step 2: Install Composer Dependencies ─────────────────────────────────────

function installComposerDependencies(): void
{
    printStep(2, 'Installing Composer dependencies');

    // Check if vendor already exists and is populated
    $vendorAutoload = ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (file_exists($vendorAutoload)) {
        printSuccess("Composer dependencies already installed");

        if (confirm("Run composer install again to make sure everything is up to date?", false)) {
            // Continue to install
        } else {
            return;
        }
    }

    // Detect or download Composer
    $composerCmd = detectComposer();

    if ($composerCmd === null) {
        printWarning("Composer not found on this system.");

        if (confirm("Download Composer automatically?", true)) {
            $composerCmd = downloadComposer();
            if ($composerCmd === null) {
                printError("Failed to download Composer.");
                printInfo("Install Composer manually: https://getcomposer.org/download/");
                printInfo("Then run: composer install --no-dev --optimize-autoloader");
                abortInstallation('Composer is required.');
            }
        } else {
            printInfo("Install Composer manually: https://getcomposer.org/download/");
            printInfo("Then run: composer install --no-dev --optimize-autoloader");
            abortInstallation('Composer is required.');
        }
    }

    printInfo("Using: {$composerCmd}");
    printInfo("Installing dependencies (this may take a few minutes)...");
    echo PHP_EOL;

    // Some hosts disable functions that Composer needs
    // Try with disable_functions="" first, fallback to normal
    $installCmd = $composerCmd . ' install --no-dev --optimize-autoloader --no-interaction --working-dir=' . escapeshellarg(ROOT);
    $result = runCommand($installCmd, true);

    if ($result['exitCode'] !== 0) {
        printWarning("Composer install failed. Trying with disable_functions workaround...");
        $installCmd = 'php -d disable_functions="" ' . str_replace('composer', escapeshellarg(ROOT . DIRECTORY_SEPARATOR . 'composer.phar'), $composerCmd)
            . ' install --no-dev --optimize-autoloader --no-interaction --working-dir=' . escapeshellarg(ROOT);
        $result = runCommand($installCmd, true);

        if ($result['exitCode'] !== 0) {
            printError("Composer install failed.");
            printInfo("Try running manually: composer install --no-dev --optimize-autoloader");
            printInfo("Or: php -d disable_functions=\"\" composer.phar install --no-dev --optimize-autoloader");
            abortInstallation('Failed to install dependencies.');
        }
    }

    echo PHP_EOL;

    if (file_exists($vendorAutoload)) {
        printSuccess("Dependencies installed successfully");
    } else {
        abortInstallation('vendor/autoload.php not found after install. Composer may have failed silently.');
    }
}

// ─── Step 3: Collect Database Credentials ──────────────────────────────────────

function collectDatabaseCredentials(): array
{
    printStep(3, 'Database configuration');

    printInfo("Enter your MySQL/MariaDB database credentials.");
    printInfo("The database must already exist (create it via hosting panel or CLI).");
    echo PHP_EOL;

    $host = prompt('Database host', 'localhost');
    $port = prompt('Database port (leave empty for default)', '');
    $database = prompt('Database name', 'chestcounter');
    $username = prompt('Database username');
    $password = promptPassword('Database password');

    if (empty($username)) {
        abortInstallation('Database username is required.');
    }

    return [
        'host' => $host,
        'port' => $port,
        'database' => $database,
        'username' => $username,
        'password' => $password,
    ];
}

// ─── Step 4: Test Database Connection ──────────────────────────────────────────

function testDatabaseConnection(array $dbConfig): void
{
    printStep(4, 'Testing database connection');

    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
        if (!empty($dbConfig['port'])) {
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
        }

        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10,
        ]);

        $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        printSuccess("Connected to MySQL/MariaDB (version: {$version})");

        // Check if database already has tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($tables)) {
            printWarning("Database '{$dbConfig['database']}' already contains " . count($tables) . " table(s).");
            if (!confirm("Continue? (migrations will be marked as applied)", true)) {
                abortInstallation('Database is not empty.');
            }
        } else {
            printSuccess("Database '{$dbConfig['database']}' is empty and ready");
        }

        $pdo = null;
    } catch (PDOException $e) {
        printError("Connection failed: " . $e->getMessage());
        abortInstallation('Cannot connect to database.');
    }
}

// ─── Step 5: Generate app_local.php ────────────────────────────────────────────

function generateAppLocal(array $dbConfig): void
{
    printStep(5, 'Generating configuration file');

    $salt = generateSecuritySalt();
    $portLine = '';
    if (!empty($dbConfig['port'])) {
        $portLine = "\n            'port' => '{$dbConfig['port']}',";
    }

    $escapedPassword = addcslashes($dbConfig['password'], "'\\");

    $content = <<<PHP
<?php
/*
 * Local configuration file.
 * Generated by the Chest Counter auto-installer.
 */
return [
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        'salt' => env('SECURITY_SALT', '{$salt}'),
    ],

    'Datasources' => [
        'default' => [
            'host' => '{$dbConfig['host']}',{$portLine}
            'username' => '{$dbConfig['username']}',
            'password' => '{$escapedPassword}',
            'database' => '{$dbConfig['database']}',
            'encoding' => 'utf8mb4',
            'url' => env('DATABASE_URL', null),
        ],

        'test' => [
            'host' => 'localhost',
            'username' => '{$dbConfig['username']}',
            'password' => '{$escapedPassword}',
            'database' => 'test_{$dbConfig['database']}',
            'url' => env('DATABASE_TEST_URL', 'sqlite://127.0.0.1/tmp/tests.sqlite'),
        ],
    ],

    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],
];

PHP;

    if (file_put_contents(APP_LOCAL_FILE, $content) === false) {
        abortInstallation('Failed to write config/app_local.php');
    }

    printSuccess("config/app_local.php created");
    printSuccess("Security salt generated: " . substr($salt, 0, 16) . "...");
}

// ─── Step 6: Create Directories ────────────────────────────────────────────────

function createDirectories(): void
{
    printStep(6, 'Creating required directories');

    $dirs = [
        'tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'models',
        'tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'persistent',
        'tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'views',
        'tmp' . DIRECTORY_SEPARATOR . 'sessions',
        'logs',
    ];

    foreach ($dirs as $dir) {
        $fullPath = ROOT . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0775, true)) {
                printSuccess("Created: {$dir}");
            } else {
                printWarning("Could not create: {$dir} (check permissions)");
            }
        } else {
            printSuccess("Exists: {$dir}");
        }
    }

    // Set permissions (Unix only)
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        $setPermissions = function (string $path) {
            @chmod($path, 0775);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $item) {
                @chmod($item->getPathname(), 0775);
            }
        };

        $setPermissions(ROOT . DIRECTORY_SEPARATOR . 'tmp');
        $setPermissions(ROOT . DIRECTORY_SEPARATOR . 'logs');
        printSuccess("Set permissions 775 on tmp/ and logs/ (recursive)");
    }
}

// ─── Step 7: Run Migrations ────────────────────────────────────────────────────

function runMigrations(): void
{
    printStep(7, 'Running database migrations');

    // Check if tables already exist (migration might need mark_migrated)
    $statusResult = runCommand('php ' . escapeshellarg(CAKE_BIN) . ' migrations status');

    if ($statusResult['exitCode'] !== 0) {
        printError("Failed to check migration status:");
        echo "  " . str_replace(PHP_EOL, PHP_EOL . "  ", $statusResult['output']) . PHP_EOL;
        abortInstallation('Migration status check failed.');
    }

    // Check if migrations are already applied
    if (strpos($statusResult['output'], '| up') !== false) {
        printSuccess("Migrations already applied");
        return;
    }

    // Try to run migrations
    printInfo("Creating database tables...");
    $result = runCommand('php ' . escapeshellarg(CAKE_BIN) . ' migrations migrate --no-interaction');

    if ($result['exitCode'] !== 0) {
        // If tables already exist, try mark_migrated
        if (strpos($result['output'], 'already exists') !== false
            || strpos($result['output'], 'Base table') !== false
            || strpos($result['output'], 'table or view already exists') !== false
        ) {
            printWarning("Tables already exist, marking migration as applied...");
            $markResult = runCommand('php ' . escapeshellarg(CAKE_BIN) . ' migrations mark_migrated 20260822220000');

            if ($markResult['exitCode'] !== 0) {
                printError("Failed to mark migration:");
                echo "  " . str_replace(PHP_EOL, PHP_EOL . "  ", $markResult['output']) . PHP_EOL;
                abortInstallation('Migration failed.');
            }
            printSuccess("Migration marked as applied (tables already existed)");
            return;
        }

        printError("Migration failed:");
        echo "  " . str_replace(PHP_EOL, PHP_EOL . "  ", $result['output']) . PHP_EOL;
        abortInstallation('Database migration failed.');
    }

    printSuccess("All tables created successfully (15 tables)");
}

// ─── Step 8: Run Seeds ─────────────────────────────────────────────────────────

function runSeeds(): void
{
    printStep(8, 'Inserting initial data (Seeds)');

    printInfo("Inserting roles, configuration, and chest types...");
    $result = runCommand('php ' . escapeshellarg(CAKE_BIN) . ' migrations seed --no-interaction');

    if ($result['exitCode'] !== 0) {
        printError("Seed failed:");
        echo "  " . str_replace(PHP_EOL, PHP_EOL . "  ", $result['output']) . PHP_EOL;
        printWarning("You may need to run seeds manually: php bin/cake.php migrations seed");
    } else {
        printSuccess("Initial data inserted successfully");
        printInfo("  → 3 roles (admin, user, bankers)");
        printInfo("  → 20 configuration parameters");
        printInfo("  → 102 standard chest types");
    }
}

// ─── Step 9: Create Admin User ─────────────────────────────────────────────────

function createAdminUser(): void
{
    printStep(9, 'Creating administrator user');

    printInfo("Enter the details for the first administrator.");
    echo PHP_EOL;

    $name = prompt('Administrator name');
    if (empty($name)) {
        abortInstallation('Administrator name is required.');
    }

    $email = prompt('Administrator email');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        abortInstallation('A valid email is required.');
    }

    $password = promptPassword('Administrator password (min 6 chars)');
    if (empty($password) || strlen($password) < 6) {
        abortInstallation('Password must be at least 6 characters long.');
    }

    $passwordConfirm = promptPassword('Confirm password');
    if ($password !== $passwordConfirm) {
        abortInstallation('Passwords do not match.');
    }

    echo PHP_EOL;
    printInfo("Creating admin: {$name} ({$email})");

    $escapedName = escapeshellarg($name);
    $escapedEmail = escapeshellarg($email);
    $escapedPassword = escapeshellarg($password);

    $result = runCommand(
        'php ' . escapeshellarg(CAKE_BIN) .
        " create_admin --name {$escapedName} --email {$escapedEmail} --password {$escapedPassword}"
    );

    if ($result['exitCode'] !== 0) {
        // Check if admin already exists (not really an error)
        if (strpos($result['output'], 'already exists') !== false) {
            printWarning("An administrator already exists in the system.");
            printInfo("Use the web interface to manage users.");
            return;
        }

        printError("Failed to create administrator:");
        echo "  " . str_replace(PHP_EOL, PHP_EOL . "  ", $result['output']) . PHP_EOL;
        printWarning("You can create it manually later: php bin/cake.php create_admin");
    } else {
        printSuccess("Administrator created successfully!");
        printInfo("  → Name: {$name}");
        printInfo("  → Email: {$email}");
    }
}

// ─── Final Summary & Cleanup ───────────────────────────────────────────────────

function clearCache(): void
{
    // Clear CakePHP cache files
    $cacheDir = ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cache';
    $cacheDirs = ['models', 'persistent', 'views'];

    foreach ($cacheDirs as $dir) {
        $path = $cacheDir . DIRECTORY_SEPARATOR . $dir;
        if (is_dir($path)) {
            $files = glob($path . DIRECTORY_SEPARATOR . '*');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file) && basename($file) !== 'empty') {
                        @unlink($file);
                    }
                }
            }
        }
    }
}

function showFinalSummary(): void
{
    echo PHP_EOL;
    echo "╔══════════════════════════════════════════════════════════╗" . PHP_EOL;
    echo "║          ✅ Installation Complete! ✅                   ║" . PHP_EOL;
    echo "╚══════════════════════════════════════════════════════════╝" . PHP_EOL;
    echo PHP_EOL;

    printSuccess("Chest Counter is ready to use!");
    echo PHP_EOL;
    printInfo("Next steps:");
    printInfo("  1. Access your application in the browser");
    printInfo("  2. Log in with the admin credentials you just created");
    printInfo("  3. Configure your clan settings in the admin panel");
    echo PHP_EOL;

    printWarning("SECURITY: Delete this installer file now!");
    printWarning("  rm install.php");
    echo PHP_EOL;
}

function selfCleanup(): void
{
    // Clean downloaded composer.phar if we downloaded it
    $composerPhar = ROOT . DIRECTORY_SEPARATOR . 'composer.phar';
    if (file_exists($composerPhar)) {
        if (confirm("Delete the downloaded composer.phar?", false)) {
            @unlink($composerPhar);
            printSuccess("composer.phar deleted.");
        }
    }

    if (confirm("Delete this installer file (install.php) for security?", true)) {
        $installerFile = __FILE__;
        if (@unlink($installerFile)) {
            printSuccess("Installer file deleted.");
        } else {
            printWarning("Could not delete installer file. Please delete it manually:");
            printWarning("  rm install.php");
        }
    } else {
        printWarning("Remember to delete install.php manually for security!");
    }
}

// ─── Main ──────────────────────────────────────────────────────────────────────

function main(): void
{
    // Must run from CLI
    if (php_sapi_name() !== 'cli') {
        echo "This script must be run from the command line." . PHP_EOL;
        echo "Usage: php install.php" . PHP_EOL;
        exit(1);
    }

    printBanner();

    if (!confirm("Start Chest Counter installation?", true)) {
        echo PHP_EOL;
        echo "  Installation cancelled." . PHP_EOL;
        echo PHP_EOL;
        exit(0);
    }

    // Step 1: Check prerequisites
    checkPrerequisites();

    // Step 2: Install Composer dependencies
    installComposerDependencies();

    // Step 3: Collect database credentials
    $dbConfig = collectDatabaseCredentials();

    // Step 4: Test database connection
    testDatabaseConnection($dbConfig);

    // Step 5: Generate config/app_local.php
    generateAppLocal($dbConfig);

    // Step 6: Create required directories
    createDirectories();

    // Step 7: Run migrations
    runMigrations();

    // Step 8: Run seeds
    runSeeds();

    // Step 9: Create admin user
    createAdminUser();

    // Clear cache for clean start
    clearCache();

    // Final summary
    showFinalSummary();

    // Self cleanup
    selfCleanup();
}

main();
