<?php
namespace App\Models;

use CodeIgniter\Model;

class PeopleModel extends Model
{
    protected $table = 'people';
    protected $primaryKey = 'person_id';
    protected $allowedFields = ['name', 'email', 'phone', 'notifications'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'person_id' => 'permit_empty|is_natural_no_zero',
        'name' => 'required|max_length[255]|is_unique[people.name,person_id,{person_id}]',
        'email' => 'required|valid_email|max_length[128]',
        'phone' => 'permit_empty|max_length[64]',
        'notifications' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Person name is required',
            'is_unique' => 'This person name already exists'
        ],
        'email' => [
            'required' => 'Email address is required',
            'valid_email' => 'Please provide a valid email address'
        ]
    ];
}
