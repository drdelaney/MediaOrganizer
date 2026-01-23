<?php
namespace App\Models;

use CodeIgniter\Model;

class SubformatModel extends Model
{
    protected $table = 'subformats';
    protected $primaryKey = 'subformat_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[subformats.name,subformat_id,{subformat_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Subtitle format name is required',
            'is_unique' => 'This subtitle format name already exists'
        ]
    ];
}
