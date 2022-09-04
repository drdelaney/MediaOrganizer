<?php

namespace App\Models;

use CodeIgniter\Model;

class Media extends Model
{
    protected $table = 'media';

    public function getTitle($title = false)
    {
        if ($title === false) {
            return $this->findAll();
        }
        return $this->where(['title' => $title])->first();
    }
}
