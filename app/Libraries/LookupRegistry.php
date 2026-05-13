<?php

namespace App\Libraries;

/**
 * Service to manage and provide media lookup capabilities
 */
class LookupRegistry
{
    /**
     * Get the list of enabled lookup types
     * 
     * @return array List of enabled lookup types (e.g. ['IMDB', 'TVDB', 'IGDB', 'MusicBrainz'])
     */
    public static function getEnabledLookups(): array
    {
        $configModel = new \App\Models\ConfigurationModel();
        $enabled = $configModel->getParam('ENABLED_LOOKUPS');
        
        if (empty($enabled)) {
            $enabled = 'IMDB,TVDB';
        }
        
        $lookups = array_map('trim', explode(',', $enabled));
        
        // Backward compatibility mapping
        $mapping = [
            'movie' => 'IMDB',
            'tv'    => 'TVDB',
            'game'  => 'IGDB',
            'music' => 'MusicBrainz'
        ];
        
        $mappedLookups = [];
        foreach ($lookups as $lookup) {
            $lookupLower = strtolower($lookup);
            if (isset($mapping[$lookupLower])) {
                $mappedLookups[] = $mapping[$lookupLower];
            } else {
                $mappedLookups[] = $lookup;
            }
        }
        
        $mappedLookups = array_unique($mappedLookups);
        
        // Filter based on populated API keys/settings
        $finalLookups = [];
        foreach ($mappedLookups as $lookup) {
            switch ($lookup) {
                case 'TMDB':
                    if (!empty($configModel->getParam('TMDB_API_KEY'))) {
                        $finalLookups[] = 'TMDB';
                    }
                    break;
                case 'IGDB':
                    if (!empty($configModel->getParam('IGDB_CLIENT_ID')) && !empty($configModel->getParam('IGDB_CLIENT_SECRET'))) {
                        $finalLookups[] = 'IGDB';
                    }
                    break;
                case 'MusicBrainz':
                    if (!empty($configModel->getParam('MUSICBRAINZ_EMAIL'))) {
                        $finalLookups[] = 'MusicBrainz';
                    }
                    break;
                case 'IMDB':
                case 'TVDB':
                    // These don't have explicit keys in the prompt, but let's keep them if they are in the list
                    $finalLookups[] = $lookup;
                    break;
                default:
                    // Any other supported types?
                    $finalLookups[] = $lookup;
                    break;
            }
        }
        
        // Basic validation of supported types
        $supported = ['IMDB', 'TVDB', 'TMDB', 'IGDB', 'MusicBrainz'];
        return array_values(array_intersect($finalLookups, $supported));
    }
    
    /**
     * Check if a specific lookup type is enabled
     * 
     * @param string $type The lookup type to check
     * @return bool
     */
    public static function isEnabled(string $type): bool
    {
        return in_array($type, self::getEnabledLookups());
    }

    /**
     * Get details for lookup types for UI display
     * 
     * @return array
     */
    public static function getLookupTypeOptions(): array
    {
        $enabled = self::getEnabledLookups();
        $options = [];
        
        if (in_array('IMDB', $enabled)) {
            $options['IMDB'] = [
                'label' => 'IMDB (Movie)',
                'icon' => 'bi-film',
                'id_label' => 'IMDb ID',
                'id_placeholder' => 'tt0133093',
                'id_field' => 'imdb_id'
            ];
        }
        
        if (in_array('TVDB', $enabled)) {
            $options['TVDB'] = [
                'label' => 'TVDB (TV)',
                'icon' => 'bi-tv',
                'id_label' => 'TVDB ID',
                'id_placeholder' => '81189',
                'id_field' => 'tvdb_id'
            ];
        }
        
        if (in_array('TMDB', $enabled)) {
            $options['TMDB'] = [
                'label' => 'TMDB (Movie)',
                'icon' => 'bi-film',
                'id_label' => 'TMDB ID',
                'id_placeholder' => '27205',
                'id_field' => 'tmdb_id'
            ];
        }
        
        if (in_array('IGDB', $enabled)) {
            $options['IGDB'] = [
                'label' => 'IGDB (Games)',
                'icon' => 'bi-controller',
                'id_label' => 'IGDB ID',
                'id_placeholder' => '12345',
                'id_field' => 'igdb_id'
            ];
        }
        
        if (in_array('MusicBrainz', $enabled)) {
            $options['MusicBrainz'] = [
                'label' => 'MusicBrainz (Music)',
                'icon' => 'bi-music-note-beamed',
                'id_label' => 'MusicBrainz ID',
                'id_placeholder' => 'MBID (UUID)',
                'id_field' => 'mbid'
            ];
        }
        
        return $options;
    }
}
