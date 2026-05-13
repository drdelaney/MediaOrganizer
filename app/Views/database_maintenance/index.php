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
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-download display-4 text-secondary"></i>
                        <h5 class="card-title mt-3">Backup Database</h5>
                        <p class="card-text">Download complete database backup (SQL dump).</p>
                        <a href="<?= base_url('database-maintenance/backupDatabase') ?>" class="btn btn-secondary" id="backup-btn">
                            <i class="bi bi-download"></i> Download Backup
                        </a>
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

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-check display-4 text-danger"></i>
                        <h5 class="card-title mt-3">Fix Config Schema</h5>
                        <p class="card-text">Increase configuration table column lengths to prevent truncation.</p>
                        <button id="fix-schema-btn" class="btn btn-danger">
                            <i class="bi bi-shield-check"></i> Fix Schema
                        </button>
                    </div>
                </div>
            </div>

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
        </div>

        <!-- Results Area -->
        <div id="results-area" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-list-check"></i> Operation Results</h5>
                </div>
                <div class="card-body">
                    <div id="results-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reindexBtn = document.getElementById('reindex-btn');
    const optimizeBtn = document.getElementById('optimize-btn');
    const convertBtn = document.getElementById('convert-btn');
    const fixSchemaBtn = document.getElementById('fix-schema-btn');
    const checkEnvBtn = document.getElementById('check-env-btn');
    const resultsArea = document.getElementById('results-area');
    const resultsContent = document.getElementById('results-content');

    function performOperation(url, button, operation) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
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

    optimizeBtn.addEventListener('click', () => {
        performOperation('<?= base_url('database-maintenance/optimizeTables') ?>', optimizeBtn, 'Optimize Tables');
    });

    convertBtn.addEventListener('click', () => {
        performOperation('<?= base_url('database-maintenance/convertToInnoDB') ?>', convertBtn, 'Convert to InnoDB');
    });

    fixSchemaBtn.addEventListener('click', () => {
        if (confirm('This will modify the configuration table schema to support longer parameter keys. Proceed?')) {
            performOperation('<?= base_url('database-maintenance/fixConfigSchema') ?>', fixSchemaBtn, 'Fix Config Schema');
        }
    });

    checkEnvBtn.addEventListener('click', () => {
        const originalText = checkEnvBtn.innerHTML;
        checkEnvBtn.disabled = true;
        checkEnvBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Checking...';

        fetch('<?= base_url('database-maintenance/checkEnvironmentAjax') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
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
