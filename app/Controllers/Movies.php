<?php

namespace App\Controllers;

use App\Models\MovieModel;

class Movies extends BaseController
{
    protected $movieModel;

    public function __construct()
    {
        $this->movieModel = new MovieModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $searchField = $this->request->getGet('searchField') ?: 'title';
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $data = [
            'movies' => $this->movieModel->getMoviesWithDetails($search, $searchField, $perPage, $offset),
            'total' => $this->movieModel->countMovies($search, $searchField),
            'currentPage' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'searchField' => $searchField,
            'mediaTypes' => $this->movieModel->getMediaTypes(),
            'title' => 'Media Library'
        ];

        return view('movies/index', $data);
    }

    public function add()
    {
        // Check API availability for informational purposes in the form
        $apiService = new \App\Libraries\MovieApiService();
        $apiAvailable = $apiService->isApiAvailable();

        $data = [
            'movie' => [],
            'mediaTypes' => $this->movieModel->getMediaTypes(),
            'collections' => $this->movieModel->getCollections(),
            'volumes' => $this->movieModel->getVolumes(),
            'videoCodecs' => $this->movieModel->getVideoCodecs(),
            'ratios' => $this->movieModel->getRatios(),
            'apiAvailable' => $apiAvailable,
            'title' => 'Add Movie'
        ];

        return view('movies/add', $data);
    }

    public function store()
    {
        // Validation rules (all fields optional to allow manual minimal creation)
        $validationRules = [
            'title' => 'permit_empty|max_length[255]',
            'o_title' => 'permit_empty|max_length[255]',
            'director' => 'permit_empty|max_length[255]',
            'year' => 'permit_empty|integer|greater_than[1800]|less_than[2100]',
            'runtime' => 'permit_empty|integer|greater_than[0]',
            'genre' => 'permit_empty|max_length[128]',
            'country' => 'permit_empty|max_length[128]',
            'studio' => 'permit_empty|max_length[128]',
            'classification' => 'permit_empty|max_length[128]',
            'rating' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[5]',
            'site' => 'permit_empty|max_length[255]',
            'o_site' => 'permit_empty|max_length[255]',
            'trailer' => 'permit_empty|max_length[256]',
            'screenplay' => 'permit_empty|max_length[256]',
            'cameraman' => 'permit_empty|max_length[256]',
            'barcode' => 'permit_empty|max_length[32]',
            'width' => 'permit_empty|integer|greater_than[0]',
            'height' => 'permit_empty|integer|greater_than[0]',
            'lookup_type' => 'permit_empty|in_list[movie,tv]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $lookupType = strtolower((string)($this->request->getPost('lookup_type') ?: 'movie'));
        if ($lookupType !== 'movie' && $lookupType !== 'tv') { $lookupType = 'movie'; }
        $imdbId = trim((string)$this->request->getPost('imdb_id'));
        $tvdbId = trim((string)$this->request->getPost('tvdb_id'));
        $title = trim((string)$this->request->getPost('title'));
        $year = $this->request->getPost('year') ?: null;
        $lookupBarcode = trim((string)$this->request->getPost('lookup_barcode'));

        $apiData = null;
        try {
            $apiService = new \App\Libraries\MovieApiService();
            if ($apiService->isApiAvailable()) {
                if ($imdbId !== '') {
                    $apiData = $apiService->findByExternalId($imdbId, $lookupType);
                } elseif ($tvdbId !== '') {
                    $apiData = $apiService->findByExternalId($tvdbId, $lookupType);
                } elseif ($lookupBarcode !== '') {
                    $apiData = $apiService->findByBarcode($lookupBarcode, $lookupType);
                } elseif ($title !== '') {
                    if ($lookupType === 'tv') {
                        $apiData = $apiService->searchTv($title, $year ? (int)$year : null);
                    } else {
                        $apiData = $apiService->searchMovie($title, $year ? (int)$year : null);
                    }
                }
            } elseif ($lookupBarcode !== '') {
                // Even if TMDB isn’t available, try to at least resolve a title from the barcode
                $apiData = $apiService->findByBarcode($lookupBarcode, $lookupType);
            }
        } catch (\Throwable $e) {
            log_message('error', 'API fetch during store failed: ' . $e->getMessage());
            // Continue with manual data
        }

        // Generate notes field with TMDB/IMDB IDs if API data contains them
        if ($apiData && (isset($apiData['tmdb_id']) || isset($apiData['imdb_id']))) {
            $apiData['notes'] = add_external_ids_to_notes(null, $apiData['tmdb_id'] ?? null, $apiData['imdb_id'] ?? null);
        }

        // Handle multiple medium_ids
        $mediumIds = $this->request->getPost('medium_ids');
        if (!is_array($mediumIds)) {
            // If single medium_id was posted (backward compatibility)
            $singleMediumId = $this->request->getPost('medium_id');
            $mediumIds = $singleMediumId ? [$singleMediumId] : [];
        }

        // Prepare insert data, preferring API data when available but allowing manual overrides
        $fields = [
            'title','o_title','director','year','runtime','genre','country','studio','classification','rating','site','o_site','trailer','screenplay','cameraman','barcode','width','height','cast','plot','notes','collection_id','volume_id','vcodec_id','ratio_id'
        ];

        $insertData = [];
        foreach ($fields as $field) {
            $valFromApi = $apiData[$field] ?? null;
            $valFromPost = $this->request->getPost($field);
            $insertData[$field] = ($valFromPost !== null && $valFromPost !== '') ? $valFromPost : ($valFromApi ?? null);
        }
        
        // Add multiple medium_ids to notes
        if (!empty($mediumIds)) {
            $insertData['notes'] = add_medium_ids_to_notes($insertData['notes'] ?? null, $mediumIds);
            // Set the highest medium_id as the main medium_id field
            $insertData['medium_id'] = max(array_map('intval', $mediumIds));
        }
        
        // If manual barcode was left empty but a lookup_barcode was provided, use it
        if (empty($insertData['barcode']) && !empty($lookupBarcode)) {
            $insertData['barcode'] = $lookupBarcode;
        }
        // Assign a unique sequential number since movies.number is NOT NULL and UNIQUE
        $insertData['number'] = $this->movieModel->getNextAvailableNumber();
        $insertData['seen'] = $this->request->getPost('seen') ? 1 : 0;
        $insertData['loaned'] = $this->request->getPost('loaned') ? 1 : 0;
        $insertData['created'] = date('Y-m-d H:i:s');
        $insertData['updated'] = $insertData['created'];

        // Insert the movie
        $movieId = $this->movieModel->insert($insertData, true);
        if ($movieId) {
            // If we have a poster URL from API, download and store it
            if ($apiData && !empty($apiData['poster_url'])) {
                try {
                    $apiService = $apiService ?? new \App\Libraries\MovieApiService();
                    $imageData = $apiService->downloadPoster($apiData['poster_url'], $movieId);
                    if ($imageData) {
                        $this->movieModel->storePosterForMovie($movieId, $imageData);
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to download/store poster on create: ' . $e->getMessage());
                }
            }
            session()->setFlashdata('success', 'Movie added successfully!');
            return redirect()->to(base_url('movies/view/' . $movieId));
        } else {
            session()->setFlashdata('error', 'Failed to add movie.');
            return redirect()->back()->withInput();
        }
    }

    public function view($movieId)
    {
        $movie = $this->movieModel->getMovieWithAllDetails($movieId);

        if (!$movie) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Movie not found');
        }

        $data = [
            'movie' => $movie,
            'title' => 'View Movie - ' . ($movie['title'] ?: $movie['o_title'] ?: 'Untitled')
        ];

        return view('movies/view', $data);
    }

    public function edit($movieId)
    {
        $movie = $this->movieModel->getMovieWithAllDetails($movieId);

        if (!$movie) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Movie not found');
        }

        // Check if TMDB API is available
        $apiService = new \App\Libraries\MovieApiService();
        $apiAvailable = $apiService->isApiAvailable();

        $data = [
            'movie' => $movie,
            'mediaTypes' => $this->movieModel->getMediaTypes(),
            'collections' => $this->movieModel->getCollections(),
            'volumes' => $this->movieModel->getVolumes(),
            'videoCodecs' => $this->movieModel->getVideoCodecs(),
            'ratios' => $this->movieModel->getRatios(),
            'apiAvailable' => $apiAvailable,
            'title' => 'Edit Movie - ' . ($movie['title'] ?: $movie['o_title'] ?: 'Untitled')
        ];

        return view('movies/edit', $data);
    }

    public function update($movieId)
    {
        $movie = $this->movieModel->find($movieId);

        if (!$movie) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Movie not found');
        }

        // Validation rules
        $validationRules = [
            'title' => 'permit_empty|max_length[255]',
            'o_title' => 'permit_empty|max_length[255]',
            'director' => 'permit_empty|max_length[255]',
            'year' => 'permit_empty|integer|greater_than[1800]|less_than[2100]',
            'runtime' => 'permit_empty|integer|greater_than[0]',
            'genre' => 'permit_empty|max_length[128]',
            'country' => 'permit_empty|max_length[128]',
            'studio' => 'permit_empty|max_length[128]',
            'classification' => 'permit_empty|max_length[128]',
            'rating' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[5]',
            'site' => 'permit_empty|max_length[255]',
            'o_site' => 'permit_empty|max_length[255]',
            'trailer' => 'permit_empty|max_length[256]',
            'screenplay' => 'permit_empty|max_length[256]',
            'cameraman' => 'permit_empty|max_length[256]',
            'barcode' => 'permit_empty|max_length[32]',
            'width' => 'permit_empty|integer|greater_than[0]',
            'height' => 'permit_empty|integer|greater_than[0]'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // If a poster URL was fetched via the API and the user chose it, download and store it now
        $fetchedPosterUrl = (string)$this->request->getPost('fetched_poster_url');
        $posterChoice = (string)$this->request->getPost('poster_choice'); // 'existing', 'fetched', or 'none'
        $hasExistingPoster = !empty($movie['poster_md5']);

        // If the user explicitly chose no poster, clear any existing poster and skip saving fetched
        if ($posterChoice === 'none') {
            try {
                $this->movieModel->clearPosterForMovie($movieId);
            } catch (\Throwable $e) {
                log_message('error', 'Failed to clear poster for movie on update: ' . $e->getMessage());
            }
            $fetchedPosterUrl = '';
        }

        $shouldSaveFetched = false;
        if ($fetchedPosterUrl) {
            if ($posterChoice === 'fetched') {
                $shouldSaveFetched = true;
            } elseif ($posterChoice === '') {
                // Backward compatibility: if no choice provided and no existing poster, save fetched by default
                if (!$hasExistingPoster) {
                    $shouldSaveFetched = true;
                }
            }
        }

        if ($shouldSaveFetched) {
            try {
                $apiService = new \App\Libraries\MovieApiService();
                // Download poster directly from the provided URL (no API key required)
                $imageData = $apiService->downloadPoster($fetchedPosterUrl, $movieId);
                if ($imageData) {
                    // This updates poster_md5 and updated timestamp internally
                    $this->movieModel->storePosterForMovie($movieId, $imageData);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Failed to download/store poster on update: ' . $e->getMessage());
                // Continue without failing the whole update
            }
        }

        // Handle multiple medium_ids
        $mediumIds = $this->request->getPost('medium_ids');
        if (!is_array($mediumIds)) {
            // If single medium_id was posted (backward compatibility)
            $singleMediumId = $this->request->getPost('medium_id');
            $mediumIds = $singleMediumId ? [$singleMediumId] : [];
        }

        // Prepare data for update
        $updateData = [
            'title' => $this->request->getPost('title') ?: null,
            'o_title' => $this->request->getPost('o_title') ?: null,
            'director' => $this->request->getPost('director') ?: null,
            'year' => $this->request->getPost('year') ?: null,
            'runtime' => $this->request->getPost('runtime') ?: null,
            'genre' => $this->request->getPost('genre') ?: null,
            'country' => $this->request->getPost('country') ?: null,
            'studio' => $this->request->getPost('studio') ?: null,
            'classification' => $this->request->getPost('classification') ?: null,
            'rating' => $this->request->getPost('rating') ?: null,
            'site' => $this->request->getPost('site') ?: null,
            'o_site' => $this->request->getPost('o_site') ?: null,
            'trailer' => $this->request->getPost('trailer') ?: null,
            'screenplay' => $this->request->getPost('screenplay') ?: null,
            'cameraman' => $this->request->getPost('cameraman') ?: null,
            'barcode' => $this->request->getPost('barcode') ?: null,
            'width' => $this->request->getPost('width') ?: null,
            'height' => $this->request->getPost('height') ?: null,
            'cast' => $this->request->getPost('cast') ?: null,
            'plot' => $this->request->getPost('plot') ?: null,
            'notes' => $this->request->getPost('notes') ?: null,
            'collection_id' => $this->request->getPost('collection_id') ?: null,
            'volume_id' => $this->request->getPost('volume_id') ?: null,
            'vcodec_id' => $this->request->getPost('vcodec_id') ?: null,
            'ratio_id' => $this->request->getPost('ratio_id') ?: null,
            'seen' => $this->request->getPost('seen') ? 1 : 0,
            'loaned' => $this->request->getPost('loaned') ? 1 : 0,
            'updated' => date('Y-m-d H:i:s')
        ];
        
        // Add multiple medium_ids to notes
        if (!empty($mediumIds)) {
            $updateData['notes'] = add_medium_ids_to_notes($updateData['notes'] ?? null, $mediumIds);
            // Set the highest medium_id as the main medium_id field
            $updateData['medium_id'] = max(array_map('intval', $mediumIds));
        }

        // Update movie data
        $success = $this->movieModel->update($movieId, $updateData);

        if ($success) {
            session()->setFlashdata('success', 'Movie updated successfully!');
            return redirect()->to(base_url('movies/view/' . $movieId));
        } else {
            session()->setFlashdata('error', 'Failed to update movie.');
            return redirect()->back()->withInput();
        }
    }

    public function delete($movieId)
    {
        // Verify movie exists
        $movie = $this->movieModel->find($movieId);

        if (!$movie) {
            session()->setFlashdata('error', 'Movie not found.');
            return redirect()->to(base_url('movies'));
        }

        // Delete the movie
        $success = $this->movieModel->delete($movieId);

        if ($success) {
            session()->setFlashdata('success', 'Movie "' . esc($movie['title'] ?: $movie['o_title']) . '" has been deleted successfully.');
            return redirect()->to(base_url('movies'));
        } else {
            session()->setFlashdata('error', 'Failed to delete movie.');
            return redirect()->back();
        }
    }

    public function toggleSeen($movieId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $movie = $this->movieModel->find($movieId);

        if (!$movie) {
            return $this->response->setJSON(['success' => false, 'message' => 'Movie not found']);
        }

        $newSeenStatus = !$movie['seen'];
        $success = $this->movieModel->toggleSeenStatus($movieId, $newSeenStatus);

        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'seen' => $newSeenStatus,
                'message' => 'Movie marked as ' . ($newSeenStatus ? 'seen' : 'unseen')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update movie status']);
        }
    }

    public function fetchFromApi($movieId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Add debugging
        error_log('fetchFromApi called for movie ID: ' . $movieId);

        $movie = $this->movieModel->find($movieId);

        if (!$movie) {
            error_log('Movie not found for ID: ' . $movieId);
            return $this->response->setJSON(['success' => false, 'message' => 'Movie not found']);
        }

        try {
            // Load the API service and check availability
            error_log('Creating MovieApiService instance...');
            $apiService = new \App\Libraries\MovieApiService();

            if (!$apiService->isApiAvailable()) {
                error_log('TMDB API not available - key not configured');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'TMDB API key not configured. Please add TMDB_API_KEY to your environment variables.'
                ]);
            }

            // Read optional custom query/year/type from JSON body (AJAX)
            $payload = $this->request->getJSON(true) ?: [];
            $customQuery = isset($payload['query']) ? trim((string)$payload['query']) : '';
            $customYear = isset($payload['year']) ? (int)$payload['year'] : null;
            $type = isset($payload['type']) ? strtolower(trim((string)$payload['type'])) : 'movie';
            if ($type !== 'tv' && $type !== 'movie') { $type = 'movie'; }

            // Use existing title and year for default search
            $defaultTitle = $movie['title'] ?: $movie['o_title'] ?: 'Unknown';
            $defaultYear = $movie['year'];

            $searchTitle = $customQuery !== '' ? $customQuery : $defaultTitle;
            $searchYear = $customYear ?: $defaultYear;

            error_log('Searching TMDB [' . $type . '] with query: ' . $searchTitle . ' (' . ($searchYear ?: 'n/a') . ')');

            // If the user provided a numeric TMDB ID, fetch directly
            if (ctype_digit($searchTitle)) {
                if ($type === 'tv') {
                    $apiData = $apiService->getTvDetails((int)$searchTitle);
                } else {
                    $apiData = $apiService->getMovieDetails((int)$searchTitle);
                }
            } else {
                // Otherwise perform a title search
                if ($type === 'tv') {
                    $apiData = $apiService->searchTv($searchTitle, $searchYear);
                } else {
                    $apiData = $apiService->searchMovie($searchTitle, $searchYear);
                }
            }

            if (!$apiData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No movie data found for "' . $searchTitle . '"'
                ]);
            }

            // Generate notes field with TMDB/IMDB IDs if they exist
            if (isset($apiData['tmdb_id']) || isset($apiData['imdb_id'])) {
                // Preserve existing notes from the movie being edited
                $existingNotes = $movie['notes'] ?? null;
                $apiData['notes'] = add_external_ids_to_notes($existingNotes, $apiData['tmdb_id'] ?? null, $apiData['imdb_id'] ?? null);
            }

            // IMPORTANT: Do not persist here. Only return data for client-side preview and form fill.
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Movie data fetched successfully from TMDB (not saved yet)'.($apiData['poster_url'] ? '' : ' (no poster available)'),
                'data' => $apiData,
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lookup movie/TV data for Add flow (AJAX preview, no persistence)
     * Accepts POST JSON or form data: lookup_type ('movie'|'tv'), title, year, imdb_id, tvdb_id
     */
    public function lookup()
    {
        if (!$this->request->isAJAX()) {
            // For non-AJAX, still return JSON to keep it simple
            $this->response->setHeader('Content-Type', 'application/json');
        }
        try {
            $apiService = new \App\Libraries\MovieApiService();
            $apiAvailable = $apiService->isApiAvailable();

            // Gather inputs (accept JSON or form-encoded)
            $payload = $this->request->getJSON(true) ?: $this->request->getPost();
            $lookupType = strtolower((string)($payload['lookup_type'] ?? 'movie'));
            if ($lookupType !== 'tv' && $lookupType !== 'movie') { $lookupType = 'movie'; }
            $imdbId = trim((string)($payload['imdb_id'] ?? ''));
            $tvdbId = trim((string)($payload['tvdb_id'] ?? ''));
            $barcode = trim((string)($payload['barcode'] ?? ''));
            $title = trim((string)($payload['title'] ?? ''));
            $year = isset($payload['year']) && $payload['year'] !== '' ? (int)$payload['year'] : null;

            // 1) External IDs via TMDB if API is available
            $apiData = null;
            if ($imdbId !== '' && $apiAvailable) {
                $apiData = $apiService->findByExternalId($imdbId, $lookupType);
            } elseif ($tvdbId !== '' && $apiAvailable) {
                $apiData = $apiService->findByExternalId($tvdbId, $lookupType);
            } elseif ($barcode !== '') {
                // 2) Local library lookup by barcode
                $existing = $this->movieModel->findByBarcode($barcode);
                if ($existing) {
                    $normalized = [
                        'title' => $existing['title'] ?? null,
                        'o_title' => $existing['o_title'] ?? null,
                        'year' => $existing['year'] ?? null,
                        'runtime' => $existing['runtime'] ?? null,
                        'genre' => $existing['genre'] ?? null,
                        'country' => $existing['country'] ?? null,
                        'studio' => $existing['studio'] ?? null,
                        'plot' => $existing['plot'] ?? null,
                        'rating' => $existing['rating'] ?? null,
                        'site' => $existing['site'] ?? null,
                        'o_site' => $existing['o_site'] ?? null,
                        'poster_url' => $existing['poster_md5'] ? base_url('movies/poster/' . $existing['movie_id']) . '?v=' . urlencode($existing['poster_md5']) : null,
                        'director' => $existing['director'] ?? null,
                        'classification' => $existing['classification'] ?? null,
                    ];
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Found an existing item in your library with this barcode.',
                        'data' => $normalized,
                        'existing' => [
                            'movie_id' => $existing['movie_id'],
                            'url' => base_url('movies/view/' . $existing['movie_id'])
                        ]
                    ]);
                }
                // Not found locally — attempt online resolution and TMDB mapping if possible
                $apiData = $apiService->findByBarcode($barcode, $lookupType);
                if (!$apiData && ($title === '' && !$apiAvailable)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No item with this barcode found locally or online.'
                    ]);
                }
            }

            // 3) Title search via TMDB (if API available)
            if (!$apiData && $title !== '' && $apiAvailable) {
                if ($lookupType === 'tv') {
                    $apiData = $apiService->searchTv($title, $year);
                } else {
                    $apiData = $apiService->searchMovie($title, $year);
                }
            }

            if (!$apiData) {
                // If API not available and no local match, inform accordingly
                if (!$apiAvailable && $imdbId === '' && $tvdbId === '' && $title === '') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Please provide a Title, IMDb ID, TVDB ID, or Barcode to search.'
                    ]);
                }
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No results found. Try refining your search.'
                ]);
            }

            // Generate notes field with TMDB/IMDB IDs if they exist
            if (isset($apiData['tmdb_id']) || isset($apiData['imdb_id'])) {
                $apiData['notes'] = add_external_ids_to_notes(null, $apiData['tmdb_id'] ?? null, $apiData['imdb_id'] ?? null);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Fetched details from TMDB. Review and apply to the form before saving.' . ($apiData['poster_url'] ? '' : ' (No poster available)'),
                'data' => $apiData
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Serve poster image from database
     */
    public function poster($movieId)
    {
        $posterData = $this->movieModel->getPosterData($movieId);
        
        if (!$posterData || !$posterData['data']) {
            // Return 404 or default image
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Poster not found');
        }
        
        // Verify MD5 hash
        if ($posterData['md5sum'] !== md5($posterData['data'])) {
            log_message('error', 'Poster data integrity check failed for movie ID: ' . $movieId);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Poster data corrupted');
        }
        
        // Set appropriate headers
        $this->response->setHeader('Content-Type', 'image/jpeg')
                      ->setHeader('Content-Length', strlen($posterData['data']))
                      ->setHeader('Cache-Control', 'public, max-age=31536000') // Cache for 1 year
                      ->setHeader('ETag', '"' . $posterData['md5sum'] . '"');
        
        return $this->response->setBody($posterData['data']);
    }

    /**
     * Loan a movie to a person
     */
    public function loan($movieId)
    {
        if ($this->request->isAJAX()) {
            $personId = $this->request->getPost('person_id');

            if (!$personId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Please select a person to loan the movie to'
                ]);
            }

            $loanModel = new \App\Models\LoanModel();
            $result = $loanModel->loanMovie($movieId, $personId);

            return $this->response->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Return a loaned movie
     */
    public function returnLoan($movieId)
    {
        if ($this->request->isAJAX()) {
            $loanModel = new \App\Models\LoanModel();
            $result = $loanModel->returnMovie($movieId);

            return $this->response->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Get list of people for loan dropdown
     */
    public function getPeople()
    {
        if ($this->request->isAJAX()) {
            $peopleModel = new \App\Models\PeopleModel();
            $people = $peopleModel->orderBy('name', 'ASC')->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'people' => $people
            ]);
        }

        return $this->response->setStatusCode(404);
    }
}