<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $intended = current_url();

        // Check if authenticated
        if (!session()->get('authenticated')) {
            // Store the intended URL even for main login if they were heading to maintenance
            if (strpos($intended, '/login') === false) {
                session()->set('intended_url', $intended);
            }
            return redirect()->to('/login');
        }

        // Check if recently authenticated (within dynamic timeout or 15 minutes default)
        $recentAuthTime = session()->get('recent_auth_time');

        $currentTime = time();
        
        $configModel = new \App\Models\ConfigurationModel();
        $deauthTimeMinutes = $configModel->getParam('deauth_time', 15);
        $recentAuthRequired = $deauthTimeMinutes * 60;

        if (!$recentAuthTime || ($currentTime - $recentAuthTime) > $recentAuthRequired) {
            // Check if it's an AJAX request
            if ($request->isAJAX()) {
                return service('response')
                    ->setJSON(['status' => 'error', 'message' => 'Session expired. Please re-authenticate.', 'reauth' => true])
                    ->setStatusCode(401);
            }

            // Store the intended URL
            
            // Only set intended_url if we're not already heading to reauth or login
            if (strpos($intended, '/reauth') === false && strpos($intended, '/login') === false) {
                session()->set('intended_url', $intended);
            }
            return redirect()->to('/reauth');
        }

        // Renew the auth timer on every successful access to maintenance pages
        session()->set('recent_auth_time', time());

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add header for the frontend to update its timer
        $recentAuthTime = session()->get('recent_auth_time');
        if ($recentAuthTime) {
            $configModel = new \App\Models\ConfigurationModel();
            $deauthTimeMinutes = $configModel->getParam('deauth_time', 15);
            $expiresAt = $recentAuthTime + ($deauthTimeMinutes * 60);
            $response->setHeader('X-Maintenance-Expires', (string)$expiresAt);
        }
    }
}