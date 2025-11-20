<?php
namespace App\Models;

use CodeIgniter\Model;

class CollectionModel extends Model
{
    protected $table = 'collections';
    protected $primaryKey = 'collection_id';
    protected $allowedFields = ['name', 'loaned'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|max_length[64]|is_unique[collections.name,collection_id,{collection_id}]',
        'loaned' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Collection name is required',
            'is_unique' => 'This collection name already exists'
        ]
    ];
}