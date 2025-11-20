<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? esc($title) : esc(app_name()) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Dark Mode CSS -->
    <link href="<?= base_url('assets/css/dark-mode.css') ?>" rel="stylesheet">
    
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
    </style>
</head>
<body>
    <!-- Navigation -->
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
                        <a class="nav-link" href="<?= base_url('movies') ?>">
                            <i class="bi bi-film"></i> Movies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('loans') ?>">
                            <i class="bi bi-person-check-fill"></i> Loaned Movies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('people') ?>">
                            <i class="bi bi-people"></i> Loaned Users
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

    <!-- Main Content -->
    <main class="container my-4">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
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
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dark Mode JS -->
    <script src="<?= base_url('assets/js/dark-mode.js') ?>"></script>
    
    <!-- Page-specific scripts -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
