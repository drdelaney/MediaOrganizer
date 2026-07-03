<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CronJobModel;
use App\Models\PosterModel;

class CronPurgePosters extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'cron:purge-posters';
    protected $description = 'Purges unused posters from the database.';
    protected $usage       = 'cron:purge-posters';

    public function run(array $params)
    {
        $cronModel = new CronJobModel();
        $jobKey = 'purge_posters';
        
        $cronModel->markStarted($jobKey);

        try {
            $posterModel = new PosterModel();
            $affectedRows = $posterModel->purgeUnused();
            
            $message = "Purged {$affectedRows} unused poster(s).";
            CLI::write($message, 'green');

            $cronModel->markFinished($jobKey, 'success', $message);
        } catch (\Exception $e) {
            $message = "Error: " . $e->getMessage();
            CLI::error($message);
            $cronModel->markFinished($jobKey, 'error', $message);
        }
    }
}
