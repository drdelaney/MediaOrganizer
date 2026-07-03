<?php

namespace App\Controllers;

use Config\Database;
use Config\Migrations;

class Setup extends BaseController
{
    public function index()
    {
        if (env('app.setupComplete') === true) {
            return redirect()->to('/')->with('error', 'Setup has already been completed.');
        }

        // Debug: Check if we can see migrations
        $migrations = \Config\Services::migrations();
        $available = $migrations->findMigrations();
        log_message('debug', 'Setup::index - Found ' . count($available) . ' migrations');
        foreach ($available as $m) {
            log_message('debug', 'Setup::index - Migration: ' . $m->name . ' in namespace ' . $m->namespace);
        }

        $data = [
            'title' => 'Setup Media Organizer',
            'checks' => $this->getChecks(),
            'setupComplete' => env('app.setupComplete') === true,
            'initialPassword' => env('auth.initialPassword'),
            'appBaseURL' => base_url(),
            'appName' => 'Media Organizer',
            'timezones' => \DateTimeZone::listIdentifiers(),
        ];

        return view('setup/index', $data);
    }

    private function getChecks()
    {
        $db = Database::connect();
        $checks = [];

        try {
            $checks['db_connection'] = [
                'name' => 'Database Connection',
                'status' => 'success',
                'message' => 'Successfully connected to ' . $db->getPlatform()
            ];
        } catch (\Throwable $e) {
            $checks['db_connection'] = [
                'name' => 'Database Connection',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        $requiredExtensions = ['intl', 'mbstring'];
        $dbDriver = $db->getPlatform();
        $dbExtensions = [
            'MySQLi' => 'mysqli',
            'SQLite3' => 'sqlite3',
            'Postgre' => 'pgsql',
            'SQLSRV' => 'sqlsrv',
        ];

        if (isset($dbExtensions[$dbDriver])) {
            $requiredExtensions[] = $dbExtensions[$dbDriver];
        }

        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (! extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        $checks['php_extensions'] = [
            'name' => 'PHP Extensions (' . implode(', ', $requiredExtensions) . ')',
            'status' => empty($missingExtensions) ? 'success' : 'error',
            'message' => empty($missingExtensions) ? 'All required extensions are loaded.' : 'Missing: ' . implode(', ', $missingExtensions)
        ];

        $checks['setup_complete'] = [
            'name' => 'Setup Complete Flag',
            'status' => env('app.setupComplete') === true ? 'success' : 'warning',
            'message' => env('app.setupComplete') === true ? 'Setup is already marked as complete in .env' : 'Setup is not yet marked as complete.'
        ];

        $initialPassword = env('auth.initialPassword');
        if (!empty($initialPassword)) {
            $checks['initial_password'] = [
                'name' => 'Initial Password',
                'status' => 'info',
                'message' => 'Initial password is set from <code>.env</code>'
            ];
        } else {
            $checks['initial_password'] = [
                'name' => 'Initial Password',
                'status' => 'warning',
                'message' => 'Initial password is NOT set in <code>.env</code>. You will be asked to set it below.'
            ];
        }

        return $checks;
    }

    public function run()
    {
        if (env('app.setupComplete') === true) {
            return redirect()->to('/')->with('error', 'Setup has already been completed.');
        }

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('setup');
        }

        // Increase execution time for migrations and seeds
        set_time_limit(300);

        $migrations = \Config\Services::migrations();
        $seeder = Database::seeder();

        try {
            // Check for missing extensions
            $requiredExtensions = ['intl', 'mbstring'];
            $dbDriver = Database::connect()->getPlatform();
            $dbExtensions = [
                'MySQLi' => 'mysqli',
                'SQLite3' => 'sqlite3',
                'Postgre' => 'pgsql',
                'SQLSRV' => 'sqlsrv',
            ];

            if (isset($dbExtensions[$dbDriver])) {
                $requiredExtensions[] = $dbExtensions[$dbDriver];
            }

            $missingExtensions = [];
            foreach ($requiredExtensions as $ext) {
                if (! extension_loaded($ext)) {
                    $missingExtensions[] = $ext;
                }
            }
            if (! empty($missingExtensions)) {
                throw new \RuntimeException('The following PHP extensions are missing: ' . implode(', ', $missingExtensions) . '. Please enable them in your php.ini');
            }

            // Run Migrations
            log_message('debug', 'Setup::run - Starting migrations');
            
            // Log Migration settings
            $migConfig = config('Migrations');
            log_message('debug', 'Setup::run - Migrations Enabled: ' . ($migConfig->enabled ? 'yes' : 'no'));
            // log_message('debug', 'Setup::run - Migrations Path: ' . $migConfig->path); // Property doesn't exist
            
            // Log current migration state
            $history = $migrations->getHistory();
            log_message('debug', 'Setup::run - Current history count: ' . count($history));
            
            // Check if migrations are actually enabled and findable
            if (! config('Migrations')->enabled) {
                 throw new \RuntimeException('Migrations are disabled in Config\Migrations');
            }

            // Force namespace if it's not picking it up
            log_message('debug', 'Setup::run - Attempting migrations->latest()');
            
            // Try manually discovering migrations if latest('App') fails
            $history = $migrations->getHistory();
            log_message('debug', 'Setup::run - History before: ' . count($history));
            
            // Also list discovered migrations here for comparison
            $found = $migrations->findMigrations();
            log_message('debug', 'Setup::run - Discovered ' . count($found) . ' migrations');

            if ($migrations->latest('App')) {
                log_message('debug', 'Setup::run - Migrations finished successfully');
            } else {
                 log_message('debug', 'Setup::run - Migrations latest(\'App\') returned false/failed. Trying without namespace.');
                 if ($migrations->latest()) {
                     log_message('debug', 'Setup::run - Migrations latest() successful');
                 } else {
                     log_message('error', 'Setup::run - Migrations failed completely');
                 }
            }
            
            $history = $migrations->getHistory();
            log_message('debug', 'Setup::run - History after: ' . count($history));
            
            // Run Seeds if database is empty
            $db = Database::connect();
            
            // For SQLite, make sure we have the tables before seeding
            if ($db->getPlatform() === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = OFF');
            }

            $tableExists = $db->tableExists('configuration');
            log_message('debug', 'Setup::run - tableExists(configuration): ' . ($tableExists ? 'yes' : 'no'));

            if ($tableExists) {
                $count = $db->table('configuration')->countAllResults();
                log_message('debug', 'Setup::run - configuration count: ' . $count);
                if ($count === 0 || ($count === 1 && $db->table('configuration')->where('param', 'version')->countAllResults() === 1)) {
                    log_message('debug', 'Setup::run - Calling InitialSeeder');
                    $seeder->call('InitialSeeder');
                    log_message('debug', 'Setup::run - InitialSeeder finished');
                }

                // Update configuration with form data
                $configModel = new \App\Models\ConfigurationModel();
                
                $appName = $this->request->getPost('app_name') ?: 'Media Organizer';
                $timezone = $this->request->getPost('timezone') ?: 'UTC';
                $initialPassword = $this->request->getPost('initial_password');
                
                $configModel->setParam('app.name', $appName);
                $configModel->setParam('app.baseURL', base_url());
                $configModel->setParam('timezone', $timezone);
                $configModel->setParam('deauth_time', '5');
                
                if (!empty($initialPassword)) {
                    $hash = password_hash($initialPassword, PASSWORD_DEFAULT);
                    $configModel->setParam('password_hash', $hash);
                }
            } else {
                log_message('debug', 'Setup::run - configuration table MISSING after migrations!');
                // Attempt to list tables to debug
                $tables = $db->listTables();
                log_message('debug', 'Setup::run - Existing tables: ' . implode(', ', $tables));
                
                throw new \RuntimeException('The "configuration" table was not created during migrations. Please check your database permissions.');
            }

            if ($db->getPlatform() === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = ON');
            }

            log_message('debug', 'Setup::run - Redirecting to setup/complete');
            return redirect()->to('setup/complete');
        } catch (\Throwable $e) {
            log_message('error', 'Setup::run - Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->to('setup')->with('error', $e->getMessage());
        }
    }

    public function markMigrationComplete()
    {
        log_message('debug', 'Setup::markMigrationComplete - Started');
        if (env('app.setupComplete') === true) {
            log_message('debug', 'Setup::markMigrationComplete - Setup already complete in env');
            return redirect()->to('/')->with('error', 'Setup has already been completed.');
        }

        if ($this->request->getMethod() !== 'POST') {
            log_message('debug', 'Setup::markMigrationComplete - Not a POST request: ' . $this->request->getMethod());
            return redirect()->to('setup');
        }

        $db = Database::connect();
        $migrationTable = config('Migrations')->table ?? 'migrations';
        log_message('debug', 'Setup::markMigrationComplete - Migration table: ' . $migrationTable);

        // Ensure the migrations table exists
        if (!$db->tableExists($migrationTable)) {
            log_message('debug', 'Setup::markMigrationComplete - Creating migration table');
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'version' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'class' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'group' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'namespace' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'time' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'batch' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->createTable($migrationTable, true);
        }

        $version = '2026-05-21-000001';
        // Class name MUST match the actual class in the migration file
        $class   = 'App\Database\Migrations\InitialSchema';

        // Check if ANY migration with this version exists
        $existing = $db->table($migrationTable)
            ->where('version', $version)
            ->get()
            ->getResultArray();
        
        log_message('debug', 'Setup::markMigrationComplete - Existing records for version ' . $version . ': ' . json_encode($existing));

        if (empty($existing)) {
            // Get the current max batch and increment
            $maxBatch = $db->table($migrationTable)->selectMax('batch')->get()->getRow();
            $batch = ($maxBatch && isset($maxBatch->batch)) ? (int)$maxBatch->batch + 1 : 1;
            log_message('debug', 'Setup::markMigrationComplete - Inserting new record with batch ' . $batch);

            $data = [
                'version'   => $version,
                'class'     => $class,
                'group'     => 'App',
                'namespace' => 'App',
                'time'      => time(),
                'batch'     => $batch,
            ];

            try {
                $result = $db->table($migrationTable)->insert($data);
                log_message('debug', 'Setup::markMigrationComplete - Insert result: ' . ($result ? 'success' : 'failed'));
                
                if (!$result) {
                    $error = $db->error();
                    log_message('error', 'Setup::markMigrationComplete - Insert failed: ' . json_encode($error));
                    session()->setFlashdata('error', 'Failed to update database: ' . $error['message']);
                    return redirect()->to('setup');
                }
            } catch (\Throwable $e) {
                log_message('error', 'Setup::markMigrationComplete - Insert exception: ' . $e->getMessage());
                session()->setFlashdata('error', 'Database error: ' . $e->getMessage());
                return redirect()->to('setup');
            }

            session()->setFlashdata('message', 'Initial schema migration marked as complete.');
            return redirect()->to('setup');
        } else {
            // If it exists but with wrong group or class, update it
            log_message('debug', 'Setup::markMigrationComplete - Record exists, checking group and class');
            
            try {
                $updateData = [];
                $firstRecord = $existing[0];

                if ($firstRecord['group'] !== 'App') {
                    $updateData['group'] = 'App';
                }
                if ($firstRecord['class'] !== $class) {
                    $updateData['class'] = $class;
                }

                if (!empty($updateData)) {
                    $updateResult = $db->table($migrationTable)
                        ->where('version', $version)
                        ->update($updateData);
                    
                    log_message('debug', 'Setup::markMigrationComplete - Update result: ' . ($updateResult ? 'success' : 'no changes') . ' Updated: ' . json_encode($updateData));
                    session()->setFlashdata('message', 'Initial schema migration record corrected and marked as complete.');
                } else {
                    log_message('debug', 'Setup::markMigrationComplete - Record already correct');
                    session()->setFlashdata('message', 'Initial schema migration was already marked as complete.');
                }
            } catch (\Throwable $e) {
                log_message('error', 'Setup::markMigrationComplete - Update exception: ' . $e->getMessage());
                session()->setFlashdata('error', 'Database update error: ' . $e->getMessage());
                return redirect()->to('setup');
            }
        }

        return redirect()->to('setup');
    }

    public function complete()
    {
        // NOTE: The user is expected to manually update .env for now, 
        // as per instructions: "i will handle this myself" for .env.example
        // but wait, "we can lock this out with a .env file update called "app.setupComplete""
        // I will try to update .env if it's writable, otherwise I'll just show the message.
        
        $envFile = ROOTPATH . '.env';
        $updated = false;
        
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (strpos($content, 'app.setupComplete') !== false) {
                $content = preg_replace('/app\.setupComplete\s*=\s*(true|false|0|1)/', 'app.setupComplete = true', $content);
            } else {
                $content .= "\napp.setupComplete = true\n";
            }
            
            if (is_writable($envFile)) {
                file_put_contents($envFile, $content);
                $updated = true;
            }
        }

        $data = [
            'title' => 'Setup Complete',
            'updated' => $updated
        ];

        return view('setup/complete', $data);
    }
}
