<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SetupFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip check for Setup controller itself to avoid infinite loop
        $path = $request->getUri()->getPath();
        if ($path === 'setup' || strpos($path, 'setup/') === 0 || $path === '/setup' || strpos($path, '/setup/') === 0 || strpos($path, 'index.php/setup') !== false) {
            return null;
        }

        // Check for core tables to ensure the database is fully initialized
        // We do this regardless of setupComplete flag because the database might have been wiped
        // but .env remained.
        try {
            $db = \Config\Database::connect();
            $requiredTables = ['configuration', 'movies', 'tags', 'people'];
            foreach ($requiredTables as $table) {
                if (!$db->tableExists($table)) {
                    return redirect()->route('setup_public');
                }
            }
            
            $config = $db->table('configuration')->where('param', 'version')->get()->getRow();
            if (!$config) {
                return redirect()->route('setup_public');
            }
        } catch (\Throwable $e) {
            // If database connection fails, we likely need setup
            return redirect()->route('setup_public');
        }

        // Additional check for setupComplete flag
        if (env('app.setupComplete') !== true) {
            // If tables exist but setupComplete is not true, we might still want to redirect to setup
            // however, if the user is in the middle of setup, the index check at the top handles it.
            // This is just a safety.
            return redirect()->route('setup_public');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
