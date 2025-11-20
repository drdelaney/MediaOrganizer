<?php

namespace App\Controllers;

use App\Models\MovieModel;

class Home extends BaseController
{
    public function index()
    {
        $movieModel = new MovieModel();
        $totalMovies = $movieModel->countMovies();
        
        $data = [
            'title' => app_name() . ' - Home',
            'totalMovies' => $totalMovies
        ];

        return view('home/index', $data);
    }
}
