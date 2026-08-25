<?php

namespace App\Controllers;

use App\Models\MediumModel;
use App\Models\CollectionModel;
use App\Models\VolumeModel;
use App\Models\VCodecModel;
use App\Models\PeopleModel;
use App\Models\AChannelModel;
use App\Models\ACodecModel;
use App\Models\LanguageModel;
use App\Models\PosterModel;
use App\Models\RatioModel;
use App\Models\SubformatModel;

class DatabaseMaintenance extends BaseController
{
    protected $db;
    protected $mediaModel;
    protected $collectionModel;
    protected $volumeModel;
    protected $vcodecModel;
    protected $peopleModel;
    protected $achannelModel;
    protected $acodecModel;
    protected $languageModel;
    protected $posterModel;
    protected $ratioModel;
    protected $subformatModel;
    protected $cronJobModel;
    protected $allowedTables = [
        'achannels', 'acodecs', 'collections', 'configuration', 'cron_jobs', 'filters', 'languages', 'loans', 'media',
        'migrations', 'movie_lang', 'movie_tag', 'movies', 'people', 'posters',
        'ratios', 'subformats', 'tags', 'vcodecs', 'volumes'
    ];

    public function __construct()
    {
        helper(['form', 'url', 'timezone']);
        $this->db = \Config\Database::connect();
        $this->mediaModel = new MediumModel();
        $this->collectionModel = new CollectionModel();
        $this->volumeModel = new VolumeModel();
        $this->vcodecModel = new VCodecModel();
        $this->peopleModel = new PeopleModel();
        $this->achannelModel = new AChannelModel();
        $this->acodecModel = new ACodecModel();
        $this->languageModel = new LanguageModel();
        $this->posterModel = new PosterModel();
        $this->ratioModel = new RatioModel();
        $this->subformatModel = new SubformatModel();
        $this->cronJobModel = new \App\Models\CronJobModel();
    }

    /**
     * Re-index all tables in the database
     */
    public function reindexTables()
    {
        try {
            $tables = $this->getAllTables();
            $results = [];

            foreach ($tables as $table) {
                $result = $this->reindexTable($table);
                $results[] = $result;
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Database re-indexing completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error during re-indexing: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Optimize all tables
     */
    public function optimizeTables()
    {
        try {
            $tables = $this->getAllTables();
            $results = [];

            $driver = $this->db->getPlatform();

            foreach ($tables as $table) {
                if ($this->validateTable($table)) {
                    if ($driver === 'SQLite3') {
                        // SQLite uses VACUUM for the whole database, but we can't easily 
                        // run VACUUM per table via simple query if it's already in a transaction 
                        // (though CI queries aren't usually in one unless specified).
                        // However, ANALYZE is per table and helps with optimization.
                        $this->db->query("ANALYZE `{$table}`");
                        $results[] = "Analyzed table: {$table}";
                    } elseif ($driver === 'MySQLi') {
                        $this->db->query("OPTIMIZE TABLE `{$table}`");
                        $results[] = "Optimized table: {$table} (MySQL)";
                    } elseif ($driver === 'Postgre') {
                        $this->db->query("VACUUM FULL `{$table}`");
                        $results[] = "Optimized table: {$table} (Postgres)";
                    } else {
                        $results[] = "Optimization not supported for " . $driver;
                    }
                } else {
                    $results[] = "Skipped invalid table: {$table}";
                }
            }

            if ($driver === 'SQLite3') {
                // For SQLite, we also run VACUUM once at the end
                $this->db->query("VACUUM");
                $results[] = "Database VACUUM completed";
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Database optimization completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error during optimization: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Convert tables to InnoDB engine
     */
    public function convertToInnoDB()
    {
        try {
            $driver = $this->db->getPlatform();
            if ($driver === 'SQLite3') {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Engine conversion skipped (SQLite does not use storage engines like MySQL)',
                    'results' => ['SQLite does not support engine conversion. Tables are always in SQLite format.']
                ]);
            }

            // Engine conversion is MySQL-specific
            if ($driver !== 'MySQLi') {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Engine conversion not applicable for ' . $driver,
                    'results' => []
                ]);
            }

            $tables = $this->getAllTables();
            $results = [];

            foreach ($tables as $table) {
                if (!$this->validateTable($table)) {
                    $results[] = "Skipped invalid table: {$table}";
                    continue;
                }

                // Check current engine
                $engineQuery = "SELECT ENGINE FROM information_schema.TABLES 
                               WHERE TABLE_SCHEMA = DATABASE() 
                               AND TABLE_NAME = ?";
                $engineResult = $this->db->query($engineQuery, [$table])->getRowArray();

                if ($engineResult && $engineResult['ENGINE'] !== 'InnoDB') {
                    $this->db->query("ALTER TABLE `{$table}` ENGINE = InnoDB");
                    $results[] = "Converted {$table} from {$engineResult['ENGINE']} to InnoDB";
                } else {
                    $results[] = "Table {$table} is already InnoDB";
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Engine conversion completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error during engine conversion: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Fix configuration schema if needed
     */
    public function fixConfigSchema()
    {
        try {
            $driver = $this->db->getPlatform();
            if ($driver === 'SQLite3') {
                // SQLite doesn't support MODIFY COLUMN. 
                // We'd need to recreate the table, but since SQLite has dynamic typing,
                // VARCHAR(length) isn't strictly enforced for storage.
                // We can skip this or just update the version.
                
                $configModel = new \App\Models\ConfigurationModel();
                $configModel->setParam('version', '7');

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Configuration version updated (SQLite schema adjustment skipped)'
                ]);
            }

            // Check if column lengths are already sufficient
            $fields = $this->db->getFieldData('configuration');
            $paramLength = 0;
            $valueLength = 0;

            foreach ($fields as $field) {
                if ($field->name === 'param') $paramLength = $field->max_length;
                if ($field->name === 'value') $valueLength = $field->max_length;
            }

            if ($paramLength >= 64 && $valueLength >= 255) {
                $configModel = new \App\Models\ConfigurationModel();
                $configModel->setParam('version', '7');
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Configuration schema is already up to date'
                ]);
            }

            // Apply schema fix (MySQL specific MODIFY COLUMN)
            $this->db->query("ALTER TABLE configuration MODIFY COLUMN param VARCHAR(64) NOT NULL");
            $this->db->query("ALTER TABLE configuration MODIFY COLUMN value VARCHAR(255) NOT NULL");
            
            $configModel = new \App\Models\ConfigurationModel();
            $configModel->setParam('version', '7');

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Configuration schema fixed successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error fixing configuration schema: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX endpoint for environment checks
     */
    public function checkEnvironmentAjax()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $checks = $this->checkEnvironment();
            
            // Determine overall status
            $hasErrors = false;
            $hasWarnings = false;
            
            foreach ($checks as $check) {
                if ($check['status'] === 'error') {
                    $hasErrors = true;
                }
                if ($check['status'] === 'warning') {
                    $hasWarnings = true;
                }
            }
            
            $status = $hasErrors ? 'error' : ($hasWarnings ? 'warning' : 'success');
            $message = $hasErrors ? 'Some critical checks failed' : ($hasWarnings ? 'All checks passed with warnings' : 'All checks passed');
            
            return $this->response->setJSON([
                'status' => $status,
                'message' => $message,
                'results' => $checks
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error checking environment: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check PHP and package environment
     */
    private function checkEnvironment()
    {
        $checks = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $minPhpVersion = '7.4.0';
        $checks['php_version'] = [
            'name' => 'PHP Version',
            'current' => $phpVersion,
            'required' => '>= ' . $minPhpVersion,
            'status' => version_compare($phpVersion, $minPhpVersion, '>=') ? 'success' : 'error',
            'message' => version_compare($phpVersion, $minPhpVersion, '>=') ? 'PHP version is compatible' : 'PHP version is too old'
        ];

        // Required PHP Extensions
        $requiredExtensions = [
            'intl' => 'Internationalization support',
            'mbstring' => 'Multibyte string support',
            'json' => 'JSON support',
            'curl' => 'CURL support for API calls',
            'openssl' => 'OpenSSL for secure connections',
            'xml' => 'XML support',
            'filter' => 'Data filtering',
            'hash' => 'Hash functions',
        ];

        // Database specific extensions
        $dbDriver = $this->db->getPlatform();
        $dbExtensions = [
            'MySQLi' => ['mysqli' => 'MySQL database support'],
            'SQLite3' => ['sqlite3' => 'SQLite3 database support'],
            'Postgre' => ['pgsql' => 'PostgreSQL database support'],
            'SQLSRV' => ['sqlsrv' => 'MSSQL database support'],
        ];

        // Add the current DB extension to required
        if (isset($dbExtensions[$dbDriver])) {
            foreach ($dbExtensions[$dbDriver] as $ext => $desc) {
                $requiredExtensions[$ext] = $desc;
            }
        }

        foreach ($requiredExtensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $checks['ext_' . $ext] = [
                'name' => 'PHP Extension: ' . $ext,
                'description' => $description,
                'status' => $loaded ? 'success' : 'error',
                'message' => $loaded ? 'Installed' : 'Missing - ' . $description
            ];
        }

        // Optional but recommended extensions
        $optionalExtensions = [
            'gd' => 'Image processing support',
            'imagick' => 'Advanced image processing',
            'zip' => 'ZIP archive support',
            'fileinfo' => 'File information support'
        ];

        // Add other database extensions as optional if not currently used
        // Removed as per user request to only check for configured engine

        foreach ($optionalExtensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $checks['opt_' . $ext] = [
                'name' => 'Optional Extension: ' . $ext,
                'description' => $description,
                'status' => $loaded ? 'success' : 'warning',
                'message' => $loaded ? 'Installed' : 'Not installed - ' . $description
            ];
        }

        // CodeIgniter framework check
        $ciVersion = \CodeIgniter\CodeIgniter::CI_VERSION;
        $checks['framework'] = [
            'name' => 'CodeIgniter Framework',
            'current' => $ciVersion,
            'status' => 'success',
            'message' => 'CodeIgniter ' . $ciVersion . ' loaded successfully'
        ];

        // Memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->returnBytes($memoryLimit);
        $recommendedMemory = 128 * 1024 * 1024; // 128MB
        $checks['memory'] = [
            'name' => 'PHP Memory Limit',
            'current' => $memoryLimit,
            'recommended' => '128M or higher',
            'status' => $memoryLimitBytes >= $recommendedMemory ? 'success' : 'warning',
            'message' => $memoryLimitBytes >= $recommendedMemory ? 'Memory limit is adequate' : 'Memory limit may be too low for large operations'
        ];

        return $checks;
    }

    /**
     * Convert PHP ini memory value to bytes
     */
    private function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    /**
     * Show maintenance dashboard
     */
    public function index()
    {
        $configModel = new \App\Models\ConfigurationModel();
        
        // Ensure default version exists if not present
        $configModel->ensureParam('version', '0');
        
        $configVersion = (int)$configModel->getParam('version', 0);
        $needsFix = $configVersion < 7;

        $migrations = \Config\Services::migrations(null, $this->db);
        // Ensure we check the App namespace for our migrations
        $available = $migrations->setNamespace('App')->findMigrations();
        
        // Get all applied migrations from history across all groups/namespaces for better accuracy
        $history = [];
        if ($this->db->tableExists('migrations')) {
            $history = $this->db->table('migrations')->get()->getResult();
        }

        $historyUids = [];
        foreach ($history as $row) {
            // Replicate CI4's getObjectUid logic: stripped version + class name
            $historyUids[] = preg_replace('/[^0-9]/', '', $row->version) . $row->class;
        }

        $hasPendingMigrations = false;
        foreach ($available as $migration) {
            if (!in_array($migration->uid, $historyUids)) {
                $hasPendingMigrations = true;
                break;
            }
        }

        // Check for pending notifications (overdue loans with notifications enabled)
        $hasPendingNotifications = false;
        $overdueCount = 0;
        try {
            $loanModel = new \App\Models\LoanModel();
            $loanedMedia = $loanModel->getAllLoanedMedia();
            $now = time();
            $thirtyDaysAgo = $now - (30 * 24 * 60 * 60);

            foreach ($loanedMedia as $loan) {
                if (!empty($loan['date'])) {
                    $loanDate = strtotime($loan['date']);
                    if ($loanDate < $thirtyDaysAgo) {
                        // Check if notifications are enabled for this person and they have an email
                        if (($loan['person_notifications'] ?? 1) == 1 && !empty($loan['person_email'])) {
                            $hasPendingNotifications = true;
                            $overdueCount++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Could not check pending notifications: ' . $e->getMessage());
        }

        // Get current migration info directly from the database for accuracy
        $currentMigration = 'None';
        $lastAppliedTime = null;
        
        if ($this->db->tableExists('migrations')) {
            $lastMigration = $this->db->table('migrations')
                ->orderBy('id', 'DESC')
                ->get(1)
                ->getRow();
                
            if ($lastMigration) {
                $className = $lastMigration->class;
                // Strip namespace if present for cleaner display
                if (($pos = strrpos($className, '\\')) !== false) {
                    $className = substr($className, $pos + 1);
                }
                $currentMigration = $lastMigration->version . ' (' . $className . ')';
                $lastAppliedTime = $lastMigration->time;
            }
        }

        $data = [
            'title' => 'Database Maintenance',
            'tables' => $this->getTableInfo(),
            'environment' => $this->checkEnvironment(),
            'needsFix' => $needsFix,
            'hasPendingMigrations' => $hasPendingMigrations,
            'hasPendingNotifications' => $hasPendingNotifications,
            'overdueCount' => $overdueCount,
            'currentMigration' => $currentMigration,
            'lastAppliedTime' => $lastAppliedTime,
            'dbDriver' => $this->db->getPlatform(),
            'cronJobs' => $this->getCronJobsData(),
            'systemCronLastRun' => $configModel->getParam('system_cron_last_run')
        ];

        return view('database_maintenance/index', $data);
    }

    /**
     * Get all table names in the database
     */
    private function getAllTables()
    {
        return $this->db->listTables();
    }

    /**
     * Validate table name against whitelist
     */
    private function validateTable($table)
    {
        return in_array($table, $this->allowedTables);
    }

    /**
     * Re-index a specific table
     */
    private function reindexTable($table)
    {
        try {
            if (!$this->validateTable($table)) {
                return "Error: Invalid table name {$table}";
            }

            // Database agnostic re-indexing or maintenance is limited in CI4
            // but we can at least check if indexes exist
            $indexes = $this->db->getIndexData($table);

            if (empty($indexes)) {
                return "No indexes found for table: {$table}";
            }

            // Platform-specific maintenance
            if ($this->db->getPlatform() === 'MySQLi') {
                $this->db->query("ALTER TABLE `{$table}` DISABLE KEYS");
                $this->db->query("ALTER TABLE `{$table}` ENABLE KEYS");
                return "Re-indexed table: {$table} (MySQL)";
            } elseif ($this->db->getPlatform() === 'SQLite3') {
                $this->db->query("REINDEX `{$table}`");
                return "Re-indexed table: {$table} (SQLite)";
            } elseif ($this->db->getPlatform() === 'Postgre') {
                $this->db->query("REINDEX TABLE `{$table}`");
                return "Re-indexed table: {$table} (Postgres)";
            }

            return "Index check completed for: {$table}";

        } catch (\Exception $e) {
            return "Error re-indexing {$table}: " . $e->getMessage();
        }
    }

    /**
     * Apply pending migrations
     */
    public function applyMigrations()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('database-maintenance');
        }

        // Consolidate migration groups to avoid double-application due to group mismatch
        if ($this->db->tableExists('migrations')) {
            $defaultGroup = config('Database')->defaultGroup ?? 'default';
            $this->db->table('migrations')
                ->where('group !=', $defaultGroup)
                ->update(['group' => $defaultGroup]);
        }

        try {
            $migrations = \Config\Services::migrations(null, $this->db);
            $migrations->setNamespace('App');
            
            if ($migrations->latest()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Latest migrations applied successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No migrations to apply or migration failed.'
                ]);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Migration failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get detailed table information
     */
    private function getTableInfo()
    {
        if ($this->db->getPlatform() === 'SQLite3') {
            $tables = $this->db->listTables();
            $info = [];
            foreach ($tables as $table) {
                if (in_array($table, $this->allowedTables)) {
                    // Get row count
                    $rowCount = $this->db->table($table)->countAllResults();

                    // Get table size (approximate for SQLite)
                    // Note: SQLite doesn't easily provide size per table like MySQL
                    $info[] = [
                        'name' => $table,
                        'engine' => 'SQLite',
                        'rows' => $rowCount,
                        'size_mb' => 0 // SQLite doesn't provide easy per-table size
                    ];
                }
            }
            return $info;
        }

        $results = [];
        foreach ($this->allowedTables as $table) {
            if (!$this->db->tableExists($table)) continue;

            $info = [
                'name' => $table,
                'engine' => 'N/A',
                'rows' => $this->db->table($table)->countAllResults(),
                'size_mb' => 'N/A'
            ];

            if ($this->db->getPlatform() === 'MySQLi') {
                $q = $this->db->query("SELECT ENGINE, ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as size_mb 
                                     FROM information_schema.TABLES 
                                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table])->getRowArray();
                if ($q) {
                    $info['engine'] = $q['ENGINE'];
                    $info['size_mb'] = $q['size_mb'];
                }
            } elseif ($this->db->getPlatform() === 'SQLite3') {
                $info['engine'] = 'SQLite';
                // Try to get size from database file
                $dbPath = $this->db->database;
                if (file_exists($dbPath)) {
                    $info['size_mb'] = round(filesize($dbPath) / 1024 / 1024, 2);
                }
            } elseif ($this->db->getPlatform() === 'Postgre') {
                $info['engine'] = 'PostgreSQL';
                $q = $this->db->query("SELECT pg_size_pretty(pg_total_relation_size(?)) as size", [$table])->getRowArray();
                if ($q) {
                    $info['size_mb'] = $q['size'];
                }
            }

            $results[] = $info;
        }
        return $results;
    }

    /**
     * Show lookup tables management page
     */
    public function ping()
    {
        return $this->response->setJSON(['status' => 'success', 'message' => 'Timer renewed']);
    }

    public function manageLookups()
    {
        $tagModel = new \App\Models\TagModel();
        $tags = $tagModel->orderBy('name', 'ASC')->findAll();

        // Get movie count for each tag
        foreach ($tags as &$tag) {
            $tag['movie_count'] = $tagModel->getMediaCountForTag($tag['tag_id']);
        }

        $configModel = new \App\Models\ConfigurationModel();
        
        // Ensure default settings exist in the database for the form
        $configModel->ensureParam('timezone', 'UTC');
        $configModel->ensureParam('deauth_time', '15');
        $configModel->ensureParam('user_agent', 'MediaOrganizer/1.0');
        $configModel->ensureParam('app.name', 'Media Organizer');
        $configModel->ensureParam('app.baseURL', 'http://localhost:8080/');
        $configModel->ensureParam('ENABLED_LOOKUPS', 'IMDB,TVDB');

        $currentTimezone = $configModel->getParam('timezone', 'UTC');
        $deauthTime = $configModel->getParam('deauth_time', 15);
        $userAgent = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');
        $appName = $configModel->getParam('app.name', 'Media Organizer');
        $appBaseURL = $configModel->getParam('app.baseURL', 'http://localhost:8080/');
        $enabledLookups = $configModel->getParam('ENABLED_LOOKUPS', 'IMDB,TVDB');

        $lookupSettings = [
            'TMDB_API_KEY' => $configModel->getParam('TMDB_API_KEY', ''),
            'IMDB_API_KEY' => $configModel->getParam('IMDB_API_KEY', ''),
            'TVDB_API_KEY' => $configModel->getParam('TVDB_API_KEY', ''),
            'IGDB_CLIENT_ID' => $configModel->getParam('IGDB_CLIENT_ID', ''),
            'IGDB_CLIENT_SECRET' => $configModel->getParam('IGDB_CLIENT_SECRET', ''),
            'MUSICBRAINZ_EMAIL' => $configModel->getParam('MUSICBRAINZ_EMAIL', ''),
            'UPCITEMDB_API_KEY' => $configModel->getParam('UPCITEMDB_API_KEY', ''),
            'ENABLED_LOOKUPS' => $enabledLookups,
        ];

        $emailSettings = [
            'protocol' => $configModel->getParam('email.protocol', 'mail'),
            'SMTPHost' => $configModel->getParam('email.SMTPHost', ''),
            'SMTPUser' => $configModel->getParam('email.SMTPUser', ''),
            'SMTPPass' => $configModel->getParam('email.SMTPPass', ''),
            'SMTPPort' => $configModel->getParam('email.SMTPPort', 25),
            'SMTPCrypto' => $configModel->getParam('email.SMTPCrypto', 'tls'),
            'fromEmail' => $configModel->getParam('email.fromEmail', ''),
            'fromName' => $configModel->getParam('email.fromName', 'Media Organizer'),
            'SMTPVerifyPeer' => $configModel->getParam('email.SMTPVerifyPeer', 'true'),
            'SMTPVerifyPeerName' => $configModel->getParam('email.SMTPVerifyPeerName', 'true'),
        ];

        // Identify which settings are overridden by .env
        $overridden = [
            'app.name' => $configModel->isEnvOverridden('app.name'),
            'app.baseURL' => $configModel->isEnvOverridden('app.baseURL'),
            'timezone' => $configModel->isEnvOverridden('timezone'),
            'deauth_time' => $configModel->isEnvOverridden('deauth_time'),
            'user_agent' => $configModel->isEnvOverridden('user_agent'),
            'TMDB_API_KEY' => $configModel->isEnvOverridden('TMDB_API_KEY'),
            'IMDB_API_KEY' => $configModel->isEnvOverridden('IMDB_API_KEY'),
            'TVDB_API_KEY' => $configModel->isEnvOverridden('TVDB_API_KEY'),
            'IGDB_CLIENT_ID' => $configModel->isEnvOverridden('IGDB_CLIENT_ID'),
            'IGDB_CLIENT_SECRET' => $configModel->isEnvOverridden('IGDB_CLIENT_SECRET'),
            'MUSICBRAINZ_EMAIL' => $configModel->isEnvOverridden('MUSICBRAINZ_EMAIL'),
            'UPCITEMDB_API_KEY' => $configModel->isEnvOverridden('UPCITEMDB_API_KEY'),
            'ENABLED_LOOKUPS' => $configModel->isEnvOverridden('ENABLED_LOOKUPS'),
            'email.protocol' => $configModel->isEnvOverridden('email.protocol'),
            'email.fromEmail' => $configModel->isEnvOverridden('email.fromEmail'),
            'email.fromName' => $configModel->isEnvOverridden('email.fromName'),
            'email.SMTPHost' => $configModel->isEnvOverridden('email.SMTPHost'),
            'email.SMTPUser' => $configModel->isEnvOverridden('email.SMTPUser'),
            'email.SMTPPass' => $configModel->isEnvOverridden('email.SMTPPass'),
            'email.SMTPPort' => $configModel->isEnvOverridden('email.SMTPPort'),
            'email.SMTPCrypto' => $configModel->isEnvOverridden('email.SMTPCrypto'),
            'email.SMTPVerifyPeer' => $configModel->isEnvOverridden('email.SMTPVerifyPeer'),
            'email.SMTPVerifyPeerName' => $configModel->isEnvOverridden('email.SMTPVerifyPeerName'),
        ];

        try {
            $people = $this->peopleModel->orderBy('name')->findAll();
        } catch (\Throwable $e) {
            log_message('error', 'Could not load people in manageLookups: ' . $e->getMessage());
            $people = [];
        }

        $data = [
            'title' => 'Manage Lookup Tables',
            'mediums' => $this->mediaModel->orderBy('name')->findAll(),
            'collections' => $this->collectionModel->orderBy('name')->findAll(),
            'volumes' => $this->volumeModel->orderBy('name')->findAll(),
            'codecs' => $this->vcodecModel->orderBy('name')->findAll(),
            'tags' => $tags,
            'people' => $people,
            'achannels' => $this->achannelModel->orderBy('name')->findAll(),
            'acodecs' => $this->acodecModel->orderBy('name')->findAll(),
            'languages' => $this->languageModel->orderBy('name')->findAll(),
            'ratios' => $this->ratioModel->orderBy('name')->findAll(),
            'subformats' => $this->subformatModel->orderBy('name')->findAll(),
            'poster_count' => $this->posterModel->countAllResults(),
            'currentTimezone' => $currentTimezone,
            'deauthTime' => $deauthTime,
            'userAgent' => $userAgent,
            'appName' => $appName,
            'appBaseURL' => $appBaseURL,
            'lookupSettings' => $lookupSettings,
            'emailSettings' => $emailSettings,
            'overridden' => $overridden,
            'availableTimezones' => \DateTimeZone::listIdentifiers()
        ];

        return view('database_maintenance/manage_lookups', $data);
    }

    /**
     * Update configuration parameters
     */
    public function updateConfig()
    {
        if ($this->request->isAJAX()) {
            $configModel = new \App\Models\ConfigurationModel();
            $timezone = $this->request->getPost('timezone');
            $deauthTime = $this->request->getPost('deauth_time');
            $userAgent = $this->request->getPost('user_agent');
            $appName = $this->request->getPost('app_name');
            $appBaseURL = $this->request->getPost('app_baseURL');

            $success = true;

            $errors = [];

            if ($timezone !== null && !$configModel->isEnvOverridden('timezone')) {
                if (!$configModel->setParam('timezone', $timezone)) {
                    $success = false;
                    $errors[] = "Failed to save timezone";
                }
            }

            if ($deauthTime !== null && !$configModel->isEnvOverridden('deauth_time')) {
                if (!$configModel->setParam('deauth_time', (string)$deauthTime)) {
                    $success = false;
                    $errors[] = "Failed to save deauth_time";
                }
            }

            if ($userAgent !== null && !$configModel->isEnvOverridden('user_agent')) {
                if (!$configModel->setParam('user_agent', (string)$userAgent)) {
                    $success = false;
                    $errors[] = "Failed to save user_agent";
                }
            }

            if ($appName !== null && !$configModel->isEnvOverridden('app.name')) {
                if (!$configModel->setParam('app.name', (string)$appName)) {
                    $success = false;
                    $errors[] = "Failed to save app.name";
                }
            }

            if ($appBaseURL !== null && !$configModel->isEnvOverridden('app.baseURL')) {
                if (!$configModel->setParam('app.baseURL', (string)$appBaseURL)) {
                    $success = false;
                    $errors[] = "Failed to save app.baseURL";
                }
            }

            // Email Settings
            $emailParams = [
                'email.protocol', 'email.SMTPHost', 'email.SMTPUser', 'email.SMTPPass',
                'email.SMTPPort', 'email.SMTPCrypto', 'email.fromEmail', 'email.fromName',
                'email.SMTPVerifyPeer', 'email.SMTPVerifyPeerName'
            ];

            foreach ($emailParams as $param) {
                if ($configModel->isEnvOverridden($param)) continue;
                $value = $this->request->getPost(str_replace('email.', 'email_', $param));
                if ($value !== null) {
                    if (!$configModel->setParam($param, (string)$value)) {
                        $success = false;
                        $errors[] = "Failed to save $param";
                    }
                }
            }

            // Media Lookup Settings
            $tmdbKey = $this->request->getPost('TMDB_API_KEY');
            $imdbKey = $this->request->getPost('IMDB_API_KEY');
            $tvdbKey = $this->request->getPost('TVDB_API_KEY');
            $mbEmail = $this->request->getPost('MUSICBRAINZ_EMAIL');
            $igdbId = $this->request->getPost('IGDB_CLIENT_ID');
            $igdbSecret = $this->request->getPost('IGDB_CLIENT_SECRET');

            $lookupParams = [
                'TMDB_API_KEY' => $tmdbKey,
                'IMDB_API_KEY' => $imdbKey,
                'TVDB_API_KEY' => $tvdbKey,
                'IGDB_CLIENT_ID' => $igdbId,
                'IGDB_CLIENT_SECRET' => $igdbSecret,
                'MUSICBRAINZ_EMAIL' => $mbEmail,
            ];

            foreach ($lookupParams as $param => $value) {
                if ($configModel->isEnvOverridden($param)) continue;
                if ($value !== null) {
                    if (!$configModel->setParam($param, (string)$value)) {
                        $success = false;
                        $errors[] = "Failed to save $param";
                    }
                }
            }

            // Handle ENABLED_LOOKUPS checkboxes
            if (!$configModel->isEnvOverridden('ENABLED_LOOKUPS')) {
                $enabledLookups = $this->request->getPost('ENABLED_LOOKUPS');
                if (is_array($enabledLookups)) {
                    // Filter out lookups that don't have required API keys/settings
                    $filteredLookups = [];
                    foreach ($enabledLookups as $lookup) {
                        // Use getParam to get current value (might be from env or post)
                        $lookupTmdbKey = $configModel->getParam('TMDB_API_KEY');
                        $lookupImdbKey = $configModel->getParam('IMDB_API_KEY');
                        $lookupTvdbKey = $configModel->getParam('TVDB_API_KEY');
                        $lookupMbEmail = $configModel->getParam('MUSICBRAINZ_EMAIL');
                        $lookupIgdbId = $configModel->getParam('IGDB_CLIENT_ID');
                        $lookupIgdbSecret = $configModel->getParam('IGDB_CLIENT_SECRET');

                        if ($lookup === 'TMDB' && empty($lookupTmdbKey)) continue;
                        if ($lookup === 'IMDB' && empty($lookupImdbKey)) continue;
                        if ($lookup === 'TVDB' && empty($lookupTvdbKey)) continue;
                        if ($lookup === 'IGDB' && (empty($lookupIgdbId) || empty($lookupIgdbSecret))) continue;
                        if ($lookup === 'MusicBrainz' && empty($lookupMbEmail)) continue;
                        $filteredLookups[] = $lookup;
                    }

                    $enabledLookupsStr = implode(',', $filteredLookups);
                    if (!$configModel->setParam('ENABLED_LOOKUPS', $enabledLookupsStr)) {
                        $success = false;
                        $errors[] = "Failed to save ENABLED_LOOKUPS";
                    }
                } else {
                    // If none checked, it might be empty or null
                    if (!$configModel->setParam('ENABLED_LOOKUPS', '')) {
                        $success = false;
                        $errors[] = "Failed to save ENABLED_LOOKUPS";
                    }
                }
            }

            if ($success) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Configuration updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update configuration: ' . implode(', ', $errors)
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    // MEDIUM MANAGEMENT
    public function addMedium()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');

            if ($this->mediaModel->insert(['name' => $name])) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Medium added successfully',
                    'id' => $this->mediaModel->getInsertID()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->mediaModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function updateMedium($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'medium_id' => $id,
                'name' => $name
            ];

            if ($this->mediaModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Medium updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->mediaModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function deleteMedium($id)
    {
        if ($this->request->isAJAX()) {
            // Check if in use
            $inUse = $this->db->table('movies')->where('medium_id', $id)->countAllResults();

            if ($inUse > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Cannot delete: Medium is used by {$inUse} movie(s)"
                ]);
            }

            if ($this->mediaModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Medium deleted successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete medium'
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    // COLLECTION MANAGEMENT
    public function addCollection()
    {
        if ($this->request->isAJAX()) {
            $data = [
                'name' => $this->request->getPost('name'),
                'loaned' => $this->request->getPost('loaned') ?: 0
            ];

            if ($this->collectionModel->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Collection added successfully',
                    'id' => $this->collectionModel->getInsertID()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->collectionModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function updateCollection($id)
    {
        if ($this->request->isAJAX()) {
            $data = [
                'collection_id' => $id,
                'name' => $this->request->getPost('name'),
                'loaned' => $this->request->getPost('loaned') ?: 0
            ];

            if ($this->collectionModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Collection updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->collectionModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function deleteCollection($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movies')->where('collection_id', $id)->countAllResults();

            if ($inUse > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Cannot delete: Collection is used by {$inUse} movie(s)"
                ]);
            }

            if ($this->collectionModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Collection deleted successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete collection'
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    // VOLUME MANAGEMENT (similar pattern)
    public function addVolume()
    {
        if ($this->request->isAJAX()) {
            $data = [
                'name' => $this->request->getPost('name'),
                'loaned' => $this->request->getPost('loaned') ?: 0
            ];

            if ($this->volumeModel->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Volume added successfully',
                    'id' => $this->volumeModel->getInsertID()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->volumeModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function updateVolume($id)
    {
        if ($this->request->isAJAX()) {
            $data = [
                'volume_id' => $id,
                'name' => $this->request->getPost('name'),
                'loaned' => $this->request->getPost('loaned') ?: 0
            ];

            if ($this->volumeModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Volume updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->volumeModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function deleteVolume($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movies')->where('volume_id', $id)->countAllResults();

            if ($inUse > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Cannot delete: Volume is used by {$inUse} movie(s)"
                ]);
            }

            if ($this->volumeModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Volume deleted successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete volume'
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    // CODEC MANAGEMENT
    public function addCodec()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');

            if ($this->vcodecModel->insert(['name' => $name])) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Codec added successfully',
                    'id' => $this->vcodecModel->getInsertID()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->vcodecModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function updateCodec($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'vcodec_id' => $id,
                'name' => $name
            ];

            if ($this->vcodecModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Codec updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->vcodecModel->errors())
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    public function deleteCodec($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movies')->where('vcodec_id', $id)->countAllResults();

            if ($inUse > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Cannot delete: Codec is used by {$inUse} movie(s)"
                ]);
            }

            if ($this->vcodecModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Codec deleted successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete codec'
            ]);
        }
        
        return $this->response->setStatusCode(404);
    }

    /**
     * Generate and download database backup (best effort for current platform)
     */
    public function backupDatabase()
    {
        try {
            $dbName = $this->db->getDatabase();
            $tables = $this->getAllTables();
            
            // Set headers for download
            $filename = $dbName . '_backup_' . date('Y-m-d_His') . '.sql';
            
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Start building SQL dump
            fwrite($output, "-- MediaOrganizer Database Backup\n");
            fwrite($output, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($output, "-- Database: " . $dbName . "\n");
            fwrite($output, "-- Platform: " . $this->db->getPlatform() . "\n\n");

            if ($this->db->getPlatform() === 'MySQLi') {
                fwrite($output, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
                fwrite($output, "SET time_zone = \"+00:00\";\n\n");
                fwrite($output, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
                fwrite($output, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
                fwrite($output, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
                fwrite($output, "/*!40101 SET NAMES utf8mb4 */;\n\n");
            }
            
            foreach ($tables as $table) {
                if (!$this->validateTable($table)) {
                    continue;
                }

                // Add table structure (best effort)
                fwrite($output, "--\n-- Table structure for table `{$table}`\n--\n\n");
                
                if ($this->db->getPlatform() === 'MySQLi') {
                    fwrite($output, "DROP TABLE IF EXISTS `{$table}`;\n");
                    $createTable = $this->db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
                    fwrite($output, $createTable['Create Table'] . ";\n\n");
                } elseif ($this->db->getPlatform() === 'SQLite3') {
                    fwrite($output, "DROP TABLE IF EXISTS `{$table}`;\n");
                    $createTable = $this->db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table])->getRowArray();
                    if ($createTable && isset($createTable['sql'])) {
                        fwrite($output, $createTable['sql'] . ";\n\n");
                    }
                } else {
                    fwrite($output, "-- (Full schema backup only supported on MySQL and SQLite. Use native tools for other platforms.)\n\n");
                }
                
                // Get table data in chunks to save memory
                $builder = $this->db->table($table);
                $totalRows = $builder->countAllResults(false);
                
                if ($totalRows > 0) {
                    fwrite($output, "--\n-- Dumping data for table `{$table}`\n--\n\n");
                    
                    // Get column names for INSERT statement
                    $firstRow = $builder->get(1)->getRowArray();
                    if ($firstRow) {
                        $columns = array_keys($firstRow);
                        $columnList = '`' . implode('`, `', $columns) . '`';
                        
                        // Process in chunks of 100 rows
                        $chunkSize = 100;
                        for ($offset = 0; $offset < $totalRows; $offset += $chunkSize) {
                            $rows = $builder->get($chunkSize, $offset)->getResultArray();
                            foreach ($rows as $row) {
                                $values = [];
                                foreach ($row as $value) {
                                    if ($value === null) {
                                        $values[] = 'NULL';
                                    } else {
                                        $values[] = $this->db->escape($value);
                                    }
                                }
                                fwrite($output, "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n");
                            }
                        }
                    }
                    fwrite($output, "\n");
                }
            }
            
            if ($this->db->getPlatform() === 'MySQLi') {
                fwrite($output, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
                fwrite($output, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
                fwrite($output, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");
            }
            
            fclose($output);
            exit;
                
        } catch (\Exception $e) {
            // If headers haven't been sent yet, we can try to report the error properly
            if (!headers_sent()) {
                // If AJAX request, return JSON error
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Error creating backup: ' . $e->getMessage()
                    ]);
                }
                
                // Otherwise redirect with error message
                return redirect()->to(base_url('database-maintenance'))
                    ->with('error', 'Error creating backup: ' . $e->getMessage());
            } else {
                // Headers sent, we can only log it and stop
                log_message('error', 'Error creating backup: ' . $e->getMessage());
                exit;
            }
        }
    }

    /**
     * Download the raw SQLite database file
     */
    public function downloadRawSqlite()
    {
        if ($this->db->getPlatform() !== 'SQLite3') {
            return redirect()->to(base_url('database-maintenance'))
                ->with('error', 'Raw SQLite download is only available for SQLite databases.');
        }

        $config = config('Database');
        $group = $this->db->group ?? $config->defaultGroup;
        $dbConfig = $config->$group;
        $dbPath = $dbConfig['database'];

        if ($dbPath !== ':memory:' && ! str_contains($dbPath, DIRECTORY_SEPARATOR)) {
            $dbPath = WRITEPATH . $dbPath;
        }

        if ($dbPath === ':memory:' || !file_exists($dbPath)) {
            return redirect()->to(base_url('database-maintenance'))
                ->with('error', 'Database file not found or is in-memory.');
        }

        return $this->response->download($dbPath, null)->setFileName(basename($dbPath));
    }

    /**
     * View all movies for a specific tag
     */
    public function viewTagMedia($tagId)
    {
        $tagModel = new \App\Models\TagModel();
        $tag = $tagModel->find($tagId);

        if (!$tag) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Tag not found');
        }

        // Get all media with this tag
        $media = $this->db->table('movie_tag mt')
            ->select('m.movie_id, m.title, m.o_title, m.year, m.runtime, m.rating, m.seen, m.loaned, m.poster_md5, med.name as medium_name')
            ->join('movies m', 'm.movie_id = mt.movie_id')
            ->join('media med', 'med.medium_id = m.medium_id', 'left')
            ->where('mt.tag_id', $tagId)
            ->orderBy('m.title', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Media Tagged: ' . $tag['name'],
            'tag' => $tag,
            'media' => $media,
            'mediaCount' => count($media)
        ];

        return view('database_maintenance/tag_media', $data);
    }

    /**
     * Add a new tag
     */
    public function addTag()
    {
        if ($this->request->isAJAX()) {
            $tagModel = new \App\Models\TagModel();
            $data = [
                'name' => trim($this->request->getPost('name'))
            ];

            if ($tagModel->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Tag added successfully',
                    'id' => $tagModel->getInsertID()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $tagModel->errors())
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Update an existing tag
     */
    public function updateTag($id)
    {
        if ($this->request->isAJAX()) {
            $tagModel = new \App\Models\TagModel();
            $data = [
                'tag_id' => $id,
                'name' => trim($this->request->getPost('name'))
            ];

            if ($tagModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Tag updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $tagModel->errors())
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Delete a tag
     */
    public function deleteTag($id)
    {
        if ($this->request->isAJAX()) {
            $tagModel = new \App\Models\TagModel();

            // Check if tag is in use
            $mediaCount = $tagModel->getMediaCountForTag($id);

            if ($mediaCount > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Cannot delete: Tag is used by {$mediaCount} movie(s). Please remove it from all movies first."
                ]);
            }

            if ($tagModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Tag deleted successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete tag'
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    // ACHANNEL MANAGEMENT
    public function addAChannel()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            if ($this->achannelModel->insert(['name' => $name])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Audio channel added successfully', 'id' => $this->achannelModel->getInsertID()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->achannelModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function updateAChannel($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'achannel_id' => $id,
                'name' => $name
            ];
            if ($this->achannelModel->update($id, $data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Audio channel updated successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->achannelModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function deleteAChannel($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movie_lang')->where('achannel_id', $id)->countAllResults();
            if ($inUse > 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => "Cannot delete: Audio channel is used by {$inUse} movie language entry(s)"]);
            }
            if ($this->achannelModel->delete($id)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Audio channel deleted successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete audio channel']);
        }
        return $this->response->setStatusCode(404);
    }

    // ACODEC MANAGEMENT
    public function addACodec()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            if ($this->acodecModel->insert(['name' => $name])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Audio codec added successfully', 'id' => $this->acodecModel->getInsertID()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->acodecModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function updateACodec($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'acodec_id' => $id,
                'name' => $name
            ];
            if ($this->acodecModel->update($id, $data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Audio codec updated successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->acodecModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function deleteACodec($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movie_lang')->where('acodec_id', $id)->countAllResults();
            if ($inUse > 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => "Cannot delete: Audio codec is used by {$inUse} movie language entry(s)"]);
            }
            if ($this->acodecModel->delete($id)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Audio codec deleted successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete audio codec']);
        }
        return $this->response->setStatusCode(404);
    }

    // LANGUAGE MANAGEMENT
    public function addLanguage()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            if ($this->languageModel->insert(['name' => $name])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Language added successfully', 'id' => $this->languageModel->getInsertID()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->languageModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function updateLanguage($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'lang_id' => $id,
                'name' => $name
            ];
            if ($this->languageModel->update($id, $data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Language updated successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->languageModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function deleteLanguage($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movie_lang')->where('lang_id', $id)->countAllResults();
            if ($inUse > 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => "Cannot delete: Language is used by {$inUse} movie language entry(s)"]);
            }
            if ($this->languageModel->delete($id)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Language deleted successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete language']);
        }
        return $this->response->setStatusCode(404);
    }

    // RATIO MANAGEMENT
    public function addRatio()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            if ($this->ratioModel->insert(['name' => $name])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Ratio added successfully', 'id' => $this->ratioModel->getInsertID()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->ratioModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function updateRatio($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'ratio_id' => $id,
                'name' => $name
            ];
            if ($this->ratioModel->update($id, $data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Ratio updated successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->ratioModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function deleteRatio($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movies')->where('ratio_id', $id)->countAllResults();
            if ($inUse > 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => "Cannot delete: Ratio is used by {$inUse} movie(s)"]);
            }
            if ($this->ratioModel->delete($id)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Ratio deleted successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete ratio']);
        }
        return $this->response->setStatusCode(404);
    }

    // SUBFORMAT MANAGEMENT
    public function addSubformat()
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            if ($this->subformatModel->insert(['name' => $name])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Subtitle format added successfully', 'id' => $this->subformatModel->getInsertID()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->subformatModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function updateSubformat($id)
    {
        if ($this->request->isAJAX()) {
            $name = $this->request->getPost('name');
            $data = [
                'subformat_id' => $id,
                'name' => $name
            ];
            if ($this->subformatModel->update($id, $data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Subtitle format updated successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => implode(', ', $this->subformatModel->errors())]);
        }
        return $this->response->setStatusCode(404);
    }

    public function deleteSubformat($id)
    {
        if ($this->request->isAJAX()) {
            $inUse = $this->db->table('movie_lang')->where('subformat_id', $id)->countAllResults();
            if ($inUse > 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => "Cannot delete: Subtitle format is used by {$inUse} movie language entry(s)"]);
            }
            if ($this->subformatModel->delete($id)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Subtitle format deleted successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete subtitle format']);
        }
        return $this->response->setStatusCode(404);
    }

    // POSTER MANAGEMENT
    public function purgePosters()
    {
        if ($this->request->isAJAX()) {
            $affectedRows = $this->posterModel->purgeUnused();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Purged {$affectedRows} unused poster(s) from the database."
            ]);
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Get cron jobs data
     */
    private function getCronJobsData()
    {
        try {
            if (!$this->db->tableExists('cron_jobs')) {
                return [];
            }
            return $this->cronJobModel->findAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Toggle cron job enabled status
     */
    public function toggleCronJob($id)
    {
        if ($this->request->isAJAX()) {
            $enabled = $this->request->getPost('enabled');
            if ($this->cronJobModel->update($id, ['enabled' => $enabled])) {
                return $this->response->setJSON(['status' => 'success']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update job status']);
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Run cron job manually
     */
    public function runCronJob($id)
    {
        if ($this->request->isAJAX()) {
            $job = $this->cronJobModel->find($id);
            if (!$job) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Job not found']);
            }

            // Run using Spark command via exec/system or better, via command service
            try {
                // Since we are in a web context, we can't easily use the command service 
                // because it's designed for CLI. We'll call the command's run method directly.
                $command = null;
                /** @var \Psr\Log\LoggerInterface $logger */
                $logger = service('logger');
                switch ($job['job_key']) {
                    case 'purge_posters':
                        $command = new \App\Commands\CronPurgePosters($logger, \Config\Services::commands());
                        break;
                    case 'loan_reminders':
                        $command = new \App\Commands\CronLoanReminders($logger, \Config\Services::commands());
                        break;
                }

                if ($command) {
                    // This might write to CLI output which we don't want, but let's try.
                    // Actually, let's just use the same logic as the command here or make a Service.
                    // For simplicity, I'll just trigger the command.
                    ob_start();
                    $command->run([]);
                    ob_end_clean();
                    
                    $updatedJob = $this->cronJobModel->find($id);
                    return $this->response->setJSON([
                        'status' => 'success', 
                        'message' => 'Job executed: ' . $updatedJob['last_message'],
                        'job' => $updatedJob
                    ]);
                }
                
                return $this->response->setJSON(['status' => 'error', 'message' => 'Command not found for job: ' . $job['job_key']]);
            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Error running job: ' . $e->getMessage()]);
            }
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Update cron job schedule
     */
    public function updateCronSchedule($id)
    {
        if ($this->request->isAJAX()) {
            $schedule = $this->request->getPost('schedule');
            
            // Basic validation for cron expression (at least 5 parts)
            if (empty($schedule) || count(explode(' ', trim($schedule))) < 5) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid cron expression']);
            }

            if ($this->cronJobModel->update($id, ['schedule' => $schedule])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Schedule updated successfully']);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update schedule']);
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Duplicate Detector page
     */
    public function duplicateDetector()
    {
        // 1. Get all media
        $allMedia = $this->mediaModel->db->table('movies m')
            ->select('m.movie_id, m.title, m.o_title, m.year, m.barcode, m.notes')
            ->where("m.notes NOT LIKE '%<!skipduplicate>%'")
            ->get()
            ->getResultArray();

        $duplicates = [];
        $processedIds = [];

        // 2. Identify potential duplicates
        foreach ($allMedia as $i => $media1) {
            if (in_array($media1['movie_id'], $processedIds)) continue;

            $group = [$media1];
            $norm1 = normalize_title_for_search($media1['title']);
            $normO1 = normalize_title_for_search($media1['o_title']);
            $title1 = trim((string)$media1['title']);
            $oTitle1 = trim((string)$media1['o_title']);
            $barcode1 = trim((string)$media1['barcode']);
            $imdb1 = get_imdb_id_from_notes($media1['notes']);
            $tmdb1 = get_tmdb_id_from_notes($media1['notes']);
            $tvdb1 = get_tvdb_id_from_notes($media1['notes']);
            $igdb1 = get_igdb_id_from_notes($media1['notes']);
            $mbid1 = get_mbid_from_notes($media1['notes']);

            foreach ($allMedia as $j => $media2) {
                if ($i === $j) continue;
                if (in_array($media2['movie_id'], $processedIds)) continue;

                $isMatch = false;

                // Match by Barcode (High confidence)
                $barcode2 = trim((string)$media2['barcode']);
                if ($barcode1 !== '' && $barcode1 === $barcode2) {
                    $isMatch = true;
                }

                // Match by External IDs
                if (!$isMatch) {
                    $imdb2 = get_imdb_id_from_notes($media2['notes']);
                    if ($imdb1 && $imdb2 && $imdb1 === $imdb2) $isMatch = true;
                    
                    if (!$isMatch) {
                        $tmdb2 = get_tmdb_id_from_notes($media2['notes']);
                        if ($tmdb1 && $tmdb2 && $tmdb1 === $tmdb2) $isMatch = true;
                    }
                    
                    if (!$isMatch) {
                        $tvdb2 = get_tvdb_id_from_notes($media2['notes']);
                        if ($tvdb1 && $tvdb2 && $tvdb1 === $tvdb2) $isMatch = true;
                    }
                    
                    if (!$isMatch) {
                        $igdb2 = get_igdb_id_from_notes($media2['notes']);
                        if ($igdb1 && $igdb2 && $igdb1 === $igdb2) $isMatch = true;
                    }
                    
                    if (!$isMatch) {
                        $mbid2 = get_mbid_from_notes($media2['notes']);
                        if ($mbid1 && $mbid2 && $mbid1 === $mbid2) $isMatch = true;
                    }
                }

                // Match by Exact Title or Original Title
                if (!$isMatch) {
                    $title2 = trim((string)$media2['title']);
                    $oTitle2 = trim((string)$media2['o_title']);
                    
                    if (($title1 !== '' && ($title1 === $title2 || $title1 === $oTitle2)) ||
                        ($oTitle1 !== '' && ($oTitle1 === $title2 || $oTitle1 === $oTitle2))) {
                        $isMatch = true;
                    }
                }

                // Match by Normalized Title + Year
                if (!$isMatch) {
                    $norm2 = normalize_title_for_search($media2['title']);
                    $normO2 = normalize_title_for_search($media2['o_title']);
                    
                    $titleMatch = ($norm1 !== '' && ($norm1 === $norm2 || $norm1 === $normO2)) ||
                                 ($normO1 !== '' && ($normO1 === $norm2 || $normO1 === $normO2));
                    
                    if ($titleMatch && $media1['year'] == $media2['year']) {
                        $isMatch = true;
                    }
                }

                if ($isMatch) {
                    $group[] = $media2;
                    $processedIds[] = $media2['movie_id'];
                }
            }

            if (count($group) > 1) {
                $duplicates[] = $group;
                $processedIds[] = $media1['movie_id'];
            }
        }

        return view('database_maintenance/duplicate_detector', [
            'duplicates' => $duplicates,
            'title' => 'Duplicate Detector'
        ]);
    }
}
