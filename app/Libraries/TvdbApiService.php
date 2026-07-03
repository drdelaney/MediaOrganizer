<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;

class TvdbApiService
{
    private $tvdbApiKey;
    private $userAgent;
    private $client;
    private $token;

    public function __construct()
    {
        $configModel = new \App\Models\ConfigurationModel();
        
        $this->tvdbApiKey = $configModel->getParam('TVDB_API_KEY');
        $this->userAgent = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');

        $this->client = \Config\Services::curlrequest([
            'baseURI' => 'https://api4.thetvdb.com/v4/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/json',
            ],
        ]);
    }

    private function authenticate()
    {
        if ($this->token) return true;
        if (!$this->tvdbApiKey) return false;

        try {
            $response = $this->client->post('login', [
                'json' => ['apikey' => $this->tvdbApiKey]
            ]);
            $data = json_decode($response->getBody(), true);
            if (isset($data['data']['token'])) {
                $this->token = $data['data']['token'];
                return true;
            }
        } catch (\Exception $e) {
            log_message('error', 'TVDB Auth Error: ' . $e->getMessage());
        }
        return false;
    }

    private function getHeaders()
    {
        return [
            'User-Agent' => $this->userAgent,
            'Accept'     => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }

    public function isApiAvailable()
    {
        return !empty($this->tvdbApiKey);
    }

    public function searchMedia($title, $year = null)
    {
        $results = $this->searchTvMultiple($title, $year, 1);
        if (!empty($results['results'])) {
            return $this->getTvDetails($results['results'][0]['tvdb_id']);
        }
        return null;
    }

    public function searchTvMultiple($title, $year = null, $limit = 20, $page = 1)
    {
        if (!$this->isApiAvailable() || !$this->authenticate()) {
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => $page];
        }

        try {
            $params = [
                'query' => $title,
                'type' => 'series'
            ];
            if ($year) $params['year'] = $year;

            $response = $this->client->get('search?' . http_build_query($params), [
                'headers' => $this->getHeaders()
            ]);
            $data = json_decode($response->getBody(), true);

            $results = [];
            if (isset($data['data'])) {
                foreach ($data['data'] as $item) {
                    $results[] = [
                        'tvdb_id' => $item['tvdb_id'],
                        'title' => $item['name'],
                        'year' => $item['year'] ?? null,
                        'poster_url' => $item['image_url'] ?? null,
                        'overview' => $item['overview'] ?? null,
                        'type' => 'TVDB'
                    ];
                }
            }

            return [
                'results' => $results,
                'total_results' => count($results),
                'total_pages' => 1,
                'page' => $page
            ];
        } catch (\Exception $e) {
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => $page];
        }
    }

    public function getTvDetails($tvdbId)
    {
        if (!$this->isApiAvailable() || !$this->authenticate()) {
            return null;
        }

        try {
            $response = $this->client->get("series/{$tvdbId}/extended", [
                'headers' => $this->getHeaders()
            ]);
            $data = json_decode($response->getBody(), true);
            $tvData = $data['data'] ?? null;

            if (!$tvData) return null;

            return [
                'title' => $tvData['name'] ?? null,
                'o_title' => $tvData['originalName'] ?? null,
                'year' => isset($tvData['year']) ? $tvData['year'] : null,
                'runtime' => null, // TVDB extended has many fields, keeping it simple
                'genre' => isset($tvData['genres']) ? implode(', ', array_column($tvData['genres'], 'name')) : null,
                'plot' => $tvData['overview'] ?? null,
                'site' => "https://thetvdb.com/dereferrer/series/{$tvdbId}",
                'poster_url' => $tvData['image'] ?? null,
                'tvdb_id' => $tvdbId,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function findByExternalId($externalId)
    {
        return $this->getTvDetails($externalId);
    }

    public function downloadPoster($posterUrl, $movieId = null)
    {
        if (empty($posterUrl)) return null;
        try {
            // Check if we need to authenticate for the image URL (some TVDB images require it, some don't)
            // But usually image_url is a direct link or requires the same token if it's protected.
            // Let's try direct first.
            $response = $this->client->get($posterUrl);
            if ($response->getStatusCode() === 200) {
                return $response->getBody();
            }
        } catch (\Exception $e) {
            log_message('error', 'TVDB Poster download failed: ' . $e->getMessage());
        }
        return null;
    }
    public function getPosters($tvdbId, $type = 'TVDB', $limit = 10)
    {
        if (!$this->isApiAvailable() || !$this->authenticate()) {
            return [];
        }

        try {
            $response = $this->client->get("series/{$tvdbId}/extended", [
                'headers' => $this->getHeaders()
            ]);
            $data = json_decode($response->getBody(), true);
            $artwork = $data['data']['artworks'] ?? [];

            $posters = [];
            $count = 0;

            foreach ($artwork as $item) {
                if ($count >= $limit) break;
                
                // Type 2 is usually Poster in TVDB v4
                if (isset($item['type']) && $item['type'] == 2 && !empty($item['image'])) {
                    $posters[] = [
                        'url' => $item['image'],
                        'thumbnail' => $item['thumbnail'] ?? $item['image'],
                        'width' => $item['width'] ?? null,
                        'height' => $item['height'] ?? null,
                        'vote_average' => ($item['score'] ?? 0) / 10 // score is 0-100 usually
                    ];
                    $count++;
                }
            }

            // Fallback to main image if no posters found in artworks
            if (empty($posters)) {
                $details = $this->getTvDetails($tvdbId);
                if ($details && !empty($details['poster_url'])) {
                    $posters[] = [
                        'url' => $details['poster_url'],
                        'thumbnail' => $details['poster_url'],
                        'width' => null,
                        'height' => null,
                        'vote_average' => 0
                    ];
                }
            }

            return $posters;
        } catch (\Exception $e) {
            log_message('error', 'TVDB API Error getting posters: ' . $e->getMessage());
            return [];
        }
    }
}
