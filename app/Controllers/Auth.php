<?php
namespace App\Controllers;

class Auth extends BaseController
{
    protected $passwordFile;
    protected $configModel;

    public function __construct()
    {
        $this->passwordFile = WRITEPATH . 'auth/password.json';
        $this->configModel = new \App\Models\ConfigurationModel();

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
        // Check if password is set up
        if ($this->getStoredPasswordHash() === null) {
            return redirect()->back()->with('error', 'Authentication is not configured. Please set "auth.initialPassword" in your .env file to populate the initial password.');
        }

        $password = $this->request->getPost('password');

        if ($this->verifyPassword($password)) {
            // Set session
            session()->set([
                'authenticated' => true,
                'auth_time' => time(),
                'recent_auth_time' => time() // Set initial reauth time upon login
            ]);

            // Handle remember-me
            if ($this->request->getPost('remember')) {
                $this->setAuthCookie();
            } else {
                // Delete any existing cookie if remember-me is not checked
                helper('cookie');
                delete_cookie('auth_token');
            }

            // Redirect to intended URL or home
            $intendedUrl = session()->get('intended_url') ?: base_url();
            session()->remove('intended_url');

            return redirect()->to($intendedUrl);
        }

        return redirect()->back()->with('error', 'Invalid password');
    }

    // Verify password against stored hash
    private function verifyPassword($password)
    {
        $storedHash = $this->getStoredPasswordHash();
        if (!$storedHash) {
            return false;
        }
        return password_verify($password, $storedHash);
    }

    // Get stored password hash or migrate if necessary
    private function getStoredPasswordHash()
    {
        // 1. Check database first
        $dbHash = $this->configModel->getParam('password_hash');
        if ($dbHash) {
            return $dbHash;
        }

        // 2. Database empty, check if we can migrate from file
        if (file_exists($this->passwordFile)) {
            $data = json_decode(file_get_contents($this->passwordFile), true);
            if (isset($data['password_hash'])) {
                $hash = $data['password_hash'];
                $this->savePasswordHash($hash); // Save to DB
                // We ignore the file from now on as per requirements
                return $hash;
            }
        }

        // 3. No DB, no file, check .env
        $initialPassword = env('auth.initialPassword');
        if ($initialPassword) {
            $hash = password_hash($initialPassword, PASSWORD_DEFAULT);
            $this->savePasswordHash($hash);
            return $hash;
        }

        // 4. No password found
        return null;
    }

    // Save password hash to database
    private function savePasswordHash($hash)
    {
        $this->configModel->setParam('password_hash', $hash);
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

        $deauthTimeMinutes = $this->configModel->getParam('deauth_time', 15);
        $recentAuthRequired = $deauthTimeMinutes * 60;

        if ($recentAuthTime && (time() - $recentAuthTime) < $recentAuthRequired) {
            $intendedUrl = session()->get('intended_url') ?: base_url();
            return redirect()->to($intendedUrl);
        }

        return view('auth/reauth');
    }

    // Process re-authentication
    public function processReauth()
    {
        // Check if password is set up
        if ($this->getStoredPasswordHash() === null) {
            return redirect()->back()->with('error', 'Authentication is not configured. Please set "auth.initialPassword" in your .env file to populate the initial password.');
        }

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