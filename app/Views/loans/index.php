<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
/** @var array $loanedMedia */
$loanedMedia = $loanedMedia ?? [];
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-person-check-fill"></i> Currently Loaned Media
            <small class="text-muted fs-6">(<?= count($loanedMedia) ?> active loans)</small>
        </h1>

        <!-- Notification Area -->
        <div id="notificationArea"></div>

        <!-- Bulk Actions Bar -->
        <?php if (!empty($loanedMedia)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    <strong>Select All</strong>
                                </label>
                            </div>
                            <small class="text-muted" id="selectionCount">0 loan(s) selected</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" id="sendReminderBtn" disabled>
                                <i class="bi bi-envelope"></i> Send Email Reminder(s)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Loaned Media Table -->
        <?php if (empty($loanedMedia)): ?>
            <div class="text-center py-5">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <h3 class="mt-3 text-muted">No Media Currently Loaned Out</h3>
                <p class="text-muted">All media that are in your collection</p>
                <a href="<?= base_url('media') ?>" class="btn btn-primary">
                    <i class="bi bi-collection-play"></i> View Media Library
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;">
                                <label for="selectAllHeader" class="visually-hidden">Select All</label>
                                <input type="checkbox" class="form-check-input" id="selectAllHeader" style="cursor: pointer;">
                            </th>
                            <th style="width: 60px;">Poster</th>
                            <th>Title</th>
                            <th>Loaned To</th>
                            <th>Contact</th>
                            <th>Loan Date</th>
                            <th>Days Out</th>
                            <th>Medium</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loanedMedia as $loan): ?>
                            <tr class="loan-row <?= empty($loan['loan_id']) ? 'table-warning' : '' ?>" data-loan-id="<?= $loan['loan_id'] ?>">
                                <!-- Checkbox -->
                                <td>
                                    <?php if (!empty($loan['loan_id'])): ?>
                                        <label for="loan-checkbox-<?= $loan['loan_id'] ?>" class="visually-hidden">Select loan for <?= esc($loan['title'] ?: $loan['o_title'] ?: 'this media entry') ?></label>
                                        <input type="checkbox" class="form-check-input loan-checkbox" 
                                               id="loan-checkbox-<?= $loan['loan_id'] ?>"
                                               data-loan-id="<?= $loan['loan_id'] ?>"
                                               data-person-id="<?= $loan['person_id'] ?>"
                                               data-person-email="<?= esc($loan['person_email'] ?? '') ?>"
                                               style="cursor: pointer;">
                                    <?php else: ?>
                                        <i class="bi bi-exclamation-triangle-fill text-warning" title="Broken Loan Record"></i>
                                    <?php endif; ?>
                                </td>
                                <!-- Poster -->
                                <td>
                                    <?php if ($loan['poster_md5']): ?>
                                        <img src="<?= base_url('media/poster/' . $loan['movie_id']) ?>"
                                             class="poster-thumbnail" 
                                             alt="<?= esc($loan['title']) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center poster-thumbnail">
                                            <i class="bi bi-film text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Media Title -->
                                <td>
                                    <div>
                                        <a href="<?= base_url('media/view/' . $loan['movie_id']) ?>"
                                           class="text-decoration-none">
                                            <strong><?= esc($loan['title'] ?: $loan['o_title'] ?: 'Untitled') ?></strong>
                                        </a>
                                        <?php if ($loan['title'] && $loan['o_title'] && $loan['title'] !== $loan['o_title']): ?>
                                            <br><small class="text-muted"><?= esc($loan['o_title']) ?></small>
                                        <?php endif; ?>
                                        <?php if ($loan['year']): ?>
                                            <br><small class="text-muted">(<?= esc($loan['year']) ?>)</small>
                                        <?php endif; ?>
                                        <?php if ($loan['director']): ?>
                                            <br><small class="text-muted">Dir: <?= esc($loan['director']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Loaned To -->
                                <td>
                                    <strong><?= esc($loan['person_name']) ?></strong>
                                </td>

                                <!-- Contact -->
                                <td>
                                    <?php if ($loan['person_email']): ?>
                                        <div>
                                            <i class="bi bi-envelope"></i> 
                                            <a href="mailto:<?= esc($loan['person_email']) ?>">
                                                <?= esc($loan['person_email']) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($loan['person_phone']): ?>
                                        <div>
                                            <i class="bi bi-phone"></i> 
                                            <?= esc($loan['person_phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Loan Date -->
                                <td>
                                    <?php if ($loan['date']): ?>
                                        <?= user_date($loan['date'], 'M d, Y') ?>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Days Out -->
                                <td>
                                    <?php
                                    if ($loan['date']):
                                        $loanDate = new DateTime($loan['date'], new DateTimeZone('UTC'));
                                        $now = new DateTime('now', new DateTimeZone('UTC'));
                                        $daysOut = $now->diff($loanDate)->days;
                                        $badgeClass = 'bg-success';
                                        if ($daysOut > 30) {
                                            $badgeClass = 'bg-danger';
                                        } elseif ($daysOut > 14) {
                                            $badgeClass = 'bg-warning';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= $daysOut ?> <?= $daysOut === 1 ? 'day' : 'days' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Medium -->
                                <td>
                                    <?= $loan['medium_name'] ? esc($loan['medium_name']) : '<span class="text-muted">-</span>' ?>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('media/view/' . $loan['movie_id']) ?>"
                                           class="btn btn-outline-primary" 
                                           title="View Media Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-success return-loan-btn"
                                                data-movie-id="<?= $loan['movie_id'] ?>"
                                                data-movie-title="<?= esc($loan['title'] ?: $loan['o_title'] ?: 'this media') ?>"
                                                title="Return Media">
                                            <i class="bi bi-arrow-return-left"></i> Return
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Stats -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Loaned</h5>
                            <p class="card-text display-6"><?= count($loanedMedia) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Overdue (30+ days)</h5>
                            <p class="card-text display-6">
                                <?php
                                $overdue = 0;
                                $now = new DateTime('now', new DateTimeZone('UTC'));
                                foreach ($loanedMedia as $loan) {
                                    if ($loan['date']) {
                                        $loanDate = new DateTime($loan['date'], new DateTimeZone('UTC'));
                                        if ($now->diff($loanDate)->days > 30) {
                                            $overdue++;
                                        }
                                    }
                                }
                                echo $overdue;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Unique Borrowers</h5>
                            <p class="card-text display-6">
                                <?php
                                $uniquePeople = array_unique(array_filter(array_column($loanedMedia, 'person_id')));
                                echo count($uniquePeople);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<style>
    .poster-thumbnail {
        width: 40px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        transition: transform 0.2s ease;
    }
    
    .poster-thumbnail:hover {
        transform: scale(1.5);
        z-index: 10;
        position: relative;
    }
    
    .loan-row {
        transition: background-color 0.3s ease;
    }
    
    /*noinspection CssUnusedSymbol*/
    .loan-row.returning {
        opacity: 0.6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show notification function
    function showNotification(message, type = 'success') {
        const notificationArea = document.getElementById('notificationArea');
        const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        const icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle');
        
        notificationArea.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi bi-${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        notificationArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-dismiss after 8 seconds for warnings/errors
        setTimeout(() => {
            const alert = notificationArea.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, type === 'success' ? 5000 : 8000);
    }

    // Update selection count and enable/disable send button
    function updateSelectionUI() {
        const checkboxes = document.querySelectorAll('.loan-checkbox');
        const checkedBoxes = document.querySelectorAll('.loan-checkbox:checked');
        const count = checkedBoxes.length;
        
        document.getElementById('selectionCount').textContent = `${count} loan(s) selected`;
        document.getElementById('sendReminderBtn').disabled = count === 0;
        
        // Update "Select All" checkbox state
        const selectAllTop = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        if (selectAllTop) {
            selectAllTop.checked = count > 0 && count === checkboxes.length;
            selectAllTop.indeterminate = count > 0 && count < checkboxes.length;
        }
        if (selectAllHeader) {
            selectAllHeader.checked = count > 0 && count === checkboxes.length;
            selectAllHeader.indeterminate = count > 0 && count < checkboxes.length;
        }
    }

    // Select All functionality (top bar)
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.loan-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelectionUI();
        });
    }

    // Select All functionality (table header)
    const selectAllHeader = document.getElementById('selectAllHeader');
    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.loan-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            if (selectAllCheckbox) selectAllCheckbox.checked = this.checked;
            updateSelectionUI();
        });
    }

    // Individual checkbox change
    document.querySelectorAll('.loan-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionUI);
    });

    // Send email reminder
    const sendReminderBtn = document.getElementById('sendReminderBtn');
    if (sendReminderBtn) {
        sendReminderBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.loan-checkbox:checked');
            if (checkedBoxes.length === 0) {
                showNotification('Please select at least one loan to send a reminder', 'error');
                return;
            }

            // Count unique people
            const uniquePeople = new Set();
            const loanIds = [];
            checkedBoxes.forEach(cb => {
                loanIds.push(cb.dataset.loanId);
                uniquePeople.add(cb.dataset.personId);
            });

            const peopleCount = uniquePeople.size;
            const loanCount = loanIds.length;
            const confirmMessage = `Send email reminder(s) to ${peopleCount} person(s) for ${loanCount} loan(s)?\n\n` +
                                 (peopleCount < loanCount ? 'Note: Multiple loans to the same person will be combined in one email.' : '');

            if (!confirm(confirmMessage)) {
                return;
            }

            // Disable button and show loading
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending Emails...';

            // Send request
            // Build form data with loan_ids as array
            const formData = new URLSearchParams();
            loanIds.forEach(id => {
                formData.append('loan_ids[]', id);
            });

            fetch('<?= base_url('loans/sendReminder') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.status === 'warning') {
                    showNotification(data.message, data.status);
                    
                    // Uncheck all checkboxes after successful send
                    checkedBoxes.forEach(cb => cb.checked = false);
                    updateSelectionUI();
                } else {
                    showNotification(data.message, 'error');
                }
                
                // Log details if available
                if (data.details && data.details.errors && data.details.errors.length > 0) {
                    console.error('Email errors:', data.details.errors);
                }
            })
            .catch(error => {
                showNotification('Error sending reminders: ' + error.message, 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalHtml;
            });
        });
    }

    // Return loan functionality
    document.querySelectorAll('.return-loan-btn').forEach(function(returnBtn) {
        returnBtn.addEventListener('click', function() {
            const movieId = this.dataset.movieId;
            const movieTitle = this.dataset.movieTitle;
            
            if (!confirm(`Are you sure you want to mark "${movieTitle}" as returned?`)) {
                return;
            }

            const row = this.closest('tr');
            row.classList.add('returning');
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

            fetch(`<?= base_url('loans/returnLoan/') ?>${movieId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    
                    // Fade out and remove the row
                    row.style.transition = 'opacity 0.5s ease';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        
                        // Check if table is now empty and reload page
                        const remainingRows = document.querySelectorAll('.loan-row').length;
                        if (remainingRows === 0) {
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            // Update the count in the header
                            const countElement = document.querySelector('h1 small');
                            if (countElement) {
                                countElement.textContent = `(${remainingRows} active loan${remainingRows !== 1 ? 's' : ''})`;
                            }
                        }
                    }, 500);
                } else {
                    row.classList.remove('returning');
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                row.classList.remove('returning');
                this.innerHTML = originalHtml;
                this.disabled = false;
                showNotification('Error processing return: ' + error.message, 'error');
            });
        });
    });
});
</script>
<?= $this->endsection() ?>
