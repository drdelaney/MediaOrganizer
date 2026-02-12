<?php
namespace App\Models;

use CodeIgniter\Model;

class AChannelModel extends Model
{
    protected $table = 'achannels';
    protected $primaryKey = 'achannel_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'achannel_id' => 'permit_empty',
        'name' => 'required|max_length[64]|is_unique[achannels.name,achannel_id,{achannel_id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Audio channel name is required',
            'is_unique' => 'This audio channel name already exists'
        ]
    ];
}
