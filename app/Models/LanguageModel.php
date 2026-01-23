<?php
namespace App\Models;

use CodeIgniter\Model;

class LanguageModel extends Model
{
    protected $table = 'languages';
    protected $primaryKey = 'lang_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[languages.name,lang_id,{lang_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Language name is required',
            'is_unique' => 'This language name already exists'
        ]
    ];
}
