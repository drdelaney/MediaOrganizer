<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php $tables = $tables ?? []; ?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-tools"></i> Database Maintenance
        </h1>

        <?php if (isset($needsFix) && $needsFix): ?>
        <div class="alert alert-danger">
            <i class="bi bi-shield-exclamation"></i>
            <strong>Action Required:</strong> The configuration table schema needs to be updated to version 7 to support longer parameter keys.
            Please use the <strong>Fix Schema</strong> button below.
        </div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Warning:</strong> These operations can affect your database.
            Always backup your database before performing maintenance operations.
        </div>

        <!-- Maintenance Actions -->
        <div class="row mb-4">

            <?php if (isset($needsFix) && $needsFix): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-exclamation display-4 text-danger"></i>
                        <h5 class="card-title mt-3">Fix Configuration Schema</h5>
                        <p class="card-text text-danger">Update schema to version 7 to support longer keys.</p>
                        <button id="fix-schema-btn" class="btn btn-danger">
                            <i class="bi bi-shield-fill-check"></i> Fix Schema
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-download display-4 text-secondary"></i>
                        <h5 class="card-title mt-3">Backup Database</h5>
                        <p class="card-text">Download database backup.</p>
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('database-maintenance/backupDatabase') ?>" class="btn btn-secondary" id="backup-btn">
                                <i class="bi bi-file-earmark-code"></i> SQL Dump
                            </a>
                            <?php if (isset($dbDriver) && $dbDriver === 'SQLite3'): ?>
                                <a href="<?= base_url('database-maintenance/downloadRawSqlite') ?>" class="btn btn-outline-secondary" id="backup-raw-btn">
                                    <i class="bi bi-file-earmark-binary"></i> Raw SQLite
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-list-ul display-4 text-warning"></i>
                        <h5 class="card-title mt-3">Manage Lookups</h5>
                        <p class="card-text">Manage mediums, collections, volumes, and codecs.</p>
                        <a href="<?= base_url('database-maintenance/manage-lookups') ?>" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Manage
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-clockwise display-4 text-primary"></i>
                        <h5 class="card-title mt-3">Re-index Tables</h5>
                        <p class="card-text">Rebuild all table indexes for better performance.</p>
                        <button id="reindex-btn" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise"></i> Re-index All
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-speedometer2 display-4 text-success"></i>
                        <h5 class="card-title mt-3">Optimize Tables</h5>
                        <p class="card-text">Optimize all tables to reclaim unused space.</p>
                        <button id="optimize-btn" class="btn btn-success">
                            <i class="bi bi-speedometer2"></i> Optimize All
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($dbDriver) && $dbDriver === 'MySQLi'): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-gear display-4 text-info"></i>
                        <h5 class="card-title mt-3">Convert to InnoDB</h5>
                        <p class="card-text">Convert all tables to InnoDB engine.</p>
                        <button id="convert-btn" class="btn btn-info">
                            <i class="bi bi-gear"></i> Convert All
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>


            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-files display-4 text-primary"></i>
                        <h5 class="card-title mt-3">Duplicate Detector</h5>
                        <p class="card-text">Scan the database for potential duplicate media entries.</p>
                        <a href="<?= base_url('database-maintenance/duplicate-detector') ?>" class="btn btn-primary">
                            <i class="bi bi-search"></i> Scan for Duplicates
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-code-square display-4 text-info"></i>
                        <h5 class="card-title mt-3">PHP & Package Environment</h5>
                        <p class="card-text">Check PHP version, extensions, and dependencies.</p>
                        <button id="check-env-btn" class="btn btn-info">
                            <i class="bi bi-code-square"></i> Show Details
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($pendingMigrationsCount) && $pendingMigrationsCount > 0): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-database-gear display-4 text-danger"></i>
                        <h5 class="card-title mt-3">Apply Migrations</h5>
                        <p class="card-text">Apply latest database migrations to keep your schema up to date.</p>
                        <div class="btn-group w-100">
                            <button id="apply-migrations-btn" class="btn btn-danger">
                                <i class="bi bi-database-up"></i> Apply Now
                            </button>
                            <?php if (env('app.setupComplete') !== true): ?>
                            <a href="<?= base_url('setup') ?>" class="btn btn-outline-danger" title="Go to Setup Page">
                                <i class="bi bi-gear"></i> Setup
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check display-4 text-secondary"></i>
                        <h5 class="card-title mt-3">Cron Jobs</h5>
                        <p class="card-text">Manage and monitor background tasks and scheduled maintenance.</p>
                        <button id="show-cron-btn" class="btn btn-secondary">
                            <i class="bi bi-calendar-check"></i> Manage Crons
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Area -->
        <div id="results-area" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Operation Results</h5>
                    <button type="button" class="btn-close" onclick="document.getElementById('results-area').style.display='none'"></button>
                </div>
                <div class="card-body">
                    <div id="results-content"></div>
                </div>
            </div>
        </div>

        <!-- Cron Jobs Area -->
        <div id="cron-area" style="display: none;" class="mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Cron Jobs Management</h5>
                    <button type="button" class="btn-close" onclick="document.getElementById('cron-area').style.display='none'"></button>
                </div>
                <div class="card-body">
                    <div class="alert <?= (isset($systemCronLastRun) && $systemCronLastRun) ? 'alert-success' : 'alert-warning' ?> mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi <?= (isset($systemCronLastRun) && $systemCronLastRun) ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> fs-3"></i>
                            </div>
                            <div>
                                <h6>System Cron Status</h6>
                                <?php if (isset($systemCronLastRun) && $systemCronLastRun): ?>
                                    <p class="mb-0">The system cron is active. Last run: <strong><?= $systemCronLastRun ?></strong></p>
                                <?php else: ?>
                                    <p class="mb-1">The system cron has not run yet or is not set up.</p>
                                    <small>To enable automatic background tasks, add the following entry to your server's crontab (running as <strong>www-data</strong> or your web user):</small>
                                    <div class="bg-dark text-light p-2 mt-2 rounded">
                                        <code>* * * * * php <?= ROOTPATH ?>spark cron:run > /dev/null 2>&1</code>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Job Name</th>
                                    <th class="text-nowrap">Schedule</th>
                                    <th class="text-nowrap">Last Run</th>
                                    <th class="text-nowrap">Next Run</th>
                                    <th>Message</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cronJobs) || !is_array($cronJobs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No cron jobs found. Make sure to apply migrations.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cronJobs as $job): ?>
                                        <tr>
                                            <td>
                                                <?php if ($job['enabled']): ?>
                                                    <?php if ($job['status'] === 'success'): ?>
                                                        <span class="badge bg-success" title="Success"><i class="bi bi-check-circle"></i></span>
                                                    <?php elseif ($job['status'] === 'running'): ?>
                                                        <span class="badge bg-primary" title="Running"><i class="bi bi-hourglass-split"></i></span>
                                                    <?php elseif ($job['status'] === 'error'): ?>
                                                        <span class="badge bg-danger" title="Error"><i class="bi bi-x-circle"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary" title="Pending"><i class="bi bi-clock"></i></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-dark" title="Disabled"><i class="bi bi-pause-circle"></i></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= esc($job['name']) ?></strong><br>
                                                <small class="text-muted"><?= esc($job['job_key']) ?></small>
                                            </td>
                                            <td class="text-nowrap"><code><?= esc($job['schedule'] ?? 'N/A') ?></code></td>
                                            <td class="text-nowrap"><?= $job['last_run'] ?: '<span class="text-muted">Never</span>' ?></td>
                                            <td class="text-nowrap"><?= $job['next_run'] ?: '<span class="text-muted">As needed</span>' ?></td>
                                            <td><small><?= esc($job['last_message']) ?></small></td>
                                            <td class="text-end">
                                                <div class="form-check form-switch d-inline-block align-middle me-3" title="Enable/Disable">
                                                    <input class="form-check-input cron-toggle" type="checkbox" data-id="<?= $job['id'] ?>" <?= $job['enabled'] ? 'checked' : '' ?> id="cron-toggle-<?= $job['id'] ?>">
                                                    <label class="form-check-label visually-hidden" for="cron-toggle-<?= $job['id'] ?>">Enable/Disable <?= esc($job['name']) ?></label>
                                                </div>
                                                <button class="btn btn-sm btn-outline-secondary edit-cron-btn" data-id="<?= $job['id'] ?>" data-name="<?= esc($job['name']) ?>" data-schedule="<?= esc($job['schedule'] ?? '0 0 * * *') ?>" title="Edit Schedule">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary run-cron-btn" data-id="<?= $job['id'] ?>" title="Run Now">
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Cron Modal -->
<div class="modal fade" id="editCronModal" tabindex="-1" aria-labelledby="editCronModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCronModalLabel">Edit Cron Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-cron-form">
                    <input type="hidden" id="edit-cron-id">
                    <div class="mb-3">
                        <label for="edit-cron-name" class="form-label">Job Name</label>
                        <input type="text" class="form-control" id="edit-cron-name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit-cron-schedule" class="form-label">Schedule (Cron Expression)</label>
                        <input type="text" class="form-control" id="edit-cron-schedule" required>
                        <div class="form-text">
                            Format: <code>minute hour day month day-of-week</code><br>
                            Examples:<br>
                            <code>0 4 * * 0</code> (Sunday at 4 AM)<br>
                            <code>0 0 * * *</code> (Daily at Midnight)<br>
                            <code>0 0 1 * *</code> (Monthly on the 1st)
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-cron-schedule-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reindexBtn = document.getElementById('reindex-btn');
    const fixSchemaBtn = document.getElementById('fix-schema-btn');
    const optimizeBtn = document.getElementById('optimize-btn');
    const convertBtn = document.getElementById('convert-btn');
    const applyMigrationsBtn = document.getElementById('apply-migrations-btn');
    const checkEnvBtn = document.getElementById('check-env-btn');
    const showCronBtn = document.getElementById('show-cron-btn');
    const resultsArea = document.getElementById('results-area');
    const resultsContent = document.getElementById('results-content');
    const cronArea = document.getElementById('cron-area');

    const saveCronScheduleBtn = document.getElementById('save-cron-schedule-btn');
    const editCronModal = new bootstrap.Modal(document.getElementById('editCronModal'));

    function performOperation(url, button, operation) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => {
            if (response.status === 401) return;
            return response.json();
        })
        .then(data => {
            if (!data) return;
            showResults(data, operation);
        })
        .catch(error => {
            showResults({
                status: 'error',
                message: 'Network error: ' + error.message
            }, operation);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    function showResults(data, operation) {
        resultsContent.innerHTML = '';
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${data.status === 'success' ? 'success' : 'danger'}`;
        
        const h6 = document.createElement('h6');
        h6.innerHTML = `<i class="bi bi-${data.status === 'success' ? 'check-circle' : 'x-circle'}"></i> `;
        h6.appendChild(document.createTextNode(operation));
        alertDiv.appendChild(h6);
        
        const p = document.createElement('p');
        const strong = document.createElement('strong');
        strong.textContent = data.message;
        p.appendChild(strong);
        alertDiv.appendChild(p);
        
        if (data.results && data.results.length > 0) {
            const ul = document.createElement('ul');
            ul.className = 'mb-0';
            data.results.forEach(result => {
                const li = document.createElement('li');
                li.textContent = result;
                ul.appendChild(li);
            });
            alertDiv.appendChild(ul);
        }

        resultsContent.appendChild(alertDiv);
        resultsArea.style.display = 'block';
        resultsArea.scrollIntoView({ behavior: 'smooth' });
    }

    function showEnvironmentResults(data) {
        const statusColors = {
            'success': 'success',
            'warning': 'warning',
            'error': 'danger'
        };
        
        const statusIcons = {
            'success': 'check-circle-fill',
            'warning': 'exclamation-triangle-fill',
            'error': 'x-circle-fill'
        };

        let html = `<div class="alert alert-${statusColors[data.status]}">`;
        html += `<h6><i class="bi bi-${statusIcons[data.status]}"></i> Environment Check</h6>`;
        html += `<p><strong>${data.message}</strong></p>`;
        html += '</div>';

        if (data.results) {
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-bordered">';
            html += '<thead><tr><th>Status</th><th>Component</th><th>Details</th></tr></thead>';
            html += '<tbody>';
            
            Object.entries(data.results).forEach(([, check]) => {
                const statusIcon = statusIcons[check.status] || 'info-circle';
                const statusColor = statusColors[check.status] || 'secondary';
                
                html += '<tr>';
                html += `<td class="text-center"><i class="bi bi-${statusIcon} text-${statusColor}"></i></td>`;
                html += `<td><strong>${check.name}</strong>`;
                if (check.description) {
                    html += `<br><small class="text-muted">${check.description}</small>`;
                }
                html += '</td>';
                html += '<td>';
                html += check.message;
                if (check.current) {
                    html += `<br><small><strong>Current:</strong> ${check.current}</small>`;
                }
                if (check.required) {
                    html += `<br><small><strong>Required:</strong> ${check.required}</small>`;
                }
                if (check.recommended) {
                    html += `<br><small><strong>Recommended:</strong> ${check.recommended}</small>`;
                }
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            html += '</div>';
        }

        resultsContent.innerHTML = html;
        resultsArea.style.display = 'block';
        resultsArea.scrollIntoView({ behavior: 'smooth' });
    }

    reindexBtn.addEventListener('click', () => {
        performOperation('<?= base_url('database-maintenance/reindexTables') ?>', reindexBtn, 'Re-index Tables');
    });

    if (fixSchemaBtn) {
        fixSchemaBtn.addEventListener('click', () => {
            performOperation('<?= base_url('database-maintenance/fixConfigSchema') ?>', fixSchemaBtn, 'Fix Configuration Schema');
        });
    }

    optimizeBtn.addEventListener('click', () => {
        performOperation('<?= base_url('database-maintenance/optimizeTables') ?>', optimizeBtn, 'Optimize Tables');
    });

    if (convertBtn) {
        convertBtn.addEventListener('click', () => {
            performOperation('<?= base_url('database-maintenance/convertToInnoDB') ?>', convertBtn, 'Convert to InnoDB');
        });
    }

    if (applyMigrationsBtn) {
        applyMigrationsBtn.addEventListener('click', () => {
            performOperation('<?= base_url('database-maintenance/applyMigrations') ?>', applyMigrationsBtn, 'Apply Migrations');
        });
    }

    showCronBtn.addEventListener('click', () => {
        cronArea.style.display = cronArea.style.display === 'none' ? 'block' : 'none';
        if (cronArea.style.display === 'block') {
            cronArea.scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Cron Job Toggles
    document.querySelectorAll('.cron-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const jobId = this.dataset.id;
            const enabled = this.checked ? 1 : 0;
            
            fetch(`<?= base_url('database-maintenance/cron/toggle') ?>/${jobId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: `enabled=${enabled}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Error: ' + data.message);
                    this.checked = !this.checked;
                }
            });
        });
    });

    // Run Cron Manually
    document.querySelectorAll('.run-cron-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.id;
            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch(`<?= base_url('database-maintenance/cron/run') ?>/${jobId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // Reload to show updated status
                } else {
                    alert('Error: ' + data.message);
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
                this.disabled = false;
                this.innerHTML = originalHtml;
            });
        });
    });

    // Edit Cron Schedule
    document.querySelectorAll('.edit-cron-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-cron-id').value = this.dataset.id;
            document.getElementById('edit-cron-name').value = this.dataset.name;
            document.getElementById('edit-cron-schedule').value = this.dataset.schedule;
            editCronModal.show();
        });
    });

    saveCronScheduleBtn.addEventListener('click', function() {
        const jobId = document.getElementById('edit-cron-id').value;
        const schedule = document.getElementById('edit-cron-schedule').value;
        
        saveCronScheduleBtn.disabled = true;
        saveCronScheduleBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        fetch(`<?= base_url('database-maintenance/cron/update-schedule') ?>/${jobId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            },
            body: `schedule=${encodeURIComponent(schedule)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
                saveCronScheduleBtn.disabled = false;
                saveCronScheduleBtn.innerHTML = 'Save Changes';
            }
        })
        .catch(error => {
            alert('Network error: ' + error.message);
            saveCronScheduleBtn.disabled = false;
            saveCronScheduleBtn.innerHTML = 'Save Changes';
        });
    });

    checkEnvBtn.addEventListener('click', () => {
        const originalText = checkEnvBtn.innerHTML;
        checkEnvBtn.disabled = true;
        checkEnvBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Checking...';

        fetch('<?= base_url('database-maintenance/checkEnvironmentAjax') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => {
            if (response.status === 401) return;
            return response.json();
        })
        .then(data => {
            if (!data) return;
            showEnvironmentResults(data);
        })
        .catch(error => {
            showEnvironmentResults({
                status: 'error',
                message: 'Network error: ' + error.message,
                results: {}
            });
        })
        .finally(() => {
            checkEnvBtn.disabled = false;
            checkEnvBtn.innerHTML = originalText;
        });
    });
});
</script>
<?= $this->endsection() ?>
