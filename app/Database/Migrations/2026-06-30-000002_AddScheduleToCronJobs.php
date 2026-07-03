<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScheduleToCronJobs extends Migration
{
    public function up()
    {
        $fields = [
            'schedule' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => '0 0 * * *', // Default to daily at midnight
                'after' => 'name'
            ],
        ];
        $this->forge->addColumn('cron_jobs', $fields);

        // Update existing jobs with their current hardcoded schedules
        $db = \Config\Database::connect();
        $db->table('cron_jobs')
            ->where('job_key', 'purge_posters')
            ->update(['schedule' => '0 4 * * 0']); // Sunday at 4am

        $db->table('cron_jobs')
            ->where('job_key', 'loan_reminders')
            ->update(['schedule' => '0 0 * * 0']); // Sunday at midnight (weekly)
    }

    public function down()
    {
        $this->forge->dropColumn('cron_jobs', 'schedule');
    }
}
