<?php

namespace App\Libraries;

/**
 * Service to interact with IGDB API for video game metadata
 * Requires Twitch Developer credentials: Client ID and Client Secret
 */
class GameApiService
{
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $userAgent;
    private $client;

    public function __construct()
    {
        $configModel = new \App\Models\ConfigurationModel();
        
        $this->clientId = $configModel->getParam('IGDB_CLIENT_ID');
        $this->clientSecret = $configModel->getParam('IGDB_CLIENT_SECRET');
        $this->userAgent = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');
        
        $this->client = \Config\Services::curlrequest([
            'baseURI' => 'https://api.igdb.com/v4/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $this->userAgent,
            ],
        ]);
    }

    /**
     * Check if API is available and configured
     */
    public function isApiAvailable(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Obtain access token from Twitch
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $tokenClient = \Config\Services::curlrequest([
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
            ]);
            $response = $tokenClient->post('https://id.twitch.tv/oauth2/token', [
                'query' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                $this->accessToken = $data['access_token'] ?? null;
                return $this->accessToken;
            }
        } catch (\Exception $e) {
            log_message('error', 'IGDB Auth Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Search for a game by title
     */
    public function searchMedia(string $title, ?int $year = null): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $body = 'search "' . addslashes($title) . '"; fields id,name,first_release_date,summary,genres.name,involved_companies.company.name,cover.url,platforms.name,url; limit 1;';

        try {
            $response = $this->client->post('games', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => 'Bearer ' . $token,
                ],
                'body' => $body
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode($response->getBody(), true);
            if (!empty($data[0])) {
                return $this->formatGame($data[0]);
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'IGDB Search Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get game details by IGDB ID
     */
    public function getMediaDetails(int $id): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $body = "fields id,name,first_release_date,summary,genres.name,involved_companies.company.name,cover.url,platforms.name,url; where id = {$id};";

        try {
            $response = $this->client->post('games', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => 'Bearer ' . $token,
                ],
                'body' => $body
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode($response->getBody(), true);
            if (!empty($data[0])) {
                return $this->formatGame($data[0]);
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'IGDB Details Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search multiple games
     */
    public function searchMultiple(string $title, ?int $year = null, int $limit = 20, int $page = 1): array
    {
        $token = $this->getAccessToken();
        if (!$token) return ['results' => []];

        // Parse tags from title if present
        $originalTitle = $title;
        $platformsFilter = null;
        if (preg_match('/system:\s*"([^"]+)"/i', $title, $matches)) {
            $platformsFilter = $matches[1];
            $title = str_replace($matches[0], '', $title);
        } elseif (preg_match('/system:\s*(\S+)/i', $title, $matches)) {
            $platformsFilter = $matches[1];
            $title = str_replace($matches[0], '', $title);
        }

        if (preg_match('/year:\s*"?(\d{4})"?/i', $title, $matches)) {
            $year = (int)$matches[1];
            $title = str_replace($matches[0], '', $title);
        }

        $title = trim($title);
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        if ($year) {
            $startOfYear = mktime(0, 0, 0, 1, 1, $year);
            $endOfYear = mktime(23, 59, 59, 12, 31, $year);
            $whereClause .= ' & first_release_date >= ' . $startOfYear . ' & first_release_date <= ' . $endOfYear;
        }
        if ($platformsFilter) {
            $whereClause .= ' & platforms.name ~ *"' . addslashes($platformsFilter) . '"*';
        }

        $body = 'search "' . addslashes($title) . '"; fields id,name,first_release_date,cover.url,summary,platforms.name; limit ' . $limit . '; offset ' . $offset . ';';
        if ($whereClause) {
            // If we have a where clause, we might need to use 'where' instead of just relying on 'search' 
            // but IGDB allows combining search and where.
            if ($title) {
                $body .= ' where name ~ *"' . addslashes($title) . '"*' . $whereClause . ';';
            } else {
                $body .= ' where ' . ltrim($whereClause, ' &') . ';';
            }
        }

        try {
            $response = $this->client->post('games', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => 'Bearer ' . $token,
                ],
                'body' => $body
            ]);

            if ($response->getStatusCode() !== 200) {
                return ['results' => []];
            }

            $data = json_decode($response->getBody(), true);
            $results = [];

            foreach ($data as $game) {
                $platforms = [];
                if (!empty($game['platforms'])) {
                    foreach ($game['platforms'] as $p) $platforms[] = $p['name'];
                }

                $results[] = [
                    'igdb_id'     => $game['id'],
                    'title'       => $game['name'] ?? 'Unknown',
                    'year'        => isset($game['first_release_date']) ? date('Y', $game['first_release_date']) : null,
                    'poster_url'  => isset($game['cover']['url']) ? 'https:' . str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null,
                    'overview'    => $game['summary'] ?? null,
                    'platforms'   => !empty($platforms) ? implode(', ', $platforms) : null,
                    'type'        => 'IGDB'
                ];
            }

            // Estimate total pages/results since IGDB search doesn't return them easily.
            // If we got exactly $limit results, there's likely more.
            $hasMore = count($results) === $limit;
            $totalResults = $offset + count($results) + ($hasMore ? 1 : 0);
            $totalPages = $hasMore ? $page + 1 : $page;

            return [
                'results'       => $results,
                'total_results' => $totalResults,
                'page'          => $page,
                'total_pages'   => $totalPages,
                'is_estimated'  => $hasMore // Flag to indicate that totals are estimated
            ];
        } catch (\Exception $e) {
            log_message('error', 'IGDB Multiple Search Error: ' . $e->getMessage());
            return ['results' => []];
        }
    }

    private function formatGame(array $game): array
    {
        $genres = [];
        if (!empty($game['genres'])) {
            foreach ($game['genres'] as $g) $genres[] = $g['name'];
        }

        $companies = [];
        if (!empty($game['involved_companies'])) {
            foreach ($game['involved_companies'] as $ic) {
                if (!empty($ic['company']['name'])) $companies[] = $ic['company']['name'];
            }
        }

        $platforms = [];
        if (!empty($game['platforms'])) {
            foreach ($game['platforms'] as $p) $platforms[] = $p['name'];
        }

        $plot = ($game['summary'] ?? '') . "\n\nPlatforms: " . implode(', ', $platforms);

        return [
            'title'      => $game['name'] ?? null,
            'o_title'    => $game['name'] ?? null,
            'year'       => isset($game['first_release_date']) ? date('Y', $game['first_release_date']) : null,
            'studio'     => !empty($companies) ? $companies[0] : null,
            'genre'      => !empty($genres) ? implode(', ', $genres) : 'Game',
            'plot'       => trim($plot),
            'site'       => $game['url'] ?? null,
            'igdb_id'    => (string)$game['id'],
            'poster_url' => isset($game['cover']['url']) ? 'https:' . str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null,
        ];
    }
}
