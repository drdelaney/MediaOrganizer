<?php
namespace App\Models;

use CodeIgniter\Model;

class ACodecModel extends Model
{
    protected $table = 'acodecs';
    protected $primaryKey = 'acodec_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[acodecs.name,acodec_id,{acodec_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Audio codec name is required',
            'is_unique' => 'This audio codec name already exists'
        ]
    ];
}
