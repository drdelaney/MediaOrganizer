<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? esc($title) : esc(app_name()) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Dark Mode CSS -->
    <link href="<?= base_url('assets/js/dark-mode.js') ?>" rel="prefetch"> <!-- Pre-fetching for performance -->
    <link href="<?= base_url('assets/css/dark-mode.css') ?>" rel="stylesheet">
    
    <!-- CSRF Token for AJAX -->
    <meta name="<?= csrf_header() ?>" content="<?= csrf_hash() ?>">
    
    <style>
        /* noinspection CssUnusedSymbol */
        .poster-thumbnail {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }
        /* noinspection CssUnusedSymbol */
        .rating-stars {
            color: #ffc107;
        }
        /* noinspection CssUnusedSymbol */
        .btn-highlight-pulse {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.5);
            transition: box-shadow 0.2s ease-in-out;
        }
    </style>
</head>
<body>
    <?php
    // Check if setup is complete
    $setupComplete = false;
    static $cachedSetupComplete = null;
    if ($cachedSetupComplete !== null) {
        $setupComplete = $cachedSetupComplete;
    } else {
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('configuration')) {
                $configModel = new \App\Models\ConfigurationModel();
                $setupComplete = (bool) $configModel->getParam('app.setupComplete', false);
            } else {
                $setupComplete = (bool) env('app.setupComplete', false);
            }
        } catch (\Throwable $e) {
            $setupComplete = (bool) env('app.setupComplete', false);
        }
        $cachedSetupComplete = $setupComplete;
    }

    if (!$setupComplete && service('router')->getMatchedRoute()[0] !== 'setup'): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-0 rounded-0 text-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Setup Incomplete!</strong> Please <a href="<?= base_url('setup') ?>" class="alert-link">complete the setup</a> to ensure all features work correctly.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    // Check if initial password is still in use
    $showInitialPasswordWarning = false;
    static $cachedInitialPasswordWarning = null;
    if (session()->get('authenticated')) {
        if ($cachedInitialPasswordWarning !== null) {
            $showInitialPasswordWarning = $cachedInitialPasswordWarning;
        } else {
            try {
                $db = \Config\Database::connect();
                if ($db->tableExists('configuration')) {
                    $initialPassword = env('auth.initialPassword');
                    if (!empty($initialPassword)) {
                        $configModel = new \App\Models\ConfigurationModel();
                        $storedHash = $configModel->getParam('password_hash');
                        if ($storedHash && password_verify($initialPassword, $storedHash)) {
                            $showInitialPasswordWarning = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently fail if DB is not ready
            }
            $cachedInitialPasswordWarning = $showInitialPasswordWarning;
        }
    }

    if ($showInitialPasswordWarning && service('router')->getMatchedRoute()[0] !== 'settings/password'): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 text-center" role="alert">
            <i class="bi bi-shield-lock-fill"></i>
            <strong>Security Warning!</strong> You are still using the initial password. Please <a href="<?= base_url('settings/password') ?>" class="alert-link">change your password</a> immediately for security.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation -->
    <?php if (!isset($hide_nav) || !$hide_nav): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="bi bi-collection-play"></i> <?= esc(app_name()) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('media') ?>">
                            <i class="bi bi-film"></i> Media
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('public') ?>">
                            <i class="bi bi-globe"></i> Public List
                        </a>
                    </li>
                    <?php if ($wishlistTag = get_wishlist_tag()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('media?tag=' . $wishlistTag['tag_id']) ?>">
                                <i class="bi bi-heart"></i> Wishlist
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('loans') ?>">
                            <i class="bi bi-person-check-fill"></i> Loaned Media
                        </a>
                    </li>
                    <?php if (session()->get('authenticated')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('database-maintenance') ?>">
                                <i class="bi bi-tools"></i> Maintenance
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Right side navigation -->
                <ul class="navbar-nav">
                    <?php if (session()->get('authenticated')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown"
                               role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('settings/password') ?>">
                                        <i class="bi bi-key"></i> Change Password
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('settings/about') ?>">
                                        <i class="bi bi-info-circle"></i> Support / About
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('logout') ?>">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <button id="theme-toggle" class="btn btn-link nav-link" title="Toggle theme">
                            <i class="bi bi-sun theme-icon sun-icon"></i>
                            <i class="bi bi-moon-stars theme-icon moon-icon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container my-4">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <?php if (!isset($hide_nav) || !$hide_nav): ?>
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> <?= esc(app_name()) ?>. 
                        <span class="ms-2">
                            <button id="theme-toggle-footer" class="btn btn-link btn-sm text-decoration-none p-0">
                                <i class="bi bi-palette"></i> Toggle Theme
                            </button>
                        </span>
                    </p>
                    <?php if (ENVIRONMENT === 'development' && session()->get('authenticated')): ?>
                        <div class="mt-2 text-muted small" id="debug-auth-timer">
                            <?php
                                $recentAuthTime = session()->get('recent_auth_time');
                                if ($recentAuthTime):
                                    $configModel = new \App\Models\ConfigurationModel();
                                    $deauthTimeMinutes = $configModel->getParam('deauth_time', 15);
                                    $expiresAt = $recentAuthTime + ($deauthTimeMinutes * 60);
                                    $timeLeft = $expiresAt - time();
                            ?>
                                <span class="badge bg-info text-dark" title="Maintenance session expiration timer (Development Mode Only)">
                                    <i class="bi bi-shield-lock"></i> Maintenance Expiry: 
                                    <span id="auth-timer-countdown" data-expires="<?= $expiresAt ?>" data-now="<?= time() ?>">
                                        <?= gmdate($timeLeft > 3600 ? "H:i:s" : "i:s", max(0, $timeLeft)) ?>
                                    </span>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary" title="Development Mode Only">
                                    <i class="bi bi-shield-slash"></i> Maintenance: Locked
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dark Mode JS -->
    <script src="<?= base_url('assets/js/dark-mode.js') ?>"></script>
    
    <script>
        /**
         * Global fetch wrapper to include CSRF token in all POST/PUT/DELETE requests
         */
        (function() {
            const { fetch: originalFetch } = window;
            
            // Helper to get cookie value
            const getCookie = (name) => {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            };

            window.fetch = async (...args) => {
                let [resource, config] = args;
                
                // Ensure config exists for method check
                if (!config) {
                    config = {};
                }
                
                // Standardize method
                const method = config.method ? config.method.toUpperCase() : 'GET';
                
                if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
                    const csrfHeaderName = '<?= csrf_header() ?>';
                    const csrfTokenName = '<?= csrf_token() ?>';
                    const csrfCookieName = '<?= config('Security')->cookieName ?>';
                    
                    // Try to get token from cookie first (most up-to-date), then meta tag
                    let token = getCookie(csrfCookieName);
                    if (!token) {
                        const csrfMeta = document.querySelector(`meta[name="${csrfHeaderName}"]`);
                        if (csrfMeta) {
                            token = csrfMeta.content;
                        }
                    }
                    
                    if (token) {
                        config.headers = config.headers || {};
                        
                        // Handle different header types
                        if (config.headers instanceof Headers) {
                            config.headers.set(csrfHeaderName, token);
                        } else {
                            if (typeof config.headers.set === 'function') {
                                config.headers.set(csrfHeaderName, token);
                            } else {
                                config.headers[csrfHeaderName] = token;
                            }
                        }
                        
                        // Also inject as form field for FormData
                        if (config.body instanceof FormData) {
                            config.body.set(csrfTokenName, token);
                        }
                    }
                }
                
                const response = await originalFetch(resource, config);
                
                // Update meta tag if a new token is provided in the response headers
                const newToken = response.headers.get('<?= csrf_header() ?>');
                if (newToken) {
                    const csrfMeta = document.querySelector(`meta[name="<?= csrf_header() ?>"]`);
                    if (csrfMeta) {
                        csrfMeta.content = newToken;
                    }
                }
                
                // If we get a 403, it might be a CSRF failure
                if (response.status === 403) {
                    console.warn('Possible CSRF failure (403).');
                }
                
                // Handle session expiration for AJAX
                if (response.status === 401) {
                    try {
                        const data = await response.clone().json();
                        if (data.reauth) {
                            window.location.href = '<?= base_url('reauth') ?>';
                            return response;
                        }
                    } catch (e) {
                        // Not JSON or no reauth flag, handle as usual
                    }
                    
                    // Fallback for standard auth expiration
                    window.location.href = '<?= base_url('login') ?>';
                    return response;
                }

                // Update maintenance timer if header is present
                const maintenanceExpires = response.headers.get('X-Maintenance-Expires');
                if (maintenanceExpires) {
                    const timerSpan = document.getElementById('auth-timer-countdown');
                    if (timerSpan) {
                        timerSpan.dataset.expires = maintenanceExpires;
                        const badge = timerSpan.closest('.badge');
                        if (badge) {
                            badge.classList.remove('bg-danger');
                            badge.classList.add('bg-info');
                        }
                    } else {
                        // If it was locked, we might need to reload or reconstruct the UI
                        // For now, a reload is safest if the UI needs to change significantly
                        const debugContainer = document.getElementById('debug-auth-timer');
                        if (debugContainer && debugContainer.innerHTML.includes('Locked')) {
                            location.reload();
                        }
                    }
                }
                
                return response;
            };
        })();
    </script>
    
    <!-- Page-specific scripts -->
    <?php if (ENVIRONMENT === 'development' && session()->get('authenticated')): ?>
    <script>
        (function() {
            const timerSpan = document.getElementById('auth-timer-countdown');
            if (timerSpan) {
                const serverNow = parseInt(timerSpan.dataset.now);
                const clientNow = Math.floor(Date.now() / 1000);
                const offset = clientNow - serverNow;

                const updateTimer = () => {
                    const currentExpires = parseInt(timerSpan.dataset.expires);
                    const now = Math.floor(Date.now() / 1000) - offset;
                    const timeLeft = Math.max(0, currentExpires - now);
                    
                    if (timeLeft <= 0) {
                        timerSpan.textContent = 'Expired';
                        const badge = timerSpan.closest('.badge');
                        if (badge) {
                            badge.classList.replace('bg-info', 'bg-danger');
                        }
                        return;
                    }
                    
                    const hours = Math.floor(timeLeft / 3600);
                    const minutes = Math.floor((timeLeft % 3600) / 60);
                    const seconds = timeLeft % 60;
                    
                    let timeStr = '';
                    if (hours > 0) {
                        timeStr += hours.toString().padStart(2, '0') + ':';
                    }
                    timeStr += minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
                    
                    timerSpan.textContent = timeStr;
                };
                
                updateTimer(); // Run immediately
                setInterval(updateTimer, 1000);
            }
        })();
    </script>
    <?php endif; ?>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
