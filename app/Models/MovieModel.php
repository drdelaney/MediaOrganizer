<?php

namespace App\Models;

use CodeIgniter\Model;

class MovieModel extends Model
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
     * Clear poster reference for a movie (does not delete shared poster data row)
     */
    public function clearPosterForMovie($movieId)
    {
        $updateData = [
            'poster_md5' => null,
            'image' => null,
            'updated' => date('Y-m-d H:i:s')
        ];
        return $this->update($movieId, $updateData);
    }

    /**
     * Get single movie with all related data
     */
    public function getMovieWithAllDetails($movieId)
    {
        $movie = $this->db->table('movies m')
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
            ->where('m.movie_id', $movieId)
            ->get()
            ->getRowArray();

        return $movie;
    }

    /**
     * Get movies with related data
     */
    public function getMoviesWithDetails($search = null, $searchField = 'title', $limit = 50, $offset = 0)
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

        if ($search) {
            if ($searchField === 'all') {
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
                $builder->like('m.' . $searchField, $search);
            }
        }

        $builder->orderBy('m.created', 'DESC');
        
        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Count total movies (with search filter)
     */
    public function countMovies($search = null, $searchField = 'title')
    {
        $builder = $this->db->table('movies m');

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
                $builder->like('m.' . $searchField, $search);
            }
        }

        return $builder->countAllResults();
    }

    /**
     * Toggle seen status for a movie
     */
    public function toggleSeenStatus($movieId, $seenStatus)
    {
        return $this->update($movieId, [
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
     * Update movie with API data and poster
     */
    public function updateWithApiData($movieId, $apiData, $posterData = null)
    {
        // Get current movie data to preserve existing notes
        $currentMovie = $this->find($movieId);
        $existingNotes = $currentMovie['notes'] ?? null;
        
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
            
            // Update movie with poster reference
            $updateData['poster_md5'] = $posterMd5;
            $updateData['image'] = "poster_{$movieId}_{$posterMd5}.jpg"; // Keep for compatibility
        }

        return $this->update($movieId, $updateData);
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
     * Find a movie by exact barcode
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
     * Get poster data for a movie
     */
    public function getPosterData($movieId)
    {
        $result = $this->db->table('movies m')
            ->select('p.data, p.md5sum')
            ->join('posters p', 'm.poster_md5 = p.md5sum', 'left')
            ->where('m.movie_id', $movieId)
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
     * Store poster for a movie using the posters table
     */
    public function storePosterForMovie($movieId, $imageData)
    {
        if (!$imageData) {
            return false;
        }

        $posterMd5 = md5($imageData);
        
        // Store in posters table
        $this->storePosterData($posterMd5, $imageData);
        
        // Update movie record
        $updateData = [
            'poster_md5' => $posterMd5,
            'image' => "poster_{$movieId}_{$posterMd5}.jpg",
            'updated' => date('Y-m-d H:i:s')
        ];

        return $this->update($movieId, $updateData);
    }
}
