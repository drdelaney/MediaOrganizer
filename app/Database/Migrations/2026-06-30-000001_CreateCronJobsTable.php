<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCronJobsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'job_key'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'last_run'    => ['type' => 'DATETIME', 'null' => true],
            'next_run'    => ['type' => 'DATETIME', 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'last_message'=> ['type' => 'TEXT', 'null' => true],
            'enabled'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('job_key');
        $this->forge->createTable('cron_jobs');

        // Insert initial cron jobs
        $db = \Config\Database::connect();
        $db->table('cron_jobs')->insertBatch([
            [
                'job_key'    => 'purge_posters',
                'name'       => 'Weekly Poster Purge',
                'enabled'    => 1,
                'status'     => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'job_key'    => 'loan_reminders',
                'name'       => 'Weekly Loan Reminders',
                'enabled'    => 1,
                'status'     => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('cron_jobs');
    }
}
