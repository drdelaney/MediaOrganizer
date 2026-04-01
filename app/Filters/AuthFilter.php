<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check session authentication
        if (!session()->get('authenticated')) {
            // Only check cookie if remember-me was enabled
            if (!$this->checkAuthCookie()) {
                if ($request->isAJAX()) {
                    return service('response')
                        ->setJSON(['status' => 'error', 'message' => 'Session expired. Please log in again.'])
                        ->setStatusCode(401);
                }
                return redirect()->to('/login')
                    ->with('message', 'Please log in to continue');
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No after-processing needed
    }

    private function checkAuthCookie()
    {
        helper('cookie');
        $authToken = get_cookie('auth_token');

        if ($authToken) {
            // Restore the session WITH remember-me flag
            session()->set([
                'authenticated' => true,
                'auth_time' => time(),
                'remember_me' => true  // This was a remembered session
            ]);
            return true;
        }

        return false;
    }
}