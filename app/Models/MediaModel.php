<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model {
    protected $table = 'media';
    protected $allowedFields = [
        'title'
    ];

    public function getTitle($title = false) {
        if ($title === false) {
            return $this->findAll();
        }
        return $this->where(['title' => $title])->first();
    }
}
