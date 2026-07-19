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

        $configModel = new \App\Models\ConfigurationModel();
        $timezone = $configModel->getParam('timezone', 'UTC');
        
        $now = \CodeIgniter\I18n\Time::now($timezone);
        $runCount = 0;

        foreach ($jobs as $job) {
            if ($cronModel->isDue($job['schedule'] ?? '0 0 * * *', $job['last_run'])) {
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
        $configModel->setParam('system_cron_last_run', \CodeIgniter\I18n\Time::now($timezone)->format('Y-m-d H:i:s'));
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
