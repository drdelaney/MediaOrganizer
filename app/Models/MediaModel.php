<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model
{
    protected $table = 'movies';
    protected $primaryKey = 'movie_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'number', 'collection_id', 'volume_id', 'medium_id', 'ratio_id', 'vcodec_id', 'poster_md5',
        'loaned', 'seen', 'rating', 'color', 'cond', 'layers', 'region',
        'media_num', 'runtime', 'year', 'width', 'height', 'barcode',
        'o_title', 'title', 'director', 'screenplay', 'cameraman',
        'o_site', 'site', 'trailer', 'country', 'genre', 'studio',
        'classification', 'cast', 'plot', 'notes', 'image', 'created', 'updated'
    ];

    /**
     * Get the next available unique number for movies.number
     */
    public function getNextAvailableNumber(): int
    {
        $row = $this->db->table($this->table)
            ->select('IFNULL(MAX(number), 0) AS maxnum', false)
            ->get()
            ->getRowArray();
        $max = isset($row['maxnum']) ? (int)$row['maxnum'] : 0;
        return $max + 1;
    }

    /**
     * Clear poster reference for media (does not delete shared poster data row)
     */
    public function clearPosterForMedia($mediaId)
    {
        $updateData = [
            'poster_md5' => null,
            'image' => null,
            'updated' => gmdate('Y-m-d H:i:s')
        ];
        return $this->update($mediaId, $updateData);
    }

    /**
     * Get single media entry with all related data
     */
    public function getMediaWithAllDetails($mediaId)
    {
        $media = $this->db->table('movies m')
            ->select('m.*, 
                      c.name as collection_name,
                      v.name as volume_name,
                      vc.name as vcodec_name,
                      r.name as ratio_name,
                      med.name as medium_name')
            ->join('collections c', 'm.collection_id = c.collection_id', 'left')
            ->join('volumes v', 'm.volume_id = v.volume_id', 'left')
            ->join('vcodecs vc', 'm.vcodec_id = vc.vcodec_id', 'left')
            ->join('ratios r', 'm.ratio_id = r.ratio_id', 'left')
            ->join('media med', 'm.medium_id = med.medium_id', 'left')
            ->where('m.movie_id', $mediaId)
            ->get()
            ->getRowArray();

        return $media;
    }

    public function getRandomUnseenMedia()
    {
        $wishlistTag = $this->db->table('tags')
            ->where('LOWER(name)', 'wishlist')
            ->get()
            ->getRowArray();

        $builder = $this->db->table('movies m')
            ->where('m.seen', 0);

        if ($wishlistTag) {
            $subQuery = $this->db->table('movie_tag')
                ->select('movie_id')
                ->where('tag_id', $wishlistTag['tag_id']);
            $builder->whereNotIn('m.movie_id', $subQuery);
        }

        // Use different random order based on database driver
        if ($this->db->DBDriver === 'SQLite3') {
            $builder->orderBy('RANDOM()');
        } else {
            $builder->orderBy('RAND()');
        }

        return $builder->limit(1)->get()->getRowArray();
    }

    /**
     * Get media with related data
     * Uses fuzzy matching for title/o_title fields
     */
    public function getMediaWithDetails($search = null, $searchField = 'title', $limit = 50, $offset = 0, $tagId = null, $excludeWishlist = false, $sortBy = 'created', $sortOrder = 'DESC', $wishlistOnly = false, $mediumId = null)
    {
        $builder = $this->db->table('movies m')
            ->select('m.*, 
                      c.name as collection_name,
                      v.name as volume_name,
                      vc.name as vcodec_name,
                      med.name as medium_name')
            ->join('collections c', 'm.collection_id = c.collection_id', 'left')
            ->join('volumes v', 'm.volume_id = v.volume_id', 'left')
            ->join('vcodecs vc', 'm.vcodec_id = vc.vcodec_id', 'left')
            ->join('media med', 'm.medium_id = med.medium_id', 'left');

        // Apply medium_id filter if provided
        if ($mediumId !== null && $mediumId !== '') {
            $builder->where('m.medium_id', $mediumId);
        }

        // Log the query if needed for debugging
        // log_message('debug', 'Sorting by: ' . print_r($sortBy, true));

        if ($tagId) {
            $builder->join('movie_tag mt', 'm.movie_id = mt.movie_id')
                ->where('mt.tag_id', $tagId);
        } elseif ($wishlistOnly || $excludeWishlist) {
            $wishlistTag = $this->db->table('tags')
                ->where('LOWER(name)', 'wishlist')
                ->get()
                ->getRowArray();
            
            if ($wishlistTag) {
                if ($wishlistOnly) {
                    $builder->join('movie_tag mt', 'm.movie_id = mt.movie_id')
                        ->where('mt.tag_id', $wishlistTag['tag_id']);
                } else {
                    $subQuery = $this->db->table('movie_tag')
                        ->select('movie_id')
                        ->where('tag_id', $wishlistTag['tag_id']);
                    $builder->whereNotIn('m.movie_id', $subQuery);
                }
            } elseif ($wishlistOnly) {
                // If wishlist tag doesn't exist, return no results for wishlistOnly
                $builder->where('1=0', null, false);
            }
        }

        if ($search) {
            // For title searches, we'll use fuzzy matching in PHP after fetching candidates
            // For other fields, use regular LIKE search
            if ($searchField === 'title' || $searchField === 'o_title') {
                // Get normalized search term
                $normalizedSearch = normalize_title_for_search($search);
                
                if ($normalizedSearch !== '') {
                    // Extract key words for initial SQL filtering
                    $searchWords = explode(' ', $normalizedSearch);
                    $primaryWord = $searchWords[0] ?? '';
                    
                    if ($primaryWord !== '') {
                        // Broad initial filter to reduce dataset
                        $builder->groupStart()
                            ->like('m.title', $primaryWord)
                            ->orLike('m.o_title', $primaryWord)
                            ->groupEnd();
                    }
                }
            } elseif ($searchField === 'all') {
                $builder->groupStart()
                    ->like('m.title', $search)
                    ->orLike('m.o_title', $search)
                    ->orLike('m.director', $search)
                    ->orLike('m.genre', $search)
                    ->orLike('m.country', $search)
                    ->orLike('m.studio', $search)
                    ->orLike('m.barcode', $search)
                    ->groupEnd();
            } else {
                // Non-title fields use regular LIKE
                $allowedFields = ['director', 'genre', 'country', 'studio', 'barcode', 'notes', 'year', 'movie_id'];
                if (in_array($searchField, $allowedFields)) {
                    if ($searchField === 'movie_id') {
                        $builder->where('m.movie_id', $search);
                    } else {
                        $builder->like('m.' . $searchField, $search);
                    }
                } else {
                    // Default to title if field not allowed
                    $builder->like('m.title', $search);
                }
            }
        }

        // Apply sorting
        if (is_array($sortBy)) {
            foreach ($sortBy as $field => $order) {
                if ($field === 'title') {
                    $builder->orderBy('COALESCE(NULLIF(m.title, ""), m.o_title)', $order, false);
                } elseif ($field === 'type') {
                    $builder->orderBy('m.medium_id', $order);
                } elseif ($field === 'movie_id') {
                    $builder->orderBy('m.movie_id', $order);
                } else {
                    $allowedSortFields = ['movie_id', 'title', 'o_title', 'year', 'director', 'rating', 'runtime', 'created', 'updated'];
                    if (in_array($field, $allowedSortFields)) {
                        $builder->orderBy('m.' . $field, $order);
                    }
                }
            }
        } elseif ($sortBy === 'title') {
            // Sort by title, falling back to o_title if title is empty
            $builder->orderBy('COALESCE(NULLIF(m.title, ""), m.o_title)', $sortOrder, false);
        } elseif ($sortBy === 'type') {
            $builder->orderBy('m.medium_id', $sortOrder);
        } else {
            if ($sortBy === 'movie_id') {
                $builder->orderBy('m.movie_id', $sortOrder);
            } else {
                $allowedSortFields = ['movie_id', 'title', 'o_title', 'year', 'director', 'rating', 'runtime', 'created', 'updated'];
                if (in_array($sortBy, $allowedSortFields)) {
                    $builder->orderBy('m.' . $sortBy, $sortOrder);
                }
            }
        }

        // Get all candidates (no limit yet if doing fuzzy search on titles)
        $needsFuzzyFilter = $search && ($searchField === 'title' || $searchField === 'o_title');
        
        if (!$needsFuzzyFilter && $limit > 0) {
            $builder->limit($limit, $offset);
        }
        
        $results = $builder->get()->getResultArray();
        
        // Apply fuzzy filtering if searching by title
        if ($needsFuzzyFilter && $search) {
            $normalizedSearch = normalize_title_for_search($search);
            $filtered = [];
            
            foreach ($results as $media) {
                $mediaTitleNorm = normalize_title_for_search($media['title']);
                $mediaOTitleNorm = normalize_title_for_search($media['o_title']);
                
                // Check if normalized search is contained in normalized titles
                if (strpos($mediaTitleNorm, $normalizedSearch) !== false ||
                    strpos($mediaOTitleNorm, $normalizedSearch) !== false) {
                    $filtered[] = $media;
                }
            }
            
            $results = $filtered;
            
            // Apply limit and offset after filtering
            if ($limit > 0) {
                $results = array_slice($results, $offset, $limit);
            }
        }

        return $results;
    }

    /**
     * Count total media (with search filter)
     * Uses fuzzy matching for title/o_title fields
     */
    public function countMedia($search = null, $searchField = 'title', $tagId = null, $excludeWishlist = false)
    {
        if ($search && ($searchField === 'title' || $searchField === 'o_title')) {
            // For title searches, we need to get all candidates and filter in PHP
            $normalizedSearch = normalize_title_for_search($search);
            
            if ($normalizedSearch === '') {
                return 0;
            }
            
            // Extract key word for initial filtering
            $searchWords = explode(' ', $normalizedSearch);
            $primaryWord = $searchWords[0] ?? '';
            
            $builder = $this->db->table('movies m')
                ->select('m.title, m.o_title, m.movie_id');
            
            if ($tagId) {
                $builder->join('movie_tag mt', 'm.movie_id = mt.movie_id')
                    ->where('mt.tag_id', $tagId);
            } elseif ($excludeWishlist) {
                // If no tag is explicitly selected and exclusion is requested, exclude media with the 'wishlist' tag
                $wishlistTag = $this->db->table('tags')
                    ->where('LOWER(name)', 'wishlist')
                    ->get()
                    ->getRowArray();
                
                if ($wishlistTag) {
                    $subQuery = $this->db->table('movie_tag')
                        ->select('movie_id')
                        ->where('tag_id', $wishlistTag['tag_id']);
                    $builder->whereNotIn('m.movie_id', $subQuery);
                }
            }

            if ($primaryWord !== '') {
                $builder->groupStart()
                    ->like('m.title', $primaryWord)
                    ->orLike('m.o_title', $primaryWord)
                    ->groupEnd();
            }
            
            $results = $builder->get()->getResultArray();
            
            // Filter using normalized comparison
            $count = 0;
            foreach ($results as $media) {
                $mediaTitleNorm = normalize_title_for_search($media['title']);
                $mediaOTitleNorm = normalize_title_for_search($media['o_title']);
                
                if (strpos($mediaTitleNorm, $normalizedSearch) !== false ||
                    strpos($mediaOTitleNorm, $normalizedSearch) !== false) {
                    $count++;
                }
            }
            
            return $count;
        }
        
        // Non-title searches use regular LIKE
        $builder = $this->db->table('movies m');

        if ($tagId) {
            $builder->join('movie_tag mt', 'm.movie_id = mt.movie_id')
                ->where('mt.tag_id', $tagId);
        } elseif ($excludeWishlist) {
            // If no tag is explicitly selected and exclusion is requested, exclude media with the 'wishlist' tag
            $wishlistTag = $this->db->table('tags')
                ->where('LOWER(name)', 'wishlist')
                ->get()
                ->getRowArray();
            
            if ($wishlistTag) {
                $subQuery = $this->db->table('movie_tag')
                    ->select('movie_id')
                    ->where('tag_id', $wishlistTag['tag_id']);
                $builder->whereNotIn('m.movie_id', $subQuery);
            }
        }

        if ($search) {
            if ($searchField === 'all') {
                $builder->groupStart()
                    ->like('m.title', $search)
                    ->orLike('m.o_title', $search)
                    ->orLike('m.director', $search)
                    ->orLike('m.genre', $search)
                    ->orLike('m.country', $search)
                    ->orLike('m.studio', $search)
                    ->groupEnd();
            } else {
                $allowedFields = ['director', 'genre', 'country', 'studio', 'barcode', 'notes', 'year', 'movie_id'];
                if (in_array($searchField, $allowedFields)) {
                    if ($searchField === 'movie_id') {
                        $builder->where('m.movie_id', $search);
                    } else {
                        $builder->like('m.' . $searchField, $search);
                    }
                } else {
                    // Default to title if field not allowed
                    $builder->like('m.title', $search);
                }
            }
        }

        return $builder->countAllResults();
    }

    /**
     * Toggle seen status for media
     */
    public function toggleSeenStatus($mediaId, $seenStatus)
    {
        return $this->update($mediaId, [
            'seen' => $seenStatus ? 1 : 0,
            'updated' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all media types for dropdown
     */
    public function getMediaTypes()
    {
        return $this->db->table('media')
            ->select('medium_id, name')
            ->orderBy('name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all collections for dropdown
     */
    public function getCollections()
    {
        return $this->db->table('collections')
            ->select('collection_id, name')
            ->orderBy('name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all volumes for dropdown
     */
    public function getVolumes()
    {
        return $this->db->table('volumes')
            ->select('volume_id, name')
            ->orderBy('name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all video codecs for dropdown
     */
    public function getVideoCodecs()
    {
        return $this->db->table('vcodecs')
            ->select('vcodec_id, name')
            ->orderBy('name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all ratios for dropdown
     */
    public function getRatios()
    {
        return $this->db->table('ratios')
            ->select('ratio_id, name')
            ->orderBy('name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all tags for dropdown
     */
    public function getTags()
    {
        return $this->db->table('tags')
            ->select('tag_id, name')
            ->orderBy('name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get tag by name (case-insensitive)
     */
    public function getTagByName($name)
    {
        return $this->db->table('tags')
            ->where('LOWER(name)', strtolower($name))
            ->get()
            ->getRowArray();
    }

    /**
     * Update media with API data and poster
     */
    public function updateWithApiData($mediaId, $apiData, $posterData = null)
    {
        // Get current movie data to preserve existing notes
        $currentMedia = $this->find($mediaId);
        $existingNotes = $currentMedia['notes'] ?? null;
        
        // Extract TMDB/IMDB IDs from API data if available
        $tmdbId = $apiData['tmdb_id'] ?? null;
        $imdbId = $apiData['imdb_id'] ?? null;
        
        // Update notes with external IDs using helper function
        $updatedNotes = add_external_ids_to_notes($existingNotes, $tmdbId, $imdbId);
        
        $updateData = [
            'title' => $apiData['title'] ?: null,
            'o_title' => $apiData['o_title'] ?: null,
            'director' => $apiData['director'] ?: null,
            'year' => $apiData['year'] ?: null,
            'runtime' => $apiData['runtime'] ?: null,
            'genre' => $apiData['genre'] ?: null,
            'country' => $apiData['country'] ?: null,
            'studio' => $apiData['studio'] ?: null,
            'plot' => $apiData['plot'] ?: null,
            'site' => $apiData['site'] ?: null,
            'o_site' => $apiData['o_site'] ?: null,
            'classification' => $apiData['classification'] ?: null,
            'rating' => $apiData['rating'] ?: null,
            'notes' => $updatedNotes,
            'updated' => date('Y-m-d H:i:s')
        ];

        if ($posterData) {
            $posterMd5 = md5($posterData);
            
            // Store poster in posters table
            $this->storePosterData($posterMd5, $posterData);
            
            // Update media with poster reference
            $updateData['poster_md5'] = $posterMd5;
            $updateData['image'] = "poster_{$mediaId}_{$posterMd5}.jpg"; // Keep for compatibility
        }

        return $this->update($mediaId, $updateData);
    }

    /**
     * Store poster data in posters table
     */
    private function storePosterData($md5sum, $posterData)
    {
        // Check if poster already exists
        $existingPoster = $this->db->table('posters')
            ->where('md5sum', $md5sum)
            ->get()
            ->getRowArray();

        if (!$existingPoster) {
            // Insert new poster
            $this->db->table('posters')->insert([
                'md5sum' => $md5sum,
                'data' => $posterData
            ]);
            
            log_message('info', 'Poster stored with MD5: ' . $md5sum);
        } else {
            log_message('info', 'Poster already exists with MD5: ' . $md5sum);
        }
    }

    /**
     * Find a media by exact barcode
     */
    public function findByBarcode(string $barcode): ?array
    {
        if ($barcode === '') return null;
        return $this->db->table('movies m')
            ->select('m.*, 
                      c.name as collection_name,
                      v.name as volume_name,
                      vc.name as vcodec_name,
                      r.name as ratio_name,
                      med.name as medium_name')
            ->join('collections c', 'm.collection_id = c.collection_id', 'left')
            ->join('volumes v', 'm.volume_id = v.volume_id', 'left')
            ->join('vcodecs vc', 'm.vcodec_id = vc.vcodec_id', 'left')
            ->join('ratios r', 'm.ratio_id = r.ratio_id', 'left')
            ->join('media med', 'm.medium_id = med.medium_id', 'left')
            ->where('m.barcode', $barcode)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * Find media by title using fuzzy/normalized matching
     * Handles special characters, punctuation, "the" placement, etc.
     * 
     * @param string $title The title to search for
     * @param int|null $year Optional year to narrow results
     * @return array Array of matching media
     */
    public function findByTitleFuzzy(string $title, ?int $year = null): array
    {
        if ($title === '') return [];
        
        // Get the normalized search term
        $normalizedSearch = normalize_title_for_search($title);
        
        if ($normalizedSearch === '') return [];
        
        // Get all media (we'll filter in PHP for better fuzzy matching)
        // Start with a broad LIKE search to reduce the dataset
        $builder = $this->db->table('movies m')
            ->select('m.*, 
                      c.name as collection_name,
                      v.name as volume_name,
                      vc.name as vcodec_name,
                      r.name as ratio_name,
                      med.name as medium_name')
            ->join('collections c', 'm.collection_id = c.collection_id', 'left')
            ->join('volumes v', 'm.volume_id = v.volume_id', 'left')
            ->join('vcodecs vc', 'm.vcodec_id = vc.vcodec_id', 'left')
            ->join('ratios r', 'm.ratio_id = r.ratio_id', 'left')
            ->join('media med', 'm.medium_id = med.medium_id', 'left');
        
        // Extract key words from normalized title for initial filtering
        $searchWords = explode(' ', $normalizedSearch);
        $primaryWord = $searchWords[0] ?? '';
        
        if ($primaryWord !== '') {
            $builder->groupStart()
                ->like('m.title', $primaryWord)
                ->orLike('m.o_title', $primaryWord)
                ->groupEnd();
        }
        
        // Add year filter if provided
        if ($year !== null) {
            $builder->where('m.year', $year);
        }
        
        $candidates = $builder->get()->getResultArray();
        
        // Now filter using normalized comparison
        $matches = [];
        foreach ($candidates as $media) {
            $mediaTitleNorm = normalize_title_for_search($media['title']);
            $mediaOTitleNorm = normalize_title_for_search($media['o_title']);
            
            if ($mediaTitleNorm === $normalizedSearch || $mediaOTitleNorm === $normalizedSearch) {
                $matches[] = $media;
            }
        }
        
        return $matches;
    }

    /**
     * Search media with fuzzy title matching
     * Returns first match or null
     * 
     * @param string $title The title to search for
     * @param int|null $year Optional year to narrow results
     * @return array|null The first matching media or null
     */
    public function searchByTitleFuzzy(string $title, ?int $year = null): ?array
    {
        $matches = $this->findByTitleFuzzy($title, $year);
        return !empty($matches) ? $matches[0] : null;
    }

    /**
     * Get poster data for media
     */
    public function getPosterData($mediaId)
    {
        $result = $this->db->table('movies m')
            ->select('p.data, p.md5sum')
            ->join('posters p', 'm.poster_md5 = p.md5sum', 'left')
            ->where('m.movie_id', $mediaId)
            ->get()
            ->getRowArray();
        
        if ($result && $result['data']) {
            return [
                'data' => $result['data'],
                'md5sum' => $result['md5sum']
            ];
        }
        
        return null;
    }

    /**
     * Store poster for a media using the posters table
     */
    public function storePosterForMedia($mediaId, $imageData)
    {
        if (!$imageData) {
            return false;
        }

        $posterMd5 = md5($imageData);
        
        // Store in posters table
        $this->storePosterData($posterMd5, $imageData);
        
        // Update media record
        $updateData = [
            'poster_md5' => $posterMd5,
            'image' => "poster_{$mediaId}_{$posterMd5}.jpg",
            'updated' => date('Y-m-d H:i:s')
        ];

        return $this->update($mediaId, $updateData);
    }
}
