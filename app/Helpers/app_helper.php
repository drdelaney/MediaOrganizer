<?php

/**
 * Get the application name from environment variable
 * 
 * @return string The application name
 */
if (!function_exists('app_name')) {
    function app_name(): string
    {
        return env('app.name', 'Media Organizer');
    }
}

/**
 * Add TMDB and/or IMDB IDs to notes field using tag format
 * Tags: <!tmdb>xxxx and <!imdb>xxxx
 * 
 * @param string|null $existingNotes The current notes content
 * @param string|null $tmdbId The TMDB ID to store
 * @param string|null $imdbId The IMDB ID to store
 * @return string The updated notes with IDs embedded
 */
if (!function_exists('add_external_ids_to_notes')) {
    function add_external_ids_to_notes(?string $existingNotes, ?string $tmdbId, ?string $imdbId): string
    {
        $notes = $existingNotes ?? '';
        
        // Remove any existing TMDB/IMDB tags to avoid duplicates (match only tag and value on same line)
        $notes = preg_replace('/<!tmdb>[^\n]*\n?/', '', $notes);
        $notes = preg_replace('/<!imdb>[^\n]*\n?/', '', $notes);
        
        // Trim the remaining notes
        $notes = trim($notes);
        
        // Add new tags at the beginning, each followed by a newline
        $tags = '';
        if ($tmdbId !== null && $tmdbId !== '') {
            $tags .= '<!tmdb>' . $tmdbId . "\n";
        }
        if ($imdbId !== null && $imdbId !== '') {
            $tags .= '<!imdb>' . $imdbId . "\n";
        }
        
        // If we have tags and existing notes, add ONE newline separator before existing notes
        if ($tags !== '' && $notes !== '') {
            $result = $tags . $notes;
        } else {
            $result = $tags . $notes;
        }
        
        return $result;
    }
}

/**
 * Extract TMDB ID from notes field
 * 
 * @param string|null $notes The notes content
 * @return string|null The TMDB ID if found, null otherwise
 */
if (!function_exists('get_tmdb_id_from_notes')) {
    function get_tmdb_id_from_notes(?string $notes): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }
        
        if (preg_match('/<!tmdb>(\S+)/', $notes, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}

/**
 * Extract IMDB ID from notes field
 * 
 * @param string|null $notes The notes content
 * @return string|null The IMDB ID if found, null otherwise
 */
if (!function_exists('get_imdb_id_from_notes')) {
    function get_imdb_id_from_notes(?string $notes): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }
        
        if (preg_match('/<!imdb>(\S+)/', $notes, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}

/**
 * Remove TMDB/IMDB tags from notes for display purposes
 * 
 * @param string|null $notes The notes content with tags
 * @return string The notes without TMDB/IMDB tags
 */
if (!function_exists('strip_external_ids_from_notes')) {
    function strip_external_ids_from_notes(?string $notes): string
    {
        if ($notes === null || $notes === '') {
            return '';
        }
        
        // Remove TMDB and IMDB tags
        $clean = preg_replace('/<!tmdb>\S+\s*/', '', $notes);
        $clean = preg_replace('/<!imdb>\S+\s*/', '', $clean);
        
        return trim($clean);
    }
}

/**
 * Add multiple medium IDs to notes field using tag format
 * Tag format: <!medium_id>xxx,yyy,zzz
 * 
 * @param string|null $existingNotes The current notes content
 * @param array $mediumIds Array of medium_id integers
 * @return string The updated notes with medium IDs embedded
 */
if (!function_exists('add_medium_ids_to_notes')) {
    function add_medium_ids_to_notes(?string $existingNotes, array $mediumIds): string
    {
        $notes = $existingNotes ?? '';
        
        // Remove any existing medium_id tag to avoid duplicates (match only tag and value on same line)
        $notes = preg_replace('/<!medium_id>[^\n]*\n?/', '', $notes);
        
        // Trim the remaining notes
        $notes = trim($notes);
        
        // Filter out empty/null values and ensure integers
        $mediumIds = array_filter($mediumIds, function($id) {
            return $id !== null && $id !== '' && $id !== 0 && $id !== '0';
        });
        
        // Convert to integers and remove duplicates
        $mediumIds = array_unique(array_map('intval', $mediumIds));
        
        // Sort for consistency (highest first as per requirement)
        rsort($mediumIds);
        
        // Add new tag at the beginning if we have IDs, followed by a newline
        $tags = '';
        if (!empty($mediumIds)) {
            $tags .= '<!medium_id>' . implode(',', $mediumIds) . "\n";
        }
        
        // If we have tags and existing notes, add ONE newline separator before existing notes
        if ($tags !== '' && $notes !== '') {
            $result = $tags . $notes;
        } else {
            $result = $tags . $notes;
        }
        
        return $result;
    }
}

/**
 * Extract medium IDs from notes field
 * 
 * @param string|null $notes The notes content
 * @return array Array of medium_id integers (empty if none found)
 */
if (!function_exists('get_medium_ids_from_notes')) {
    function get_medium_ids_from_notes(?string $notes): array
    {
        if ($notes === null || $notes === '') {
            return [];
        }
        
        if (preg_match('/<!medium_id>([^<\s]+)/', $notes, $matches)) {
            // Split by comma and convert to integers
            $ids = explode(',', $matches[1]);
            $ids = array_map('intval', $ids);
            // Filter out zeros
            $ids = array_filter($ids, function($id) {
                return $id > 0;
            });
            return array_values($ids);
        }
        
        return [];
    }
}

/**
 * Get the highest medium ID from notes (for storing in medium_id field)
 * 
 * @param string|null $notes The notes content
 * @return int|null The highest medium_id if found, null otherwise
 */
if (!function_exists('get_highest_medium_id_from_notes')) {
    function get_highest_medium_id_from_notes(?string $notes): ?int
    {
        $mediumIds = get_medium_ids_from_notes($notes);
        
        if (empty($mediumIds)) {
            return null;
        }
        
        return max($mediumIds);
    }
}
