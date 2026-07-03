<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CronJobModel;
use App\Models\LoanModel;

class CronLoanReminders extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'cron:loan-reminders';
    protected $description = 'Sends email reminders for media loaned over 30 days ago.';
    protected $usage       = 'cron:loan-reminders';

    public function run(array $params)
    {
        $cronModel = new CronJobModel();
        $jobKey = 'loan_reminders';
        
        $cronModel->markStarted($jobKey);

        try {
            $loanModel = new LoanModel();
            $loanedMedia = $loanModel->getAllLoanedMedia();
            
            $now = time();
            $overdueLoans = [];
            $allLoansByPerson = [];
            
            foreach ($loanedMedia as $loan) {
                if (empty($loan['date'])) continue;
                
                $personId = $loan['person_id'];
                if (!$personId) continue;

                if (!isset($allLoansByPerson[$personId])) {
                    $allLoansByPerson[$personId] = [
                        'person_name' => $loan['person_name'],
                        'person_email' => $loan['person_email'],
                        'loans' => []
                    ];
                }

                $loanDate = strtotime($loan['date']);
                $diff = $now - $loanDate;
                $days = floor($diff / (60 * 60 * 24));
                
                $loan['is_overdue'] = ($days > 30);
                $allLoansByPerson[$personId]['loans'][] = $loan;

                if ($loan['is_overdue']) {
                    $overdueLoans[] = $loan;
                }
            }

            if (empty($allLoansByPerson)) {
                $message = "No active loans found.";
                CLI::write($message, 'green');
                $cronModel->markFinished($jobKey, 'success', $message);
                return;
            }

            $email = \Config\Services::email();
            $emailConfig = config('Email');
            $successCount = 0;
            $failedCount = 0;
            $notifiedUsers = [];

            foreach ($allLoansByPerson as $personId => $personData) {
                // Only send email to person if they have overdue loans
                $overdueItems = array_filter($personData['loans'], function($l) {
                    return $l['is_overdue'];
                });

                if (empty($overdueItems)) {
                    continue;
                }

                if (empty($personData['person_email'])) {
                    $failedCount++;
                    continue;
                }

                $email->clear();
                $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName ?: 'Media Organizer');
                $email->setTo($personData['person_email']);
                $email->setSubject('Overdue Media Reminder');
                
                $messageContent = $this->buildSimpleReminderEmail($personData['person_name'], $overdueItems);
                $email->setMessage($messageContent);

                if ($email->send()) {
                    $successCount++;
                    $notifiedUsers[] = $personId;
                } else {
                    $failedCount++;
                }
            }

            // Send summary email to admin with ALL loans
            $this->sendAdminSummary($emailConfig, $allLoansByPerson, $notifiedUsers, $failedCount);

            $message = "Processed reminders. Notified: {$successCount}, Failed: {$failedCount}, Total persons with loans: " . count($allLoansByPerson) . ". Summary email sent to admin.";
            CLI::write($message, $failedCount > 0 ? 'yellow' : 'green');

            $cronModel->markFinished($jobKey, 'success', $message);

        } catch (\Exception $e) {
            $message = "Error: " . $e->getMessage();
            CLI::error($message);
            $cronModel->markFinished($jobKey, 'error', $message);
        }
    }

    private function sendAdminSummary($emailConfig, $allLoansByPerson, $notifiedPersonIds, $failedCount)
    {
        $email = \Config\Services::email();
        $email->clear();
        $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName ?: 'Media Organizer');
        $email->setTo($emailConfig->fromEmail);
        $email->setSubject('Weekly Media Loan Summary');

        $html = "<h3>Weekly Media Loan Summary</h3>";
        
        if (!empty($allLoansByPerson)) {
            $html .= "<p>Current status of all loaned media:</p>";
            $html .= "<table style='border-collapse: collapse; width: 100%; border: 1px solid #dee2e6;'>";
            $html .= "<thead><tr style='background-color: #f2f2f2;'><th style='border: 1px solid #dee2e6; padding: 8px;'>User</th><th style='border: 1px solid #dee2e6; padding: 8px;'>Email</th><th style='border: 1px solid #dee2e6; padding: 8px;'>Items (Date)</th><th style='border: 1px solid #dee2e6; padding: 8px;'>Status</th></tr></thead>";
            $html .= "<tbody>";
            foreach ($allLoansByPerson as $personId => $user) {
                $items = array_map(function($loan) {
                    $color = $loan['is_overdue'] ? 'red' : 'inherit';
                    return "<span style='color: {$color};'>{$loan['title']} ({$loan['date']})</span>";
                }, $user['loans']);
                
                $status = "";
                if (in_array($personId, $notifiedPersonIds)) {
                    $status = "<span style='color: green;'>Notified (Overdue)</span>";
                } elseif (array_filter($user['loans'], function($l) { return $l['is_overdue']; })) {
                    if (empty($user['person_email'])) {
                        $status = "<span style='color: orange;'>Overdue (No Email)</span>";
                    } else {
                        $status = "<span style='color: red;'>Notification Failed</span>";
                    }
                } else {
                    $status = "On Loan";
                }

                $html .= "<tr>";
                $html .= "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$user['person_name']}</td>";
                $html .= "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$user['person_email']}</td>";
                $html .= "<td style='border: 1px solid #dee2e6; padding: 8px;'>" . implode("<br>", $items) . "</td>";
                $html .= "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$status}</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        } else {
            $html .= "<p>No media is currently on loan.</p>";
        }

        if ($failedCount > 0) {
            $html .= "<p style='color: red;'><strong>Note:</strong> {$failedCount} overdue notification(s) could not be sent (check logs or missing email addresses).</p>";
        }

        $email->setMessage($html);
        $email->send();
    }

    private function buildSimpleReminderEmail($personName, $loans)
    {
        $mediaList = "<ul>";
        foreach ($loans as $loan) {
            $mediaList .= "<li><strong>{$loan['title']}</strong> (Loaned on: {$loan['date']})</li>";
        }
        $mediaList .= "</ul>";

        return "
            <p>Hi {$personName},</p>
            <p>This is a friendly reminder that you have the following media items on loan for over 30 days:</p>
            {$mediaList}
            <p>Please return them at your earliest convenience.</p>
            <p>Thank you!</p>
        ";
    }
}
