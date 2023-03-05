<?php
namespace App\Controllers;

use App\Models\MediaModel;

class Media extends BaseController {
    private string $pageTitle = 'Media';
    private string $browsePage = 'innerpages/media/browse';
    private string $entityPage = 'innerpages/media/media';
    private string $pageActiveNav = 'media';

    private $model;

    public function __construct() {
        $this->model = new MediaModel();
    }
    public function getIndex() {
        //include helper form
        $data = [];
        $data['media'] = $this->model->getAll();
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->browsePage;
        $data['activeNav'] = $this->pageActiveNav;
        echo view('innerpages/template', $data);
    }

    public function getTitle($title) : string {
        $data['media'] = $this->model->getTitle($title);
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->entityPage;
        $data['activeNav'] = $this->pageActiveNav;
        if (empty($data['media'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the movie item: ' . $title);
        }
        return view('innerpages/template', $data);
    }

    public function getSearch() : string {
        $search = $_GET['search'];
        //TODO: $filter = $_GET['filter'];
        $data['media'] = $this->model->getSearch($search);
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->browsePage;
        $data['activeNav'] = $this->pageActiveNav;
        if (empty($data['media'])) {
            $data['not_found'] = 'Cannot find the news item: ' . $search;
            // Probably better to just throw an error and show all media instead of just returning a blank page with an error of not found.
            //throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        return view('innerpages/template', $data);
    }

    public function getId($id) {
        $data['media'] = $this->model->getById($id);
        $data['title'] = $this->pageTitle;
        $data['main_content'] = $this->entityPage;
        $data['activeNav'] = $this->pageActiveNav;
        if (empty($data['media'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the movie by id: ' . $id);
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
            $data['title'] = $this->pageTitle;
            $data['main_content'] = $this->entityPage;
            $data['activeNav'] = $this->pageActiveNav;
        } else {
            $data['validation'] = $this->validator;
            $data['main_content'] = $this->browsePage;
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
