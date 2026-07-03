<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        // 1. achannels
        $this->forge->addField([
            'achannel_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('achannel_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('achannels');

        // 2. acodecs
        $this->forge->addField([
            'acodec_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('acodec_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('acodecs');

        // 3. collections
        $this->forge->addField([
            'collection_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 64],
            'loaned'        => ['type' => 'SMALLINT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('collection_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('collections');

        // 4. configuration
        $this->forge->addField([
            'param' => ['type' => 'VARCHAR', 'constraint' => 64],
            'value' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addPrimaryKey('param');
        $this->forge->createTable('configuration');

        // 5. filters
        $this->forge->addField([
            'name' => ['type' => 'VARCHAR', 'constraint' => 64],
            'data' => [
                'type' => match ($this->db->DBDriver) {
                    'SQLite3' => 'BLOB',
                    'Postgre' => 'BYTEA',
                    'SQLSRV'  => 'VARBINARY(MAX)',
                    default   => 'BLOB',
                },
            ],
        ]);
        $this->forge->addPrimaryKey('name');
        $this->forge->createTable('filters');

        // 6. languages
        $this->forge->addField([
            'lang_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'    => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('lang_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('languages');

        // 7. media
        $this->forge->addField([
            'medium_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('medium_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('media');

        // 8. people
        $this->forge->addField([
            'person_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'     => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'phone'     => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('person_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('people');

        // 9. posters
        $posterDataField = [
            'type' => match ($this->db->DBDriver) {
                'SQLite3' => 'BLOB',
                'Postgre' => 'BYTEA',
                'SQLSRV'  => 'VARBINARY(MAX)',
                default   => 'MEDIUMBLOB',
            },
        ];

        $this->forge->addField([
            'md5sum' => ['type' => 'VARCHAR', 'constraint' => 32],
            'data'   => $posterDataField,
        ]);
        $this->forge->addPrimaryKey('md5sum');
        $this->forge->createTable('posters');

        // 10. ratios
        $this->forge->addField([
            'ratio_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'     => ['type' => 'VARCHAR', 'constraint' => 5],
        ]);
        $this->forge->addPrimaryKey('ratio_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('ratios');

        // 11. subformats
        $this->forge->addField([
            'subformat_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('subformat_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('subformats');

        // 12. tags
        $this->forge->addField([
            'tag_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'   => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('tag_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('tags');

        // 13. vcodecs
        $this->forge->addField([
            'vcodec_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 64],
        ]);
        $this->forge->addPrimaryKey('vcodec_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('vcodecs');

        // 14. volumes
        $this->forge->addField([
            'volume_id' => ['type' => 'INT', 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 64],
            'loaned'    => ['type' => 'SMALLINT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('volume_id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('volumes');

        // 15. movies
        $this->forge->addField([
            'movie_id'       => ['type' => 'INT', 'auto_increment' => true],
            'number'         => ['type' => 'INT'],
            'collection_id'  => ['type' => 'INT', 'null' => true],
            'volume_id'      => ['type' => 'INT', 'null' => true],
            'medium_id'      => ['type' => 'INT', 'null' => true],
            'ratio_id'       => ['type' => 'INT', 'null' => true],
            'vcodec_id'      => ['type' => 'INT', 'null' => true],
            'poster_md5'     => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'loaned'         => ['type' => 'SMALLINT', 'default' => 0],
            'seen'           => ['type' => 'SMALLINT', 'default' => 0],
            'rating'         => ['type' => 'SMALLINT', 'null' => true],
            'color'          => ['type' => 'SMALLINT', 'null' => true],
            'cond'           => ['type' => 'SMALLINT', 'null' => true],
            'layers'         => ['type' => 'SMALLINT', 'null' => true],
            'region'         => ['type' => 'SMALLINT', 'null' => true],
            'media_num'      => ['type' => 'SMALLINT', 'null' => true],
            'runtime'        => ['type' => 'SMALLINT', 'null' => true],
            'year'           => ['type' => 'SMALLINT', 'null' => true],
            'width'          => ['type' => 'SMALLINT', 'null' => true],
            'height'         => ['type' => 'SMALLINT', 'null' => true],
            'barcode'        => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'o_title'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'director'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'screenplay'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'cameraman'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'o_site'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'site'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'trailer'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'country'        => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'genre'          => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'studio'         => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'classification' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'cast'           => ['type' => 'TEXT', 'null' => true],
            'plot'           => ['type' => 'TEXT', 'null' => true],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'image'          => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'created'        => ['type' => 'DATETIME', 'null' => true],
            'updated'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('movie_id');
        $this->forge->addUniqueKey('number');
        $this->forge->addKey('collection_id');
        $this->forge->addKey('volume_id');
        $this->forge->addKey('medium_id');
        $this->forge->addKey('ratio_id');
        $this->forge->addKey('vcodec_id');
        $this->forge->addKey('poster_md5');
        $this->forge->addKey('o_title');
        $this->forge->addKey('title');
        // Foreign keys
        $this->forge->addForeignKey('collection_id', 'collections', 'collection_id');
        $this->forge->addForeignKey('volume_id', 'volumes', 'volume_id');
        $this->forge->addForeignKey('medium_id', 'media', 'medium_id');
        $this->forge->addForeignKey('ratio_id', 'ratios', 'ratio_id');
        $this->forge->addForeignKey('vcodec_id', 'vcodecs', 'vcodec_id');
        $this->forge->addForeignKey('poster_md5', 'posters', 'md5sum');
        $this->forge->createTable('movies');

        // 16. loans
        $this->forge->addField([
            'loan_id'       => ['type' => 'INT', 'auto_increment' => true],
            'person_id'     => ['type' => 'INT'],
            'movie_id'      => ['type' => 'INT'],
            'volume_id'     => ['type' => 'INT', 'null' => true],
            'collection_id' => ['type' => 'INT', 'null' => true],
            'date'          => ['type' => 'DATE'],
            'return_date'   => ['type' => 'DATE', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('loan_id');
        $this->forge->addKey('person_id');
        $this->forge->addKey('movie_id');
        $this->forge->addKey('volume_id');
        $this->forge->addKey('collection_id');
        $this->forge->addForeignKey('person_id', 'people', 'person_id');
        $this->forge->addForeignKey('movie_id', 'movies', 'movie_id');
        $this->forge->addForeignKey('volume_id', 'volumes', 'volume_id');
        $this->forge->addForeignKey('collection_id', 'collections', 'collection_id');
        $this->forge->createTable('loans');

        // 17. movie_lang
        $this->forge->addField([
            'ml_id'        => ['type' => 'INT', 'auto_increment' => true],
            'type'         => ['type' => 'SMALLINT', 'null' => true],
            'movie_id'     => ['type' => 'INT'],
            'lang_id'      => ['type' => 'INT'],
            'acodec_id'    => ['type' => 'INT', 'null' => true],
            'achannel_id'  => ['type' => 'INT', 'null' => true],
            'subformat_id' => ['type' => 'INT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('ml_id');
        $this->forge->addKey('movie_id');
        $this->forge->addKey('lang_id');
        $this->forge->addKey('acodec_id');
        $this->forge->addKey('achannel_id');
        $this->forge->addKey('subformat_id');
        $this->forge->addForeignKey('movie_id', 'movies', 'movie_id');
        $this->forge->addForeignKey('lang_id', 'languages', 'lang_id');
        $this->forge->addForeignKey('acodec_id', 'acodecs', 'acodec_id');
        $this->forge->addForeignKey('achannel_id', 'achannels', 'achannel_id');
        $this->forge->addForeignKey('subformat_id', 'subformats', 'subformat_id');
        $this->forge->createTable('movie_lang');

        // 18. movie_tag
        $this->forge->addField([
            'mt_id'    => ['type' => 'INT', 'auto_increment' => true],
            'movie_id' => ['type' => 'INT', 'null' => true],
            'tag_id'   => ['type' => 'INT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('mt_id');
        $this->forge->addKey('movie_id');
        $this->forge->addKey('tag_id');
        $this->forge->addForeignKey('movie_id', 'movies', 'movie_id');
        $this->forge->addForeignKey('tag_id', 'tags', 'tag_id');
        $this->forge->createTable('movie_tag');
    }

    public function down()
    {
        $this->forge->dropTable('movie_tag', true);
        $this->forge->dropTable('movie_lang', true);
        $this->forge->dropTable('loans', true);
        $this->forge->dropTable('movies', true);
        $this->forge->dropTable('volumes', true);
        $this->forge->dropTable('vcodecs', true);
        $this->forge->dropTable('tags', true);
        $this->forge->dropTable('subformats', true);
        $this->forge->dropTable('ratios', true);
        $this->forge->dropTable('posters', true);
        $this->forge->dropTable('people', true);
        $this->forge->dropTable('media', true);
        $this->forge->dropTable('languages', true);
        $this->forge->dropTable('filters', true);
        $this->forge->dropTable('configuration', true);
        $this->forge->dropTable('collections', true);
        $this->forge->dropTable('acodecs', true);
        $this->forge->dropTable('achannels', true);
    }
}
