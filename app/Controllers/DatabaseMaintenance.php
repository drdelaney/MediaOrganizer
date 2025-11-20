<?php

namespace App\Controllers;

use App\Models\MediaModel;
use App\Models\CollectionModel;
use App\Models\VolumeModel;
use App\Models\VCodecModel;
class DatabaseMaintenance extends BaseController
{
    protected $db;
    protected $mediaModel;
    protected $collectionModel;
    protected $volumeModel;
    protected $vcodecModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->mediaModel = new MediaModel();
        $this->collectionModel = new CollectionModel();
        $this->volumeModel = new VolumeModel();
        $this->vcodecModel = new VCodecModel();
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

            foreach ($tables as $table) {
                $this->db->query("OPTIMIZE TABLE `{$table}`");
                $results[] = "Optimized table: {$table}";
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
            $tables = $this->getAllTables();
            $results = [];

            foreach ($tables as $table) {
                // Check current engine
                $engineQuery = "SELECT ENGINE FROM information_schema.TABLES 
                               WHERE TABLE_SCHEMA = DATABASE() 
                               AND TABLE_NAME = '{$table}'";
                $engineResult = $this->db->query($engineQuery)->getRowArray();

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
            'mysqli' => 'MySQL database support',
            'curl' => 'CURL support for API calls',
            'openssl' => 'OpenSSL for secure connections',
            'xml' => 'XML support',
            'filter' => 'Data filtering',
            'hash' => 'Hash functions'
        ];

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
        $data = [
            'title' => 'Database Maintenance',
            'tables' => $this->getTableInfo(),
            'environment' => $this->checkEnvironment()
        ];

        return view('database_maintenance/index', $data);
    }

    /**
     * Get all table names in the database
     */
    private function getAllTables()
    {
        $query = "SHOW TABLES";
        $result = $this->db->query($query)->getResultArray();

        $tables = [];
        foreach ($result as $row) {
            $tables[] = array_values($row)[0];
        }

        return $tables;
    }

    /**
     * Re-index a specific table
     */
    private function reindexTable($table)
    {
        try {
            // Get table information
            $query = "SHOW INDEX FROM `{$table}`";
            $indexes = $this->db->query($query)->getResultArray();

            if (empty($indexes)) {
                return "No indexes found for table: {$table}";
            }

            // Re-index the table
            $this->db->query("ALTER TABLE `{$table}` DISABLE KEYS");
            $this->db->query("ALTER TABLE `{$table}` ENABLE KEYS");

            return "Re-indexed table: {$table} (" . count($indexes) . " indexes)";

        } catch (\Exception $e) {
            return "Error re-indexing {$table}: " . $e->getMessage();
        }
    }

    /**
     * Get detailed table information
     */
    private function getTableInfo()
    {
        $query = "SELECT 
                    TABLE_NAME as name,
                    ENGINE as engine,
                    TABLE_ROWS as `rows`,
                    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as size_mb
                  FROM information_schema.TABLES 
                  WHERE TABLE_SCHEMA = DATABASE()
                  ORDER BY TABLE_NAME";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Show lookup tables management page
     */
    public function manageLookups()
    {
        $data = [
            'title' => 'Manage Lookup Tables',
            'mediums' => $this->mediaModel->orderBy('name')->findAll(),
            'collections' => $this->collectionModel->orderBy('name')->findAll(),
            'volumes' => $this->volumeModel->orderBy('name')->findAll(),
            'codecs' => $this->vcodecModel->orderBy('name')->findAll()
        ];

        return view('database_maintenance/manage_lookups', $data);
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

            if ($this->mediaModel->update($id, ['name' => $name])) {
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

            if ($this->vcodecModel->update($id, ['name' => $name])) {
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
     * Generate and download database backup (mysqldump style)
     */
    public function backupDatabase()
    {
        try {
            $dbName = $this->db->getDatabase();
            $tables = $this->getAllTables();
            
            // Start building SQL dump
            $dump = "-- MediaOrganizer Database Backup\n";
            $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $dump .= "-- Database: " . $dbName . "\n\n";
            $dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $dump .= "SET time_zone = \"+00:00\";\n\n";
            $dump .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
            $dump .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
            $dump .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
            $dump .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
            
            foreach ($tables as $table) {
                // Add table structure
                $dump .= "--\n-- Table structure for table `{$table}`\n--\n\n";
                $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                // Get CREATE TABLE statement
                $createTable = $this->db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
                $dump .= $createTable['Create Table'] . ";\n\n";
                
                // Get table data
                $rows = $this->db->query("SELECT * FROM `{$table}`")->getResultArray();
                
                if (!empty($rows)) {
                    $dump .= "--\n-- Dumping data for table `{$table}`\n--\n\n";
                    
                    // Get column names for INSERT statement
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                // Escape single quotes and backslashes
                                $value = str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
                                $values[] = "'" . $value . "'";
                            }
                        }
                        $dump .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $dump .= "\n";
                }
            }
            
            $dump .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
            $dump .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
            $dump .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
            
            // Set headers for download
            $filename = $dbName . '_backup_' . date('Y-m-d_His') . '.sql';
            
            // Use CodeIgniter's download helper approach
            return $this->response->download($filename, $dump);
                
        } catch (\Exception $e) {
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
        }
    }
}
