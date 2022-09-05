<?php
namespace App\Controllers;

use App\Models\MediaModel;
use CodeIgniter\Controller;

class Media extends Controller {
    public function index() {
        //include helper form
        $model = model(MediaModel::class);
        $data = [];
        $data['media'] = $model->getTitle();
        $data['title'] = 'Media';
        $data['main_content'] = 'media';
        $data['activeNav'] = 'media';
        echo view('innerpages/template', $data);
    }

    public function view($title) {
        $model = model(MediaModel::class);
        $data['media'] = $model->getTitle($title);
        $data['main_content'] = 'media';
        if (empty($data['media'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the news item: ' . $title);
        }

        $data['title'] = $data['media']['title'];

        return view('innerpages/template', $data);
    }
}
