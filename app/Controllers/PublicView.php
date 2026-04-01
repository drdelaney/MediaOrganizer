<?php

namespace App\Controllers;

use App\Models\MediaModel;

class PublicView extends BaseController
{
    public function index()
    {
        helper('timezone');
        $model = new MediaModel();
        
        $search = $this->request->getVar('search');
        $mediumId = $this->request->getVar('medium_id');
        $wishlist = $this->request->getVar('wishlist') === '1';
        $alpha = $this->request->getVar('alpha') === '1';
        
        // The user wants a basic list sorted by type then ID by default.
        // If alphabetical sort is checked, sort by title.
        if ($alpha) {
            $sortBy = ['title' => 'ASC'];
        } else {
            $sortBy = ['type' => 'DESC', 'movie_id' => 'DESC'];
        }
        
        $media = $model->getMediaWithDetails($search, 'title', 0, 0, null, !$wishlist, $sortBy, 'DESC', $wishlist, $mediumId);
        
        // If searching and no results found with 'title', try 'all' fields
        if ($search && empty($media)) {
            $media = $model->getMediaWithDetails($search, 'all', 0, 0, null, !$wishlist, $sortBy, 'DESC', $wishlist, $mediumId);
        }

        $data = [
            'title' => 'Public Media List',
            'movies' => $media,
            'mediaTypes' => $model->getMediaTypes(),
            'search' => $search,
            'selectedMedium' => $mediumId,
            'wishlist' => $wishlist,
            'alpha' => $alpha,
            'hide_nav' => true,
        ];

        return view('public_view', $data);
    }
}
