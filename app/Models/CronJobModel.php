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
        $configModel = new \App\Models\ConfigurationModel();
        $timezone = $configModel->getParam('timezone', 'UTC');

        return $this->where('job_key', $key)->set([
            'status' => 'running',
            'last_run' => \CodeIgniter\I18n\Time::now($timezone)->format('Y-m-d H:i:s')
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
     * Determine if a job is due to run based on its schedule and last run time.
     * 
     * @param string $schedule Cron expression
     * @param string|null $lastRun Last run datetime string
     * @return bool
     */
    public function isDue(string $schedule, ?string $lastRun): bool
    {
        $configModel = new \App\Models\ConfigurationModel();
        $timezone = $configModel->getParam('timezone', 'UTC');
        
        $now = \CodeIgniter\I18n\Time::now($timezone);
        
        if (empty($lastRun)) {
            return true;
        }

        $lastRunTime = \CodeIgniter\I18n\Time::parse($lastRun, $timezone);
        
        // We calculate when it SHOULD have run after $lastRun
        // To be due, the current time must be greater than or equal to the scheduled time
        // and $lastRun must be before that scheduled time.
        
        // However, a simpler way given our current calculateNextRun logic:
        // calculateNextRun returns the NEXT occurrence AFTER 'now'.
        // That's not exactly what we need for isDue.
        
        // Let's implement a shouldRun method that checks if 'now' has passed the expected time
        // since the last run.
        
        $parts = explode(' ', trim($schedule));
        if (count($parts) < 5) return false;

        $hour = is_numeric($parts[1]) ? (int)$parts[1] : null;
        $min = is_numeric($parts[0]) ? (int)$parts[0] : null;
        
        if ($hour === null || $min === null) {
            // If we can't parse time precisely, fallback to 24h check if last_run is old
            return $now->getTimestamp() - $lastRunTime->getTimestamp() > 86400;
        }

        // Daily task: * * * * *
        if ($parts[2] === '*' && $parts[3] === '*' && $parts[4] === '*') {
            $todayScheduled = \CodeIgniter\I18n\Time::now($timezone)->setTime($hour, $min, 0);
            
            // If now is past today's scheduled time
            if ($now->getTimestamp() >= $todayScheduled->getTimestamp()) {
                // It is due if last run was before today's scheduled time
                return $lastRunTime->getTimestamp() < $todayScheduled->getTimestamp();
            }
            return false;
        }
        
        // Weekly task: * * * * dow
        if ($parts[2] === '*' && $parts[3] === '*' && $parts[4] !== '*' && is_numeric($parts[4])) {
            $targetDow = (int)$parts[4];
            if ($targetDow > 6) $targetDow = 0;
            
            $todayScheduled = \CodeIgniter\I18n\Time::now($timezone)->setTime($hour, $min, 0);
            
            // Is today the target day?
            if ($todayScheduled->format('w') == $targetDow) {
                if ($now->getTimestamp() >= $todayScheduled->getTimestamp()) {
                    return $lastRunTime->getTimestamp() < $todayScheduled->getTimestamp();
                }
            } else {
                // If not today, we need to check if the MOST RECENT scheduled occurrence has passed
                // and if we ran since then.
                // For simplicity in this env, we can just use calculateNextRun logic in reverse
                // or check if it's been > 7 days.
                if ($now->getTimestamp() - $lastRunTime->getTimestamp() > 7 * 86400) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Calculate next run time from cron expression
     * Supports basic cron format: min hour day month dow
     */
    public function calculateNextRun(string $schedule): ?string
    {
        $configModel = new \App\Models\ConfigurationModel();
        $timezone = $configModel->getParam('timezone', 'UTC');

        $parts = explode(' ', trim($schedule));
        if (count($parts) < 5) return null;

        $now = \CodeIgniter\I18n\Time::now($timezone);
        $nextRun = null;

        // Common cases
        // Check if numeric time (min hour)
        if (is_numeric($parts[0]) && is_numeric($parts[1])) {
            $hour = (int)$parts[1];
            $min = (int)$parts[0];
            
            // 0 4 * * 0 -> Weekly Sunday 4am
            if ($parts[2] === '*' && $parts[3] === '*' && $parts[4] !== '*' && is_numeric($parts[4])) {
                // dow: 0=Sunday, 1=Monday, ..., 6=Saturday (some systems use 7 for Sunday too)
                $targetDow = (int)$parts[4];
                if ($targetDow > 6) $targetDow = 0;
                
                $dowNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $dowName = $dowNames[$targetDow];
                
                // Try today first
                $todayAtTime = \CodeIgniter\I18n\Time::now($timezone)->setTime($hour, $min, 0);
                if ($todayAtTime->format('w') == $targetDow && $todayAtTime->getTimestamp() > $now->getTimestamp()) {
                    $nextRun = $todayAtTime;
                } else {
                    $nextRun = new \CodeIgniter\I18n\Time("next $dowName", $timezone);
                    $nextRun = $nextRun->setTime($hour, $min, 0);
                }
            } 
            // * * * * * -> Daily at specific time
            elseif ($parts[2] === '*' && $parts[3] === '*' && $parts[4] === '*') {
                $todayAtTime = \CodeIgniter\I18n\Time::now($timezone)->setTime($hour, $min, 0);
                
                if ($todayAtTime->getTimestamp() > $now->getTimestamp()) {
                    $nextRun = $todayAtTime;
                } else {
                    $nextRun = $todayAtTime->addDays(1);
                }
            }
        }

        if (!$nextRun) {
            // Very basic fallback if logic above didn't catch it
            $nextRun = $now->addDays(1);
        }

        return $nextRun->format('Y-m-d H:i:s');
    }
}
