<?php

namespace App\Models;

use CodeIgniter\Model;

class CronJobModel extends Model
{
    protected $table = 'cron_jobs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'job_key', 'name', 'schedule', 'last_run', 'next_run', 'status', 'last_message', 'enabled'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getJob(string $key)
    {
        return $this->where('job_key', $key)->first();
    }

    public function markStarted(string $key)
    {
        return $this->where('job_key', $key)->set([
            'status' => 'running',
            'last_run' => date('Y-m-d H:i:s')
        ])->update();
    }

    public function markFinished(string $key, string $status, string $message, ?string $nextRun = null)
    {
        $data = [
            'status' => $status,
            'last_message' => $message,
        ];
        if ($nextRun) {
            $data['next_run'] = $nextRun;
        } else {
            // Try to calculate next run from schedule if not provided
            $job = $this->getJob($key);
            if ($job && !empty($job['schedule'])) {
                $next = $this->calculateNextRun($job['schedule']);
                if ($next) {
                    $data['next_run'] = $next;
                }
            }
        }
        return $this->where('job_key', $key)->set($data)->update();
    }

    /**
     * Calculate next run time from cron expression
     * Supports basic cron format: min hour day month dow
     */
    public function calculateNextRun(string $schedule): ?string
    {
        // For simplicity, we'll use a basic approach.
        // If it's a standard cron expression, we'll try to handle common ones.
        // A better way would be using a library, but we'll implement a simple one.
        
        $parts = explode(' ', trim($schedule));
        if (count($parts) < 5) return null;

        // Common cases
        // 0 4 * * 0 -> Weekly Sunday 4am
        if ($parts[0] === '0' && $parts[1] !== '*' && $parts[2] === '*' && $parts[3] === '*' && $parts[4] !== '*') {
            $dow = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$parts[4]] ?? 'Sunday';
            $time = sprintf('%02d:00:00', $parts[1]);
            return date('Y-m-d H:i:s', strtotime("next $dow $time"));
        }

        // 0 0 * * * -> Daily midnight
        if ($parts[0] === '0' && $parts[1] !== '*' && $parts[2] === '*' && $parts[3] === '*' && $parts[4] === '*') {
            $time = sprintf('%02d:00:00', $parts[1]);
            return date('Y-m-d H:i:s', strtotime("tomorrow $time"));
        }

        // Fallback: If we can't parse it accurately with this simple logic, 
        // we'll just return null and let the command handle it or use a default.
        // For the purposes of this task, supporting the user's weekly/daily needs is priority.
        
        // Actually, let's try a bit more flexible approach for the time
        if (is_numeric($parts[0]) && is_numeric($parts[1])) {
            $time = sprintf('%02d:%02d:00', $parts[1], $parts[0]);
            if ($parts[4] !== '*' && is_numeric($parts[4])) {
                $dow = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$parts[4]] ?? 'Sunday';
                return date('Y-m-d H:i:s', strtotime("next $dow $time"));
            }
            if ($parts[2] === '*' && $parts[3] === '*') {
                return date('Y-m-d H:i:s', strtotime("tomorrow $time"));
            }
        }

        return date('Y-m-d H:i:s', strtotime('+1 day')); // Default fallback
    }
}
