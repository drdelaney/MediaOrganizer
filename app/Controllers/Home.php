<?php

/**
 * Media Organizer
 *
 * @package     MediaOrganizer
 * @author      drdelaney
 * @copyright   2024-2026 drdelaney
 * @license     GNU General Public License v3.0
 * @link        https://github.com/drdelaney/MediaOrganizer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
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

    public function about()
    {
        $data = [
            'title' => app_name() . ' - About'
        ];

        return view('home/about', $data);
    }
}
