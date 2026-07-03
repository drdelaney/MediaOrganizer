<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateConfigurationLimit extends Migration
{
    public function up()
    {
        // Update configuration table schema
        $fields = [
            'param' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'value' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ];
        
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->modifyColumn('configuration', $fields);
        }

        // Update version to 7
        $exists = $this->db->table('configuration')
                           ->where('param', 'version')
                           ->countAllResults();

        if ($exists > 0) {
            $this->db->table('configuration')
                     ->where('param', 'version')
                     ->update(['value' => '7']);
        } else {
            $this->db->table('configuration')->insert([
                'param' => 'version',
                'value' => '7'
            ]);
        }
    }

    public function down()
    {
        // We don't really want to revert schema lengths as it might truncate data,
        // but for completeness of migration:
        // Historically they might have been smaller, but we'll leave them at 64/255.
        
        // Revert version if needed, but version 7 is the new baseline.
    }
}
