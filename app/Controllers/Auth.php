<?php
namespace App\Controllers;

class Auth extends BaseController
{
    protected $passwordFile;

    public function __construct()
    {
        $this->passwordFile = WRITEPATH . 'auth/password.json';
        helper('cookie');
    }

    // Login page
    public function login()
    {
        // If already authenticated, redirect to home
        if (session()->get('authenticated')) {
            return redirect()->to('/');
        }
        return view('auth/login');
    }

    // Process login
    public function authenticate()
    {
        $password = $this->request->getPost('password');

        if ($this->verifyPassword($password)) {
            // Set session
            session()->set([
                'authenticated' => true,
                'auth_time' => time()
            ]);

            // Handle remember-me
            if ($this->request->getPost('remember')) {
                $this->setAuthCookie();
            } else {
                // Delete any existing cookie if remember-me is not checked
                helper('cookie');
                delete_cookie('auth_token');
            }

            return redirect()->to('/');
        }

        return redirect()->back()->with('error', 'Invalid password');
    }

    // Verify password against stored hash
    private function verifyPassword($password)
    {
        $storedHash = $this->getStoredPasswordHash();
        return password_verify($password, $storedHash);
    }

    // Get stored password hash or create from initial password
    private function getStoredPasswordHash()
    {
        if (!file_exists($this->passwordFile)) {
            $this->initializePassword();
        }

        $data = json_decode(file_get_contents($this->passwordFile), true);
        return $data['password_hash'];
    }

    // Initialize password from .env
    private function initializePassword()
    {
        $initialPassword = env('auth.initialPassword', 'admin123');
        $this->savePasswordHash(password_hash($initialPassword, PASSWORD_DEFAULT));
    }

    // Save password hash to file
    private function savePasswordHash($hash)
    {
        $dir = dirname($this->passwordFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'password_hash' => $hash,
            'updated_at' => date('c')
        ];

        file_put_contents($this->passwordFile, json_encode($data));
    }

    // Set authentication cookie for remember-me functionality
    private function setAuthCookie()
    {
        helper('cookie');

        // Generate a secure token
        $token = bin2hex(random_bytes(32));

        // Store token in session for verification (or in database for production)
        session()->set('auth_token', $token);

        // Set cookie for 30 days
        set_cookie([
            'name'   => 'auth_token',
            'value'  => $token,
            'expire' => 30 * 24 * 60 * 60, // 30 days
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Logout - destroy session and cookies
    public function logout()
    {
        // Clear session
        session()->destroy();

        // Delete remember-me cookie
        helper('cookie');
        delete_cookie('auth_token');

        return redirect()->to('/login')->with('message', 'You have been logged out');
    }

    // Show change password form
    public function changePassword()
    {
        return view('auth/change_password');
    }

    // Process password change
    public function updatePassword()
    {
        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Verify current password
        if (!$this->verifyPassword($currentPassword)) {
            return redirect()->back()->with('error', 'Current password is incorrect');
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            return redirect()->back()->with('error', 'New password must be at least 8 characters');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'New passwords do not match');
        }

        // Save new password
        $this->savePasswordHash(password_hash($newPassword, PASSWORD_DEFAULT));

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    // Show re-authentication form
    public function reauth()
    {
        // If already recently authenticated, redirect back
        $recentAuthTime = session()->get('recent_auth_time');
        if ($recentAuthTime && (time() - $recentAuthTime) < (15 * 60)) {
            $intendedUrl = session()->get('intended_url') ?: base_url();
            return redirect()->to($intendedUrl);
        }

        return view('auth/reauth');
    }

    // Process re-authentication
    public function processReauth()
    {
        $password = $this->request->getPost('password');

        if ($this->verifyPassword($password)) {
            // Update recent auth time
            session()->set('recent_auth_time', time());

            // Redirect to intended URL or default
            $intendedUrl = session()->get('intended_url') ?: base_url('database-maintenance');
            session()->remove('intended_url');

            return redirect()->to($intendedUrl);
        }

        return redirect()->back()->with('error', 'Invalid password');
    }
}