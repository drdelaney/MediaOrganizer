<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;

class MovieApiService
{
    private $tmdbApiKey;
    private $userAgent;
    private $client;

    public function __construct()
    {
        $configModel = new \App\Models\ConfigurationModel();
        
        $this->tmdbApiKey = $configModel->getParam('TMDB_API_KEY');
        $this->userAgent = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');

        $this->client = \Config\Services::curlrequest([
            'baseURI' => 'https://api.themoviedb.org/3/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/json',
            ],
        ]);
    }

    /**
     * Check if API is available and configured
     */
    public function isApiAvailable()
    {
        $available = !empty($this->tmdbApiKey);
        error_log('TMDB API availability check: ' . ($available ? 'AVAILABLE' : 'NOT AVAILABLE'));
        return $available;
    }

    /**
     * Search for a movie by title and optional year
     */
    public function searchMedia($title, $year = null)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }

        $params = [
            'api_key' => $this->tmdbApiKey,
            'query' => $title,
            'language' => 'en-US'
        ];

        if ($year) {
            $params['year'] = $year;
        }

        try {
            log_message('info', 'TMDB API: Searching for movie: ' . $title . ($year ? ' (' . $year . ')' : ''));

            $response = $this->client->get('search/movie?' . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);

            log_message('info', 'TMDB API: Search response received');

            if (isset($data['results']) && !empty($data['results'])) {
                // Get detailed info for the first result
                $movieId = $data['results'][0]['id'];
                log_message('info', 'TMDB API: Found movie ID: ' . $movieId);
                return $this->getMediaDetails($movieId);
            }

            log_message('warning', 'TMDB API: No results found for: ' . $title);
            return null;

        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error: ' . $e->getMessage());
            throw new \Exception('Failed to fetch movie data from TMDB: ' . $e->getMessage());
        }
    }

    /**
     * Search for movies by title and return multiple results
     *
     * @param string $title The title to search for
     * @param int|null $year Optional year to filter by
     * @param int $limit Maximum number of results to return (default 10)
     * @param int $page Page number for pagination (default 1)
     * @return array Array with 'results', 'total_results', 'total_pages', 'page'
     */
    public function searchMovieMultiple($title, $year = null, $limit = 20, $page = 1)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }

        // Parse year tag if present (handle year:YYYY or year:"YYYY")
        if (preg_match('/year:\s*"?(\d{4})"?/i', $title, $matches)) {
            $year = (int)$matches[1];
            $title = str_replace($matches[0], '', $title);
        }
        $title = trim($title);

        if (empty($title) && $year) {
            $title = (string)$year;
        }

        $params = [
            'api_key' => $this->tmdbApiKey,
            'query' => $title,
            'language' => 'en-US',
            'page' => $page
        ];

        if ($year) {
            $params['year'] = $year;
        }

        try {
            log_message('info', 'TMDB API: Searching for multiple movies: ' . $title . ($year ? ' (' . $year . ')' : '') . ' (page ' . $page . ')');

            $response = $this->client->get('search/movie?' . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);

            if (!isset($data['results']) || empty($data['results'])) {
                log_message('warning', 'TMDB API: No results found for: ' . $title);
                return [
                    'results' => [],
                    'total_results' => 0,
                    'total_pages' => 0,
                    'page' => $page
                ];
            }

            $results = [];
            $count = 0;

            foreach ($data['results'] as $movie) {
                if ($count >= $limit) break;

                $results[] = [
                    'tmdb_id' => (string)$movie['id'],
                    'title' => $movie['title'] ?? null,
                    'original_title' => $movie['original_title'] ?? null,
                    'year' => $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : null,
                    'poster_url' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w185' . $movie['poster_path'] : null,
                    'overview' => $movie['overview'] ?? null,
                    'type' => LookupRegistry::isEnabled('TMDB') ? 'TMDB' : 'IMDB'
                ];
                $count++;
            }

            log_message('info', 'TMDB API: Found ' . count($results) . ' movie results (page ' . $page . ' of ' . ($data['total_pages'] ?? 1) . ')');

            return [
                'results' => $results,
                'total_results' => $data['total_results'] ?? count($results),
                'total_pages' => $data['total_pages'] ?? 1,
                'page' => $page
            ];

        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error: ' . $e->getMessage());
            throw new \Exception('Failed to fetch movie data from TMDB: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed movie information by TMDB ID
     */
    public function getMediaDetails($tmdbId)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }

        try {
            log_message('info', 'TMDB API: Getting movie details for ID: ' . $tmdbId);

            $params = [
                'api_key' => $this->tmdbApiKey,
                'language' => 'en-US'
            ];

            $response = $this->client->get("movie/{$tmdbId}?" . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }

            $movieData = json_decode($response->getBody(), true);
            
            log_message('info', 'TMDB API: Movie details received for: ' . ($movieData['title'] ?? 'Unknown'));

            // Get external IDs (IMDB, etc.)
            $externalIds = $this->getExternalIds($tmdbId, 'IMDB');

            // Format the data for our application
            return [
                'title' => $movieData['title'] ?? null,
                'o_title' => $movieData['original_title'] ?? null,
                'year' => $movieData['release_date'] ? date('Y', strtotime($movieData['release_date'])) : null,
                'runtime' => $movieData['runtime'] ?? null,
                'genre' => $this->formatGenres($movieData['genres'] ?? []),
                'country' => $this->formatCountries($movieData['production_countries'] ?? []),
                'studio' => $this->formatStudios($movieData['production_companies'] ?? []),
                'plot' => $movieData['overview'] ?? null,
                'rating' => $movieData['vote_average'] ? round($movieData['vote_average'] / 2) : null, // Convert 10-point to 5-point scale
                // Per requirement: 'site' should be a link to TMDB. Preserve the original homepage in 'o_site'.
                'site' => 'https://www.themoviedb.org/movie/' . $tmdbId,
                'o_site' => $movieData['homepage'] ?? null,
                'poster_url' => $movieData['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movieData['poster_path'] : null,
                'director' => $this->getDirector($tmdbId),
                'classification' => $this->getClassification($tmdbId),
                'cast' => $this->getCast($tmdbId),
                'tmdb_id' => (string)$tmdbId,
                'imdb_id' => $externalIds['imdb_id'] ?? null,
                'tvdb_id' => $externalIds['tvdb_id'] ?? null,
            ];

        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error getting details: ' . $e->getMessage());
            throw new \Exception('Failed to get movie details: ' . $e->getMessage());
        }
    }

    /**
     * Search for a TV show by title and optional year
     */
    public function searchTv($title, $year = null)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }
        $params = [
            'api_key' => $this->tmdbApiKey,
            'query' => $title,
            'language' => 'en-US'
        ];
        if ($year) {
            // TV search supports first_air_date_year
            $params['first_air_date_year'] = $year;
        }
        try {
            log_message('info', 'TMDB API: Searching for TV: ' . $title . ($year ? ' (' . $year . ')' : ''));
            $response = $this->client->get('search/tv?' . http_build_query($params));
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }
            $data = json_decode($response->getBody(), true);
            if (isset($data['results']) && !empty($data['results'])) {
                $tvId = $data['results'][0]['id'];
                log_message('info', 'TMDB API: Found TV ID: ' . $tvId);
                return $this->getTvDetails($tvId);
            }
            log_message('warning', 'TMDB API: No TV results found for: ' . $title);
            return null;
        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error (TV search): ' . $e->getMessage());
            throw new \Exception('Failed to fetch TV data from TMDB: ' . $e->getMessage());
        }
    }

    /**
     * Search for TV shows by title and return multiple results
     *
     * @param string $title The title to search for
     * @param int|null $year Optional year to filter by
     * @param int $limit Maximum number of results to return (default 10)
     * @param int $page Page number for pagination (default 1)
     * @return array Array with 'results', 'total_results', 'total_pages', 'page'
     */
    public function searchTvMultiple($title, $year = null, $limit = 20, $page = 1)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }

        // Parse year tag if present (handle year:YYYY or year:"YYYY")
        if (preg_match('/year:\s*"?(\d{4})"?/i', $title, $matches)) {
            $year = (int)$matches[1];
            $title = str_replace($matches[0], '', $title);
        }
        $title = trim($title);

        if (empty($title) && $year) {
            $title = (string)$year;
        }

        $params = [
            'api_key' => $this->tmdbApiKey,
            'query' => $title,
            'language' => 'en-US',
            'page' => $page
        ];

        if ($year) {
            $params['first_air_date_year'] = $year;
        }

        try {
            log_message('info', 'TMDB API: Searching for multiple TV shows: ' . $title . ($year ? ' (' . $year . ')' : '') . ' (page ' . $page . ')');

            $response = $this->client->get('search/tv?' . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);

            if (!isset($data['results']) || empty($data['results'])) {
                log_message('warning', 'TMDB API: No TV results found for: ' . $title);
                return [
                    'results' => [],
                    'total_results' => 0,
                    'total_pages' => 0,
                    'page' => $page
                ];
            }

            $results = [];
            $count = 0;

            foreach ($data['results'] as $tv) {
                if ($count >= $limit) break;

                $results[] = [
                    'tmdb_id' => (string)$tv['id'],
                    'title' => $tv['name'] ?? null,
                    'original_title' => $tv['original_name'] ?? null,
                    'year' => $tv['first_air_date'] ? date('Y', strtotime($tv['first_air_date'])) : null,
                    'poster_url' => $tv['poster_path'] ? 'https://image.tmdb.org/t/p/w185' . $tv['poster_path'] : null,
                    'overview' => $tv['overview'] ?? null,
                    'type' => 'TVDB'
                ];
                $count++;
            }

            log_message('info', 'TMDB API: Found ' . count($results) . ' TV results (page ' . $page . ' of ' . ($data['total_pages'] ?? 1) . ')');

            return [
                'results' => $results,
                'total_results' => $data['total_results'] ?? count($results),
                'total_pages' => $data['total_pages'] ?? 1,
                'page' => $page
            ];

        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error (TV search): ' . $e->getMessage());
            throw new \Exception('Failed to fetch TV data from TMDB: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed TV show information by TMDB ID
     */
    public function getTvDetails($tmdbId)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }
        try {
            log_message('info', 'TMDB API: Getting TV details for ID: ' . $tmdbId);
            $params = [
                'api_key' => $this->tmdbApiKey,
                'language' => 'en-US'
            ];
            $response = $this->client->get("tv/{$tmdbId}?" . http_build_query($params));
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }
            $tvData = json_decode($response->getBody(), true);
            log_message('info', 'TMDB API: TV details received for: ' . ($tvData['name'] ?? 'Unknown'));

            // Map fields
            $title = $tvData['name'] ?? null;
            $oTitle = $tvData['original_name'] ?? null;
            $year = !empty($tvData['first_air_date']) ? date('Y', strtotime($tvData['first_air_date'])) : null;
            $runtime = null;
            if (!empty($tvData['episode_run_time']) && is_array($tvData['episode_run_time']) && count($tvData['episode_run_time']) > 0) {
                $runtime = (int)$tvData['episode_run_time'][0];
            }
            $genre = $this->formatGenres($tvData['genres'] ?? []);
            $country = $this->formatCountries($tvData['production_countries'] ?? []);
            if (!$country && !empty($tvData['origin_country'])) {
                // origin_country is array of ISO codes; join as string
                $country = implode(', ', $tvData['origin_country']);
            }
            $studio = $this->formatStudios($tvData['production_companies'] ?? []);
            if (!$studio && !empty($tvData['networks'])) {
                $studio = $tvData['networks'][0]['name'] ?? null;
            }
            $plot = $tvData['overview'] ?? null;
            $rating = isset($tvData['vote_average']) ? round($tvData['vote_average'] / 2) : null;
            $site = 'https://www.themoviedb.org/tv/' . $tmdbId;
            $o_site = $tvData['homepage'] ?? null;
            $poster_url = !empty($tvData['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $tvData['poster_path'] : null;

            // "Director" analogue for TV: first creator name
            $director = null;
            if (!empty($tvData['created_by']) && is_array($tvData['created_by']) && count($tvData['created_by']) > 0) {
                $director = $tvData['created_by'][0]['name'] ?? null;
            }
            $classification = $this->getTvClassification($tmdbId);
            
            // Get external IDs (IMDB, TVDB, etc.)
            $externalIds = $this->getExternalIds($tmdbId, 'tv');

            return [
                'title' => $title,
                'o_title' => $oTitle,
                'year' => $year,
                'runtime' => $runtime,
                'genre' => $genre,
                'country' => $country,
                'studio' => $studio,
                'plot' => $plot,
                'rating' => $rating,
                'site' => $site,
                'o_site' => $o_site,
                'poster_url' => $poster_url,
                'director' => $director,
                'classification' => $classification,
                'cast' => $this->getTvCast($tmdbId),
                'tmdb_id' => (string)$tmdbId,
                'imdb_id' => $externalIds['imdb_id'] ?? null,
                'tvdb_id' => $externalIds['tvdb_id'] ?? null,
            ];
        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error getting TV details: ' . $e->getMessage());
            throw new \Exception('Failed to get TV details: ' . $e->getMessage());
        }
    }

    /**
     * Get TV content rating (certification)
     */
    private function getTvClassification($tmdbId)
    {
        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
            ];
            $response = $this->client->get("tv/{$tmdbId}/content_ratings?" . http_build_query($params));
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $data = json_decode($response->getBody(), true);
            if (!isset($data['results']) || empty($data['results'])) {
                return null;
            }
            $preferred = ['US', 'GB', 'CA', 'AU', 'NZ'];
            $ratings = [];
            foreach ($data['results'] as $entry) {
                $cc = $entry['iso_3166_1'] ?? null;
                $rat = trim($entry['rating'] ?? '');
                if ($cc && $rat !== '') {
                    $ratings[$cc] = $rat;
                }
            }
            foreach ($preferred as $cc) {
                if (!empty($ratings[$cc])) return $ratings[$cc];
            }
            // fallback any
            foreach ($ratings as $rat) {
                if ($rat) return $rat;
            }
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get TV classification: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Get movie director from credits
     */
    private function getDirector($tmdbId)
    {
        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
                'language' => 'en-US'
            ];

            $response = $this->client->get("movie/{$tmdbId}/credits?" . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $creditsData = json_decode($response->getBody(), true);
            
            if (isset($creditsData['crew'])) {
                foreach ($creditsData['crew'] as $member) {
                    if ($member['job'] === 'Director') {
                        return $member['name'];
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get director info: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get movie cast from credits
     */
    private function getCast($tmdbId, $limit = 10)
    {
        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
                'language' => 'en-US'
            ];

            $response = $this->client->get("movie/{$tmdbId}/credits?" . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $creditsData = json_decode($response->getBody(), true);
            
            if (isset($creditsData['cast']) && !empty($creditsData['cast'])) {
                $castList = [];
                $count = 0;
                foreach ($creditsData['cast'] as $actor) {
                    if ($count >= $limit) break;
                    $actorName = $actor['name'] ?? '';
                    $character = $actor['character'] ?? '';
                    if ($actorName) {
                        if ($character) {
                            $castList[] = $actorName . ' as ' . $character;
                        } else {
                            $castList[] = $actorName;
                        }
                        $count++;
                    }
                }
                return !empty($castList) ? implode("\n", $castList) : null;
            }
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get cast info: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get TV show cast from credits
     */
    private function getTvCast($tmdbId, $limit = 10)
    {
        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
                'language' => 'en-US'
            ];

            $response = $this->client->get("tv/{$tmdbId}/credits?" . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $creditsData = json_decode($response->getBody(), true);
            
            if (isset($creditsData['cast']) && !empty($creditsData['cast'])) {
                $castList = [];
                $count = 0;
                foreach ($creditsData['cast'] as $actor) {
                    if ($count >= $limit) break;
                    $actorName = $actor['name'] ?? '';
                    $character = $actor['character'] ?? '';
                    if ($actorName) {
                        if ($character) {
                            $castList[] = $actorName . ' as ' . $character;
                        } else {
                            $castList[] = $actorName;
                        }
                        $count++;
                    }
                }
                return !empty($castList) ? implode("\n", $castList) : null;
            }
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get TV cast info: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get external IDs (IMDB, etc.) for a movie or TV show
     * 
     * @param int $tmdbId The TMDB ID
     * @param string $type 'movie' or 'tv'
     * @return array Array with 'imdb_id' and other external IDs
     */
    private function getExternalIds($tmdbId, $type = 'IMDB')
    {
        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
            ];

            $endpoint = ($type === 'tv') ? "tv/{$tmdbId}/external_ids" : "movie/{$tmdbId}/external_ids";
            $response = $this->client->get($endpoint . '?' . http_build_query($params));
            
            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $data = json_decode($response->getBody(), true);
            
            return [
                'imdb_id' => $data['imdb_id'] ?? null,
                'tvdb_id' => $data['tvdb_id'] ?? null,
                'facebook_id' => $data['facebook_id'] ?? null,
                'instagram_id' => $data['instagram_id'] ?? null,
                'twitter_id' => $data['twitter_id'] ?? null,
            ];
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get external IDs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get MPAA/TV classification (certification) from TMDB release dates
     */
    private function getClassification($tmdbId)
    {
        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
            ];

            $response = $this->client->get("movie/{$tmdbId}/release_dates?" . http_build_query($params));
            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode($response->getBody(), true);
            if (!isset($data['results']) || empty($data['results'])) {
                return null;
            }

            // Preferred country order for certifications
            $preferred = ['US', 'GB', 'CA', 'AU', 'NZ'];

            $byCountry = [];
            foreach ($data['results'] as $entry) {
                $country = $entry['iso_3166_1'] ?? null;
                if (!$country || empty($entry['release_dates'])) continue;
                foreach ($entry['release_dates'] as $rd) {
                    $cert = trim($rd['certification'] ?? '');
                    if ($cert !== '') {
                        $byCountry[$country] = $byCountry[$country] ?? [];
                        $byCountry[$country][] = $cert;
                    }
                }
            }

            // Return the first certification from preferred countries
            foreach ($preferred as $cc) {
                if (!empty($byCountry[$cc])) {
                    return $byCountry[$cc][0];
                }
            }

            // Fallback: any country certification
            foreach ($byCountry as $certs) {
                if (!empty($certs)) return $certs[0];
            }
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get classification: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Format genres array to string
     */
    private function formatGenres($genres)
    {
        if (empty($genres)) {
            return null;
        }

        $genreNames = array_map(function($genre) {
            return $genre['name'];
        }, $genres);

        return implode(', ', $genreNames);
    }

    /**
     * Format production countries to string
     */
    private function formatCountries($countries)
    {
        if (empty($countries)) {
            return null;
        }

        $countryNames = array_map(function($country) {
            return $country['name'];
        }, $countries);

        return implode(', ', $countryNames);
    }

    /**
     * Format production companies to string
     */
    private function formatStudios($companies)
    {
        if (empty($companies)) {
            return null;
        }

        // Get the first production company
        return $companies[0]['name'] ?? null;
    }

    /**
     * Download poster image
     */
    public function downloadPoster($posterUrl, $movieId)
    {
        if (empty($posterUrl)) {
            return null;
        }

        try {
            log_message('info', 'TMDB API: Downloading poster from: ' . $posterUrl);

            // Use cURL directly to ensure redirects are followed (image CDN may 30x)
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $posterUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'MediaOrganizer/1.0 (+https://github.com/drdelaney/MediaOrganizer)'
            ]);

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($imageData === false || $httpCode < 200 || $httpCode >= 300) {
                throw new \Exception('Failed to download poster, status: ' . ($httpCode ?: 'N/A') . ' error: ' . $curlErr);
            }
            
            log_message('info', 'TMDB API: Poster downloaded successfully, size: ' . strlen($imageData) . ' bytes');

            return $imageData;

        } catch (\Exception $e) {
            log_message('error', 'TMDB API: Poster download error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a movie/TV entry by external ID (IMDb ID like tt1234567 or TVDB numeric ID)
     * $type: 'movie' or 'tv' preferred media type when both are possible
     */
    public function findByExternalId($externalId, $type = 'IMDB')
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }

        $externalId = trim((string)$externalId);
        if ($externalId === '') {
            return null;
        }

        // Determine external source
        $source = null;
        if (preg_match('/^tt\d+$/i', $externalId)) {
            $source = 'imdb_id';
        } elseif (ctype_digit($externalId)) {
            $source = 'tvdb_id';
        }
        if (!$source) {
            throw new \InvalidArgumentException('Unsupported external ID format');
        }

        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
                'external_source' => $source,
            ];
            $response = $this->client->get('find/' . urlencode($externalId) . '?' . http_build_query($params));
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }
            $data = json_decode($response->getBody(), true);

            $movieResults = $data['movie_results'] ?? [];
            $tvResults = $data['tv_results'] ?? [];

            // Prefer based on requested type
            if ($type === 'tv' && !empty($tvResults)) {
                $tmdbId = $tvResults[0]['id'];
                return $this->getTvDetails($tmdbId);
            }
            if ($type === 'IMDB' && !empty($movieResults)) {
                $tmdbId = $movieResults[0]['id'];
                return $this->getMediaDetails($tmdbId);
            }
            // Fallback to whichever exists
            if (!empty($movieResults)) {
                return $this->getMediaDetails($movieResults[0]['id']);
            }
            if (!empty($tvResults)) {
                return $this->getTvDetails($tvResults[0]['id']);
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error (find by external): ' . $e->getMessage());
            throw new \Exception('Failed to resolve external ID: ' . $e->getMessage());
        }
    }

    /**
     * Resolve a UPC/EAN barcode to a product title, then map to TMDB movie/TV details when possible
     * This uses UPCItemDB public trial endpoint (rate-limited). If TMDB is available, we search there.
     * Returns normalized data like searchMovie/searchTv or minimal title-only data when TMDB unavailable.
     */
    public function findByBarcode(string $barcode, string $type = 'IMDB')
    {
        $barcode = preg_replace('/[^0-9]/', '', (string)$barcode);
        if ($barcode === '') {
            return null;
        }
        try {
            // 1) Query UPCItemDB trial API
            $url = 'https://api.upcitemdb.com/prod/trial/lookup?upc=' . urlencode($barcode);
            $resp = $this->client->get($url, [ 'headers' => [ 'Accept' => 'application/json' ]]);
            if ($resp->getStatusCode() !== 200) {
                log_message('warning', 'UPC lookup failed with status ' . $resp->getStatusCode());
            }
            $body = json_decode($resp->getBody() ?? '{}', true);
            $title = null;
            if (isset($body['items']) && is_array($body['items']) && count($body['items']) > 0) {
                // Prefer first item with title
                foreach ($body['items'] as $item) {
                    $t = trim((string)($item['title'] ?? ''));
                    if ($t !== '') { $title = $t; break; }
                }
            }
            if (!$title) {
                // Try description field as fallback
                if (!empty($body['items'][0]['description'])) {
                    $title = trim((string)$body['items'][0]['description']);
                }
            }
            if (!$title) {
                return null; // Could not resolve barcode to a title
            }

            // Clean title (remove typical media suffixes e.g., (Blu-ray), [DVD], etc.)
            $clean = preg_replace('/\s*[\[(].*?[)\]]\s*/', ' ', $title);
            $clean = preg_replace('/\s{2,}/', ' ', $clean);
            $clean = trim($clean);

            // Extract possible year
            $year = null;
            if (preg_match('/\b(19\d{2}|20\d{2})\b/', $clean, $m)) {
                $year = (int)$m[1];
            }

            // 2) If TMDB available, search there to map to movie/TV
            if ($this->isApiAvailable()) {
                if ($type === 'tv') {
                    return $this->searchTv($clean, $year);
                }
                return $this->searchMedia($clean, $year);
            }

            // 3) Fallback minimal dataset
            return [
                'title' => $clean,
                'o_title' => null,
                'year' => $year,
                'runtime' => null,
                'genre' => null,
                'country' => null,
                'studio' => null,
                'plot' => null,
                'rating' => null,
                'site' => null,
                'o_site' => null,
                'poster_url' => null,
                'director' => null,
                'classification' => null,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Barcode lookup/mapping failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get multiple poster images for a movie/TV show from TMDB
     * Returns array of poster URLs (up to $limit)
     *
     * @param int $tmdbId The TMDB ID
     * @param string $type 'movie' or 'tv'
     * @param int $limit Maximum number of posters to return
     * @return array Array of poster URLs
     */
    public function getPosters($tmdbId, $type = 'IMDB', $limit = 5)
    {
        if (!$this->isApiAvailable()) {
            throw new \Exception('TMDB API key not configured');
        }

        try {
            $params = [
                'api_key' => $this->tmdbApiKey,
                'include_image_language' => 'en,null'
            ];

            $endpoint = ($type === 'tv') ? "tv/{$tmdbId}/images" : "movie/{$tmdbId}/images";
            $response = $this->client->get($endpoint . '?' . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);

            if (empty($data['posters'])) {
                return [];
            }

            $posters = [];
            $count = 0;

            // Sort by vote average (descending) to get the most popular posters first
            usort($data['posters'], function($a, $b) {
                return ($b['vote_average'] ?? 0) <=> ($a['vote_average'] ?? 0);
            });

            foreach ($data['posters'] as $poster) {
                if ($count >= $limit) break;
                if (!empty($poster['file_path'])) {
                    $posters[] = [
                        'url' => 'https://image.tmdb.org/t/p/w500' . $poster['file_path'],
                        'thumbnail' => 'https://image.tmdb.org/t/p/w185' . $poster['file_path'],
                        'width' => $poster['width'] ?? null,
                        'height' => $poster['height'] ?? null,
                        'vote_average' => $poster['vote_average'] ?? 0
                    ];
                    $count++;
                }
            }

            log_message('info', 'TMDB API: Retrieved ' . count($posters) . ' posters for ' . $type . ' ID: ' . $tmdbId);
            return $posters;

        } catch (\Exception $e) {
            log_message('error', 'TMDB API Error getting posters: ' . $e->getMessage());
            throw new \Exception('Failed to get poster images: ' . $e->getMessage());
        }
    }
}
