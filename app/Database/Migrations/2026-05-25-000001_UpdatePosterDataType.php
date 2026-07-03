<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePosterDataType extends Migration
{
    public function up()
    {
        $fields = [
            'data' => [
                'type' => match ($this->db->DBDriver) {
                    'SQLite3' => 'BLOB',
                    'Postgre' => 'BYTEA',
                    'SQLSRV'  => 'VARBINARY(MAX)',
                    default   => 'LONGBLOB',
                },
            ],
        ];

        // SQLite doesn't support modifying columns directly via Forge easily for all types, 
        // and it doesn't have MEDIUMBLOB vs LONGBLOB distinction (it uses BLOB).
        // For MySQL/MariaDB and others, we want to ensure it's LONGBLOB.
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->modifyColumn('posters', $fields);
        }
    }

    public function down()
    {
        $fields = [
            'data' => [
                'type' => match ($this->db->DBDriver) {
                    'SQLite3' => 'BLOB',
                    'Postgre' => 'BYTEA',
                    'SQLSRV'  => 'VARBINARY(MAX)',
                    default   => 'MEDIUMBLOB',
                },
            ],
        ];

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->modifyColumn('posters', $fields);
        }
    }
}
