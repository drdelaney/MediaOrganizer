<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotificationsToPeople extends Migration
{
    public function up()
    {
        $fields = [
            'notifications' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'phone'
            ],
        ];
        $this->forge->addColumn('people', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('people', 'notifications');
    }
}
