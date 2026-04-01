<?php

namespace App\Controllers;

use App\Models\MediaModel;

class Home extends BaseController
{
    public function index()
    {
        $mediaModel = new MediaModel();
        $totalMedia = $mediaModel->countMedia(null, 'title', null, true);
        
        $data = [
            'title' => app_name() . ' - Home',
            'totalMedia' => $totalMedia
        ];

        return view('home/index', $data);
    }
}
