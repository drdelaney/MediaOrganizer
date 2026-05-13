<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.1'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
const FCPATH = __DIR__ . DIRECTORY_SEPARATOR;

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Paths();

// Check if .env file exists
if (! is_file(FCPATH . '../.env')) {
    $readme = is_file(FCPATH . '../README.md') ? file_get_contents(FCPATH . '../README.md') : 'README.md not found.';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Setup Required</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">Configuration File Missing</h4>
                        </div>
                        <div class="card-body">
                            <p class="lead">The <code>.env</code> file was not found in the project root.</p>
                            <p>Please follow the setup instructions from the README below:</p>
                            <div class="bg-light p-3 border rounded mb-4" style="max-height: 400px; overflow-y: auto;">
                                <pre class="mb-0"><code><?= htmlspecialchars($readme) ?></code></pre>
                            </div>
                            <div class="d-grid">
                                <a href="<?= $_SERVER['REQUEST_URI'] ?>" class="btn btn-primary btn-lg">Recheck Configuration</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit(1);
}

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

// Check database connection and initialization
// We need to do this after Boot.php has loaded common functions and autoloader
// but before Boot::bootWeb runs the full application.
// However, Boot::bootWeb returns the exit code after running.
// So we must intercept inside or before.

// Let's do a trick: we define a temporary check here.
class BootWrapper extends Boot {
    public static function setup(Paths $paths) {
        static::definePathConstants($paths);
        static::loadConstants();
        static::loadCommonFunctions();
        static::loadAutoloader();
        static::loadDotEnv($paths);
        static::defineEnvironment();
    }
}

(function () use ($paths) {
    BootWrapper::setup($paths);

    try {
        $db = db_connect();
        $db->connect(); // Force connection
        
        // Check if a key table exists (e.g., movies)
        if (! $db->tableExists('movies')) {
            throw new Exception('Table "movies" does not exist.');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
        $dbUser = $_ENV['database.default.username'] ?? getenv('database.default.username') ?: 'your_username';
        $dbName = $_ENV['database.default.database'] ?? getenv('database.default.database') ?: 'your_database';

        // Restore database values in error if it was masked (common in CI4)
        if (str_contains($error, '****')) {
            if ($dbUser !== 'your_username') {
                $error = str_replace('****', $dbUser, $error);
            } elseif ($dbName !== 'your_database') {
                $error = str_replace('****', $dbName, $error);
            }
        }

        // Check if it's an access error (common codes or keywords)
        $isAccessError = false;
        if (str_contains(strtolower($error), 'access denied') || str_contains(strtolower($error), 'permissions')) {
            $isAccessError = true;
        }

        // Check if database itself is missing
        $isMissingDatabase = false;
        if (str_contains(strtolower($error), 'unknown database')) {
            $isMissingDatabase = true;
        }

        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        if (!$isAccessError && !$isMissingDatabase && $isPost && isset($_POST['action']) && $_POST['action'] === 'initialize') {
            try {
                $db = db_connect();
                $schemaFile = ROOTPATH . '_install/griffith_schema.sql';
                $dataFile = ROOTPATH . '_install/griffith_default_data.sql';
                
                if (!is_file($schemaFile)) {
                    throw new Exception("Schema file not found: $schemaFile");
                }

                $queries = file_get_contents($schemaFile);
                if (isset($_POST['with_data']) && $_POST['with_data'] === '1') {
                    if (is_file($dataFile)) {
                        $queries .= "\n" . file_get_contents($dataFile);
                    }
                }

                // Execute multi-query
                $mysqli = $db->connID;
                if (class_exists('mysqli') && $mysqli instanceof mysqli) {
                    if ($mysqli->multi_query($queries)) {
                        while ($mysqli->next_result()) {
                            if ($mysqli->errno) {
                                break;
                            }
                        }
                    } else {
                        throw new Exception("SQL Error: " . $mysqli->error);
                    }
                } else {
                    // Fallback for other drivers (very basic split)
                    $queryArray = explode(";\n", $queries);
                    foreach ($queryArray as $q) {
                        $q = trim($q);
                        if ($q !== '') {
                            $db->query($q);
                        }
                    }
                }

                // Save additional configuration if provided
                $configValues = [
                    'app.baseURL' => $_POST['app_baseurl'] ?? '',
                    'app.name' => $_POST['app_name'] ?? '',
                ];

                foreach ($configValues as $param => $value) {
                    if ($value !== '') {
                        $db->table('configuration')->replace([
                            'param' => $param,
                            'value' => $value
                        ]);
                    }
                }

                // Upgrade to version 7 schema if selected
                if (isset($_POST['schema_version']) && $_POST['schema_version'] === '7') {
                    try {
                        $db->query("ALTER TABLE `configuration` MODIFY `param` VARCHAR(64) NOT NULL");
                        $db->query("ALTER TABLE `configuration` MODIFY `value` VARCHAR(255) NOT NULL");
                        
                        // Set version to 7
                        $db->table('configuration')->replace([
                            'param' => 'version',
                            'value' => '7'
                        ]);
                    } catch (Throwable $v7ex) {
                        // Ignore if it fails (might already be version 7 or columns exist)
                    }
                } else {
                    // Ensure version is set to 6 for default Griffith schema
                    try {
                        $db->table('configuration')->replace([
                            'param' => 'version',
                            'value' => '6'
                        ]);
                    } catch (Throwable $v6ex) {
                        // Ignore
                    }
                }

                // Redirect to avoid form resubmission and start fresh
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            } catch (Throwable $ex) {
                $error = "Initialization failed: " . $ex->getMessage();
            }
        }

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Error</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        </head>
        <body class="bg-light">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h4 class="mb-0">
                                    <?php if ($isAccessError): ?>
                                        Database Access Error
                                    <?php elseif ($isMissingDatabase): ?>
                                        Database Missing
                                    <?php else: ?>
                                        Database Not Ready
                                    <?php endif; ?>
                                </h4>
                            </div>
                            <div class="card-body">
                                <p class="lead text-danger">Error: <?= htmlspecialchars($error) ?></p>
                                <hr>
                                
                                <?php if ($isAccessError): ?>
                                    <h5>Suggested Permissions Commands</h5>
                                    <p>It seems there is a permissions issue. Please check the following commands from <code>README.md</code>:</p>
                                    
                                    <h6>1. Database Permissions (MySQL/MariaDB):</h6>
                                    <pre class="bg-dark text-light p-3 rounded"><code>GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, REFERENCES, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `<?= htmlspecialchars($dbName) ?>`.* TO '<?= htmlspecialchars($dbUser) ?>'@'localhost';
FLUSH PRIVILEGES;</code></pre>
                                    <p><small class="text-muted">Note: Adjust 'localhost' if your DB is on a different host.</small></p>

                                    <h6>2. File System Permissions:</h6>
                                    <pre class="bg-dark text-light p-3 rounded"><code># From the main folder run the following command:
find writable/ -type d -exec chmod 777 {} \; -print</code></pre>
                                <?php elseif ($isMissingDatabase): ?>
                                    <h5>Create Database Required</h5>
                                    <p>The database <code><?= htmlspecialchars($dbName) ?></code> does not exist. You must create it before the application can initialize the schema.</p>
                                    <p>Run the following command in your MySQL/MariaDB terminal:</p>
                                    <pre class="bg-dark text-light p-3 rounded"><code>CREATE DATABASE `<?= htmlspecialchars($dbName) ?>`;</code></pre>
                                    
                                    <p>After creating the database, ensure your user has permissions:</p>
                                    <pre class="bg-dark text-light p-3 rounded"><code>GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, REFERENCES, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `<?= htmlspecialchars($dbName) ?>`.* TO '<?= htmlspecialchars($dbUser) ?>'@'localhost';
FLUSH PRIVILEGES;</code></pre>
                                <?php else: ?>
                                    <h5>Initialize Database</h5>
                                    <p>The database connection is working, but it seems to be empty or missing the required schema.</p>
                                    <p>Please provide initial configuration and initialize the database:</p>
                                    
                                    <form method="post" class="mb-4">
                                        <input type="hidden" name="action" value="initialize">
                                        
                                        <?php
                                        // Robust protocol detection
                                        $protocol = 'http';
                                        $https_indicators = [
                                            'HTTPS' => ['on', '1', 1],
                                            'HTTP_X_FORWARDED_PROTO' => ['https'],
                                            'HTTP_FRONT_END_HTTPS' => ['on', '1', 1],
                                            'HTTP_X_FORWARDED_SSL' => ['on'],
                                            'HTTP_X_URL_SCHEME' => ['https'],
                                            'REQUEST_SCHEME' => ['https']
                                        ];

                                        foreach ($https_indicators as $key => $values) {
                                            if (isset($_SERVER[$key])) {
                                                $val = is_string($_SERVER[$key]) ? strtolower($_SERVER[$key]) : $_SERVER[$key];
                                                if (in_array($val, $values, true)) {
                                                    $protocol = 'https';
                                                    break;
                                                }
                                            }
                                        }

                                        // Port-based detection if not already detected
                                        if ($protocol === 'http') {
                                            $https_ports = [443, '443'];
                                            if ((isset($_SERVER['SERVER_PORT']) && in_array($_SERVER['SERVER_PORT'], $https_ports, true)) ||
                                                (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && in_array($_SERVER['HTTP_X_FORWARDED_PORT'], $https_ports, true))) {
                                                $protocol = 'https';
                                            }
                                        }
                                        
                                        // Robust host detection
                                        // 1. Check X-Forwarded-Host (often used by proxies)
                                        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
                                        
                                        // 2. Check HTTP_HOST
                                        if (empty($host)) {
                                            $host = $_SERVER['HTTP_HOST'] ?? '';
                                        }
                                        
                                        // 3. Check SERVER_NAME
                                        if (empty($host)) {
                                            $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
                                        }

                                        // If we have a comma-separated list (common in X-Forwarded-Host), take the first one
                                        if (strpos($host, ',') !== false) {
                                            $host = trim(explode(',', $host)[0]);
                                        }
                                        
                                        // If the current host is an IP, but we have a non-IP alternative in SERVER_NAME
                                        if (filter_var($host, FILTER_VALIDATE_IP) && isset($_SERVER['SERVER_NAME']) && !filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP)) {
                                            $host = $_SERVER['SERVER_NAME'];
                                        }

                                        $uri = $_SERVER['REQUEST_URI'] ?? '/';
                                        // Remove public/index.php if it's in the URI to get the base URL
                                        $uri = str_replace(['/index.php', '/public/index.php'], '', $uri);
                                        $currentBaseUrl = rtrim($protocol . "://" . $host . $uri, '/') . '/';
                                        
                                        $showBaseUrl = empty(env('app.baseURL'));
                                        $showAppName = empty(env('app.name'));
                                        ?>

                                        <?php if ($showBaseUrl): ?>
                                        <div class="mb-3">
                                            <label for="app_baseurl" class="form-label">Base URL</label>
                                            <input type="url" class="form-control" id="app_baseurl" name="app_baseurl" value="<?= htmlspecialchars($currentBaseUrl) ?>" required>
                                            <div class="form-text">The URL where the application is hosted (e.g., http://localhost:8080/). Detected from your current request.</div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($showAppName): ?>
                                        <div class="mb-3">
                                            <label for="app_name" class="form-label">Application Name</label>
                                            <input type="text" class="form-control" id="app_name" name="app_name" value="Media Organizer" placeholder="e.g. My Media Library">
                                            <div class="form-text">The name displayed in the browser title and header.</div>
                                        </div>
                                        <?php endif; ?>

                                        <?php
                                        $initialPassword = env('auth.initialPassword', 'admin123');
                                        ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Default Login:</strong> The default administrator password is <code><?= htmlspecialchars($initialPassword) ?></code>. 
                                            You should change this immediately after your first login.
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label d-block">Database Schema</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="schema_version" id="schemaMedia" value="7" checked>
                                                <label class="form-check-label" for="schemaMedia">
                                                    Updated Media Organizer schema (Version 7)
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="schema_version" id="schemaGriffith" value="6">
                                                <label class="form-check-label" for="schemaGriffith">
                                                    Default Griffith schema (Version 6)
                                                </label>
                                            </div>
                                            <div class="form-text">Version 7 increases field lengths for better compatibility.</div>
                                        </div>

                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="with_data" value="1" id="withData" checked>
                                            <label class="form-check-label" for="withData">
                                                Include default data (recommended for new setups)
                                            </label>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success btn-lg">Initialize Database</button>
                                        </div>
                                    </form>

                                    <script>
                                        // JavaScript fallback to ensure protocol and host match what the user is seeing
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const baseUrlInput = document.getElementById('app_baseurl');
                                            if (baseUrlInput) {
                                                let currentUrl = window.location.href;
                                                // Remove /index.php or /public/index.php and trailing stuff
                                                let detectedBase = currentUrl.split('/index.php')[0].split('/public/')[0];
                                                if (!detectedBase.endsWith('/')) {
                                                    detectedBase += '/';
                                                }
                                                
                                                // If JS detects https but PHP didn't, or if the host is different, update it
                                                // We only update if it was 'http' but the browser is 'https' to be safe
                                                if (window.location.protocol === 'https:' && baseUrlInput.value.startsWith('http:')) {
                                                    baseUrlInput.value = detectedBase;
                                                }
                                            }
                                        });
                                    </script>

                                    <hr>

                                    <h5>Manual Initialization Required</h5>
                                    <p>Alternatively, you can run the following commands manually:</p>
                                    <pre class="bg-dark text-light p-3 rounded"><code>mysql -u <?= htmlspecialchars($dbUser) ?> -p <?= htmlspecialchars($dbName) ?> < _install/griffith_schema.sql
# And optionally:
mysql -u <?= htmlspecialchars($dbUser) ?> -p <?= htmlspecialchars($dbName) ?> < _install/griffith_default_data.sql</code></pre>
                                <?php endif; ?>

                                <div class="d-grid gap-2 mt-4">
                                    <a href="<?= $_SERVER['REQUEST_URI'] ?>" class="btn btn-primary btn-lg">Check Connection Again</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit(1);
    }
})();

exit(Boot::bootWeb($paths));
