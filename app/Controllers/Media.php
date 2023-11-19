<?php
namespace App\Controllers;

use App\Models\MediaModel;

/**
 * Controller for media items
 */
class Media extends BaseController {
    private string $pageTitle = 'Media';
    private string $browsePage = 'innerpages/media/browse';
    private string $entityPage = 'innerpages/media/media';
    private string $pageActiveNav = 'media';
    private MediaModel $model;

    public function __construct() {
        //TODO: Permissions. Can edit, can view. etc.
        $this->model = new MediaModel();
    }
    /**
     * Index page for browsing media
     * @return string
     */
    public function index() : string {
        //include helper form
        $data = [];
        $this->setPageData($data);
        $data['media'] = $this->model->getAll();
        $data['main_content'] = $this->browsePage;
        return view('innerpages/template', $data);
    }
    /**
     * Searches for media title by string given.
     * @return string
     */
    public function search() : string {
        $search = $_GET['search'];
        $data = [];
        $this->setPageData($data);
        //TODO: $filter = $_GET['filter'];
        //TODO: searchByTitle, or use CI4s model where here
        $data['media'] = $this->model->getSearch($search);
        $data['main_content'] = $this->browsePage;
        if (empty($data['media'])) {
            $data['error'] = 'Cannot find the media item: ' . $search;
        }
        return view('innerpages/template', $data);
    }
    /**
     * Gets media to view.
     * @param int $id ID of media to view
     * @return string
     */
    public function view(int $id) {
        $data = [];
        $data['media'] = $this->model->find($id);
        $this->setPageData($data);
        $data['main_content'] = $this->entityPage;
        if (is_null($data['media'])) {
            $data['error'] = 'Cannot find the movie by id: ' . $id;
            $data['main_content'] = $this->browsePage;
        }
        return view('innerpages/template', $data);
    }

    /**
     * Creates media
     * @throws \ReflectionException
     */
    public function create() {
        //set rules validation form.
        $data = $this->request->getPost();
        $this->setPageData($data);
        // if valid, save.
        if ($this->model->validate($data)) {
            $this->model->setDefaults($data);
            $this->model->insert($data);
            $id = $this->model->getInsertID();
            $data['media'] = $this->model->find($id);
            $data['success'] = 'Successfully created "' . $data['media']->title . '"';
            $data['main_content'] = $this->entityPage;
        } else {
            $data['main_content'] = $this->browsePage;
            $data['error'] = $this->model->errors();
        }
        return view('innerpages/template', $data);
    }

    /**
     * Updates media information
     * @throws \ReflectionException
     */
    public function update(int $id) {
        $data = $this->request->getPost();
        if (!$this->model->validate($data)) {
            $data['error'] = $this->model->errors();
        } else {
            $data['updated'] = date("Y-m-d H:i:s", time());
            $this->model->update($id, $data);
            $data['success'] = 'Successfully updated "' . $data['media']->title . '"';
        }
        $data['media'] = $this->model->find($id);
        $this->setPageData($data);
        $data['main_content'] = $this->entityPage;
        return view('innerpages/template', $data);
    }
    /**
     * Deletes media by ID.
     * @param int $id ID of media to delete.
     * @return string
     */
    public function delete(int $id) : string {
        //TODO: Add deleted_at column.
        $media = $this->model->find($id);
        $data = [];
        $this->setPageData($data);
        if (!is_null($media)) {
            if ($this->model->delete($id)) {
                $data['success'] = 'Successfully deleted "' . $media->title . '"';
                $data['main_content'] = $this->browsePage;
            } else {
                $data['media'] = $media;
                $data['main_content'] = $this->entityPage;
                $data['error'] = "Failed to delete \"" . $media->title . '"';
            }
            return view('innerpages/template', $data);
        }
        $data['error'] = "Unable to find record to delete with ID  \"" . $id . '"';
        $data['main_content'] = $this->browsePage;
        return view('innerpages/template', $data);
    }
    /**
     * Sets basic page data that is the same on each page for this controller. Required for header.php.
     * @param array $data
     * @return void
     */
    public function setPageData(array &$data) : void {
        $data['pageTitle'] = $this->pageTitle;
        $data['activeNav'] = $this->pageActiveNav;
    }
}
