<?php
namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model
{
    protected $table = 'media';
    protected $primaryKey = 'medium_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[media.name,medium_id,{medium_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Medium name is required',
            'is_unique' => 'This medium name already exists'
        ]
    ];
}