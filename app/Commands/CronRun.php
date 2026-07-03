<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CronJobModel;

class CronRun extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'cron:run';
    protected $description = 'Runs all enabled and due cron jobs.';
    protected $usage       = 'cron:run';
    protected $arguments   = [];
    protected $options     = [];

    public function run(array $params)
    {
        $cronModel = new CronJobModel();
        $jobs = $cronModel->where('enabled', 1)->findAll();

        $now = time();
        $runCount = 0;

        foreach ($jobs as $job) {
            $due = false;
            
            if (!$job['next_run']) {
                $due = true;
            } else {
                $nextRunTime = strtotime($job['next_run']);
                if ($now >= $nextRunTime) {
                    $due = true;
                }
            }

            if ($due) {
                CLI::write("Running job: {$job['name']} ({$job['job_key']})...", 'yellow');
                $this->executeJob($job['job_key']);
                $runCount++;
            }
        }

        if ($runCount === 0) {
            CLI::write("No jobs due to run.", 'green');
        } else {
            CLI::write("Executed {$runCount} job(s).", 'green');
        }

        // Update a 'system_cron_last_run' setting to show it is active
        $configModel = new \App\Models\ConfigurationModel();
        $configModel->setParam('system_cron_last_run', date('Y-m-d H:i:s'));
    }

    private function executeJob(string $key)
    {
        switch ($key) {
            case 'purge_posters':
                $this->call('cron:purge-posters');
                break;
            case 'loan_reminders':
                $this->call('cron:loan-reminders');
                break;
            default:
                CLI::error("Unknown job key: {$key}");
        }
    }
}
