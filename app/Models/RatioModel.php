<?php
namespace App\Models;

use CodeIgniter\Model;

class RatioModel extends Model
{
    protected $table = 'ratios';
    protected $primaryKey = 'ratio_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[5]|is_unique[ratios.name,ratio_id,{ratio_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Ratio name is required',
            'max_length' => 'Ratio name cannot exceed 5 characters',
            'is_unique' => 'This ratio name already exists'
        ]
    ];
}
