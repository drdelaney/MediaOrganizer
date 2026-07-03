<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Database Setup</h3>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('message')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('message') ?>
                        </div>
                    <?php endif; ?>

                    <p>Welcome to Media Organizer. Before you can start using the application, we need to set up your database.</p>
                    
                    <h4>Environment Checks</h4>
                    <ul class="list-group mb-4">
                        <?php if (isset($checks) && is_array($checks)): ?>
                            <?php foreach ($checks as $check): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= $check['name'] ?></strong>
                                        <br><small><?= $check['message'] ?></small>
                                    </div>
                                    <span class="badge bg-<?= $check['status'] === 'success' ? 'success' : ($check['status'] === 'warning' ? 'warning' : ($check['status'] === 'info' ? 'info' : 'danger')) ?>">
                                        <?= ucfirst($check['status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <div class="card border-info mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-info">Existing Database?</h5>
                            <p class="card-text text-muted small">
                                If this system already has the required database tables, you can mark the initial schema migration as completed. 
                                This prevents setup from trying to recreate existing tables. Use this only if the database already contains the expected schema.
                            </p>
                            <form action="<?= base_url('setup/markMigrationComplete') ?>" method="post" onsubmit="return confirm('Are you sure? This should only be used on databases that already contain the original schema.');">
                                <?= csrf_field() ?>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-info btn-sm">Mark Initial Schema as Completed</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (isset($setupComplete) && $setupComplete): ?>
                        <div class="alert alert-info">
                            Setup is already marked as complete. You can still run it again if you need to refresh your database schema.
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('setup/run') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="app_base_url" class="form-label">Base URL</label>
                            <input type="text" id="app_base_url" class="form-control" value="<?= esc($appBaseURL ?? '') ?>" readonly tabindex="-1">
                            <div class="form-text">This is your application's base URL, configured in <code>.env</code>.</div>
                        </div>

                        <div class="mb-3">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control" id="app_name" name="app_name" value="<?= esc($appName ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone" name="timezone" required autocomplete="off">
                                <?php if (isset($timezones) && is_array($timezones)): ?>
                                    <?php foreach ($timezones as $tz): ?>
                                        <option value="<?= esc($tz) ?>" <?= $tz === 'UTC' ? 'selected' : '' ?>><?= esc($tz) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if (empty($initialPassword ?? '')): ?>
                            <div class="mb-4">
                                <label for="initial_password" class="form-label">Set Initial Admin Password</label>
                                <input type="password" class="form-control" id="initial_password" name="initial_password" required>
                                <div class="form-text">Please set a strong password for the admin account.</div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p class="mb-0">Using initial password from <code>.env</code>: <code><?= esc($initialPassword ?? '') ?></code></p>
                                <input type="hidden" name="initial_password" value="<?= esc($initialPassword ?? '') ?>">
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Run Database Setup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#timezone', {
            create: false,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            placeholder: 'Search or select a timezone...',
            allowEmptyOption: false,
            dropdownParent: 'body'
        });
    });
</script>
<?= $this->endSection() ?>
