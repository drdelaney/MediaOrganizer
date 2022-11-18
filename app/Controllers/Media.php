<?php
namespace App\Controllers;

use App\Models\MediaModel;

class Media extends BaseController {
    private string $pageTitle = 'Media';
    private string $pageMainContent = 'media';
    private string $pageActiveNav = 'media';

    public function getIndex() {
        //include helper form
        $model = model(MediaModel::class);
        $data = [];
        $data['media'] = $model->getAll();
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->pageMainContent;
        $data['activeNav'] = $this->pageActiveNav;
        echo view('innerpages/template', $data);
    }

    public function getTitle($title) : string {
        $model = model(MediaModel::class);
        $data['media'] = $model->getTitle($title);
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->pageMainContent;
        $data['activeNav'] = $this->pageActiveNav;
        if (empty($data['media'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the news item: ' . $title);
        }
        $data['title'] = $data['media']->title;
        return view('innerpages/template', $data);
    }

    public function getSearch() : string {
        $search = $_GET['search'];
        //TODO: $filter = $_GET['filter'];
        $model = model(MediaModel::class);
        $data['media'] = $model->getSearch($search);
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->pageMainContent;
        $data['activeNav'] = $this->pageActiveNav;
        if (empty($data['media'])) {
            $data['not_found'] = 'Cannot find the news item: ' . $search;
            // Probably better to just throw an error and show all media instead of just returning a blank page with an error of not found.
            //throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        return view('innerpages/template', $data);
    }

    public function postCreate() {
        //include helper form.
        helper(['form']);
        //set rules validation form.
        $rules = [
            'title' => 'required|min_length[3]|max_length[20]',
        ];
        // if valid, save.
        if ($this->validate($rules)) {
            $model = new MediaModel();
            $data = [
                'title' => $this->request->getVar('title'),
            ];
            $model->save($data);
        } else {
            $data['validation'] = $this->validator;
            $data['title'] = $this->pageTitle;
            $data['main_content'] = $this->pageMainContent;
            $data['activeNav'] = $this->pageActiveNav;
            $data['title'] = $this->pageTitle;
        }
        return view('innerpages/template', $data);
    }

    public function postUpdate(int $id) {
        if (!$this->validate(['title'     => 'required|min_length[3]|max_length[20]',])) {
            return view('innerpages/template', [
                'errors' => $this->validator->getErrors(),
            ]);
        }
    }
}
