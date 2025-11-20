<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if authenticated
        if (!session()->get('authenticated')) {
            return redirect()->to('/login');
        }

        // Check if recently authenticated (within last 15 minutes)
        $authTime = session()->get('auth_time');
        $recentAuthTime = session()->get('recent_auth_time');

        $currentTime = time();
        $recentAuthRequired = 15 * 60; // 15 minutes

        if (!$recentAuthTime || ($currentTime - $recentAuthTime) > $recentAuthRequired) {
            // Store the intended URL
            session()->set('intended_url', current_url());
            return redirect()->to('/reauth');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No after-processing needed
    }
}