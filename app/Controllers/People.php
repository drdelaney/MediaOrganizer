<?php

namespace App\Controllers;

use App\Models\PeopleModel;

class People extends BaseController
{
    protected $peopleModel;
    protected $db;

    public function __construct()
    {
        $this->peopleModel = new PeopleModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Display people management page
     */
    public function index()
    {
        $data = [
            'title' => 'Manage Loaned Users',
            'people' => $this->peopleModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('people/index', $data);
    }

    /**
     * Add a new person
     */
    public function add()
    {
        if ($this->request->isAJAX()) {
            $data = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email') ?: null,
                'phone' => $this->request->getPost('phone') ?: null
            ];

            if ($this->peopleModel->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Person added successfully',
                    'id' => $this->peopleModel->getInsertID()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->peopleModel->errors())
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Update an existing person
     */
    public function update($id)
    {
        if ($this->request->isAJAX()) {
            $data = [
                'person_id' => $id,
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email') ?: null,
                'phone' => $this->request->getPost('phone') ?: null
            ];

            if ($this->peopleModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Person updated successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->peopleModel->errors())
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Delete a person
     */
    public function delete($id)
    {
        if ($this->request->isAJAX()) {
            // Check if person has any active loans
            $loansInUse = $this->db->table('loans')
                ->where('person_id', $id)
                ->where('return_date IS NULL')
                ->countAllResults();

            if ($loansInUse > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Cannot delete: Person has {$loansInUse} active loan(s). Please return all items first."
                ]);
            }

            if ($this->peopleModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Person deleted successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete person'
            ]);
        }

        return $this->response->setStatusCode(404);
    }
}
