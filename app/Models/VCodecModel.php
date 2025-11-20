<?php
namespace App\Models;

use CodeIgniter\Model;

class VCodecModel extends Model
{
    protected $table = 'vcodecs';
    protected $primaryKey = 'vcodec_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[vcodecs.name,vcodec_id,{vcodec_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Codec name is required',
            'is_unique' => 'This codec name already exists'
        ]
    ];
}