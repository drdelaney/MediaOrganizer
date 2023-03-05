<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model {
    protected $table = 'media';
    protected $primaryKey = 'id';
    protected $returnType    = \App\Entities\Media::class;
    //protected $useTimestamps = false;
    //protected $createdField  = 'created_at';


    protected $allowedFields = [
        //'id', We cannot set ID, so it's not allowed.
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
            return [];
        }
        return $result;
    }

    public function getSearch(string $search, string $filter = null) {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        // Need true for case-insensitive
        $result = $builder->like('title', $search, 'both', null, true)->get();
        return $result->getResultObject();
    }

    public function getById($id) {
        $result = $this->where(['id' => $id])->first();
        if (empty($result)) {
            return [];
        }
        return $result;
    }
}
