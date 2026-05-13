<?php

namespace App\Libraries;

/**
 * Service to interact with MusicBrainz API for music metadata
 */
class MusicApiService
{
    private $userAgent;
    private $client;

    public function __construct()
    {
        $configModel = new \App\Models\ConfigurationModel();
        $baseUA = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');
        $email = $configModel->getParam('MUSICBRAINZ_EMAIL');
        
        $this->userAgent = $email ? "{$baseUA} ( {$email} )" : $baseUA;
        
        $this->client = \Config\Services::curlrequest([
            'baseURI' => 'https://musicbrainz.org/ws/2/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/json',
            ],
        ]);
    }

    /**
     * MusicBrainz is always available as it doesn't require a key (just UA)
     */
    public function isApiAvailable(): bool
    {
        return !empty($this->userAgent);
    }

    /**
     * Search for a release (album) by title
     */
    public function searchMedia(string $title, ?int $year = null): ?array
    {
        $query = 'release:' . $title;
        if ($year) {
            $query .= ' AND date:' . $year;
        }

        try {
            $response = $this->client->get('release', [
                'query' => [
                    'query' => $query,
                    'fmt'   => 'json',
                    'limit' => 1,
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode($response->getBody(), true);
            if (!empty($data['releases'])) {
                return $this->getMediaDetails($data['releases'][0]['id']);
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'MusicBrainz API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed release information by MBID
     */
    public function getMediaDetails(string $mbid): ?array
    {
        try {
            $response = $this->client->get('release/' . $mbid, [
                'query' => [
                    'inc' => 'artists+labels+recordings+release-groups',
                    'fmt' => 'json',
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode($response->getBody(), true);
            
            $artist = $data['artist-credit'][0]['name'] ?? null;
            $year = !empty($data['date']) ? substr($data['date'], 0, 4) : null;
            
            // Format tracklist for plot/notes
            $tracks = [];
            if (!empty($data['media'][0]['tracks'])) {
                foreach ($data['media'][0]['tracks'] as $track) {
                    $tracks[] = ($track['position'] ?? '?') . '. ' . ($track['title'] ?? 'Unknown');
                }
            }
            $plot = !empty($tracks) ? "Tracklist:\n" . implode("\n", $tracks) : null;

            return [
                'title'          => $data['title'] ?? null,
                'o_title'        => $data['title'] ?? null,
                'year'           => $year,
                'director'       => $artist, // Map Artist to Director field
                'studio'         => $data['label-info'][0]['label']['name'] ?? null,
                'genre'          => $data['release-group']['primary-type'] ?? 'Music',
                'plot'           => $plot,
                'site'           => 'https://musicbrainz.org/release/' . $mbid,
                'mbid'           => $mbid,
                'poster_url'     => 'https://coverartarchive.org/release/' . $mbid . '/front',
            ];
        } catch (\Exception $e) {
            log_message('error', 'MusicBrainz API Details Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search for multiple releases
     */
    public function searchMultiple(string $title, ?int $year = null, int $limit = 20, int $page = 1): array
    {
        // Parse tags from title if present
        $artistFilter = null;
        if (preg_match('/artist:\s*"([^"]+)"/i', $title, $matches)) {
            $artistFilter = $matches[1];
            $title = str_replace($matches[0], '', $title);
        } elseif (preg_match('/artist:\s*(\S+)/i', $title, $matches)) {
            $artistFilter = $matches[1];
            $title = str_replace($matches[0], '', $title);
        }

        if (preg_match('/year:\s*"?(\d{4})"?/i', $title, $matches)) {
            $year = (int)$matches[1];
            $title = str_replace($matches[0], '', $title);
        }

        $title = trim($title);

        $queryParts = [];
        if ($title) {
            $queryParts[] = 'release:"' . addslashes($title) . '"';
        }
        if ($artistFilter) {
            $queryParts[] = 'artist:"' . addslashes($artistFilter) . '"';
        }
        if ($year) {
            $queryParts[] = 'date:' . $year;
        }

        $query = implode(' AND ', $queryParts);
        if (empty($query)) {
            return ['results' => []];
        }
        $offset = ($page - 1) * $limit;

        try {
            $response = $this->client->get('release', [
                'query' => [
                    'query'  => $query,
                    'fmt'    => 'json',
                    'limit'  => $limit,
                    'offset' => $offset
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return ['results' => []];
            }

            $data = json_decode($response->getBody(), true);
            $results = [];

            if (!empty($data['releases'])) {
                foreach ($data['releases'] as $release) {
                    $results[] = [
                        'mbid'           => $release['id'],
                        'title'          => $release['title'] ?? 'Unknown',
                        'artist'         => $release['artist-credit'][0]['name'] ?? null,
                        'year'           => !empty($release['date']) ? substr($release['date'], 0, 4) : null,
                        'poster_url'     => 'https://coverartarchive.org/release/' . $release['id'] . '/front',
                        'type'           => 'MusicBrainz'
                    ];
                }
            }

            $totalResults = $data['count'] ?? count($results);
            $totalPages = ceil($totalResults / $limit);

            return [
                'results'       => $results,
                'total_results' => $totalResults,
                'page'          => $page,
                'total_pages'   => $totalPages
            ];
        } catch (\Exception $e) {
            log_message('error', 'MusicBrainz API Multiple Search Error: ' . $e->getMessage());
            return ['results' => []];
        }
    }
    
    /**
     * Download cover art
     */
    public function downloadPoster(string $url): ?string
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => $this->userAgent
            ]);

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($imageData === false || $httpCode !== 200) {
                return null;
            }

            return $imageData;
        } catch (\Exception $e) {
            return null;
        }
    }
}
