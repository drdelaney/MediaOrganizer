<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function getIndex()
    {
        $session = session();
        echo "Welcome back, ".$session->get('username');
    }
}
