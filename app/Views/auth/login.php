<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= esc(app_name()) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Add dark mode CSS -->
    <link href="<?= base_url('assets/css/dark-mode.css') ?>" rel="stylesheet">

    <script>
        // Apply theme immediately to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('mediaorganizer-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>
<body class="bg-light">
<script src="<?= base_url('assets/js/dark-mode.js') ?>"></script>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-lock-fill text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3"><?= esc(app_name()) ?></h2>
                        <p class="text-muted">Please enter your password</p>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('message')): ?>
                        <div class="alert alert-info">
                            <?= session()->getFlashdata('message') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('authenticate') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg"
                                   id="password" name="password" required autofocus>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input"
                                   id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <button id="theme-toggle" class="btn btn-link btn-sm text-decoration-none" title="Toggle theme">
            <i class="bi bi-sun theme-icon sun-icon"></i>
            <i class="bi bi-moon-stars theme-icon moon-icon"></i>
            <span class="ms-1">Toggle Theme</span>
        </button>
    </div>
</div>
</body>
</html>