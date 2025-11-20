<?php
namespace App\Models;

use CodeIgniter\Model;

class VolumeModel extends Model
{
    protected $table = 'volumes';
    protected $primaryKey = 'volume_id';
    protected $allowedFields = ['name', 'loaned'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[volumes.name,volume_id,{volume_id}]',
        'loaned' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Volume name is required',
            'is_unique' => 'This volume name already exists'
        ]
    ];
}