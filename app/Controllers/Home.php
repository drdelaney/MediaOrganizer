<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function getIndex() {
        $data = [];
        $data['title'] 		= 'Page Title. Yay';
        $data['heading']	= 'Welcome to ....';
		$data['main_content']	= 'home';	// page name
		echo view('innerpages/template', $data);
    }
}
