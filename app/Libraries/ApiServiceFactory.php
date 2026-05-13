<?php

namespace App\Libraries;

/**
 * Factory to create the appropriate API service based on lookup type
 */
class ApiServiceFactory
{
    /**
     * Create an API service for the given lookup type
     * 
     * @param string $type The lookup type (movie, tv, game, music)
     * @return object|null The API service instance
     */
    public static function create(string $type)
    {
        switch (strtoupper($type)) {
            case 'IMDB':
            case 'TMDB':
            case 'TVDB':
            case 'MOVIE':
            case 'TV':
                return new MovieApiService();
            case 'IGDB':
            case 'GAME':
                return new GameApiService();
            case 'MUSICBRAINZ':
            case 'MUSIC':
                return new MusicApiService();
            default:
                return null;
        }
    }

    /**
     * Determine the correct service based on available IDs
     * 
     * @param array $ids Array of possible IDs (imdb_id, tmdb_id, igdb_id, mbid, tvdb_id)
     * @return object|null
     */
    public static function createFromIds(array $ids)
    {
        if (!empty($ids['igdb_id'])) {
            return new GameApiService();
        }
        if (!empty($ids['mbid'])) {
            return new MusicApiService();
        }
        if (!empty($ids['tmdb_id']) || !empty($ids['imdb_id']) || !empty($ids['tvdb_id'])) {
            return new MovieApiService();
        }
        return null;
    }
}
