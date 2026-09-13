<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;

class ImdbApiService
{
    use BarcodeLookupTrait;
    private $imdbApiKey;
    private $userAgent;
    private $client;

    public function __construct()
    {
        $configModel = new \App\Models\ConfigurationModel();
        
        $this->imdbApiKey = $configModel->getParam('IMDB_API_KEY');
        $this->userAgent = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');

        $this->client = \Config\Services::curlrequest([
            'baseURI' => 'http://www.omdbapi.com/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/json',
            ],
        ], null, null, false);
    }

    /**
     * Check if API is available and configured
     */
    public function isApiAvailable()
    {
        return !empty($this->imdbApiKey);
    }

    /**
     * Search for a movie by title and optional year
     */
    public function searchMedia($title, $year = null)
    {
        if (!$this->isApiAvailable()) {
            return null;
        }

        $params = [
            'apikey' => $this->imdbApiKey,
            's' => $title,
            'type' => 'movie'
        ];

        if ($year) {
            $params['y'] = $year;
        }

        try {
            log_message('info', 'IMDB API (OMDb): Searching for movie: ' . $title);

            $response = $this->client->get('?' . http_build_query($params));

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);

            if (isset($data['Search']) && !empty($data['Search'])) {
                // Get detailed info for the first result
                $imdbId = $data['Search'][0]['imdbID'];
                return $this->getMediaDetails($imdbId);
            }

            return null;

        } catch (\Exception $e) {
            log_message('error', 'IMDB API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search for movies by title and return multiple results
     */
    public function searchMovieMultiple($title, $year = null, $limit = 20, $page = 1)
    {
        if (!$this->isApiAvailable()) {
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => $page];
        }

        $params = [
            'apikey' => $this->imdbApiKey,
            's' => $title,
            'type' => 'movie',
            'page' => $page
        ];

        if ($year) {
            $params['y'] = $year;
        }

        try {
            $response = $this->client->get('?' . http_build_query($params));
            $data = json_decode($response->getBody(), true);

            if (!isset($data['Search'])) {
                return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => $page];
            }

            $results = [];
            foreach ($data['Search'] as $item) {
                $results[] = [
                    'imdb_id' => $item['imdbID'],
                    'title' => $item['Title'],
                    'year' => $item['Year'],
                    'poster_url' => ($item['Poster'] !== 'N/A') ? $item['Poster'] : null,
                    'type' => 'IMDB'
                ];
            }

            return [
                'results' => $results,
                'total_results' => (int)($data['totalResults'] ?? count($results)),
                'total_pages' => ceil(($data['totalResults'] ?? count($results)) / 10),
                'page' => $page
            ];
        } catch (\Exception $e) {
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => $page];
        }
    }

    /**
     * Get detailed movie information by IMDB ID
     */
    public function getMediaDetails($imdbId)
    {
        if (!$this->isApiAvailable()) {
            return null;
        }

        try {
            $params = [
                'apikey' => $this->imdbApiKey,
                'i' => $imdbId,
                'plot' => 'full'
            ];

            $response = $this->client->get('?' . http_build_query($params));
            $movieData = json_decode($response->getBody(), true);

            if (!isset($movieData['Title'])) {
                return null;
            }

            return [
                'title' => $movieData['Title'] ?? null,
                'o_title' => null,
                'year' => isset($movieData['Year']) ? substr($movieData['Year'], 0, 4) : null,
                'runtime' => isset($movieData['Runtime']) ? (int)preg_replace('/[^0-9]/', '', $movieData['Runtime']) : null,
                'genre' => $movieData['Genre'] ?? null,
                'country' => $movieData['Country'] ?? null,
                'studio' => $movieData['Production'] ?? null,
                'plot' => $movieData['Plot'] ?? null,
                'rating' => isset($movieData['imdbRating']) ? round(floatval($movieData['imdbRating']) / 2) : null,
                'site' => "https://www.imdb.com/title/{$imdbId}/",
                'poster_url' => ($movieData['Poster'] !== 'N/A') ? $movieData['Poster'] : null,
                'director' => $movieData['Director'] ?? null,
                'classification' => $movieData['Rated'] ?? null,
                'cast' => $movieData['Actors'] ?? null,
                'imdb_id' => $imdbId,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function findByExternalId($externalId)
    {
        return $this->getMediaDetails($externalId);
    }

    public function downloadPoster($posterUrl, $movieId = null)
    {
        if (empty($posterUrl)) return null;
        log_message('debug', 'ImdbApiService: downloadPoster from ' . $posterUrl);

        helper('app');

        try {
            // If the URL is absolute (starts with http), it may be
            // attacker-controlled, so fetch via the SSRF-safe helper, which
            // validates the destination host (and every redirect hop)
            // resolves only to a public address.
            // Otherwise, it's relative to the trusted, configured baseURI (OMDB).
            if (strpos($posterUrl, 'http') === 0) {
                return fetch_remote_image($posterUrl, 'MediaOrganizer/1.0');
            }

            $response = $this->client->get($posterUrl);

            if ($response->getStatusCode() === 200) {
                return $response->getBody();
            }
            log_message('debug', 'ImdbApiService: downloadPoster status code: ' . $response->getStatusCode());
            return null;
        } catch (\Exception $e) {
            log_message('debug', 'ImdbApiService: downloadPoster exception: ' . $e->getMessage());
            return null;
        }
    }
    public function getPosters($imdbId, $type = 'IMDB', $limit = 10)
    {
        if (!$this->isApiAvailable()) {
            return [];
        }

        // OMDb only provides one poster per ID usually.
        // But we can try to return what we have in a consistent format.
        $details = $this->getMediaDetails($imdbId);
        if ($details && !empty($details['poster_url'])) {
            return [[
                'url' => $details['poster_url'],
                'thumbnail' => $details['poster_url'],
                'width' => null,
                'height' => null,
                'vote_average' => 0
            ]];
        }

        return [];
    }

    /**
     * Find details by barcode using UPCItemDB
     */
    public function findByBarcode(string $barcode)
    {
        $barcode = preg_replace('/[^0-9]/', '', (string)$barcode);
        if ($barcode === '') {
            return null;
        }

        try {
            $item = $this->lookupBarcode($barcode);
            if ($item && isset($item['error']) && $item['error'] === 'EXCEED_LIMIT') {
                return $item;
            }
            if (!$item || empty($item['title'])) {
                return null;
            }

            // Clean title
            $clean = preg_replace('/\s*[\[(].*?[)\]]\s*/', ' ', $item['title']);
            $clean = preg_replace('/\s{2,}/', ' ', $clean);
            $clean = trim($clean);

            // Extract year
            $year = null;
            if (preg_match('/\b(19\d{2}|20\d{2})\b/', $clean, $m)) {
                $year = (int)$m[1];
            }

            // Search OMDb
            if ($this->isApiAvailable()) {
                return $this->searchMedia($clean, $year);
            }

            // Fallback
            return [
                'title' => $clean,
                'year' => $year,
                'plot' => $item['description'] ?? null,
                'studio' => $item['brand'] ?? null,
                'poster_url' => !empty($item['images']) ? $item['images'][0] : null,
                'type' => 'IMDB'
            ];
        } catch (\Exception $e) {
            log_message('error', 'IMDB Barcode lookup failed: ' . $e->getMessage());
            return null;
        }
    }
}
