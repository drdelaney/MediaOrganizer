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
        'updated',
        'created',
    ];

    protected $validationRules = [
        'collection_id' => 'permit_empty|integer',
        'volume_id' => 'permit_empty|integer',
        'medium_id' => 'permit_empty|integer',
        'vcodec_id' => 'permit_empty|integer',
        'loaned' => 'required|in_list[0,1]',
        'seen' => 'required|in_list[0,1]',
        'rating' => 'permit_empty|integer',
        'color' => 'permit_empty|integer',
        'cond' => 'permit_empty|integer',
        'layers' => 'permit_empty|integer',
        'region' => 'permit_empty|integer',
        'media_num' => 'permit_empty|integer',
        'runtime' => 'permit_empty|integer',
        'year' => 'permit_empty|integer',
        'o_title' => 'permit_empty|string|max_length[255]',
        'title' => 'required|string|max_length[255]',
        'director' => 'permit_empty|string|max_length[255]',
        'o_site' => 'permit_empty|string|max_length[255]',
        'site' => 'permit_empty|string|max_length[255]',
        'trailer' => 'permit_empty|string|max_length[256]',
        'country' => 'permit_empty|string|max_length[128]',
        'genre' => 'permit_empty|string|max_length[128]',
        'image' => 'permit_empty|string|max_length[128]',
        'studio' => 'permit_empty|string|max_length[128]',
        'classification' => 'permit_empty|string|max_length[128]',
        'cast' => 'permit_empty|string',
        'plot' => 'permit_empty|string',
        'notes' => 'permit_empty|string',
        'poster_md5' => 'permit_empty|string|max_length[32]',
        'screenplay' => 'permit_empty|string|max_length[256]',
        'cameraman' => 'permit_empty|string|max_length[256]',
        'ratio_id' => 'permit_empty|integer',
        'width' => 'permit_empty|integer',
        'barcode' => 'permit_empty|string|max_length[32]',
        'height' => 'permit_empty|integer',
        'updated' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'created' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'alt_medium_id' => 'permit_empty|integer',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;


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

    /**
     * Sets default values for saved media fields
     * @param array $data
     * @return void
     */
    public function setDefaults(array &$data) {
        $data['loaned']     = $data['loaned'] ?? 0;
        $data['seen']       = $data['seen'] ?? 0;
        $data['rating']     = $data['rating'] ?? 0;
        $data['region']     = $data['region'] ?? 0;
        $data['media_num'] = $data['media_num'] ?? 0;
        // Set o_title to title if not explicitly set.
        $data['o_title']     = $data['o_title'] ?? $data['title'];
        $data['updated']     = $data['updated'] ?? date("Y-m-d H:i:s", time());
        $data['created']     = $data['created'] ?? date("Y-m-d H:i:s", time());
    }
}
