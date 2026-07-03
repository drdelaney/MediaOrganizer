<?php

namespace App\Controllers;

use App\Models\LoanModel;

class Loans extends BaseController
{
    protected $loanModel;

    public function __construct()
    {
        helper(['form', 'url', 'timezone']);
        $this->loanModel = new LoanModel();
    }

    /**
     * Display all currently loaned media
     */
    public function index()
    {
        $loanedMedia = $this->loanModel->getAllLoanedMedia();

        $data = [
            'title' => 'Currently Loaned Media',
            'loanedMedia' => $loanedMedia
        ];

        return view('loans/index', $data);
    }

    /**
     * Return a loaned media
     */
    public function returnLoan($mediaId)
    {
        if ($this->request->isAJAX()) {
            $result = $this->loanModel->returnMedia($mediaId);

            return $this->response->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Send email reminders for selected loans
     */
    public function sendReminder()
    {
        if ($this->request->isAJAX()) {
            $selectedLoans = $this->request->getPost('loan_ids');

            if (empty($selectedLoans) || !is_array($selectedLoans)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Please select at least one loan to send a reminder'
                ]);
            }

            // Get all loan details for selected loans
            $loanedMedia = $this->loanModel->getAllLoanedMedia();
            $selectedLoanData = array_filter($loanedMedia, function($loan) use ($selectedLoans) {
                return in_array($loan['loan_id'], $selectedLoans);
            });

            if (empty($selectedLoanData)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No valid loans found'
                ]);
            }

            // Group loans by person_id to send one email per person
            $loansByPerson = [];
            foreach ($selectedLoanData as $loan) {
                $personId = $loan['person_id'];
                if (!isset($loansByPerson[$personId])) {
                    $loansByPerson[$personId] = [
                        'person_name' => $loan['person_name'],
                        'person_email' => $loan['person_email'],
                        'loans' => []
                    ];
                }
                $loansByPerson[$personId]['loans'][] = $loan;
            }

            // Send emails one at a time per person
            $email = \Config\Services::email();
            $emailConfig = config('Email');
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($loansByPerson as $personId => $personData) {
                if (empty($personData['person_email'])) {
                    $failedCount++;
                    $errors[] = "No email address for {$personData['person_name']}";
                    continue;
                }

                $email->clear();
                $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName ?: 'Media Organizer');
                $email->setReplyTo($emailConfig->fromEmail, $emailConfig->fromName ?: 'Media Organizer');
                $email->setTo($personData['person_email']);
                $email->setSubject('Friendly Reminder: Media Return Request');
                
                // Build email message
                $message = $this->buildReminderEmail($personData['person_name'], $personData['loans']);
                $email->setMessage($message);

                if ($email->send()) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = "Failed to send email to {$personData['person_name']}: " . $email->printDebugger(['headers']);
                    log_message('error', 'Email failed for ' . $personData['person_email'] . ': ' . $email->printDebugger(['headers']));
                }
            }

            // Prepare response
            $responseMessage = '';
            if ($successCount > 0) {
                $responseMessage .= "Successfully sent {$successCount} reminder email(s). ";
            }
            if ($failedCount > 0) {
                $responseMessage .= "{$failedCount} email(s) failed to send.";
            }

            return $this->response->setJSON([
                'status' => $failedCount === 0 ? 'success' : ($successCount > 0 ? 'warning' : 'error'),
                'message' => trim($responseMessage),
                'details' => [
                    'success' => $successCount,
                    'failed' => $failedCount,
                    'errors' => $errors
                ]
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Build HTML email message for loan reminder
     */
    private function buildReminderEmail($personName, $loans)
    {
        $emailConfig = config('Email');
        $mediaCount = count($loans);
        $mediaWord = 'media';
        
        $message = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .media-list { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .media-item { margin: 10px 0; padding: 10px; border-bottom: 1px solid #e0e0e0; }
        .media-item:last-child { border-bottom: none; }
        .media-title { font-weight: bold; color: #007bff; }
        .media-details { font-size: 0.9em; color: #666; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 2px solid #007bff; font-size: 0.9em; color: #666; }
        .days-out { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 0.85em; font-weight: bold; }
        .days-recent { background-color: #d4edda; color: #155724; }
        .days-medium { background-color: #fff3cd; color: #856404; }
        .days-overdue { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🎬 Media Return Reminder</h2>
        </div>
        <div class='content'>
            <p>Dear " . htmlspecialchars($personName) . ",</p>
            
            <p>I hope this message finds you well! This is a friendly reminder that you currently have <strong>{$mediaCount} {$mediaWord}</strong> on loan from my collection.</p>
            
            <div class='media-list'>
                <h3>Loaned Media:</h3>";

        foreach ($loans as $loan) {
            $title = htmlspecialchars($loan['title'] ?: $loan['o_title'] ?: 'Untitled');
            $year = $loan['year'] ? " (" . htmlspecialchars($loan['year']) . ")" : '';
            
            // Only include medium line if medium_name is not empty
            $mediumLine = '';
            if (!empty($loan['medium_name']) && trim($loan['medium_name']) !== '') {
                $mediumLine = "<span class=\"media-details\">Format: " . htmlspecialchars($loan['medium_name']) . "</span><br>";
            }
            
            // Calculate days out
            $loanDate = new \DateTime($loan['date']);
            $now = new \DateTime();
            $daysOut = $now->diff($loanDate)->days;
            $daysClass = 'days-recent';
            if ($daysOut > 30) {
                $daysClass = 'days-overdue';
            } elseif ($daysOut > 14) {
                $daysClass = 'days-medium';
            }
            $daysText = "<span class='days-out {$daysClass}'>{$daysOut} " . ($daysOut === 1 ? 'day' : 'days') . " out</span>";
            
            $message .= ">
                    <div class=\"media-title\">{$title}{$year}</div>
                    {$mediumLine}{$daysText}<br>
                </div>
                </p>";
        }

        $message .= "
            </div>
            
            <p>When you have a moment, I would greatly appreciate it if you could return " . ($mediaCount === 1 ? 'this' : 'these') . " at your earliest convenience.</p>
            
            <p>If you've already returned " . ($mediaCount === 1 ? 'it' : 'them') . " or if there are any issues, please let me know and I'll update my records accordingly.</p>
            
            <p>Thank you so much for your understanding!</p>
            
            <div class='footer'>
                <p><strong>Best regards,</strong><br>" . htmlspecialchars($emailConfig->fromName) . "</p>
                <p style='font-size: 0.85em; color: #999;'>This is an automated reminder from " . htmlspecialchars(app_name()) . ". Please do not reply to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>";

        return $message;
    }
}
