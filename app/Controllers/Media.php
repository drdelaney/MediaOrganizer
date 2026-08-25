<?php

namespace App\Controllers;

use App\Models\MediaModel;

class Media extends BaseController
{
    protected $mediaModel;

    public function __construct()
    {
        helper(['form', 'url', 'timezone']);
        $this->mediaModel = new MediaModel();
    }

    public function index()
    {
        $search = $this->request->getVar('search');
        $searchField = $this->request->getVar('searchField') ?: 'title';
        
        // Validate searchField
        $allowedSearchFields = ['title', 'o_title', 'director', 'genre', 'country', 'studio', 'barcode', 'notes', 'movie_id', 'all', 'tmdb_id', 'imdb_id', 'tvdb_id', 'igdb_id', 'mbid'];
        if (!in_array($searchField, $allowedSearchFields)) {
            $searchField = 'title';
        }

        $tagId = $this->request->getVar('tag');
        $mediumId = $this->request->getVar('medium');
        $sortByModified = $this->request->getVar('sort_modified') === '1';
        $excludeSeen = $this->request->getVar('exclude_seen') === '1';
        $excludeWishlist = true;
        
        if ($tagId === 'all_with_wishlist') {
            $excludeWishlist = false;
            $tagId = null; // Clear tagId so model doesn't filter by a specific tag
        }
        
        $page = (int) ($this->request->getVar('page') ?? 1);

        if ($this->request->getVar('serendipitous')) {
            $randomMedia = $this->mediaModel->getRandomUnseenMedia();
            if ($randomMedia) {
                return redirect()->to(base_url('media?search=' . $randomMedia['movie_id'] . '&searchField=movie_id&serendipitous_found=1'));
            }
            return redirect()->to(base_url('media'))->with('error', 'No unseen media found!');
        }
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $sortColumn = $sortByModified ? 'updated' : 'created';

        $data = [
            'media' => $this->mediaModel->getMediaWithDetails($search, $searchField, $perPage, $offset, $tagId, $excludeWishlist, $sortColumn, 'DESC', false, $mediumId, $excludeSeen),
            'total' => $this->mediaModel->countMedia($search, $searchField, $tagId, $excludeWishlist, $mediumId, $excludeSeen),
            'currentPage' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'searchField' => $searchField,
            'sortByModified' => $sortByModified,
            'excludeSeen' => $excludeSeen,
            'tagId' => $this->request->getVar('tag'), // Use original tag param for view
            'mediumId' => $mediumId,
            'mediaTypes' => $this->mediaModel->getMediaTypes(),
            'tags' => $this->mediaModel->getTags(),
            'wishlistTag' => get_wishlist_tag(),
            'title' => 'Media Library'
        ];

        return view('media/index', $data);
    }

    public function add()
    {
        $lookupRegistry = new \App\Libraries\LookupRegistry();
        $lookupOptions = $lookupRegistry->getLookupTypeOptions();
        
        // Get enabled lookups
        $enabledLookups = \App\Libraries\LookupRegistry::getEnabledLookups();

        $data = [
            'media' => [],
            'mediaTypes' => $this->mediaModel->getMediaTypes(),
            'collections' => $this->mediaModel->getCollections(),
            'volumes' => $this->mediaModel->getVolumes(),
            'videoCodecs' => $this->mediaModel->getVideoCodecs(),
            'ratios' => $this->mediaModel->getRatios(),
            'allTags' => $this->mediaModel->getTags(),
            'movieTags' => [],
            'lookupOptions' => $lookupOptions,
            'enabledLookups' => $enabledLookups,
            'title' => 'Add Media'
        ];

        return view('media/add', $data);
    }

    public function store()
    {
        // Validation rules - original title is required, at least one medium must be selected
        $validationRules = [
            'title' => 'permit_empty|max_length[255]',
            'o_title' => 'required|max_length[255]',
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
            'lookup_type' => 'permit_empty',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Additional validation: Check if at least one media format is selected
        $mediumIds = $this->request->getPost('medium_ids');
        if (!is_array($mediumIds)) {
            $singleMediumId = $this->request->getPost('medium_id');
            $mediumIds = $singleMediumId ? [$singleMediumId] : [];
        }

        if (empty($mediumIds)) {
            return redirect()->back()->withInput()->with('errors', ['medium' => 'At least one media format must be selected.']);
        }

        $enabled = \App\Libraries\LookupRegistry::getEnabledLookups();
        $lookupType = (string)($this->request->getPost('lookup_type') ?: (!empty($enabled) ? $enabled[0] : 'IMDB'));
        
        $apiData = null;
        try {
            if (\App\Libraries\LookupRegistry::isEnabled($lookupType)) {
                $imdbId = trim((string)$this->request->getPost('imdb_id'));
                $tmdbId = trim((string)$this->request->getPost('tmdb_id'));
                $tvdbId = trim((string)$this->request->getPost('tvdb_id'));
                $igdbId = trim((string)$this->request->getPost('igdb_id'));
                $mbid = trim((string)$this->request->getPost('mbid'));
                $lookupTitle = trim((string)$this->request->getPost('lookup_title'));
                $lookupYear = $this->request->getPost('lookup_year') ?: null;
                $lookupBarcode = trim((string)$this->request->getPost('lookup_barcode'));

                $apiService = \App\Libraries\ApiServiceFactory::create($lookupType);
                if ($apiService && $apiService->isApiAvailable()) {
                    if ($imdbId !== '') {
                        $apiData = method_exists($apiService, 'findByExternalId') ? $apiService->findByExternalId($imdbId, $lookupType) : null;
                    } elseif ($tmdbId !== '') {
                        $apiData = method_exists($apiService, 'getMediaDetails') ? $apiService->getMediaDetails($tmdbId) : null;
                    } elseif ($tvdbId !== '') {
                        $apiData = method_exists($apiService, 'getTvDetails') ? $apiService->getTvDetails($tvdbId) : null;
                    } elseif ($igdbId !== '') {
                        $apiData = $apiService->getMediaDetails((int)$igdbId);
                    } elseif ($mbid !== '') {
                        $apiData = $apiService->getMediaDetails($mbid);
                    } elseif ($lookupBarcode !== '' && method_exists($apiService, 'findByBarcode')) {
                        $apiData = $apiService->findByBarcode($lookupBarcode, $lookupType);
                    } elseif ($lookupTitle !== '') {
                        if ($lookupType === 'TVDB' && method_exists($apiService, 'searchTv')) {
                            $apiData = $apiService->searchTv($lookupTitle, $lookupYear ? (int)$lookupYear : null);
                        } else {
                            $apiData = $apiService->searchMedia($lookupTitle, $lookupYear ? (int)$lookupYear : null);
                        }
                    }
                } elseif ($lookupBarcode !== '' && $apiService && method_exists($apiService, 'findByBarcode')) {
                    // Even if TMDB isn’t available, try to at least resolve a title from the barcode
                    $apiData = $apiService->findByBarcode($lookupBarcode, $lookupType);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'API fetch during store failed: ' . $e->getMessage());
            // Continue with manual data
        }

        // Generate notes field with all supported external IDs if API data contains them
        $notes = $this->request->getPost('notes');
        $externalIds = [
            'tmdb' => $this->request->getPost('tmdb_id') ?: ($apiData['tmdb_id'] ?? null),
            'imdb' => $this->request->getPost('imdb_id') ?: ($apiData['imdb_id'] ?? null),
            'tvdb' => $this->request->getPost('tvdb_id') ?: ($apiData['tvdb_id'] ?? null),
            'igdb' => $this->request->getPost('igdb_id') ?: ($apiData['igdb_id'] ?? null),
            'mbid' => $this->request->getPost('mbid') ?: ($apiData['mbid'] ?? null),
            'source' => $lookupType,
        ];
        $notes = add_external_ids_to_notes($notes, $externalIds);

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
            if ($field === 'notes') {
                $insertData['notes'] = $notes;
                continue;
            }
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
        $insertData['number'] = $this->mediaModel->getNextAvailableNumber();
        $insertData['seen'] = $this->request->getPost('seen') ? 1 : 0;
        
        // Check if "wishlist" tag is among the tags to be added
        $tagIds = $this->request->getPost('tag_ids');
        $isWishlist = false;
        if (is_array($tagIds) && !empty($tagIds)) {
            $tagModel = new \App\Models\TagModel();
            foreach ($tagIds as $tagId) {
                $tag = $tagModel->find($tagId);
                if ($tag && strtolower($tag['name']) === 'wishlist') {
                    $isWishlist = true;
                    break;
                }
            }
        }

        // Cannot loan a wishlisted media
        if ($isWishlist) {
            $insertData['loaned'] = 0;
        } else {
            $insertData['loaned'] = $this->request->getPost('loaned') ? 1 : 0;
        }

        $insertData['created'] = database_now();
        $insertData['updated'] = $insertData['created'];

        // Insert the media
        $mediaId = $this->mediaModel->insert($insertData, true);
        if ($mediaId) {
            // Handle loan if provided
            $loanPersonId = $this->request->getPost('loan_person_id');
            if ($insertData['loaned'] && $loanPersonId) {
                $loanModel = new \App\Models\LoanModel();
                $loanModel->loanMedia($mediaId, $loanPersonId);
            }

            // Save tags if provided
            $tagIds = $this->request->getPost('tag_ids');
            if (is_array($tagIds) && !empty($tagIds)) {
                $tagModel = new \App\Models\TagModel();
                $tagModel->setTagsForMedia($mediaId, $tagIds);
            }

            log_message('debug', 'Media stored, ID: ' . $mediaId . '. Starting poster handling.');

            // Fix indentation and ensure poster handling is inside the if($mediaId) block
            // Handle poster - check in this order: custom upload, selected URL, API data
            $imageData = null;
            
            // 1. Check for custom uploaded poster file
            $posterFile = $this->request->getFile('poster_upload_file');
            if ($posterFile && $posterFile->isValid() && !$posterFile->hasMoved()) {
                log_message('debug', 'Custom poster upload detected.');
                try {
                    $imageData = $this->validateAndNormalizePosterUpload($posterFile);
                    if ($imageData === null) {
                        log_message('debug', 'Rejected invalid/unsupported poster upload for: ' . $posterFile->getClientName());
                    } else {
                        log_message('debug', 'Image data read. Length: ' . strlen($imageData));
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to process uploaded poster: ' . $e->getMessage());
                    $imageData = null;
                }
            }
            
            // 2. If no upload, check for selected poster URL from modal
            if (!$imageData) {
                $selectedPosterUrl = $this->request->getPost('selected_poster_url');
                if (!empty($selectedPosterUrl)) {
                    log_message('debug', 'Selected poster URL detected: ' . $selectedPosterUrl);
                    try {
                        // Use factory to get correct service for downloading
                        $posterLookupType = $this->request->getPost('lookup_type') ?: $lookupType;
                        $downloadService = \App\Libraries\ApiServiceFactory::create($posterLookupType);
                        
                        if ($downloadService && method_exists($downloadService, 'downloadPoster')) {
                            log_message('debug', 'Using service to download poster: ' . $posterLookupType);
                            $imageData = $downloadService->downloadPoster($selectedPosterUrl, $mediaId);
                        } else {
                            log_message('debug', 'Using default downloadRemotePoster.');
                            // Use default download if no service or method exists
                            $imageData = $this->downloadRemotePoster($selectedPosterUrl);
                        }
                        log_message('debug', 'Image data acquired. Length: ' . ($imageData ? strlen($imageData) : '0'));
                    } catch (\Throwable $e) {
                        log_message('error', 'Failed to download selected poster: ' . $e->getMessage());
                    }
                }
            }
            
            // 3. If still no poster and we have API data with poster URL, use that
            if (!$imageData && $apiData && !empty($apiData['poster_url'])) {
                log_message('debug', 'Falling back to API data poster URL: ' . $apiData['poster_url']);
                try {
                    // Use the already created apiService if possible
                    if (isset($apiService) && method_exists($apiService, 'downloadPoster')) {
                        $imageData = $apiService->downloadPoster($apiData['poster_url'], $mediaId);
                    } else {
                        // Use default download if no service or method exists
                        $imageData = $this->downloadRemotePoster($apiData['poster_url']);
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to download API poster: ' . $e->getMessage());
                }
            }
                
            // Store the poster if we got image data from any source
            if ($imageData) {
                try {
                    $this->mediaModel->storePosterForMedia($mediaId, $imageData);
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to store poster on create: ' . $e->getMessage());
                }
            }
            
            session()->setFlashdata('success', 'Media added successfully!');
            return redirect()->to(base_url('media/view/' . $mediaId));
        } else {
            session()->setFlashdata('error', 'Failed to add media.');
            return redirect()->back()->withInput();
        }
    }

    public function view($mediaId)
    {
        $movie = $this->mediaModel->getMediaWithAllDetails($mediaId);

        if (!$movie) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Media not found');
        }

        // Get tags for this movie
        $tagModel = new \App\Models\TagModel();
        $movieTags = $tagModel->getTagsForMedia($mediaId);
        $allTags = $this->mediaModel->getTags();

        // Get enabled lookups
        $lookupRegistry = new \App\Libraries\LookupRegistry();
        $enabledLookups = $lookupRegistry->getEnabledLookups();
        $lookupOptions = $lookupRegistry->getLookupTypeOptions();

        // Get current loan if any
        $loanModel = new \App\Models\LoanModel();
        $currentLoan = $loanModel->getActiveLoanForMedia($mediaId);

        $data = [
            'movie' => $movie,
            'mediaTags' => $movieTags,
            'allTags' => $allTags,
            'currentLoan' => $currentLoan,
            'enabledLookups' => $enabledLookups,
            'lookupOptions' => $lookupOptions,
            'title' => 'View Media - ' . ($movie['title'] ?: $movie['o_title'] ?: 'Untitled')
        ];

        return view('media/view', $data);
    }

    public function edit($mediaId)
    {
        $movie = $this->mediaModel->getMediaWithAllDetails($mediaId);

        if (!$movie) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Movie not found');
        }

        // Get current loan if any
        $loanModel = new \App\Models\LoanModel();
        $currentLoan = $loanModel->getActiveLoanForMedia($mediaId);

        $lookupRegistry = new \App\Libraries\LookupRegistry();
        $lookupOptions = $lookupRegistry->getLookupTypeOptions();

        // Get tags for this movie
        $tagModel = new \App\Models\TagModel();
        $movieTags = $tagModel->getTagsForMedia($mediaId);
        $allTags = $this->mediaModel->getTags();

        $data = [
            'movie' => $movie,
            'mediaTypes' => $this->mediaModel->getMediaTypes(),
            'collections' => $this->mediaModel->getCollections(),
            'volumes' => $this->mediaModel->getVolumes(),
            'videoCodecs' => $this->mediaModel->getVideoCodecs(),
            'ratios' => $this->mediaModel->getRatios(),
            'movieTags' => $movieTags,
            'allTags' => $allTags,
            'currentLoan' => $currentLoan,
            'lookupOptions' => $lookupOptions,
            'enabledLookups' => \App\Libraries\LookupRegistry::getEnabledLookups(),
            'lookupSource' => get_source_from_notes($movie['notes']),
            'title' => 'Edit Media - ' . ($movie['title'] ?: $movie['o_title'] ?: 'Untitled')
        ];

        return view('media/edit', $data);
    }

    public function update($mediaId)
    {
        $media = $this->mediaModel->find($mediaId);

        if (!$media) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Media not found');
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
        $hasExistingPoster = !empty($media['poster_md5']);
        
        $enabled = \App\Libraries\LookupRegistry::getEnabledLookups();
        $lookupType = (string)($this->request->getPost('lookup_type') ?: (!empty($enabled) ? $enabled[0] : 'IMDB'));

        // If the user explicitly chose no poster, clear any existing poster and skip saving fetched
        if ($posterChoice === 'none') {
            try {
                $this->mediaModel->clearPosterForMedia($mediaId);
            } catch (\Throwable $e) {
                log_message('error', 'Failed to clear poster for media on update: ' . $e->getMessage());
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

        // Handle manual poster upload
        $posterFile = $this->request->getFile('poster_upload_file');
        if ($posterFile && $posterFile->isValid() && !$posterFile->hasMoved()) {
            try {
                $imageData = $this->validateAndNormalizePosterUpload($posterFile);
                if ($imageData) {
                    $this->mediaModel->storePosterForMedia($mediaId, $imageData);
                    // If we uploaded a file, don't save the fetched URL
                    $shouldSaveFetched = false;
                } else {
                    log_message('debug', 'Rejected invalid/unsupported poster upload on update for: ' . $posterFile->getClientName());
                }
            } catch (\Throwable $e) {
                log_message('error', 'Failed to store uploaded poster on update: ' . $e->getMessage());
            }
        }

        if ($shouldSaveFetched) {
            try {
                // Determine type from notes or fallback to IMDB
                $type = (string)$this->request->getPost('lookup_type');
                if (!$type) {
                    $type = 'IMDB';
                    if (get_igdb_id_from_notes($media['notes'])) $type = 'IGDB';
                    elseif (get_mbid_from_notes($media['notes'])) $type = 'MusicBrainz';
                    elseif (get_tvdb_id_from_notes($media['notes'])) $type = 'TVDB';
                }

                if (\App\Libraries\LookupRegistry::isEnabled($type)) {
                    $apiService = \App\Libraries\ApiServiceFactory::create($type);
                    if ($apiService) {
                        // Download poster directly from the provided URL (no API key required)
                        $imageData = $apiService->downloadPoster($fetchedPosterUrl, $mediaId);
                        if ($imageData) {
                            // This updates poster_md5 and updated timestamp internally
                            $this->mediaModel->storePosterForMedia($mediaId, $imageData);
                        }
                    }
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
            'updated' => database_now()
        ];

        // Ensure the source tag is preserved or updated during manual update
        $lookupType = (string)$this->request->getPost('lookup_type');
        if ($lookupType && \App\Libraries\LookupRegistry::isEnabled($lookupType)) {
            $externalIds = [
                'tmdb' => $this->request->getPost('tmdb_id') ?: get_tmdb_id_from_notes($updateData['notes']),
                'imdb' => $this->request->getPost('imdb_id') ?: get_imdb_id_from_notes($updateData['notes']),
                'tvdb' => $this->request->getPost('tvdb_id') ?: get_tvdb_id_from_notes($updateData['notes']),
                'igdb' => $this->request->getPost('igdb_id') ?: get_igdb_id_from_notes($updateData['notes']),
                'mbid' => $this->request->getPost('mbid') ?: get_mbid_from_notes($updateData['notes']),
                'source' => $lookupType
            ];
            $updateData['notes'] = add_external_ids_to_notes($updateData['notes'], $externalIds);
        }

        // Check for wishlist tag in the posted tag_ids
        $tagIds = $this->request->getPost('tag_ids');
        $tagIdsSent = $this->request->getPost('tag_ids_sent');
        $isWishlist = false;

        if (is_array($tagIds) || $tagIdsSent) {
            $submittedTags = is_array($tagIds) ? $tagIds : [];
            if (!empty($submittedTags)) {
                $tagModel = new \App\Models\TagModel();
                foreach ($submittedTags as $tagId) {
                    $tag = $tagModel->find($tagId);
                    if ($tag && strtolower($tag['name']) === 'wishlist') {
                        $isWishlist = true;
                        break;
                    }
                }
            }
        } else {
            // If tag_ids wasn't sent, check existing tags
            $tagModel = new \App\Models\TagModel();
            $existingTags = $tagModel->getTagsForMedia($mediaId);
            foreach ($existingTags as $tag) {
                if (strtolower($tag['name']) === 'wishlist') {
                    $isWishlist = true;
                    break;
                }
            }
        }

        // Cannot loan a wishlist media
        if ($isWishlist) {
            $updateData['loaned'] = 0;
            
            // If it was already loaned, we might need to handle returning it, 
            // but for now let's just force it to 0. 
            // In a real scenario, we might want to prevent adding the "wishlist" tag if it's loaned.
        } else {
            $updateData['loaned'] = $this->request->getPost('loaned') ? 1 : 0;
        }
        
        // Add multiple medium_ids to notes
        if (!empty($mediumIds)) {
            $updateData['notes'] = add_medium_ids_to_notes($updateData['notes'] ?? null, $mediumIds);
            // Set the highest medium_id as the main medium_id field
            $updateData['medium_id'] = max(array_map('intval', $mediumIds));
        }

        // Update media data
        $success = $this->mediaModel->update($mediaId, $updateData);

        // Update tags
        $tagIds = $this->request->getPost('tag_ids');
        $tagIdsSent = $this->request->getPost('tag_ids_sent');
        if (is_array($tagIds) || $tagIdsSent) {
            $tagModel = new \App\Models\TagModel();
            $tagModel->setTagsForMedia($mediaId, is_array($tagIds) ? $tagIds : []);
        }

        if ($success) {
            // Handle loan changes
            $loanPersonId = $this->request->getPost('loan_person_id');
            if ($updateData['loaned'] && $loanPersonId) {
                $loanModel = new \App\Models\LoanModel();
                $currentLoan = $loanModel->getActiveLoanForMedia($mediaId);
                
                // Only create a new loan if it's not already loaned to the same person
                if (!$currentLoan || $currentLoan['person_id'] != $loanPersonId) {
                    // If it was already loaned to someone else, return it first
                    if ($currentLoan) {
                        $loanModel->returnMedia($mediaId);
                    }
                    $loanModel->loanMedia($mediaId, $loanPersonId);
                }
            } elseif (!$updateData['loaned']) {
                // If checkbox is unchecked, ensuring it's returned
                $loanModel = new \App\Models\LoanModel();
                $currentLoan = $loanModel->getActiveLoanForMedia($mediaId);
                if ($currentLoan) {
                    $loanModel->returnMedia($mediaId);
                }
            }

            session()->setFlashdata('success', 'Media updated successfully!');
            return redirect()->to(base_url('media/view/' . $mediaId));
        } else {
            session()->setFlashdata('error', 'Failed to update media.');
            return redirect()->back()->withInput();
        }
    }

    public function delete($mediaId)
    {
        // Verify media exists
        $media = $this->mediaModel->find($mediaId);

        if (!$media) {
            session()->setFlashdata('error', 'Media not found.');
            return redirect()->to(base_url('media'));
        }

        // Delete the media
        $success = $this->mediaModel->delete($mediaId);

        if ($success) {
            session()->setFlashdata('success', 'Media "' . esc($media['title'] ?: $media['o_title']) . '" has been deleted successfully.');
            return redirect()->to(base_url('media'));
        } else {
            session()->setFlashdata('error', 'Failed to delete media.');
            return redirect()->back();
        }
    }

    public function toggleSeen($mediaId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $media = $this->mediaModel->find($mediaId);

        if (!$media) {
            return $this->response->setJSON(['success' => false, 'message' => 'Media not found']);
        }

        $newSeenStatus = !$media['seen'];
        $success = $this->mediaModel->toggleSeenStatus($mediaId, $newSeenStatus);

        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'seen' => $newSeenStatus,
                'message' => 'Media marked as ' . ($newSeenStatus ? 'seen' : 'unseen')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update media status']);
        }
    }

    public function fetchFromApi($mediaId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $media = $this->mediaModel->find($mediaId);
        if (!$media) {
            return $this->response->setJSON(['success' => false, 'message' => 'Media not found']);
        }

        try {
            // Read optional custom query/year/type from JSON body (AJAX)
            $payload = $this->request->getJSON(true) ?: [];
            $type = isset($payload['type']) ? $payload['type'] : 'IMDB';

            if (!\App\Libraries\LookupRegistry::isEnabled($type)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lookup service ' . $type . ' is disabled in settings.']);
            }
            
            // Auto-detect type if not provided
            if (!isset($payload['type'])) {
                if (get_igdb_id_from_notes($media['notes'])) $type = 'IGDB';
                elseif (get_mbid_from_notes($media['notes'])) $type = 'MusicBrainz';
                elseif (get_tvdb_id_from_notes($media['notes'])) $type = 'TVDB';
            }

            $apiService = \App\Libraries\ApiServiceFactory::create($type);
            if (!$apiService || !$apiService->isApiAvailable()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'API service for ' . $type . ' is not available or configured.'
                ]);
            }

            $customQuery = isset($payload['query']) ? trim((string)$payload['query']) : '';
            $customYear = isset($payload['year']) ? (int)$payload['year'] : null;
            $searchTitle = $customQuery !== '' ? $customQuery : ($media['title'] ?: $media['o_title'] ?: 'Unknown');
            $searchYear = $customYear ?: $media['year'];

            $apiData = null;
            
            // If the user provided a direct ID (numeric for TMDB/IGDB, UUID for MusicBrainz), fetch directly
            if (ctype_digit($searchTitle) || (strlen($searchTitle) > 30 && strpos($searchTitle, '-') !== false)) {
                if ($type === 'TVDB' && method_exists($apiService, 'getTvDetails')) {
                    $apiData = $apiService->getTvDetails($searchTitle);
                } else {
                    $apiData = $apiService->getMediaDetails($searchTitle);
                }
            } else {
                // Otherwise perform a title search
                $page = isset($payload['page']) && is_numeric($payload['page']) ? (int)$payload['page'] : 1;
                $searchData = [];
                if ($type === 'TVDB' && method_exists($apiService, 'searchTvMultiple')) {
                    $searchData = $apiService->searchTvMultiple($searchTitle, $searchYear, 10, $page);
                } elseif (method_exists($apiService, 'searchMovieMultiple')) {
                    $searchData = $apiService->searchMovieMultiple($searchTitle, $searchYear, 10, $page);
                } elseif (method_exists($apiService, 'searchMultiple')) {
                    $searchData = $apiService->searchMultiple($searchTitle, $searchYear, 10, $page);
                }

                $results = $searchData['results'] ?? [];
                $totalPages = $searchData['total_pages'] ?? 1;
                $totalResults = $searchData['total_results'] ?? 0;
                $currentPage = $searchData['page'] ?? 1;
                $isEstimated = $searchData['is_estimated'] ?? false;

                // If multiple results found, return them for user selection
                if (count($results) > 1 || $totalPages > 1) {
                    $message = 'Found ' . $totalResults . ' matches. Please select one.';
                    if ($isEstimated && count($results) > 0) {
                        $message = 'Showing ' . count($results) . ' matches. More available...';
                    }
                    return $this->response->setJSON([
                        'success' => true,
                        'multiple' => true,
                        'message' => $message,
                        'results' => $results,
                        'page' => $currentPage,
                        'total_pages' => $totalPages,
                        'total_results' => $totalResults,
                        'is_estimated' => $isEstimated
                    ]);
                }

                // If exactly one result, fetch full details
                if (count($results) === 1 && $totalPages === 1) {
                    $id = $results[0]['imdb_id'] ?? $results[0]['tmdb_id'] ?? $results[0]['tvdb_id'] ?? $results[0]['igdb_id'] ?? $results[0]['mbid'] ?? null;
                    if ($id) {
                        if ($type === 'TVDB' && method_exists($apiService, 'getTvDetails')) {
                            $apiData = $apiService->getTvDetails($id);
                        } else {
                            $apiData = $apiService->getMediaDetails($id);
                        }
                    }
                }
            }

            if (!$apiData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No media data found for "' . $searchTitle . '"'
                ]);
            }

            // Generate notes field with external IDs
            $apiData['notes'] = add_external_ids_to_notes($media['notes'] ?? null, [
                'tmdb' => $apiData['tmdb_id'] ?? null,
                'imdb' => $apiData['imdb_id'] ?? null,
                'tvdb' => $apiData['tvdb_id'] ?? null,
                'igdb' => $apiData['igdb_id'] ?? null,
                'mbid' => $apiData['mbid'] ?? null,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Media data fetched successfully (not saved yet)'.($apiData['poster_url'] ? '' : ' (no poster available)'),
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
     * Mark a media entry as exempt from duplicate lookups (AJAX)
     */
    public function exempt($mediaId)
    {
        if (!$this->request->isAJAX()) {
            $this->response->setHeader('Content-Type', 'application/json');
        }

        $media = $this->mediaModel->find($mediaId);
        if (!$media) {
            return $this->response->setJSON(['success' => false, 'message' => 'Media not found.']);
        }

        $notes = (string)$media['notes'];
        if (strpos($notes, '<!skipduplicate>') === false) {
            $notes = trim($notes);
            if ($notes !== '') {
                $notes .= "\n<!skipduplicate>";
            } else {
                $notes = "<!skipduplicate>";
            }
        }

        $this->mediaModel->update($mediaId, ['notes' => $notes]);

        return $this->response->setJSON(['success' => true, 'message' => 'Media marked as exempt.']);
    }

    /**
     * Check for potential duplicates in the library (AJAX)
     */
    public function checkDuplicate()
    {
        if (!$this->request->isAJAX()) {
            $this->response->setHeader('Content-Type', 'application/json');
        }

        try {
            $payload = $this->request->getJSON(true) ?: $this->request->getPost();
            $title = trim((string)($payload['title'] ?? ''));
            $oTitle = trim((string)($payload['o_title'] ?? ''));
            $year = isset($payload['year']) && $payload['year'] !== '' ? (int)$payload['year'] : null;
            $barcode = trim((string)($payload['barcode'] ?? ''));

            $duplicates = [];

            // 1. Check by barcode (highest confidence)
            if ($barcode !== '') {
                $existing = $this->mediaModel->findByBarcode($barcode);
                if ($existing && strpos((string)$existing['notes'], '<!skipduplicate>') === false) {
                    $duplicates[] = $existing;
                }
            }

            // 2. Check by title/year (if no barcode match or barcode was empty)
            if (empty($duplicates)) {
                if ($title !== '') {
                    $matches = $this->mediaModel->findByTitleFuzzy($title, $year);
                    foreach ($matches as $match) {
                        if (strpos((string)$match['notes'], '<!skipduplicate>') === false) {
                            $duplicates[] = $match;
                        }
                    }
                }
                
                // Also check original title if it's different
                if ($oTitle !== '' && $oTitle !== $title) {
                    $matches = $this->mediaModel->findByTitleFuzzy($oTitle, $year);
                    foreach ($matches as $match) {
                        if (strpos((string)$match['notes'], '<!skipduplicate>') !== false) continue;
                        
                        // Avoid duplicates in our list
                        $alreadyFound = false;
                        foreach ($duplicates as $d) {
                            if ($d['movie_id'] === $match['movie_id']) {
                                $alreadyFound = true;
                                break;
                            }
                        }
                        if (!$alreadyFound) {
                            $duplicates[] = $match;
                        }
                    }
                }
            }

            if (!empty($duplicates)) {
                $formatted = array_map(function($d) {
                    return [
                        'movie_id' => $d['movie_id'],
                        'title' => $d['title'],
                        'year' => $d['year'],
                        'url' => base_url('media/view/' . $d['movie_id']),
                        'edit_url' => base_url('media/edit/' . $d['movie_id'])
                    ];
                }, $duplicates);

                return $this->response->setJSON([
                    'success' => true,
                    'duplicates' => $formatted
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'duplicates' => []
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
     * Accepts POST JSON or form data: lookup_type (IMDB|TVDB|IGDB|MusicBrainz), title, year, imdb_id, tvdb_id
     */
    public function lookup()
    {
        if (!$this->request->isAJAX()) {
            // For non-AJAX, still return JSON to keep it simple
            $this->response->setHeader('Content-Type', 'application/json');
        }
        try {
            $payload = $this->request->getJSON(true) ?: $this->request->getPost();
            $enabled = \App\Libraries\LookupRegistry::getEnabledLookups();
            $lookupType = (string)($payload['lookup_type'] ?? (!empty($enabled) ? $enabled[0] : 'IMDB'));
            
            if (!\App\Libraries\LookupRegistry::isEnabled($lookupType)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lookup service ' . $lookupType . ' is disabled in settings.']);
            }
            
            $apiService = \App\Libraries\ApiServiceFactory::create($lookupType);
            if (!$apiService) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid lookup type: ' . $lookupType]);
            }
            $apiAvailable = $apiService->isApiAvailable();

            // If API not available for the selected lookup type, provide a clear message
            if (!$apiAvailable) {
                $keyName = $lookupType . '_API_KEY';
                if ($lookupType === 'MusicBrainz') $keyName = 'MUSICBRAINZ_EMAIL';
                if ($lookupType === 'IGDB') $keyName = 'IGDB_CLIENT_ID/SECRET';
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Search failed because $keyName is not configured. Please check your application settings."
                ]);
            }

            $imdbId = trim((string)($payload['imdb_id'] ?? ''));
            $tvdbId = trim((string)($payload['tvdb_id'] ?? ''));
            $igdbId = trim((string)($payload['igdb_id'] ?? ''));
            $mbid = trim((string)($payload['mbid'] ?? ''));
            $barcode = trim((string)($payload['barcode'] ?? ''));
            $title = trim((string)($payload['title'] ?? ''));
            $year = isset($payload['year']) && $payload['year'] !== '' ? (int)$payload['year'] : null;

            // 1) External IDs via API if available
            $apiData = null;
            if ($imdbId !== '' && $apiAvailable && method_exists($apiService, 'findByExternalId')) {
                $apiData = $apiService->findByExternalId($imdbId, $lookupType);
            } elseif ($tvdbId !== '' && $apiAvailable && method_exists($apiService, 'findByExternalId')) {
                $apiData = $apiService->findByExternalId($tvdbId, $lookupType);
            } elseif ($igdbId !== '' && $apiAvailable) {
                $apiData = $apiService->getMediaDetails((int)$igdbId);
            } elseif ($mbid !== '' && $apiAvailable) {
                $apiData = $apiService->getMediaDetails($mbid);
            } elseif ($barcode !== '') {
                // 2) Local library lookup by barcode (exact match)
                $existing = $this->mediaModel->findByBarcode($barcode);
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
                        'poster_url' => $existing['poster_md5'] ? base_url('media/poster/' . $existing['movie_id']) . '?v=' . urlencode($existing['poster_md5']) : null,
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
                // Not found locally by barcode — attempt online resolution if possible
                if ($apiAvailable && method_exists($apiService, 'findByBarcode')) {
                    $apiData = $apiService->findByBarcode($barcode, $lookupType);
                }

                // Check for rate limit error from primary service
                if ($apiData && isset($apiData['error']) && $apiData['error'] === 'EXCEED_LIMIT') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $apiData['message'] ?? 'Barcode lookup limit exceeded. Please try again later.'
                    ]);
                }

                // If no result from primary service, try other services as cross-type fallback
                if (!$apiData) {
                    $allTypes = ['TMDB', 'IMDB', 'TVDB', 'IGDB', 'MusicBrainz'];
                    foreach ($allTypes as $type) {
                        if ($type === $lookupType) continue; // Skip primary already tried
                        
                        if (\App\Libraries\LookupRegistry::isEnabled($type)) {
                            $fallbackService = \App\Libraries\ApiServiceFactory::create($type);
                            if ($fallbackService && method_exists($fallbackService, 'findByBarcode')) {
                                $fallbackData = $fallbackService->findByBarcode($barcode, $type);
                                
                                // Check for rate limit error from fallback service
                                if ($fallbackData && isset($fallbackData['error']) && $fallbackData['error'] === 'EXCEED_LIMIT') {
                                    return $this->response->setJSON([
                                        'success' => false,
                                        'message' => $fallbackData['message'] ?? 'Barcode lookup limit exceeded. Please try again later.'
                                    ]);
                                }

                                if ($fallbackData) {
                                    $apiData = $fallbackData;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!$apiData && ($title === '' && !$apiAvailable)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No item with this barcode found locally or online.'
                    ]);
                }
            }

            // 3) Title search via API
            if (!$apiData && $title !== '' && $apiAvailable) {
                // Get page parameter for pagination
                $page = isset($payload['page']) && is_numeric($payload['page']) ? (int)$payload['page'] : 1;

                // Search for multiple results
                $searchData = [];
                if ($lookupType === 'TVDB' && method_exists($apiService, 'searchTvMultiple')) {
                    $searchData = $apiService->searchTvMultiple($title, $year, 20, $page);
                } elseif (method_exists($apiService, 'searchMovieMultiple')) {
                    $searchData = $apiService->searchMovieMultiple($title, $year, 20, $page);
                } elseif (method_exists($apiService, 'searchMultiple')) {
                    $searchData = $apiService->searchMultiple($title, $year, 20, $page);
                }

                $results = $searchData['results'] ?? [];
                $totalPages = $searchData['total_pages'] ?? 1;
                $totalResults = $searchData['total_results'] ?? 0;
                $currentPage = $searchData['page'] ?? 1;
                $isEstimated = $searchData['is_estimated'] ?? false;

                // If multiple results found, return them for user selection
                if (count($results) > 1 || $totalPages > 1) {
                    $message = 'Found ' . $totalResults . ' matches. Please select one.';
                    if ($isEstimated && count($results) > 0) {
                        $message = 'Showing ' . count($results) . ' matches. More available...';
                    }
                    return $this->response->setJSON([
                        'success' => true,
                        'multiple' => true,
                        'message' => $message,
                        'results' => $results,
                        'page' => $currentPage,
                        'total_pages' => $totalPages,
                        'total_results' => $totalResults,
                        'is_estimated' => $isEstimated
                    ]);
                }

                // If exactly one result, fetch full details
                if (count($results) === 1 && $totalPages === 1) {
                    $id = $results[0]['imdb_id'] ?? $results[0]['tmdb_id'] ?? $results[0]['tvdb_id'] ?? $results[0]['igdb_id'] ?? $results[0]['mbid'] ?? null;
                    if ($id) {
                        if ($lookupType === 'TVDB' && method_exists($apiService, 'getTvDetails')) {
                            $apiData = $apiService->getTvDetails($id);
                        } else {
                            $apiData = $apiService->getMediaDetails($id);
                        }
                    }
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
            if (isset($apiData['tmdb_id']) || isset($apiData['imdb_id']) || isset($apiData['tvdb_id']) || isset($apiData['igdb_id']) || isset($apiData['mbid'])) {
                $apiData['notes'] = add_external_ids_to_notes(null, [
                    'tmdb' => $apiData['tmdb_id'] ?? null,
                    'imdb' => $apiData['imdb_id'] ?? null,
                    'tvdb' => $apiData['tvdb_id'] ?? null,
                    'igdb' => $apiData['igdb_id'] ?? null,
                    'mbid' => $apiData['mbid'] ?? null,
                ]);
            }

            $msgPrefix = '';
            if ($lookupType === 'TMDB') {
                $msgPrefix = 'Fetched details from TMDB. ';
            } elseif ($lookupType === 'IMDB') {
                $msgPrefix = 'Fetched details from IMDB. ';
            } elseif ($lookupType === 'TVDB') {
                $msgPrefix = 'Fetched details from TVDB. ';
            } else {
                $msgPrefix = 'Fetched details from API. ';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $msgPrefix . 'Review and apply to the form before saving.' . (isset($apiData['poster_url']) && $apiData['poster_url'] ? '' : ' (No poster available)'),
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
    public function poster($mediaId)
    {
        log_message('debug', 'Serving poster for media ID: ' . $mediaId);
        $posterData = $this->mediaModel->getPosterData($mediaId);
        
        if (!$posterData || !isset($posterData['data'])) {
            log_message('debug', 'Poster not found in database for media ID: ' . $mediaId);
            // Return 404 or default image
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Poster not found');
        }
        
        $data = $posterData['data'];
        log_message('debug', 'Poster data retrieved. Size: ' . (is_string($data) ? strlen($data) : 'resource') . ' bytes');

        // If data is a resource (e.g. from PDO/SQLite), read it
        if (is_resource($data)) {
            log_message('debug', 'Poster data is a resource, reading...');
            $data = stream_get_contents($data);
            log_message('debug', 'Poster data read. New size: ' . strlen($data) . ' bytes');
        }
        
        // Verify MD5 hash
        $calculatedMd5 = md5($data);
        if ($posterData['md5sum'] !== $calculatedMd5) {
            log_message('error', 'Poster data integrity check failed for media ID: ' . $mediaId . '. DB MD5: ' . $posterData['md5sum'] . ', Calculated: ' . $calculatedMd5);
            // For now, let's still try to serve it if it's not empty, to see what happens
            if (empty($data)) {
                 throw new \CodeIgniter\Exceptions\PageNotFoundException('Poster data corrupted and empty');
            }
        }

        // Determine content type from data
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($data);
        log_message('debug', 'Detected MIME type: ' . $mimeType);

        // Fallback to image/jpeg if detection fails
        if (!$mimeType || $mimeType === 'application/octet-stream') {
            $mimeType = 'image/jpeg';
            log_message('debug', 'MIME type fallback to image/jpeg');
        }

        // Never reflect a detected type outside a small image allowlist as
        // the response Content-Type. Stored poster data that sniffs to,
        // e.g., text/html must not be served as such -- doing so would let
        // a stored non-image payload execute as a document in the browser.
        $allowedPosterMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedPosterMimeTypes, true)) {
            log_message('error', 'Refusing to serve poster for media ID ' . $mediaId . ' with disallowed detected MIME type: ' . $mimeType);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Poster not found');
        }

        // Set appropriate headers
        $this->response->setHeader('Content-Type', $mimeType)
                      ->setHeader('X-Content-Type-Options', 'nosniff')
                      ->setHeader('Content-Length', (string)strlen($data))
                      ->setHeader('Cache-Control', 'public, max-age=31536000') // Cache for 1 year
                      ->setHeader('ETag', '"' . $posterData['md5sum'] . '"');
        
        // Clean any output buffers to prevent corruption
        // We use ob_get_length() to check if there is actually anything in the buffer
        while (ob_get_level() > 0 && ob_get_length() !== false) {
            ob_end_clean();
        }

        return $this->response->setBody($data);
    }

    /**
     * Loan media to a person
     */
    public function loan($mediaId)
    {
        if ($this->request->isAJAX()) {
            $personId = $this->request->getPost('person_id');

            if (!$personId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Please select a person to loan the media to'
                ]);
            }

            $loanModel = new \App\Models\LoanModel();
            $result = $loanModel->loanMedia($mediaId, $personId);

            return $this->response->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Get loan history for a specific media item
     */
    public function loanHistory($mediaId)
    {
        $loanModel = new \App\Models\LoanModel();
        $history = $loanModel->getLoanHistoryForMedia($mediaId);

        return $this->response->setJSON($history);
    }

    public function returnLoan($mediaId)
    {
        if ($this->request->isAJAX()) {
            $loanModel = new \App\Models\LoanModel();
            $result = $loanModel->returnMedia($mediaId);

            return $this->response->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);
        }

        return $this->response->setStatusCode(404);
    }

    /**
     * Send email reminder for a loan
     */
    public function sendReminder($loanId)
    {
        if ($this->request->isAJAX()) {
            $loanModel = new \App\Models\LoanModel();
            $loan = $loanModel->select('loans.*, people.name as person_name, people.email, people.notifications, movies.title, movies.o_title')
                ->join('people', 'people.person_id = loans.person_id')
                ->join('movies', 'movies.movie_id = loans.movie_id')
                ->where('loans.loan_id', $loanId)
                ->first();

            if (!$loan) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Loan record not found.'
                ]);
            }

            if ((int)$loan['notifications'] === 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Email notifications are disabled for ' . $loan['person_name'] . '.'
                ]);
            }

            if (empty($loan['email'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No email address found for this person.'
                ]);
            }

            $email = \Config\Services::email();
            
            $mediaTitle = $loan['title'] ?: $loan['o_title'];
            $subject = "Reminder: Media Loan - " . $mediaTitle;
            
            $message = "Hi " . $loan['person_name'] . ",\n\n";
            $message .= "This is a friendly reminder that you have borrowed the following media: " . $mediaTitle . ".\n";
            $message .= "Please return it when you are finished with it.\n\n";
            $message .= "Thank you!";

            $email->setTo($loan['email']);
            $email->setSubject($subject);
            $email->setMessage($message);

            if ($email->send()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Reminder email sent successfully to ' . $loan['email']
                ]);
            } else {
                // For debugging, you might want to log $email->printDebugger();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to send email. Please check your email configuration.'
                ]);
            }
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

    /**
     * Add a new person via AJAX
     */
    public function addPerson()
    {
        if ($this->request->isAJAX()) {
            $peopleModel = new \App\Models\PeopleModel();
            
            $data = [
                'name'          => $this->request->getPost('name'),
                'email'         => $this->request->getPost('email'),
                'phone'         => $this->request->getPost('phone'),
                'notifications' => $this->request->getPost('notifications') ?? 1,
            ];

            if ($peopleModel->insert($data)) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'person_id' => $peopleModel->getInsertID(),
                    'name'      => $data['name']
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $peopleModel->errors()
                ]);
            }
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Add a new collection via AJAX
     */
    public function addCollection()
    {
        if ($this->request->isAJAX()) {
            $collectionModel = new \App\Models\CollectionModel();
            
            $data = [
                'name'   => $this->request->getPost('name'),
                'loaned' => 0,
            ];

            if ($collectionModel->insert($data)) {
                return $this->response->setJSON([
                    'status'        => 'success',
                    'collection_id' => $collectionModel->getInsertID(),
                    'name'          => $data['name']
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $collectionModel->errors()
                ]);
            }
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Add a new volume via AJAX
     */
    public function addVolume()
    {
        if ($this->request->isAJAX()) {
            $volumeModel = new \App\Models\VolumeModel();
            
            $data = [
                'name'   => $this->request->getPost('name'),
                'loaned' => 0,
            ];

            if ($volumeModel->insert($data)) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'volume_id' => $volumeModel->getInsertID(),
                    'name'      => $data['name']
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $volumeModel->errors()
                ]);
            }
        }
        return $this->response->setStatusCode(404);
    }

    /**
     * Fetch details for a specific TMDB ID (used when user selects from multiple search results)
     */
    public function fetchDetails()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $payload = $this->request->getJSON(true) ?: [];
            $enabled = \App\Libraries\LookupRegistry::getEnabledLookups();
            $type = isset($payload['type']) ? $payload['type'] : (!empty($enabled) ? $enabled[0] : 'IMDB');
            
            if (!\App\Libraries\LookupRegistry::isEnabled($type)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lookup service ' . $type . ' is disabled in settings.']);
            }
            
            $apiService = \App\Libraries\ApiServiceFactory::create($type);
            if (!$apiService || !$apiService->isApiAvailable()) {
                return $this->response->setJSON(['success' => false, 'message' => 'API service not available.']);
            }

            $id = $payload['imdb_id'] ?? $payload['tmdb_id'] ?? $payload['tvdb_id'] ?? $payload['igdb_id'] ?? $payload['mbid'] ?? $payload['id'] ?? null;
            if (!$id) {
                return $this->response->setJSON(['success' => false, 'message' => 'No ID provided.']);
            }

            // Fetch full details
            $apiData = null;
            if ($type === 'TVDB' && method_exists($apiService, 'getTvDetails')) {
                $apiData = $apiService->getTvDetails($id);
            } else {
                $apiData = $apiService->getMediaDetails($id);
            }

            if (!$apiData) {
                return $this->response->setJSON(['success' => false, 'message' => 'Could not fetch details.']);
            }

            // Generate notes field with external IDs
            $apiData['notes'] = add_external_ids_to_notes(null, [
                'tmdb' => $apiData['tmdb_id'] ?? null,
                'imdb' => $apiData['imdb_id'] ?? null,
                'tvdb' => $apiData['tvdb_id'] ?? null,
                'igdb' => $apiData['igdb_id'] ?? null,
                'mbid' => $apiData['mbid'] ?? null,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Fetched details from API.',
                'data' => $apiData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get multiple poster options from available APIs for new media (before creation)
     */
    public function fetchPostersForNew()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $payload = $this->request->getJSON(true) ?: [];
            $enabled = \App\Libraries\LookupRegistry::getEnabledLookups();
            $type = isset($payload['type']) ? $payload['type'] : (!empty($enabled) ? $enabled[0] : 'TMDB');
            $selectedSource = isset($payload['source']) && !empty($payload['source']) ? $payload['source'] : null;
            $searchTerm = isset($payload['searchTerm']) && !empty($payload['searchTerm']) ? $payload['searchTerm'] : null;

            if ($selectedSource) {
                $type = $selectedSource;
            }
            
            if (!\App\Libraries\LookupRegistry::isEnabled($type)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lookup service ' . $type . ' is disabled in settings.']);
            }
            
            $apiService = \App\Libraries\ApiServiceFactory::create($type);
            if (!$apiService || !$apiService->isApiAvailable()) {
                return $this->response->setJSON(['success' => false, 'message' => 'API service not available.']);
            }

            $id = $payload['imdb_id'] ?? $payload['tmdb_id'] ?? $payload['tvdb_id'] ?? $payload['igdb_id'] ?? $payload['mbid'] ?? $payload['id'] ?? null;

            // If search term is provided, try searching for multiple results and aggregate posters
            if ($searchTerm) {
                $title = $searchTerm;
                $results = [];
                
                if ($type === 'IMDB' && method_exists($apiService, 'searchMovieMultiple')) {
                    $searchRes = $apiService->searchMovieMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } elseif ($type === 'TMDB' || $type === 'MOVIE') {
                    $searchRes = $apiService->searchMovieMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } elseif ($type === 'TV' || $type === 'TVDB') {
                    $searchRes = $apiService->searchTvMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } elseif (method_exists($apiService, 'searchMultiple')) {
                    $searchRes = $apiService->searchMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } else {
                    $apiData = $apiService->searchMedia($title, null);
                    if ($apiData) $results[] = $apiData;
                }

                if (empty($results)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Could not find media on API.']);
                }

                $posters = [];
                foreach ($results as $res) {
                    $resId = null;
                    if ($type === 'IMDB') {
                        $resId = $res['imdb_id'] ?? null;
                    } elseif ($type === 'TMDB' || $type === 'TVDB' || $type === 'MOVIE' || $type === 'TV') {
                        $resId = $res['tmdb_id'] ?? $res['tvdb_id'] ?? null;
                    } elseif ($type === 'IGDB' || $type === 'GAME') {
                        $resId = $res['igdb_id'] ?? $res['id'] ?? null;
                    } elseif ($type === 'MusicBrainz' || $type === 'MUSIC') {
                        $resId = $res['mbid'] ?? null;
                    }

                    if ($resId) {
                        if (method_exists($apiService, 'getPosters')) {
                            $resPosters = $apiService->getPosters($resId, $type, 5);
                            foreach ($resPosters as $rp) {
                                // Add a label to help user identify which movie this poster belongs to
                                $rp['label'] = ($res['title'] ?? '') . ' (' . ($res['year'] ?? '') . ')';
                                $posters[] = $rp;
                            }
                        } elseif (!empty($res['poster_url'])) {
                            $posters[] = [
                                'url' => $res['poster_url'],
                                'thumbnail' => $res['poster_url'],
                                'width' => null,
                                'height' => null,
                                'label' => ($res['title'] ?? '') . ' (' . ($res['year'] ?? '') . ')'
                            ];
                        }
                    }
                }
            } else {
                // No search term, use the ID (from notes or payload)
                if (!$id) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Could not resolve media ID.']);
                }

                // Get multiple posters
                if (method_exists($apiService, 'getPosters')) {
                    $posters = $apiService->getPosters($id, $type, 15);
                } else {
                    $details = ($type === 'TVDB' && method_exists($apiService, 'getTvDetails')) ? $apiService->getTvDetails($id) : $apiService->getMediaDetails($id);
                    if ($details && !empty($details['poster_url'])) {
                        $posters[] = [
                            'url' => $details['poster_url'],
                            'thumbnail' => $details['poster_url'],
                            'width' => null,
                            'height' => null
                        ];
                    }
                }
            }

            // Calculate dimensions if null
            if (!empty($posters)) {
                helper('app');
                foreach ($posters as &$poster) {
                    if ($poster['width'] === null || $poster['height'] === null) {
                        [$width, $height] = get_image_dimensions($poster['url']);
                        $poster['width'] = $width;
                        $poster['height'] = $height;
                    }
                }
            }

            if (empty($posters)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No posters found from ' . $type]);
            }

            return $this->response->setJSON([
                'success' => true,
                'posters' => $posters,
                'source' => $type,
                'message' => 'Found ' . count($posters) . ' poster(s) from ' . $type
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get multiple poster options from available APIs for media
     */
    public function fetchPosters($mediaId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $media = $this->mediaModel->find($mediaId);
        if (!$media) {
            return $this->response->setJSON(['success' => false, 'message' => 'Media not found']);
        }

        try {
            $payload = $this->request->getJSON(true) ?: [];
            $selectedSource = isset($payload['source']) && !empty($payload['source']) ? $payload['source'] : null;
            $searchTerm = isset($payload['searchTerm']) && !empty($payload['searchTerm']) ? $payload['searchTerm'] : null;

            $notes = $media['notes'] ?? '';
            $type = 'TMDB'; // Default

            if ($selectedSource) {
                $type = $selectedSource;
            } else {
                if (get_imdb_id_from_notes($notes) && \App\Libraries\LookupRegistry::isEnabled('IMDB')) {
                    $type = 'IMDB';
                } elseif (get_tmdb_id_from_notes($notes) && \App\Libraries\LookupRegistry::isEnabled('TMDB')) {
                    $type = 'TMDB';
                } elseif (get_igdb_id_from_notes($notes) && \App\Libraries\LookupRegistry::isEnabled('IGDB')) {
                    $type = 'IGDB';
                } elseif (get_mbid_from_notes($notes) && \App\Libraries\LookupRegistry::isEnabled('MusicBrainz')) {
                    $type = 'MusicBrainz';
                } elseif (get_tvdb_id_from_notes($notes) && \App\Libraries\LookupRegistry::isEnabled('TVDB')) {
                    $type = 'TVDB';
                } else {
                    // Default to first enabled lookup if current ones are not enabled
                    $enabled = \App\Libraries\LookupRegistry::getEnabledLookups();
                    $type = !empty($enabled) ? $enabled[0] : 'TMDB';
                }
            }

            if (!\App\Libraries\LookupRegistry::isEnabled($type)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lookup service ' . $type . ' is disabled in settings.']);
            }

            // Get ID based on type
            $id = null;
            if (!$searchTerm) {
                if ($type === 'IMDB') {
                    $id = get_imdb_id_from_notes($notes);
                } elseif ($type === 'TMDB') {
                    $id = get_tmdb_id_from_notes($notes);
                } elseif ($type === 'IGDB') {
                    $id = get_igdb_id_from_notes($notes);
                } elseif ($type === 'MusicBrainz') {
                    $id = get_mbid_from_notes($notes);
                } elseif ($type === 'TVDB') {
                    $id = get_tvdb_id_from_notes($notes);
                }
            }

            $apiService = \App\Libraries\ApiServiceFactory::create($type);
            if (!$apiService || !$apiService->isApiAvailable()) {
                return $this->response->setJSON(['success' => false, 'message' => 'API service not available.']);
            }

            // If search term is provided, try searching for multiple results and aggregate posters
            if ($searchTerm) {
                $title = $searchTerm;
                $results = [];

                if ($type === 'IMDB' && method_exists($apiService, 'searchMovieMultiple')) {
                    $searchRes = $apiService->searchMovieMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } elseif ($type === 'TMDB' || $type === 'MOVIE') {
                    $searchRes = $apiService->searchMovieMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } elseif ($type === 'TV' || $type === 'TVDB') {
                    $searchRes = $apiService->searchTvMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } elseif (method_exists($apiService, 'searchMultiple')) {
                    $searchRes = $apiService->searchMultiple($title, null, 10);
                    $results = $searchRes['results'] ?? [];
                } else {
                    $apiData = $apiService->searchMedia($title, null);
                    if ($apiData) $results[] = $apiData;
                }

                if (empty($results)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Could not find media on API.']);
                }

                $posters = [];
                foreach ($results as $res) {
                    $resId = null;
                    if ($type === 'IMDB') {
                        $resId = $res['imdb_id'] ?? null;
                    } elseif ($type === 'TMDB' || $type === 'TVDB' || $type === 'MOVIE' || $type === 'TV') {
                        $resId = $res['tmdb_id'] ?? $res['tvdb_id'] ?? null;
                    } elseif ($type === 'IGDB' || $type === 'GAME') {
                        $resId = $res['igdb_id'] ?? $res['id'] ?? null;
                    } elseif ($type === 'MusicBrainz' || $type === 'MUSIC') {
                        $resId = $res['mbid'] ?? null;
                    }

                    if ($resId) {
                        if (method_exists($apiService, 'getPosters')) {
                            $resPosters = $apiService->getPosters($resId, $type, 5);
                            foreach ($resPosters as $rp) {
                                $rp['label'] = ($res['title'] ?? '') . ' (' . ($res['year'] ?? '') . ')';
                                $posters[] = $rp;
                            }
                        } elseif (!empty($res['poster_url'])) {
                            $posters[] = [
                                'url' => $res['poster_url'],
                                'thumbnail' => $res['poster_url'],
                                'width' => null,
                                'height' => null,
                                'label' => ($res['title'] ?? '') . ' (' . ($res['year'] ?? '') . ')'
                            ];
                        }
                    }
                }
            } else {
                // If no search term and no ID in notes, try searching with current title
                if (!$id) {
                    $title = $media['title'] ?: $media['o_title'];
                    if (!$title) {
                        return $this->response->setJSON(['success' => false, 'message' => 'No ID found and no title available for search.']);
                    }

                    $apiData = ($type === 'TVDB' && method_exists($apiService, 'searchTv')) ? $apiService->searchTv($title, $media['year']) : $apiService->searchMedia($title, $media['year']);
                    if (!$apiData) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Could not find media on API.']);
                    }
                    
                    if ($type === 'IMDB') {
                        $id = $apiData['imdb_id'] ?? null;
                    } elseif ($type === 'TMDB' || $type === 'TVDB' || $type === 'MOVIE' || $type === 'TV') {
                        $id = $apiData['tmdb_id'] ?? $apiData['tvdb_id'] ?? null;
                    } elseif ($type === 'IGDB' || $type === 'GAME') {
                        $id = $apiData['igdb_id'] ?? null;
                    } elseif ($type === 'MusicBrainz' || $type === 'MUSIC') {
                        $id = $apiData['mbid'] ?? null;
                    }
                }

                if (!$id) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Could not resolve media ID.']);
                }

                // Get multiple posters
                if (method_exists($apiService, 'getPosters')) {
                    $posters = $apiService->getPosters($id, $type, 15);
                } else {
                    $details = ($type === 'TVDB' && method_exists($apiService, 'getTvDetails')) ? $apiService->getTvDetails($id) : $apiService->getMediaDetails($id);
                    if ($details && !empty($details['poster_url'])) {
                        $posters[] = [
                            'url' => $details['poster_url'],
                            'thumbnail' => $details['poster_url'],
                            'width' => null,
                            'height' => null
                        ];
                    }
                }
            }

            // Calculate dimensions if null
            if (!empty($posters)) {
                helper('app');
                foreach ($posters as &$poster) {
                    if ($poster['width'] === null || $poster['height'] === null) {
                        [$width, $height] = get_image_dimensions($poster['url']);
                        $poster['width'] = $width;
                        $poster['height'] = $height;
                    }
                }
            }

            if (empty($posters)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No posters found from ' . $type]);
            }

            return $this->response->setJSON([
                'success' => true,
                'posters' => $posters,
                'source' => $type,
                'message' => 'Found ' . count($posters) . ' poster(s) from ' . $type
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Update tags for media (AJAX)
     */
    public function updateMediaTags($mediaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $media = $this->mediaModel->find($mediaId);

        if (!$media) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Media not found'
            ]);
        }

        $tagIds = $this->request->getPost('tag_ids');

        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        // Filter out invalid values
        $tagIds = array_filter($tagIds, function($id) {
            return is_numeric($id) && $id > 0;
        });

        $tagModel = new \App\Models\TagModel();
        if ($tagModel->setTagsForMedia($mediaId, $tagIds)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Tags updated successfully'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to update tags'
        ]);
    }

    /**
     * Update only the poster for media (from URL or upload)
     */
    public function updatePoster($mediaId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $media = $this->mediaModel->find($mediaId);

        if (!$media) {
            return $this->response->setJSON(['success' => false, 'message' => 'Media not found']);
        }

        try {
            $posterUrl = $this->request->getPost('poster_url');
            $posterFile = $this->request->getFile('poster_file');
            $clearPoster = $this->request->getPost('clear_poster');

            // Option 1: Clear poster
            if ($clearPoster === 'true' || $clearPoster === '1') {
                $this->mediaModel->clearPosterForMedia($mediaId);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Poster cleared successfully',
                    'poster_url' => null
                ]);
            }

            $imageData = null;

            // Option 2: Upload from file
            if ($posterFile && $posterFile->isValid() && !$posterFile->hasMoved()) {
                $imageData = $this->validateAndNormalizePosterUpload($posterFile);

                if ($imageData === null) {
                    log_message('debug', 'Invalid file type for poster update: ' . $posterFile->getClientName());
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid file type. Please upload a JPG, PNG, or GIF image.'
                    ]);
                }
            }
            // Option 3: Download from URL
            else if (!empty($posterUrl)) {
                // Determine which service to use for downloading
                $mediaType = $this->request->getPost('media_type');
                log_message('debug', 'Update poster from URL: ' . $posterUrl . ' (Requested Type: ' . ($mediaType ?: 'None') . ')');
                
                if (empty($mediaType)) {
                    // Try to guess from notes if not provided
                    $notes = $media['notes'] ?? '';
                    if (get_imdb_id_from_notes($notes)) {
                        $mediaType = 'IMDB';
                    } elseif (get_tmdb_id_from_notes($notes)) {
                        $mediaType = 'TMDB';
                    } elseif (get_igdb_id_from_notes($notes)) {
                        $mediaType = 'IGDB';
                    } elseif (get_mbid_from_notes($notes)) {
                        $mediaType = 'MusicBrainz';
                    } elseif (get_tvdb_id_from_notes($notes)) {
                        $mediaType = 'TVDB';
                    } else {
                        $mediaType = 'IMDB';
                    }
                    log_message('debug', 'Guessed media type for poster download: ' . $mediaType);
                }

                $apiService = \App\Libraries\ApiServiceFactory::create($mediaType);

                if ($apiService && method_exists($apiService, 'downloadPoster')) {
                    log_message('debug', 'Using API service ' . $mediaType . ' to download poster.');
                    $imageData = $apiService->downloadPoster($posterUrl, $mediaId);
                } 
                
                // Fallback to generic downloader if API downloader failed or doesn't exist
                if (!$imageData) {
                    log_message('debug', 'Using generic remote downloader for poster.');
                    $imageData = $this->downloadRemotePoster($posterUrl);
                }

                if (!$imageData) {
                    log_message('error', 'All download attempts failed for poster URL: ' . $posterUrl);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to download poster from URL'
                    ]);
                }
            }
            else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No poster URL or file provided'
                ]);
            }

            // Store the poster
            if ($imageData) {
                log_message('debug', 'Storing poster for media: ' . $mediaId . ' (Size: ' . strlen($imageData) . ' bytes)');
                $success = $this->mediaModel->storePosterForMedia($mediaId, $imageData);

                if ($success) {
                    log_message('debug', 'Poster stored successfully for media: ' . $mediaId);
                    $updatedMedia = $this->mediaModel->find($mediaId);
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Poster updated successfully',
                        'poster_url' => base_url('media/poster/' . $mediaId) . '?v=' . urlencode($updatedMedia['poster_md5'])
                    ]);
                } else {
                    log_message('error', 'Failed to store poster in database for media: ' . $mediaId);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to store poster'
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'No valid image data'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error updating poster: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Validate an uploaded poster file and return normalized image bytes,
     * or null if the file isn't a genuine, supported image.
     *
     * The MIME type must be server-detected (fileinfo, via
     * UploadedFile::getMimeType()) as an allowed image type -- the
     * client-supplied MIME type and filename extension are attacker
     * controlled and are never sufficient on their own to accept a file.
     * Non-JPEG images are re-encoded through GD; if that decode fails
     * (i.e. the bytes aren't actually a valid image despite the detected
     * type), the upload is rejected rather than stored as-is.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $posterFile
     * @return string|null
     */
    private function validateAndNormalizePosterUpload($posterFile): ?string
    {
        $validMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        $mimeType = $posterFile->getMimeType();
        if (!in_array($mimeType, $validMimeTypes, true)) {
            log_message('debug', 'Rejected poster upload: unsupported detected MIME type ' . $mimeType);
            return null;
        }

        $imageData = file_get_contents($posterFile->getTempName());
        if ($imageData === false || $imageData === '') {
            return null;
        }

        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            return $imageData;
        }

        if (!function_exists('imagecreatefromstring')) {
            log_message('debug', 'GD extension missing, skipping conversion to JPEG.');
            return $imageData;
        }

        $image = @imagecreatefromstring($imageData);
        if ($image === false) {
            log_message('debug', 'Rejected poster upload: detected MIME type ' . $mimeType . ' but image data failed to decode.');
            return null;
        }

        ob_start();
        imagejpeg($image, null, 90);
        $normalized = ob_get_clean();
        imagedestroy($image);

        return $normalized ?: null;
    }

    /**
     * Generic remote poster downloader
     *
     * @param string $url
     * @return string|null
     */
    private function downloadRemotePoster(string $url): ?string
    {
        log_message('debug', 'downloadRemotePoster: Attempting to download from ' . $url);

        // fetch_remote_image() validates the host (and every redirect hop)
        // resolves only to a public address, to prevent SSRF via
        // user-supplied poster URLs targeting internal/loopback services.
        return fetch_remote_image($url, 'MediaOrganizer/1.0');
    }
}