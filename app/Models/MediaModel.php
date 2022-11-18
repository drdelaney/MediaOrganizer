<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model {
    protected $table = 'media';
    protected $primaryKey = 'id';

    //protected $useTimestamps = false;
    //protected $createdField  = 'created_at';


    protected $allowedFields = [
        //'id', We cannot set ID, so it's not allowed.
        'number',
        'collection_id',
        'volume_id',
        'medium_id',
        'vcodec_id',
        'loaned',
        'seen',
        'rating',
        'color',
        'cond',
        'layers',
        'region',
        'media_num',
        'runtime',
        'year',
        'o_title',
        'title',
        'director',
        'o_site',
        'site',
        'trailer',
        'country',
        'genre',
        'image',
    ];

    protected $validationRules = [

    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;


    public function getAll() {
        return $this->findAll();
    }

    public function getTitle(string $title) {
        $result = $this->where(['title' => $title])->first();
        if (empty($result)) {
            return $this->findAll();
        }
        return $result;
    }

    public function getSearch(string $search, string $filter = null) {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $result = $builder->like('title', $search)->get();
        return $result->getResultObject();
    }
}
